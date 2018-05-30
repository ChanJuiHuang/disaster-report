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

function addQuote($s)
{
  if ($s === '' || $s === null) {
    return 'NULL';
  }
  return $s = "'" . $s . "'";
}

$places = [];
$isHappenedDisaster = [];
$isEffectTraffic = [];
$return_times = [];
$disasters = [];
$other_disaster = NULL;
$cities = [];
$happened_places = [];
$descriptions = [];
$hurt_people = [];
$dead_people = [];
$trapped_people = [];
extract($_POST, EXTR_IF_EXISTS);

try {
  if ($_POST['csrfToken'] !== $_SESSION['csrfToken']) {
    throw new Exception(null, 3);
  }

  $conn = new PDO($dsn, $db_user, $db_password);
  $conn->beginTransaction();
  // 刪除舊資料
  $placeStatusStmt = $conn->prepare("DELETE FROM place_status WHERE topic_id = {$queryString['topic_id']} AND team_id = '{$queryString['team_id']}'");
  $placeStatusStmt->execute();

  // 新增地點狀況
  if (!empty($places)) {
    $place_status_ids = [];
    foreach ($places as $index => $place) {
      $placeholders = [];
      $isHappenedDisaster[$index] = addQuote($isHappenedDisaster[$index]);
      $isEffectTraffic[$index] = addQuote($isEffectTraffic[$index]);
      $return_times[$index] = addQuote($return_times[$index]);

      $placeholders[] = "('{$place}', {$isHappenedDisaster[$index]}, {$isEffectTraffic[$index]}, {$return_times[$index]}, {$queryString['topic_id']}, '{$queryString['team_id']}')";
      $placeStatusStmt = $conn->prepare("INSERT INTO place_status(name, is_happened_disaster, is_effect_traffic, return_time, topic_id, team_id) VALUES " . join(',', $placeholders));
      $placeStatusStmt->execute();
      $place_status_ids[$index] = $conn->lastInsertId();
    }
  }

  // 新增地點災情
  if (!empty($disasters)) {
    $placeholders = [];
    $happenedDisasterIndex = [];
    foreach ($disasters as $index => $place_disasters) {
      foreach ($place_disasters as $place_disaster) {
        $isOther = 0;
        $otherDisasterName = NULL;
        if ($place_disaster === 'other') {
          $otherDisasterName = $other_disaster[$index];
          $isOther = 1;
        }
        $place_disaster = addQuote($place_disaster);
        $otherDisasterName = addQuote($otherDisasterName);
        $placeholders[] = "({$place_disaster}, {$isOther}, {$otherDisasterName}, {$place_status_ids[$index]})";
      }
      $happenedDisasterIndex[] = $index;
    }
    $placeDisastersStmt = $conn->prepare("INSERT INTO place_disasters(name, isother, other_disaster_name, place_status_id) VALUES " . join(',', $placeholders));
    $placeDisastersStmt->execute();
  }

  // 新增災情概述
  if (!empty($cities)) {
    $placeholders = [];
    foreach ($happenedDisasterIndex as $index) {
      $city = addQuote($cities[$index]);
      $happened_place = addQuote($happened_places[$index]);
      $description = addQuote($descriptions[$index]);
      $hurt_people[$index] = addQuote($hurt_people[$index]);
      $dead_people[$index] = addQuote($dead_people[$index]);
      $trapped_people[$index] = addQuote($trapped_people[$index]);
      $placeholders[] = "({$city}, {$happened_place}, {$description}, {$hurt_people[$index]}, {$dead_people[$index]}, {$trapped_people[$index]}, {$place_status_ids[$index]})";
    }
    $disasterStatusStmt = $conn->prepare("INSERT INTO disaster_status(city, address, description, hurt_people, dead_people, trapped_people, place_status_id) VALUES " . join(',', $placeholders));
    $disasterStatusStmt->execute();
  }

  $conn->commit();

  header("Location: /disaster_report/routes/reports/index.php?topic_id={$queryString['topic_id']}&team_id={$queryString['team_id']}");
} catch (Exception $e) {
  header("Location: /disaster_report/routes/reports/disaster_status/create.php?topic_id={$queryString['topic_id']}&team_id={$queryString['team_id']}&fail={$e->getCode()}");
  session_flash('error', '伺服器連線錯誤，請登出並重新整理頁面，再次登入！');
}
