<?php

require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/session.php');
require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/isLogin.php');
require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/config/db_config.php');

session();

if (!$isLogin()) {
  header('Location: /disaster_report/index.php');
  return;
}

if (isset($_SERVER['QUERY_STRING'])) {
  parse_str($_SERVER['QUERY_STRING'], $queryString);
}

$places = null;
extract($_POST, EXTR_IF_EXISTS);

try {
  if ($_POST['csrfToken'] !== $_SESSION['csrfToken']) {
    throw new Exception(null, 3);
  }
  $conn = new PDO($dsn, $db_user, $db_password);
  $conn->beginTransaction();

  $placesStmt = $conn->prepare("DELETE FROM places WHERE team_id='{$_SESSION['team_NO']}'");
  $placesStmt->execute();

  if (!empty($places)) {
    $placeholders = [];
    $teams = array_fill(0, count($places), $_SESSION['team_NO']);
    for ($i = 0; $i < count($places); $i++) {
      $placeholders[] = "(?, '{$places[$i]}')";
    }
    $placesStmt = $conn->prepare("INSERT INTO places(team_id, name) VALUES " . join(',', $placeholders));
    $placesStmt->execute($teams);
  }
  $conn->commit();

  header("Location: /disaster_report/routes/topics/index.php?type={$queryString['type']}");
} catch (Exception $e) {
  header("Location: /disaster_report/routes/places/edit.php?fail={$e->getCode()}");
  session_flash('error', '伺服器連線錯誤，請登出並重新整理頁面，再次登入！');
}
