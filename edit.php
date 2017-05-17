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

  //つぶやきを表示するSLECT文を実行する
  $sql = sprintf('SELECT `members`.`nick_name`,`members`.`picture_path`,`tweets`.* FROM `tweets` INNER JOIN `members` on `tweets`.`member_id` = `members`.`member_id` WHERE `tweet_id` = "%d"',
    mysqli_real_escape_string($db,$_REQUEST['tweet_id'])
    );
  $tweets = mysqli_query($db,$sql) or die(mysqli_error($db));
  $tweet = mysqli_fetch_assoc($tweets);

  //つぶやきを保存するUPDATEを作って、実行して、indexに戻る
  //保存ボタンが押されたことの確認
  if(isset($_POST) && !empty($_POST['tweet'])){

    //SQLの実行
    $sql = sprintf('UPDATE `tweets` SET `tweet` = "%s" WHERE `tweet_id` = %d',
    mysqli_real_escape_string($db,$_POST['tweet']),
    mysqli_real_escape_string($db,$_POST['tweet_id'])
    );

    mysqli_query($db,$sql) or die(mysqli_error($db));

    header("location: index.php");
    exit();
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
      <div class="col-md-4 col-md-offset-4 content-margin-top">
        <div class="msg">
         <img src="member_picture/<?php echo $tweet['picture_path']; ?>" width="100" height="100">
          <p>投稿者 : <span class="name"><?php echo $tweet['nick_name']; ?></span></p>
          <p>
            つぶやき : <br>
            <form method="post" action="" class="form-horizontal" role="form">
            <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"><?php echo $tweet['tweet']; ?></textarea>
            <input type="hidden" name="tweet_id" value="<?php echo $tweet['tweet_id']; ?>">
            <input type="submit" class="btn btn-info" value="保存">
          </p>
          <p class="day">
            <!-- ここにはdayが入る-->
          </p>
        </div>
        <a href="index.html">&laquo;&nbsp;一覧へ戻る</a>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-md-4 col-md-offset-4 content-margin-top">
        <div class="msg">
          <img src="http://c85c7a.medialib.glogster.com/taniaarca/media/71/71c8671f98761a43f6f50a282e20f0b82bdb1f8c/blog-images-1349202732-fondo-steve-jobs-ipad.jpg" width="100" height="100">
          <p>投稿者 : <span class="name"> Seed kun </span></p>
          <p>
            つぶやき : <br>
            つぶやき４つぶやき４つぶやき４
          </p>
          <p class="day">
            2016-01-28 18:04
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
        </div>
        <a href="index.html">&laquo;&nbsp;一覧へ戻る</a>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
  </body>
</html>