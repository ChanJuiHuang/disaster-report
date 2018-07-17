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

try {
  $conn = new PDO($dsn, $db_user, $db_password);
  $topicStmt = $conn->prepare("SELECT name FROM topics WHERE id = {$queryString['topic_id']}");
  $topicStmt->execute();
  $topic = $topicStmt->fetch(PDO::FETCH_ASSOC);
  $teamsStmt = $conn->prepare("SELECT teams.id, teams.name FROM active_team_informations JOIN teams ON active_team_informations.team_id = teams.id WHERE active_team_informations.topic_id = {$queryString['topic_id']}");
  $teamsStmt->execute();
  $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);

  // 統計資料
  $statisticDataStmt = $conn->prepare
  ("SELECT team_name, car_count, people_count, place_name, description, hurt_people, dead_people, trapped_people, return_time, STRING_AGG(member_name, ', ') AS all_members, processing_situation, remark, current_situation, management_time, release_time, update_time FROM (
    SELECT teams.id, teams.name AS team_name, active_team_informations.car_count, active_team_informations.people_count, place_status.name AS place_name, disaster_status.description, disaster_status.hurt_people, disaster_status.dead_people, disaster_status.trapped_people, place_status.return_time, active_members.name AS member_name, place_status.processing_situation, place_status.remark, place_status.current_situation, place_status.management_time, place_status.release_time, place_status.update_time FROM place_status
    JOIN teams ON teams.id = place_status.team_id
    JOIN active_team_informations ON active_team_informations.team_id = place_status.team_id AND active_team_informations.topic_id = place_status.topic_id
    JOIN place_disasters ON place_disasters.place_status_id = place_status.id
    JOIN disaster_status ON disaster_status.place_status_id = place_status.id
    JOIN active_members ON active_members.team_id = place_status.team_id AND active_members.topic_id = place_status.topic_id
    WHERE place_status.topic_id = {$queryString['topic_id']} AND place_status.is_happened_disaster = 1
    GROUP BY teams.id, team_name, active_team_informations.car_count, active_team_informations.people_count, place_name, disaster_status.description, disaster_status.hurt_people, disaster_status.dead_people, disaster_status.trapped_people, place_status.return_time, active_members.name, place_status.processing_situation, place_status.remark, place_status.current_situation, place_status.management_time, place_status.release_time, place_status.update_time 
    ORDER BY teams.id
    ) AS t
  GROUP BY id, team_name, car_count, people_count, place_name, description, hurt_people, dead_people, trapped_people, return_time, processing_situation, remark, current_situation, management_time, release_time, update_time");
  $statisticDataStmt->execute();
  $statisticData = $statisticDataStmt->fetchAll(PDO::FETCH_ASSOC);
  // var_dump($statisticData);
} catch (Exception $e) {
  header("Location: /disaster_report/routes/auth/logout.php?fail={$e->getCode()}");
  return;
}
?>

<!DOCTYPE html>
<html lang="en">

<?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_header.php'); ?>

<body>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_navbar.php'); ?>
  <div class="container">
    <div class="row">
      <div class="col-md-10 offset-md-1">
        <div class="card">
          <div class="card-title card-header" style="font-size: 6vmin; text-align: center;"><?= $topic['name'] ?></div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-10 offset-md-1">
                <?php if ($_SESSION['is_center']) { ?>
                <a href="/disaster_report/routes/topics/edit.php?topic_id=<?= $queryString['topic_id'] ?>">
                  <button type="button" class="btn btn-secondary">編輯主題</button>
                </a>
                <a href="/disaster_report/routes/topics/teams/edit.php?topic_id=<?= $queryString['topic_id'] ?>">
                  <button type="button" class="btn btn-success">編輯出勤分隊</button>
                </a>
                <a href="/disaster_report/routes/topics/deletePage.php?topic_id=<?= $queryString['topic_id'] ?>">
                  <button type="button" class="btn btn-danger">刪除主題</button>
                </a>
                <?php } ?>
              </div>
            </div>
            <div class="row">
              <div class="col-md-10 offset-md-1">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th scope="col">出勤分隊</th>
                      <th scope="col">車次</th>
                      <th scope="col">人次</th>
                      <th scope="col">地點</th>
                      <th scope="col">災情概述</th>
                      <th scope="col">處理情形</th>
                      <th scope="col">備註</th>
                      <th scope="col">目前狀況</th>
                      <th scope="col">受傷、死亡、受困人數</th>
                      <th scope="col">回報時間</th>
                      <th scope="col">出勤人員</th>
                      <th scope="col">列管時間</th>
                      <th scope="col">解除時間</th>
                      <th scope="col">更新時間</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($statisticData as $data) { ?>
                    <tr>
                      <td><?= $data['team_name'] ?></td>
                      <td><?= $data['car_count'] ?></td>
                      <td><?= $data['people_count'] ?></td>
                      <td><?= $data['place_name'] ?></td>
                      <td><?= $data['description'] ?></td>
                      <td><?= $data['processing_situation'] ?></td>
                      <td><?= $data['remark'] ?></td>
                      <td><?= $data['current_situation'] ?></td>
                      <td><?= $data['hurt_people'] ?>, <?= $data['dead_people'] ?>, <?= $data['trapped_people'] ?></td>
                      <td><?= $data['return_time'] ? date('Y-m-d H:i:s', strtotime($data['return_time'])) : '' ?></td>
                      <td><?= $data['all_members'] ?></td>
                      <td><?= $data['management_time'] ? date('Y-m-d H:i:s', strtotime($data['management_time'])) : '' ?></td>
                      <td><?= $data['release_time'] ? date('Y-m-d H:i:s', strtotime($data['release_time'])) : '' ?></td>
                      <td><?= $data['update_time'] ? date('Y-m-d H:i:s', strtotime($data['update_time'])) : '' ?></td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th scope="col">出勤分隊</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($teams as $team) { ?>
                    <tr>
                      <th scope="row">
                        <a href="/disaster_report/routes/reports/index.php?topic_id=<?= $queryString['topic_id'] ?>&team_id=<?= $team['id'] ?>">
                          <?= $team['name'] ?>
                        </a>
                      </th>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

<?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_script.php'); ?>

</html>