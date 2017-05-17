<?php
  //Sessionをスタートする
  session_start();

  //データベースに接続する
  require('dbconnect.php');

  //delete_flagを0から1に更新する
  $sql = 'UPDATE `tweets` SET `delete_flag` = 1 WHERE `tweet_id` = '.$_REQUEST['tweet_id'];
  $tweets = mysqli_query($db,$sql) or die(mysqli_error($db));

  header("location: index.php");
  exit();

?>