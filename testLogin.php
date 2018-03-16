<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>
<body>
  <h1>You are Login^^</h1>
</body>
</html>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/isLogin.php');

session_start();
var_dump($_SESSION);
if (!$isLogin()) {
  header('Location: /disaster_report/index.php');
}