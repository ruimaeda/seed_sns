<?php
  //Sessionをスタートする
  session_start();

  //データベースに接続する
  require('dbconnect.php');

  //GET送信されたtweet_idを取得する

  //SQL文の作成（likesテーブルのINSERT文）
  $sql = sprintf('INSERT INTO `likes`(`member_id`,`tweet_id`) VALUES ("%s","%s")',
    mysqli_real_escape_string($db,$_SESSION['login_member_id']),
    mysqli_real_escape_string($db,$_GET['tweet_id'])
    );

    //SQL文の実行
    $likes = mysqli_query($db,$sql) or die(mysqli_error($db));

  //index.phpnに戻る
  header("location: index.php");
  exit();

?>