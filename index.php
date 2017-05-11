<?php
  //Sessionをスタートする
  session_start();

  //ログイン状態をチェックする→強制ログアウトを作る
  //ログインしていると判断できる条件
  // 1.セッションにidが入っていること
  // 2.最後の行動から1時間以内であること
  if(isset($_SESSION['login_member_id']) && ($_SESSION['time'] + 3600 > time()) ){
    //ログインしている and 最後の行動から1時間以内
    //セッションの時間を更新
    $_SESSION['time'] = time();

  } else {
    //ログインしていない or 最後の行動から1時間以上経った
    header('Location: login.php');
    exit();

  }

  //データベースに接続する
  require('dbconnect.php');

  //mysql専用のログイン文
  //$dbh = mysqli_connect('localhost','root','','seed_sns') or die(mysqli_connect_error());
  //mysqli_set_charset($db,'utf8');

  //1-2.オンラインでデータベースに接続する
  //$dsn = 'mysql:dbname=LAA0079983-onelinebbs;host=mysql103.phy.lolipop.lan';
  //$user = 'LAA0079983';
  //$password = 'w41ca4182';
  //$dbh = new PDO($dsn, $user, $password);
  //$dbh->query('SET NAMES utf8');

  //フェッチで取得したデータを格納する配列を用意
  //配列を初期化する
  //$post_datas = array();

  //入力内容を変数に置き換える
  //初期画面ではnicknameとcommentの変数が作れないため、post送信されたら以下を実行する
  if (!empty($_POST)){
    //補足：つぶやきが空っぽではない時だけ、INSERTする
    $tweet = htmlspecialchars($_POST['tweet'], ENT_QUOTES, 'utf-8');
    $login_member_id = htmlspecialchars($_SESSION['login_member_id']);
    $reply_tweet_id = 0;

    //2-1. INSERT文を実行する（tweet内容をデータベースへ記入する）
    $sql = sprintf('INSERT INTO `tweets`(`tweet`,`member_id`,`reply_tweet_id`,`created`,`modified`) VALUES ("%s", "%s", "%s",now(),now());',
      mysqli_real_escape_string($db,$tweet),
      mysqli_real_escape_string($db,$login_member_id),
      mysqli_real_escape_string($db,$reply_tweet_id)
      );

    //SQLを実行
    mysqli_query($db,$sql) or die(mysqli_error($db));
    //データの再送信の防止、リダイレクトをかけることで再読み込みでPOST送信がしなくなる
    header("Location: index.php");
    exit();
}

    //vardumpを実行
    //var_dump($_SESSION['login_member_id']);

    //2-2. SELECT文を実行する（データベースからとりあえずログインユーザーの情報を引き出す）
    $sql = sprintf('SELECT * FROM `members` WHERE `member_id` = "%d"',
      mysqli_real_escape_string($db,$_SESSION['login_member_id'])
      );

    //SQLを実行
    $record = mysqli_query($db,$sql) or die(mysqli_error($db));
    $member = mysqli_fetch_assoc($record);

    //2-3. SELECT文を実行する（データベースから投稿内容を引き出す）
    $sql = sprintf('SELECT `members`.`nick_name`,`members`.`picture_path`,`tweets`.* FROM `tweets` INNER JOIN `members` on `tweets`.`member_id` = `members`.`member_id` ORDER BY `created` DESC'
      );
    $tweets = mysqli_query($db,$sql) or die(mysqli_error($db));
    $tweets_array = array();
    while ($tweet = mysqli_fetch_assoc($tweets)) {
      $tweets_array[] = $tweet;
    }

    //返信の場合
    if (isset($_REQUEST['res'])){
      //返信元のデータ（つぶやきとニックネーム）を取得する
      $sql = sprintf('SELECT `members`.`nick_name`,`tweets`.`tweet` FROM `tweets` INNER JOIN `members` on `tweets`.`member_id` = `members`.`member_id` WHERE `tweet_id` = "%d"',
        mysqli_real_escape_string($db,$_REQUEST['res'])
      );

      //$_REQUESTは

      $reply = mysqli_query($db,$sql) or die(mysqli_error($db));
      $reply_table = mysqli_fetch_assoc($reply);

      //[@ニックネーム つぶやき]という文字列を入力欄にセットする
      $reply_post = '@'.$reply_table['nick_name'].' '.$reply_table['tweet'];
    }


?>


<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SeedSNS</title>

    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/form.css" rel="stylesheet">
    <link href="assets/css/timeline.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">

  </head>
  <body>
  <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.html"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                <li><a href="logout.php">ログアウト</a></li>
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-4 content-margin-top">
        <legend>ようこそ <?php echo $member['nick_name'] ?> さん！</legend>
        <form method="post" action="" class="form-horizontal" role="form">
            <!-- つぶやき -->
            <div class="form-group">
              <label class="col-sm-4 control-label">つぶやき</label>
              <div class="col-sm-8">
                <?php if(isset($reply_post)){ ?>
                  <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"><?php echo $reply_post; ?></textarea>
                  <!-- valueの中身を確認する-->
                  <input type="hidden" name="reply_tweet_id" value="<?php echo $_REQUEST['res']; ?>">
                <?php }else{ ?>
                  <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"></textarea>
                <?php } ?>
              </div>
            </div>
          <ul class="paging">
            <input type="submit" class="btn btn-info" value="つぶやく">
                &nbsp;&nbsp;&nbsp;&nbsp;
                <li><a href="index.html" class="btn btn-default">前</a></li>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <li><a href="index.html" class="btn btn-default">次</a></li>
          </ul>
        </form>
      </div>

      <div class="col-md-8 content-margin-top">
      <!-- tweet内容を繰り返し表示する -->
      <?php foreach ($tweets_array as $tweet_each) { ?>
        <div class="msg">
          <img src="member_picture/<?php echo $tweet_each['picture_path']; ?>" width="48" height="48">
          <p>
            <?php echo $tweet_each['tweet']; ?> <span class="name">（<?php echo $tweet_each['nick_name']; ?>）</span>
            [<a href="index.php?res=<?php echo $tweet_each['tweet_id']; ?>">Re</a>]
          </p>
          <p class="day">
            <a href="view.php?tweet_id=<?php echo $tweet_each['tweet_id']; ?>">
              <?php echo $tweet_each['created']; ?>
            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
        </div>
      <?php } ?>


        <div class="msg">
          <img src="http://c85c7a.medialib.glogster.com/taniaarca/media/71/71c8671f98761a43f6f50a282e20f0b82bdb1f8c/blog-images-1349202732-fondo-steve-jobs-ipad.jpg" width="48" height="48">
          <p>
            つぶやき３<span class="name"> (Seed kun) </span>
            [<a href="#">Re</a>]
          </p>
          <p class="day">
            <a href="view.html">
              2016-01-28 18:03
            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
        </div>
        <div class="msg">
          <img src="http://c85c7a.medialib.glogster.com/taniaarca/media/71/71c8671f98761a43f6f50a282e20f0b82bdb1f8c/blog-images-1349202732-fondo-steve-jobs-ipad.jpg" width="48" height="48">
          <p>
            つぶやき２<span class="name"> (Seed kun) </span>
            [<a href="#">Re</a>]
          </p>
          <p class="day">
            <a href="view.html">
              2016-01-28 18:02
            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
        </div>
        <div class="msg">
          <img src="http://c85c7a.medialib.glogster.com/taniaarca/media/71/71c8671f98761a43f6f50a282e20f0b82bdb1f8c/blog-images-1349202732-fondo-steve-jobs-ipad.jpg" width="48" height="48">
          <p>
            つぶやき１<span class="name"> (Seed kun) </span>
            [<a href="#">Re</a>]
          </p>
          <p class="day">
            <a href="view.html">
              2016-01-28 18:01
            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
        </div>
      </div>

    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
  </body>
</html>
