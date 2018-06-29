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
  $teamsStmt = $conn->prepare("SELECT name FROM teams WHERE id='{$queryString['team_id']}'");
  $teamsStmt->execute();
  $team = $teamsStmt->fetch(PDO::FETCH_ASSOC);
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
          <div class="card-title card-header" style="font-size: 6vmin; text-align: center;"><?= $team['name'] ?>轄區災情資訊</div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-10 offset-md-1">
                <a href="/disaster_report/routes/reports/active_team_informations/edit.php?topic_id=<?= $queryString['topic_id'] ?>&team_id=<?= $queryString['team_id'] ?>">
                  <button type="button" class="btn btn-primary">編輯出勤資訊</button>
                </a>
                <a href="/disaster_report/routes/reports/disaster_status/edit.php?topic_id=<?= $queryString['topic_id'] ?>&team_id=<?= $queryString['team_id'] ?>">
                  <button type="button" class="btn btn-secondary">編輯災情狀況</button>
                </a>
                <a href="/disaster_report/routes/print/form.php?topic_id=<?= $queryString['topic_id'] ?>&team_id=<?= $queryString['team_id'] ?>">
                  <button type="button" class="btn btn-success">列印</button>
                </a>
              </div>
            </div>
            <div class="row">
              <div class="col-md-10 offset-md-1">
                <!-- <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th scope="col">災情地點</th>
                      <th scope="col">有無災情</th>
                      <th scope="col">有無影響交通</th>
                      <th scope="col">出勤人數</th>
                      <th scope="col">出勤車輛</th>
                      <th scope="col">回報災情時間</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                    </tr>
                  </tbody>
                </table> -->
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