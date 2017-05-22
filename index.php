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
    //$tweet = htmlspecialchars($_POST['tweet'], ENT_QUOTES, 'utf-8'); サニタイズ全文
    //サニタイズ自作関数利用
    $tweet = h($_POST['tweet']);
    $login_member_id = htmlspecialchars($_SESSION['login_member_id']);

    if (isset($_POST['reply_tweet_id'])) {
      $reply_tweet_id = $_POST['reply_tweet_id'];
    }else{
      $reply_tweet_id = 0;
    }



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

    //ページング処理

    //0.ページ番号を取得（ある場合はGET送信、ない場合は1ページ目と認識する）
    $page = '';

    //GET送信されて来たページ番号を取得
    if(isset($_GET['page'])){
      $page = $_GET['page'];
    }

    //GET送信がない場合は1ページ目と認識する
    if ($page == ''){
      $page = 1;
    }

    //1.表示する正しいページ数値を設定（min） / 最小値が必ず1になるように、$pageと1を比較して1の方が大きい場合は1を設定（$page = -100などを防ぐ）
    $page = max($page,1);

    //2.必要なページ数を計算
    //2-1. 1ページに表示する行数の決定
    $row = 5;
    if (isset($_GET['search_word']) && !empty($_GET['search_word'])){
    $sql = sprintf('SELECT COUNT(*) as cnt FROM `tweets` INNER JOIN `members` on `tweets`.`member_id` = `members`.`member_id` WHERE `delete_flag` = 0 AND `tweet` LIKE "%%%s%%" ORDER BY `tweets`.`created` DESC',
      mysqli_real_escape_string($db,$_GET['search_word'])
    );

    }else{
    $sql = 'SELECT COUNT(*) as cnt FROM `tweets` INNER JOIN `members` on `tweets`.`member_id` = `members`.`member_id` WHERE `delete_flag` = 0 ORDER BY `tweets`.`created` DESC';
    }

    $record_cnt = mysqli_query($db, $sql) or die(mysqli_error($db));
    $table_cnt = mysqli_fetch_assoc($record_cnt);
    $maxPage = ceil($table_cnt['cnt'] / $row);

    //3.表示する正しいページ数の数値を設定（max） / 最大値が必ず$maxPageになるように、$pageと$maxPageを比較して大きい方を選択（$page = 1000などを防ぐ）
    $page = min($page, $maxPage);

    //4.ページに表示する件数だけ取得する
    $start = ($page - 1) * $row;

    //vardumpを実行
    //var_dump($_SESSION['login_member_id']);

    //2-2. SELECT文を実行する（データベースからとりあえずログインユーザーの情報を引き出す）
    $sql = sprintf('SELECT * FROM `members` WHERE `member_id` = "%d"',
      mysqli_real_escape_string($db,$_SESSION['login_member_id'])
      );

    //SQLを実行
    $record = mysqli_query($db,$sql) or die(mysqli_error($db));
    $member = mysqli_fetch_assoc($record);

    //2-3-1. 検索された場合、検索結果のtweet一覧を表示する処理
    if (isset($_GET['search_word']) && !empty($_GET['search_word'])){
    $sql = sprintf('SELECT `members`.`nick_name`,`members`.`picture_path`,`tweets`.* FROM `tweets` INNER JOIN `members` on `tweets`.`member_id` = `members`.`member_id` WHERE `delete_flag`=0 AND `tweet` LIKE "%%%s%%" ORDER BY `created` DESC LIMIT %d,%d',
      mysqli_real_escape_string($db,$_GET['search_word']),
      $start,
      $row
      );

    }else{

    //2-3-2. 検索されていない場合のtweet一覧を表示する処理 / SELECT文を実行する（データベースから投稿内容を引き出す）
    $sql = sprintf('SELECT `members`.`nick_name`,`members`.`picture_path`,`tweets`.* FROM `tweets` INNER JOIN `members` on `tweets`.`member_id` = `members`.`member_id` WHERE `delete_flag`=0 ORDER BY `created` DESC LIMIT %d,%d',
      $start,
      $row
      );

    }

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

    //htmlspecialcharsを実行する自分の関数hを作る
    //$input_value：引数
    //h：関数名
    //return ◯◯：戻り値
    function h($input_value){
      return htmlspecialchars($input_value,ENT_QUOTES,'UTF-8');
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
                <!-- -->
                <?php
                  $word='';
                  if(isset($_GET['search_word']) && !empty($_GET['search_word'])){
                    $word = '&search_word='.$_GET['search_word'];
                  }
                ?>

                <li>
                <?php if ($page > 1) { ?>
                <a href="index.php?page=<?php echo $page-1; ?><?php echo $word; ?>" class="btn btn-default">前</a>
                <?php }else{ ?>
                前
                <?php } ?>
                </li>

                &nbsp;&nbsp;|&nbsp;&nbsp;
                <li>
                <?php if ($page < $maxPage) { ?>
                <a href="index.php?page=<?php echo $page+1; ?><?php echo $word; ?>" class="btn btn-default">次</a></li>
                <?php }else{ ?>
                次
                <?php } ?>
                </li>
          </ul>
        </form>
      </div>

      <div class="col-md-8 content-margin-top">
      <!-- 検索ボックス -->
      <form action="" method="get" class="form-horizontal">
        <input type="text" name="search_word">
        <input type="submit" class="btn btn-success btn-xs" value="検索">
      </form>


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
            <?php if($tweet_each['reply_tweet_id'] > 0){ ?>
              | <a href="view.php?tweet_id=<?php echo $tweet_each['reply_tweet_id']; ?>">返信元のつぶやき</a> |
            <?php } ?>

            <?php if ($_SESSION['login_member_id'] == $tweet_each['member_id']) { ?>
              [<a href="edit.php?tweet_id=<?php echo $tweet_each['tweet_id']; ?>" style="color: #00994C;">編集</a>]
              [<a href="delete.php?tweet_id=<?php echo $tweet_each['tweet_id']; ?>" style="color: #F33;">削除</a>]
            <?php } ?>
          </p>
        </div>
      <?php } ?>
      </div>

    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
  </body>
</html>
