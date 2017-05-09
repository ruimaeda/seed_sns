<?php
  //DBへ接続
  session_start();
  require('dbconnect.php');

  //自動ログイン処理
  if (isset($_COOKIE['email']) && !empty($_COOKIE['email'])){

    //COOKIEに保存されているログイン情報が入力されてPOST送信されてきたかのように$_POSTに値を代入

    $_POST['email'] = $_COOKIE['email'];
    $_POST['password'] = $_COOKIE['password'];
    $_POST['save'] = 'on';

  }

  //POST送信されていたら、emailとpasswordの入力チェックを行い、どちらかが（あるいは両方とも）未入力だった場合、エラーメッセージを出力する
  //$error['login']にblankという文字をセットして判別
  //フォームからデータがPOST送信された時
  if (!empty($_POST)) {
    //エラー項目の確認
    //メールアドレスが無い場合にkey'mail'にvalue'blank'を代入
    if (empty($_POST['email'])) {
      $error['login'] = 'blank';
    }

    //パスワードが無い場合にkey'password'にvalue'blank'を代入
    if (empty($_POST['password'])) {
      $error['login'] = 'blank';
    }

    if (empty($error)) {
      //ログイン処理
      //入力されたemail,passwordでDBから会員情報を取得できたら、正常ログイン、取得できなかったら、$error['login']にfaildを代入して、
      //パスワードの下に「ログインに失敗しました。正しくご記入ください」とメッセージを表示する
      $sql = sprintf('SELECT `email`, `password`, `member_id` FROM `members` WHERE `email` = "%s" AND `password` = "%s"',
      mysqli_real_escape_string($db,$_POST['email']),
      mysqli_real_escape_string($db,sha1($_POST['password'])));

      //SQLを実行
      $record = mysqli_query($db,$sql) or die(mysqli_error($db));
      if ($table = mysqli_fetch_assoc($record)) {
        //ログイン成功
        //SESSION変数に会員IDを保存
        $_SESSION['login_member_id'] = $table['member_id'];
        //SESSION変数にログイン時間を記録
        $_SESSION['time'] = time();

        //自動ログインをONにしてたら、cookieにログイン情報を保存する
        if($_POST['save'] == 'on'){
          //setcookie(保存するキー,保存する値,保存する期間(秒数))
          setcookie('email',$_POST['email'],time() + 60*60*24*14);
          setcookie('password',$_POST['password'],time() + 60*60*24*14);
        }

        //ログイン後のindex.php（トップページ）に遷移
        header("location: index.php");

        exit();
      }else{
        //ログイン失敗
        $error['login'] = 'faild';
      }
    }
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
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-6 col-md-offset-3 content-margin-top">
        <legend>ログイン</legend>
        <form method="post" action="" class="form-horizontal" role="form">
          <!-- メールアドレス -->
          <div class="form-group">
            <label class="col-sm-4 control-label">メールアドレス</label>
            <div class="col-sm-8">
              <input type="email" name="email" class="form-control" placeholder="例： seed@nex.com">
            </div>
          </div>
          <!-- パスワード -->
          <div class="form-group">
            <label class="col-sm-4 control-label">パスワード</label>
            <div class="col-sm-8">
              <input type="password" name="password" class="form-control" placeholder="">
                <?php if(isset($error['login']) && $error['login'] == 'blank'){ ?>
                  <p class="error">メールアドレスとパスワードを入力してください(๑•̀ㅂ•́)و✧</p>
                <?php } ?>
                <?php if(isset($error['login']) && $error['login'] == 'faild'){ ?>
                  <p class="error">ログイン失敗(๑•̀ㅂ•́)و✧</p>
                <?php } ?>
            </div>
          </div>
          <!-- 自動ログインのチェックボックス -->
          <div class="form-group">
            <label class="col-sm-4 control-label">自動ログインする</label>
            <div class="col-sm-8">
              <input type="checkbox" name="save" value="on">
            </div>
          </div>


          <input type="submit" class="btn btn-default" value="ログイン">
        </form>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
  </body>
</html>
