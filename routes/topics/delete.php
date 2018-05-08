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

try {
  if ($_POST['csrfToken'] !== $_SESSION['csrfToken']) {
    throw new Exception(null, 3);
  }
  $conn = new PDO($dsn, $db_user, $db_password);
  $topicStmt = $conn->prepare("DELETE FROM topics WHERE id = {$queryString['topic_id']}");
  $topicStmt->execute();
  var_dump("DELETE FROM topics WHERE id = {$queryString['topic_id']}");
  header("Location: /disaster_report/routes/topics/index.php?type={$queryString['type']}");
} catch (Exception $e) {
  header("Location: /disaster_report/routes/auth/logout.php?fail={$e->getCode()}");
}
