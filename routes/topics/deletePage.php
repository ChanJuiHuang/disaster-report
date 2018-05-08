<?php

require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/session.php');
require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/isLogin.php');
require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/config/db_config.php');
require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/csrfTokenModules.php');

session();
if (!$isLogin()) {
  header('Location: /disaster_report/index.php');
  return;
}

setCsrfTokenToSession();

if (isset($_SERVER['QUERY_STRING'])) {
  parse_str($_SERVER['QUERY_STRING'], $queryString);
}

try {
  $conn = new PDO($dsn, $db_user, $db_password);
  $topicStmt = $conn->prepare("SELECT name, type FROM topics WHERE id = {$queryString['topic_id']}");
  $topicStmt->execute();
  $topic = $topicStmt->fetch(PDO::FETCH_ASSOC);
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
          <div class="card-title card-header" style="font-size: 6vmin; text-align: center;">刪除主題</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-8 offset-md-2">
                <?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_messages.php'); ?>
                <form method="POST" action="/disaster_report/routes/topics/delete.php?topic_id=<?= $queryString['topic_id'] ?>&type=<?= $topic['type'] ?>">
                  <input type="hidden" class="form-control" name="csrfToken" value="<?= $_SESSION['csrfToken'] ?>">
                  <div class="form-group">
                    <h3 class="text-center"><?= $topic['name'] ?></h3>
                  </div>

                  <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        刪除
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