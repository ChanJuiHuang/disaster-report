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
  // 取出統計資料
  $statisticDataStmt = $conn->prepare("SELECT processing_situation, remark, current_situation, management_time, release_time FROM place_status WHERE topic_id = '{$queryString['topic_id']}' AND team_id = '{$queryString['team_id']}' AND name = '{$queryString['name']}'");
  $statisticDataStmt->execute();
  $statisticData = $statisticDataStmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  header("Location: /disaster_report/routes/auth/logout.php?fail={$e->getCode()}");
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
          <div class="card-title card-header" style="font-size: 6vmin; text-align: center;">更新災情統計資料</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-8 offset-md-2">
                <?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_messages.php'); ?>
                <form method="POST" action="/disaster_report/routes/reports/statistics/update.php?topic_id=<?= $queryString['topic_id'] ?>&team_id=<?= $queryString['team_id'] ?>&name=<?= $queryString['name'] ?>">
                  <input type="hidden" class="form-control" name="csrfToken" value="<?= $_SESSION['csrfToken'] ?>">
                  <div class="form-group">
                    <label for="processing_situation">處理情形：</label>
                    <div>
                      <textarea rows="4" id="processing_situation" class="form-control" name="processing_situation"><?= $statisticData['processing_situation'] ?></textarea>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="remark">備註：</label>
                    <div>
                      <textarea rows="4" id="remark" class="form-control" name="remark"><?= $statisticData['remark'] ?></textarea>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="current_situation">目前狀況：</label>
                    <div>
                      <textarea rows="4" id="current_situation" class="form-control" name="current_situation"><?= $statisticData['current_situation'] ?></textarea>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="management_time" class="management_time">
                      列管時間：
                      <img src="/disaster_report/public/clock.png" alt="clock" width="20px">
                    </label>
                    <div class="form-group">
                      <input id="management_time" type="text" class="form-control" name="management_time" value="<?= $statisticData['management_time'] ?>">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="release_time" class="release_time">
                      解除時間：
                      <img src="/disaster_report/public/clock.png" alt="clock" width="20px">
                    </label>
                    <div class="form-group">
                      <input id="release_time" type="text" class="form-control" name="release_time" value="<?= $statisticData['release_time'] ?>">
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
  $('.management_time').click(function () {
    $('#management_time').val(getDate())
  })

  $('.release_time').click(function () {
    $('#release_time').val(getDate())
  })

  function getDate() {
    let date = new Date()
    let y = date.getFullYear()
    let m = date.getMonth()+1
    let d = date.getDate()
    let h = date.getHours()
    let min = date.getMinutes()
    let s = date.getSeconds()
    return `${y}-${m}-${d} ${h}:${min}:${s}`
  }
</script>