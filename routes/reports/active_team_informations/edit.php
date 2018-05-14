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
  // 取出人員名單
  // $conn = odbc_connect($odbc_dsn, $odbc_user, $odbc_password);
  // $membersStmt = odbc_prepare($conn, "SELECT MemName from MemberData WHERE UnitID = ? ORDER BY TurnNo;");
  // $result = odbc_execute($membersStmt, [$queryString['team_id']]);
  // while ($member = odbc_fetch_array($membersStmt)) {
  //   $members[]= mb_convert_encoding($member['MemName'], 'utf-8', 'big-5');
  // }
  
  $conn = new PDO($dsn, $db_user, $db_password);
  // 取出出勤車輛資訊
  $carsStmt = $conn->prepare("SELECT name FROM active_cars WHERE topic_id = '{$queryString['topic_id']}' AND team_id = '{$queryString['team_id']}'");
  $carsStmt->execute();
  $cars = $carsStmt->fetchAll(PDO::FETCH_ASSOC);

  // 取出出勤人員資訊
  $active_membersStmt = $conn->prepare("SELECT name FROM active_members WHERE topic_id = '{$queryString['topic_id']}' AND team_id = '{$queryString['team_id']}'");
  $active_membersStmt->execute();
  $active_members = $active_membersStmt->fetchAll(PDO::FETCH_ASSOC);
  
  // 取出出勤、返隊時間
  $active_team_informationsStmt = $conn->prepare("SELECT sending_time, return_time FROM active_team_informations WHERE topic_id = {$queryString['topic_id']} AND team_id = '{$queryString['team_id']}'");
  $active_team_informationsStmt->execute();
  $active_team_informations = $active_team_informationsStmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  header("Location: /disaster_report/routes/auth/logout.php?fail={$e->getCode()}");
}

function isCheck($active_members, $member)
{
  foreach ($active_members as $active_member) {
    if ($member === $active_member['name']) {
      return 'checked';
    }
  }
  return '';
}

function transformTime($time)
{
  if ($time === '0000-00-00 00:00:00') {
    return '';
  }
  return $time;
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
          <div class="card-title card-header" style="font-size: 6vmin; text-align: center;">編輯出勤資訊</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-8 offset-md-2">
                <?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_messages.php'); ?>
                <form method="POST" action="/disaster_report/routes/reports/active_team_informations/update.php?topic_id=<?= $queryString['topic_id'] ?>&team_id=<?= $queryString['team_id'] ?>">
                  <input type="hidden" class="form-control" name="csrfToken" value="<?= $_SESSION['csrfToken'] ?>">
                  <div class="form-group">
                    <label>出勤車輛:</label>
                    <div class="form-group">
                      <button type="button" id="add-row" class="btn btn-sm btn-success">新增車輛欄位</button>
                      <button type="button" id="remove-row" class="btn btn-sm btn-danger">刪除車輛欄位</button>
                    </div>
                  </div>
                  <?php foreach ($cars as $car) { ?>
                    <div class="form-group car">
                      <input type="text" class="form-control" name="cars[]" value="<?= $car['name'] ?>" required>
                    </div>
                  <?php } ?>
                  <?php if (count($cars) === 0) { ?>
                  <div class="form-group car">
                    <input type="text" class="form-control" name="cars[]" required>
                  </div>
                  <?php } ?>

                  <div id="active-members">
                    <label>出勤人員:</label>
                  </div>
                  <div class="form-group">
                    <!-- <?php foreach ($members as $member) { ?>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="checkbox" style="zoom: 1.5" id="<?= $member ?>" name="members[]" value="<?= $member ?>" <?= isCheck($active_members, $member) ?>>
                      <label for="<?= $member ?>"><?= $member ?></label>
                    </div>
                    <?php } ?> -->
                  </div>
                  <div class="form-group">
                    <label for="sending_time" class="sending_time">出勤時間:</label>
                    <div class="form-group">
                      <input id="sending_time" type="text" class="form-control" name="sending_time" value="<?= transformTime($active_team_informations['sending_time']) ?>">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="return_time" class="return_time">返隊時間:</label>
                    <div class="form-group">
                      <input id="return_time" type="text" class="form-control" name="return_time" value="<?= transformTime($active_team_informations['return_time']) ?>">
                    </div>
                  </div>

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

<script>
  $('#add-row').click(function (event) {
    var row = '<div class="form-group car"><input type="text" class="form-control" name="cars[]" required></div>'
    $('#active-members').before(row)
    event.preventDefault()
  })

  $('#remove-row').click(function (event) {
    $('form .car:nth-last-child(6)').remove()
    event.preventDefault()
  })
</script>