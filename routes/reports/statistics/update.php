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

function addQuoteToTime($time)
{
  if ($time === '') {
    return 'NULL';
  }
  return $time = "'" . $time . "'";
}

$processing_situation = '';
$remark = '';
$current_situation = '';
$management_time = 'NULL';
$release_time = 'NULL';
extract($_POST, EXTR_IF_EXISTS);

$management_time = addQuoteToTime($management_time);
$release_time = addQuoteToTime($release_time);
$current_time = addQuoteToTime(date('Y-m-d H:i:s', time()));

try {
  if ($_POST['csrfToken'] !== $_SESSION['csrfToken']) {
    throw new Exception(null, 3);
  }
  $conn = new PDO($dsn, $db_user, $db_password);

  // 更新災情統計資料
  $statisticDataStmt = $conn->prepare("UPDATE place_status SET processing_situation = '{$processing_situation}', remark = '{$remark}', current_situation = '{$current_situation}', management_time = {$management_time}, release_time = {$release_time}, update_time = {$current_time} WHERE topic_id = {$queryString['topic_id']} AND team_id = '{$queryString['team_id']}' AND name = '{$queryString['name']}'");
  $statisticDataStmt->execute();

  header("Location: /disaster_report/routes/topics/teams/show.php?topic_id={$queryString['topic_id']}");
} catch (Exception $e) {
  header("Location: /disaster_report/routes/reports/active_team_informations/edit.php?fail={$e->getCode()}");
  session_flash('error', '伺服器連線錯誤，請登出並重新整理頁面，再次登入！');
}
