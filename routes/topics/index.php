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
  if ($_SESSION['team_NO'] === '01') {
    $query = "SELECT id, name, created_at FROM topics WHERE type = ? ORDER BY created_at;";
  } else {
    $query = "SELECT topics.id, topics.name, topics.created_at FROM topics JOIN teams_info ON topics.id = teams_info.topic_id WHERE topics.type = ? AND teams_info.team_id = '{$_SESSION['team_NO']}' ORDER BY topics.created_at;";
  }
  $topicsStmt = $conn->prepare($query);
  $topicsStmt->execute([$queryString['type']]);
  $topics = $topicsStmt->fetchAll(PDO::FETCH_ASSOC);
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
          <div class="card-title card-header" style="font-size: 6vmin; text-align: center;">主題列表</div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-10 offset-md-1">
                <?php if ($_SESSION['is_center']) { ?>
                <a href="/disaster_report/routes/topics/create.php<?= isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '' ?>">
                  <button type="button" class="btn btn-secondary">新增主題</button>
                </a>
                <?php } ?>
                <a href="/disaster_report/routes/places/edit.php?type=<?= $queryString['type'] ?>">
                  <button type="button" class="btn btn-success">編輯查報地點</button>
                </a>
              </div>
            </div>
            <div class="row">
              <div class="col-md-10 offset-md-1">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th scope="col">主題名稱</th>
                      <th scope="col">建立時間</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($topics as $topic) { ?>
                    <tr>
                      <th scope="row">
                        <?php if ($_SESSION['is_center']) { ?>
                          <a href="/disaster_report/routes/topics/teams/show.php?topic_id=<?= $topic['id'] ?>">
                            <?= $topic['name'] ?>
                          </a>
                        <?php } else {?>
                          <a href="/disaster_report/routes/reports/index.php?topic_id=<?= $topic['id'] ?>&team_id=<?= $_SESSION['team_NO'] ?>">
                            <?= $topic['name'] ?>
                          </a>
                        <?php } ?>
                      </th>
                      <td><?= date('Y-m-d H:i:s', strtotime($topic['created_at'])) ?></td>
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