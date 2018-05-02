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

if (empty($_POST['teams'])) {
  header("Location: /disaster_report/routes/topics/create.php?type={$_POST['type']}");
  session_flash('error', '請選填出勤分隊！');
} else {
  $teams = null;
  extract($_POST, EXTR_IF_EXISTS);

  try {
    if ($_COOKIE['x_csrf_token'] !== $_SESSION['csrfToken']) {
      throw new Exception(null, 3);
    }
    $conn = new PDO($dsn, $db_user, $db_password);
    $conn->beginTransaction();
    $topic_id = $queryString['topic_id'];
    $teamsStmt = $conn->prepare("DELETE FROM teams_info WHERE topic_id = {$topic_id}");
    $teamsStmt->execute();

    $placeholders = [];
    for ($i=0; $i < count($teams); $i++) { 
      $placeholders[] = "(?, {$topic_id})";
    }
    $teamsInfoStmt = $conn->prepare("INSERT INTO teams_info(team_id, topic_id) VALUES " . join(',', $placeholders));
    $teamsInfoStmt->execute($teams);
    $conn->commit();

    header("Location: /disaster_report/routes/topics/teams/show.php?topic_id={$queryString['topic_id']}");
  } catch (Exception $e) {
    header("Location: /disaster_report/routes/topics/create.php?type={$_POST['type']}&fail={$e->getCode()}");
    session_flash('error', '伺服器連線錯誤，請登出並重新整理頁面，再次登入！');
  }
}