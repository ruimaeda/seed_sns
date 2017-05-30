<?php

  $jsondata = file_get_contents('sample.json');
  //var_dump($jsondata);

  $array = json_decode($jsondata,true);
  //var_dump($array);

  echo 'お名前は、'.$array['name'].'<br/>';
  echo '性別は、'.$array['gender'];

?>

<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
  <ul>
    趣味は
    <li><?php echo $array['hobby'][0] ?></li>
    <li><?php echo $array['hobby'][1] ?></li>
    <li><?php echo $array['hobby'][2] ?></li>
  </ul>

</body>
</html>