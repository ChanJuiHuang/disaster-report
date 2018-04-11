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
  $conn->beginTransaction();
  $teamsStmt = $conn->prepare('SELECT id, name FROM teams ORDER BY id;');
  $teamsStmt->execute();
  $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);
  $corps = [array_slice($teams, 0, 8), array_slice($teams, 8, 9), array_slice($teams, 17, 8), array_slice($teams, 25, 10), array_slice($teams, 35, 1)];

  $teamsInfoStmt = $conn->prepare("SELECT team_id FROM teams_info WHERE topic_id = {$queryString['topic_id']};");
  $teamsInfoStmt->execute();
  $teamsInfo = $teamsInfoStmt->fetchAll(PDO::FETCH_ASSOC);
  $conn->commit();
} catch (Exception $e) {
  header("Location: /disaster_report/routes/auth/logout.php?fail={$e->getCode()}");
}

function isCheck($teamsInfo, $team) {
  foreach ($teamsInfo as $teamInfo) {
    if ($team['id'] === $teamInfo['team_id']) {
      return 'checked';
    }
  }
  return '';
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
          <div class="card-title card-header" style="font-size: 6vmin; text-align: center;">編輯出勤分隊</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-8 offset-md-2">
                <?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_messages.php'); ?>
                <form method="POST" action="/disaster_report/routes/topics/teams/update.php?topic_id=<?= $queryString['topic_id'] ?>">
                  <div>請選取查報分隊：</div>
                  <?php foreach ($corps as $teams) { ?>
                  <div class="form-group">
                    <div>
                      <a href="#" class="checkTeams">全選 | </a>
                      <a href="#" class="unCheckTeams">反選</a>
                    </div>
                    <?php foreach ($teams as $team) { ?>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="checkbox" style="zoom: 1.5" id="<?= $team['id'] ?>" name="teams[]" value="<?= $team['id'] ?>" <?= isCheck($teamsInfo, $team) ?>>
                      <label for="<?= $team['id'] ?>"><?= $team['name'] ?></label>
                    </div>
                    <?php } ?>
                  </div>
                  <?php } ?>

                  <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        送出
                    </button>
                  </div>
              </form>
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

<script src="/disaster_report/public/js/checkBox.js"></script>