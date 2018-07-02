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

$cars = [];
$members = [];
$sending_time = NULL;
$return_time = NULL;
extract($_POST, EXTR_IF_EXISTS);

$sending_time = addQuoteToTime($sending_time);
$return_time = addQuoteToTime($return_time);

try {
  if ($_POST['csrfToken'] !== $_SESSION['csrfToken']) {
    throw new Exception(null, 3);
  }
  $conn = new PDO($dsn, $db_user, $db_password);
  $conn->beginTransaction();

  // 更新出勤車輛
  $carsStmt = $conn->prepare("DELETE FROM active_cars WHERE topic_id = {$queryString['topic_id']} AND team_id = '{$queryString['team_id']}'");
  $carsStmt->execute();
  if (!empty($cars)) {
    $placeholders = [];
    for ($i = 0; $i < count($cars); $i++) {
      $placeholders[] = "({$queryString['topic_id']}, '{$queryString['team_id']}', '{$cars[$i]}')";
    }
    $carsStmt = $conn->prepare("INSERT INTO active_cars(topic_id, team_id, name) VALUES " . join(',', $placeholders));
    $carsStmt->execute();
  }

  // 更新出勤人員
  $membersStmt = $conn->prepare("DELETE FROM active_members WHERE topic_id = {$queryString['topic_id']} AND team_id = '{$queryString['team_id']}'");
  $membersStmt->execute();
  if (!empty($members)) {
    $placeholders = [];
    for ($i = 0; $i < count($members); $i++) {
      $placeholders[] = "({$queryString['topic_id']}, '{$queryString['team_id']}', '{$members[$i]}')";
    }
    $membersStmt = $conn->prepare("INSERT INTO active_members(topic_id, team_id, name) VALUES " . join(',', $placeholders));
    $membersStmt->execute();
  }

  // 更新出勤人數、出勤車輛數、出勤時間、返隊時間
  $car_count = count($cars);
  $people_count = count($members);
  $teamInfosStmt = $conn->prepare("UPDATE active_team_informations SET people_count={$people_count}, car_count={$car_count}, sending_time={$sending_time}, return_time={$return_time} WHERE topic_id = {$queryString['topic_id']} AND team_id = '{$queryString['team_id']}'");
  $teamInfosStmt->execute();

  $conn->commit();

  header("Location: /disaster_report/routes/reports/index.php?topic_id={$queryString['topic_id']}&team_id={$queryString['team_id']}");
} catch (Exception $e) {
  header("Location: /disaster_report/routes/reports/active_team_informations/edit.php?fail={$e->getCode()}");
  session_flash('error', '伺服器連線錯誤，請登出並重新整理頁面，再次登入！');
}
