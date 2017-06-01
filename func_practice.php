<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
	<title></title>
</head>
<body>

</body>
</html>

<?php

//引数がない
function hello(){
	echo 'hello';
}

//引数がある
function huga($hogehoge){
	echo $hogehoge;
}

// function aisatsu($names){
// 	echo '初めまして、ぼく'.$names.'<br>';
// 	echo '元気？<br>';
// }

// nexseed('ボンジュー');
// nexseed('こんにちは');
// nexseed('ニイハオ');

//2つの値の合計値を計算する関数
function plus($num1,$num2){
	$result = $num1 + $num2;
	return $result;
	//echo '合計は'.$result;
}

//関数の呼び出し
$kekka1 = plus(19,5);
echo '視聴率は'.$kekka1 / 100 .'%です';
$kekka2 = plus(19,38);
echo '合計点は'.$kekka2.'店です';


//練習問題5
//seedくんという文字を出力する「nexseed」関数を作る
function nexseed($greeting,$name){
	return $greeting.'、'.$name;
}

echo nexseed('こんにちは','seed');
echo '<br />';

//演習問題1
function multiplication($num1,$num2) {
	return $num1 * $num2;
}

echo multiplication(10,5);
echo '<br />';

//演習問題2
function average($num1,$num2) {
	$result = ($num1 + $num2) / 2;
	if ($result >= 10) {
		return $result;
	}else{
		return 0;
	}
}

echo average(100,100);
echo '<br />';

//演習問題3
function shopping($money,$price) {
	return $money - $price;

}

echo shopping(1000000,1000);
echo '<br />';

//演習問題4
function compare($num1,$num2) {
	if ($num1 >= $num2) {
		return $num1;
	}else{
		return $num2;
	}
}

echo compare(1,100);
echo '<br />';

?>