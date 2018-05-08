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

if (empty($_POST['topic'])) {
  header("Location: /disaster_report/routes/topics/create.php?type={$_POST['type']}");
  session_flash('error', '請填寫主題！');
} else {
  $topic = null;
  extract($_POST, EXTR_IF_EXISTS);

  try {
    if ($_POST['csrfToken'] !== $_SESSION['csrfToken']) {
      throw new Exception(null, 3);
    }
    $conn = new PDO($dsn, $db_user, $db_password);
    $topicStmt = $conn->prepare("UPDATE topics SET name = '{$topic}' WHERE id = {$queryString['topic_id']}");
    $topicStmt->execute();
    header("Location: /disaster_report/routes/topics/teams/show.php?topic_id={$queryString['topic_id']}");
  } catch (Exception $e) {
    header("Location: /disaster_report/routes/auth/logout.php?fail={$e->getCode()}");
  }
}

?>