<?php

require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/session.php');
require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/isLogin.php');
require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/config/db_config.php');

session();

if (!$isLogin()) {
  header('Location: /disaster_report/index.php');
  return;
}

if (empty($_POST['topic'])) {
  header("Location: /disaster_report/routes/topics/create.php?type={$_POST['type']}");
  session_flash('error', '請填寫主題！');
} elseif (empty($_POST['type'])) {
  header("Location: /disaster_report/routes/topics/create.php?type={$_POST['type']}");
} elseif (empty($_POST['teams'])) {
  header("Location: /disaster_report/routes/topics/create.php?type={$_POST['type']}");
  session_flash('error', '請選填出勤分隊！');
} else {
  $topic = null;
  $type = null;
  $teams = null;
  extract($_POST, EXTR_IF_EXISTS);

  try {
    if ($_COOKIE['x_csrf_token'] !== $_SESSION['csrfToken']) {
      throw new Exception(null, 3);
    }
    $conn = new PDO($dsn, $db_user, $db_password);
    $conn->beginTransaction();
    $topicsStmt = $conn->prepare('INSERT INTO topics(name, type) VALUES (?, ?)');
    $topicsStmt->execute([$topic, $type]);
    $topic_id = $conn->lastInsertId();

    $placeholders = [];
    for ($i=0; $i < count($teams); $i++) { 
      $placeholders[] = "(?, {$topic_id})";
    }
    $teamsStmt = $conn->prepare("INSERT INTO teams_info(team_id, topic_id) VALUES " . join(',', $placeholders));
    $teamsStmt->execute($teams);
    $conn->commit();

    header("Location: /disaster_report/routes/topics/index.php?type={$type}");
  } catch (Exception $e) {
    header("Location: /disaster_report/routes/topics/create.php?type={$_POST['type']}&fail={$e->getCode()}");
    session_flash('error', '伺服器連線錯誤，請登出並重新整理頁面，再次登入！');
  }
}