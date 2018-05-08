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
  $placesStmt = $conn->prepare("SELECT name FROM places WHERE team_id = '{$_SESSION['team_NO']}'");
  $placesStmt->execute();
  $places = $placesStmt->fetchAll(PDO::FETCH_ASSOC);
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
          <div class="card-title card-header" style="font-size: 6vmin; text-align: center;">編輯查報地點</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-8 offset-md-2">
                <?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_messages.php'); ?>
                <form method="POST" action="/disaster_report/routes/places/update.php?type=<?= $queryString['type'] ?>">
                  <input type="hidden" class="form-control" name="csrfToken" value="<?= $_SESSION['csrfToken'] ?>">
                  <div class="form-group">
                    <label>地點:</label>
                    <div class="form-group">
                      <button type="button" id="add-row" class="btn btn-sm btn-success">新增地點欄位</button>
                      <button type="button" id="remove-row" class="btn btn-sm btn-danger">刪除地點欄位</button>
                    </div>
                  </div>

                  <?php foreach ($places as $place) { ?>
                  <div class="form-group place">
                    <input type="text" class="form-control" name="places[]" value="<?= $place['name'] ?>" required>
                  </div>
                  <?php }  ?>

                  <?php if (count($places) === 0) { ?>
                  <div class="form-group place">
                    <input type="text" class="form-control" name="places[]" required>
                  </div>
                  <?php } ?>

                  <div id="submit" class="form-group">
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
<script>
  $('#add-row').click(function (event) {
    var row = '<div class="form-group place"><input type="text" class="form-control" name="places[]" required></div>'
    $('#submit').before(row)
    event.preventDefault()
  })

  $('#remove-row').click(function (event) {
    $('form .place:nth-last-child(2)').remove()
    event.preventDefault()
  })

</script>