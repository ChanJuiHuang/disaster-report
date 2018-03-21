<?php

require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/session.php');

$captchaString = "";	//驗證碼文字
$captchaLength = 5;		//驗證碼長度
 
//產生數字驗證碼
for($i=0; $i < $captchaLength; $i++){
	$captchaString = $captchaString . rand(0, 9);
}
 
$session();
$_SESSION['captcha'] = $captchaString;	//驗證碼存入SESSION內
 
header("Content-Type: image/png");	//宣告輸出為PNG影像
$captchaWidth = 100;				//驗證碼影像寬度
$captchaHeight = 30;				//驗證碼影像高度

//建立影像
$captcha = imagecreate($captchaWidth, $captchaHeight);
//設定背景顏色，範例是紅色
$backgroundColor = imagecolorallocate($captcha, 255, 0, 0);
//設定文字顏色，範例是白色
$fontColor = imagecolorallocate($captcha, 255, 255, 255);

//影像填滿背景顏色
imagefill($captcha, 0, 0, $backgroundColor);
//影像畫上驗證碼
imagettftext($captcha, 15, 0, rand(0,45), rand(15,30), $fontColor, $_SERVER['DOCUMENT_ROOT'] . "/disaster_report/public/font/arial.ttf", $captchaString);
//隨機畫上200個點，做為雜訊用
for($i = 0; $i < 200; $i++) {
	imagesetpixel($captcha, rand() % $captchaWidth , rand() % $captchaHeight , $fontColor);
}
//隨機畫上三條干擾線
for($i=0;$i<3;$i++) {	
	$line = imageColorAllocate($captcha,rand(0,255),rand(0,255),rand(0,255));
  imageline($captcha, rand(0,15), rand(0,15), rand(100,150),rand(10,50), $line);
}
//輸出驗證碼影像
imagepng($captcha);
imagedestroy($captcha);
