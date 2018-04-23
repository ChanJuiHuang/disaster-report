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
  $conn = new PDO($dsn, $db_user, $db_password);
  $topicStmt = $conn->prepare("SELECT name FROM topics WHERE id = {$queryString['topic_id']}");
  $topicStmt->execute();
  $topic = $topicStmt->fetch(PDO::FETCH_ASSOC);
  $teamsStmt = $conn->prepare("SELECT teams.name FROM teams_info JOIN teams ON teams_info.team_id = teams.id WHERE teams_info.topic_id = {$queryString['topic_id']}");
  $teamsStmt->execute();
  $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);
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
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($teams as $team) { ?>
                    <tr>
                      <th scope="row">
                        <a href="">
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