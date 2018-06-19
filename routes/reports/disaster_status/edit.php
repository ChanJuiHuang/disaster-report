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
  // 取得查報地點
  $placesStmt = $conn->prepare("SELECT id, name FROM places WHERE team_id = '{$queryString['team_id']}'");
  $placesStmt->execute();
  $places = $placesStmt->fetchAll(PDO::FETCH_ASSOC);

  // 取得place_status data
  $placeStatusStmt = $conn->prepare("SELECT id, is_happened_disaster, is_effect_traffic, return_time FROM place_status WHERE topic_id={$queryString['topic_id']} AND team_id = '{$queryString['team_id']}'");
  $placeStatusStmt->execute();
  $placeStatus = $placeStatusStmt->fetchAll(PDO::FETCH_ASSOC);

  // 取得place_disasters data
  $placeStatusIds = [];
  foreach ($placeStatus as $ps) {
    $placeStatusIds[] = $ps['id'];
  }
  $placeDisastersStmt = $conn->prepare("SELECT name, isother, other_disaster_name, place_status_id FROM place_disasters WHERE place_status_id IN (". join(',', $placeStatusIds) . ") ORDER BY place_status_id");
  $placeDisastersStmt->execute();
  $placeDisasters = $placeDisastersStmt->fetchAll(PDO::FETCH_ASSOC);

  // 取得disaster_status data
  $disasterStatusStmt = $conn->prepare("SELECT city, address, description, hurt_people, dead_people, trapped_people, place_status_id FROM disaster_status WHERE place_status_id IN (" . join(',', $placeStatusIds) . ") ORDER BY place_status_id");
  $disasterStatusStmt->execute();
  $disasterStatus = $disasterStatusStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  header("Location: /disaster_report/routes/auth/logout.php?fail={$e->getCode()}");
}

function isCheckStatus($placeStatus, $status, $happened)
{
  if (!($placeStatus[$status] ^ $happened)) {
    return 'checked';
  }
  return '';
}

function isCheckDisaster($placeDisasters, $disaster, $id)
{
  foreach ($placeDisasters as $placeDisaster) {
    if ($placeDisaster['place_status_id'] === $id && $placeDisaster['name'] === $disaster) {
      return 'checked';
    }
  }
  return '';
}

function getOtherDisaster($placeDisasters, $id)
{
  foreach ($placeDisasters as $placeDisaster) {
    if ($placeDisaster['place_status_id'] === $id && $placeDisaster['name'] === "other") {
      return $placeDisaster['other_disaster_name'];
    }
  }
  return '';
}

function getDisasterStatus($disasterStatus, $status, $id)
{
  foreach ($disasterStatus as $ds) {
    if ($ds['place_status_id'] === $id) {
      return $ds[$status];
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
          <div class="card-title card-header" style="font-size: 6vmin; text-align: center;">編輯災情狀況</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-8 offset-md-2">
                <?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_messages.php'); ?>
                <form method="POST" action="/disaster_report/routes/reports/disaster_status/update.php?topic_id=<?= $queryString['topic_id'] ?>&team_id=<?= $queryString['team_id'] ?>">
                  <input type="hidden" class="form-control" name="csrfToken" value="<?= $_SESSION['csrfToken'] ?>">
                  <?php foreach ($places as $index=>$place) { ?>
                  <div class="form-group">
                    <h5><label><?= $place['name'] ?>:</label></h5>
                    <input type="hidden" name="places[<?= $index ?>]" value="<?= $place['name'] ?>" required>
                    <div>
                      有無發生災情：
                      <input type="radio" name="isHappenedDisaster[<?= $index ?>]" value="0" onclick="hiddenPlaceDisasters(<?= $index ?>); removeData(<?= count($places) ?>)" <?= $placeStatus ? isCheckStatus($placeStatus[$index], 'is_happened_disaster', 0) : '' ?>> 無
                      <input id="happened_disaster_<?= $index ?>" type="radio" name="isHappenedDisaster[<?= $index ?>]" value="1" onclick="showPlaceDisasters(<?= $index ?>)" <?= $placeStatus ? isCheckStatus($placeStatus[$index], 'is_happened_disaster', 1) : ''?>> 有
                    </div>
                    <div>
                      有無影響交通：
                      <input type="radio" name="isEffectTraffic[<?= $index ?>]" value="0" <?= $placeStatus ? isCheckStatus($placeStatus[$index], 'is_effect_traffic', 0) : '' ?>> 無
                      <input type="radio" name="isEffectTraffic[<?= $index ?>]" value="1" <?= $placeStatus ? isCheckStatus($placeStatus[$index], 'is_effect_traffic', 1) : '' ?>> 有
                    </div>
                    <div class="return_time">
                      回報災情時間：
                      <input type="text" name="return_times[<?= $index ?>]" value="<?= $placeStatus ? $placeStatus[$index]['return_time'] : '' ?>" size="17">
                      <img src="/disaster_report/public/clock.png" alt="clock" width="24px">
                    </div>
                  </div>

                  <div class="form-group" id="place_disasters_<?= $index ?>" style="display: none">
                    <label>地點狀況：</label>
                    <div>
                      <input type="checkbox" name="disasters[<?= $index ?>][]" value="路樹災情" <?= $placeStatus ? isCheckDisaster($placeDisasters, "路樹災情", $placeStatus[$index]['id']) : '' ?>> 路樹災情
                      <input type="checkbox" name="disasters[<?= $index ?>][]" value="廣告招牌災情" <?= $placeStatus ? isCheckDisaster($placeDisasters, "廣告招牌災情", $placeStatus[$index]['id']) : '' ?>> 廣告招牌災情
                    </div>
                    <div>
                      <input type="checkbox" name="disasters[<?= $index ?>][]" value="道路、隧道災情" <?= $placeStatus ? isCheckDisaster($placeDisasters, "道路、隧道災情", $placeStatus[$index]['id']) : '' ?>> 道路、隧道災情
                      <input type="checkbox" name="disasters[<?= $index ?>][]" value="橋樑災情" <?= $placeStatus ? isCheckDisaster($placeDisasters, "橋樑災情", $placeStatus[$index]['id']) : '' ?>> 橋樑災情
                    </div>
                    <div>
                      <input type="checkbox" name="disasters[<?= $index ?>][]" value="鐵路、高鐵災情" <?= $placeStatus ? isCheckDisaster($placeDisasters, "鐵路、高鐵災情", $placeStatus[$index]['id']) : '' ?>> 鐵路、高鐵災情
                      <input type="checkbox" name="disasters[<?= $index ?>][]" value="積淹水災情" <?= $placeStatus ? isCheckDisaster($placeDisasters, "積淹水災情", $placeStatus[$index]['id']) : '' ?>> 積淹水災情
                    </div>
                    <div>
                      <input type="checkbox" name="disasters[<?= $index ?>][]" value="土石災情" <?= $placeStatus ? isCheckDisaster($placeDisasters, "土石災情", $placeStatus[$index]['id']) : '' ?>> 土石災情
                      <input type="checkbox" name="disasters[<?= $index ?>][]" value="建物毀損" <?= $placeStatus ? isCheckDisaster($placeDisasters, "建物毀損", $placeStatus[$index]['id']) : '' ?>> 建物毀損
                    </div>
                    <div>
                      <input type="checkbox" name="disasters[<?= $index ?>][]" value="水利設施災害" <?= $placeStatus ? isCheckDisaster($placeDisasters, "水利設施災害", $placeStatus[$index]['id']) : '' ?>> 水利設施災害
                      <input type="checkbox" name="disasters[<?= $index ?>][]" value="民生、基礎設施災情" <?= $placeStatus ? isCheckDisaster($placeDisasters, "民生、基礎設施災情", $placeStatus[$index]['id']) : '' ?>> 民生、基礎設施災情
                    </div>
                    <div>
                      <input type="checkbox" name="disasters[<?= $index ?>][]" value="車輛及交通事故災情" <?= $placeStatus ? isCheckDisaster($placeDisasters, "車輛及交通事故災情", $placeStatus[$index]['id']) : '' ?>> 車輛及交通事故災情
                      <input type="checkbox" name="disasters[<?= $index ?>][]" value="火災" <?= $placeStatus ? isCheckDisaster($placeDisasters, "火災", $placeStatus[$index]['id']) : '' ?>> 火災
                    </div>
                    <div>
                      <input type="checkbox" name="disasters[<?= $index ?>][]" value="other" <?= $placeStatus ? isCheckDisaster($placeDisasters, "other", $placeStatus[$index]['id']) : '' ?>> 其他災害情形:
                      <input type="text" name="other_disaster[<?= $index ?>]" value="<?= $placeStatus ? getOtherDisaster($placeDisasters, $placeStatus[$index]['id']) : '' ?>">
                    </div>
                  </div>

                  <div class="form-group" id="disaster_status_<?= $index ?>" style="display: none">
                    <label>災情概述：</label>
                    <div>
                      <label for="cities">發生鄉/鎮/市：</label>
                      <input type="text" id="cities" name="cities[<?= $index ?>]" value="<?= $placeStatus ? getDisasterStatus($disasterStatus, "city", $placeStatus[$index]['id']) : '' ?>">
                    </div>
                    <div>
                      <label for="happened_places">發生地點：</label>
                      <input type="text" id="happened_places" name="happened_places[<?= $index ?>]" value="<?= $placeStatus ? getDisasterStatus($disasterStatus, "address", $placeStatus[$index]['id']) : '' ?>">
                    </div>

                    <label for="descriptions">災情概述：</label>
                    <div>
                      <textarea rows="4" cols="50" id="descriptions" name="descriptions[<?= $index ?>]"><?= $placeStatus ? getDisasterStatus($disasterStatus, "description", $placeStatus[$index]['id']) : '' ?></textarea>
                    </div>

                    <div>
                      <label for="hurt_people">受傷人數：</label>
                      <input type="text" id="hurt_people" name="hurt_people[<?= $index ?>]" value="<?= $placeStatus ? getDisasterStatus($disasterStatus, "hurt_people", $placeStatus[$index]['id']) : '' ?>">
                    </div>
                    <div>
                      <label for="dead_people">死亡人數：</label>
                      <input type="text" id="dead_people" name="dead_people[<?= $index ?>]" value="<?= $placeStatus ? getDisasterStatus($disasterStatus, "dead_people", $placeStatus[$index]['id']) : '' ?>">
                    </div>
                    <div>
                      <label for="trapped_people">受困人數：</label>
                      <input type="text" id="trapped_people" name="trapped_people[<?= $index ?>]" value="<?= $placeStatus ? getDisasterStatus($disasterStatus, "trapped_people", $placeStatus[$index]['id']) : '' ?>">
                    </div>
                  </div>
                  <hr>
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

<script>
  $('.return_time').click(function () {
    $(this).children('input').val(getDate())
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

  function hiddenPlaceDisasters(index) {
    $(`#place_disasters_${index}`).hide()
    $(`#disaster_status_${index}`).hide()
  }

  function showPlaceDisasters(index) {
    $(`#place_disasters_${index}`).show()
    $(`#disaster_status_${index}`).show()
  }
  
  function checkHappenedDisaster(disasterCount) {
    for (let i = 0; i < disasterCount; i++) {
      if ($(`#happened_disaster_${i}`).prop('checked')) {
        showPlaceDisasters(i)
      }
    }
  }

  function removeData(disasterCount) {
    for (let i = 0; i < disasterCount; i++) {
      $(`#place_disasters_${i} input`).prop('checked', false)
      $(`#place_disasters_${i} div:nth-last-child(1) input:nth-last-child(1)`).val('')
      $(`#disaster_status_${i} input`).val('')
    }
  }

  checkHappenedDisaster(<?= count($places) ?>)
</script>