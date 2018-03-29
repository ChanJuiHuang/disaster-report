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
  $teamsStmt = $conn->prepare('SELECT id, name FROM teams ORDER BY id;');
  $teamsStmt->execute();
  $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);
  $corps = [array_slice($teams, 0, 8), array_slice($teams, 8, 9), array_slice($teams, 17, 8), array_slice($teams, 25, 10), array_slice($teams, 35, 1)];
} catch (Exception $e) {
  header("Location: /disaster_report/routes/auth/logout.php?fail={$e->getCode()}");
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
          <div class="card-title card-header" style="font-size: 6vmin; text-align: center;">新增主題</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-8 offset-md-2">
                <?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_messages.php'); ?>
                <form method="POST" action="/disaster_report/routes/topics/store.php">
                  <div class="form-group">
                    <label for="topic">主題：</label>
                    <input id="topic" type="text" class="form-control" name="topic" required>
                  </div>

                  <div class="form-group">
                    <input id="type" type="hidden" class="form-control" name="type" value="<?= isset($queryString['type']) ? $queryString['type'] : '' ?>" required>
                  </div>

                  <div>請選取查報分隊：</div>
                  <?php foreach ($corps as $teams) { ?>
                  <div class="form-group">
                    <?php foreach ($teams as $team) { ?>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="checkbox" style="zoom: 1.5" id="<?= $team['id'] ?>" name="teams[]" value="<?= $team['id'] ?>">
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