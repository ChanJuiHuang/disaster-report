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
  // 取得分隊名稱
  $teamsStmt = $conn->prepare("SELECT id, name FROM teams WHERE id='{$queryString['team_id']}';");
  $teamsStmt->execute();
  $team = $teamsStmt->fetch(PDO::FETCH_ASSOC);

  // 取得出勤資訊
  $activeTeamInfoStmt = $conn->prepare("SELECT people_count, car_count, sending_time FROM active_team_informations WHERE topic_id={$queryString['topic_id']} AND team_id='{$queryString['team_id']}'");
  $activeTeamInfoStmt->execute();
  $activeTeamInfo = $activeTeamInfoStmt->fetch(PDO::FETCH_ASSOC);

  // 取得place_status data
  $placeStatusStmt = $conn->prepare("SELECT id, name, is_happened_disaster, is_effect_traffic, return_time FROM place_status WHERE topic_id={$queryString['topic_id']} AND team_id = '{$queryString['team_id']}' ORDER BY id");
  $placeStatusStmt->execute();
  $placeStatus = $placeStatusStmt->fetchAll(PDO::FETCH_ASSOC);
  $placeIds = [];
  foreach ($placeStatus as $index=>$place) {
    $placeIds[$place['id']] = $index+1;
  }

  // 取得place_disasters data
  $placeStatusIds = [];
  foreach ($placeStatus as $place) {
    $placeStatusIds[] = $place['id'];
  }
  $placeDisastersStmt = $conn->prepare("SELECT name, isother, other_disaster_name, place_status_id FROM place_disasters WHERE place_status_id IN (" . join(',', $placeStatusIds) . ") ORDER BY place_status_id");
  $placeDisastersStmt->execute();
  $placeDisasters = $placeDisastersStmt->fetchAll(PDO::FETCH_ASSOC);

  // 取得disaster_status data
  $disasterStatusStmt = $conn->prepare("SELECT city, address, description, hurt_people, dead_people, trapped_people, place_status_id FROM disaster_status WHERE place_status_id IN (" . join(',', $placeStatusIds) . ") ORDER BY place_status_id");
  $disasterStatusStmt->execute();
  $disasterStatus = $disasterStatusStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  header("Location: /disaster_report/routes/auth/logout.php?fail={$e->getCode()}");
}

function isHappenedDisaster($isHappened)
{
  if ($isHappened) {
    return '■ 有 □ 無發生災情';
  } else {
    return '□ 有 ■ 無發生災情';
  }
}

function isEffectTraffic($isHappened)
{
  if ($isHappened) {
    return '■ 有 □ 無影響交通';
  } else {
    return '□ 有 ■ 無影響交通';
  }
}

function getMasterTeamName($teamId)
{
  $masterTeams = ['A' => '第一大隊', 'B' => '第二大隊', 'C' => '第三大隊', 'D' => '第四大隊', 'E' => '第五大隊'];
  foreach ($masterTeams as $key => $masterTeam) {
    if (strpos($teamId, $key) === 0) {
      return $masterTeam;
    }
  }
}

function getTeamName($team)
{
  $masterTeams = ['A0', 'B0', 'C0', 'D0', 'E0'];
  foreach ($masterTeams as $masterTeamId) {
    if ($team['id'] === $masterTeamId) {
      return '';
    }
  }
  return $team['name'] . '分隊';
}

function selectDisaster($disaster, $placeStatusId, $placeDisasters)
{
  foreach ($placeDisasters as $placeDisaster) {
    if ($placeStatusId === $placeDisaster['place_status_id'] && $disaster === $placeDisaster['name']) {
      return '■';
    }
  }
  return '□';
}

function showOtherDisasterName($disaster, $placeStatusId, $placeDisasters)
{
  foreach ($placeDisasters as $placeDisaster) {
    if ($placeStatusId === $placeDisaster['place_status_id'] && $disaster === $placeDisaster['name']) {
      return $placeDisaster['other_disaster_name'];
    }
  }
  return '___________';
}
?>

<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Disaster Report</title>
</head>

<body>
  <div style="margin-left: 35px">
    <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
      <span style="background-color:#d8d8d8; font-family:標楷體; font-size:22pt; font-weight:bold">表一</span>
      <span style="font-family:標楷體; font-size:18pt; text-decoration:underline"><?= getMasterTeamName($team['id']) ?></span>
      <span style="font-family:標楷體; font-size:18pt; text-decoration:underline"><?= getTeamName($team) ?></span>
      <span style="font-family:標楷體; font-size:18pt">災情查報表</span>
    </p>
    <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
      <span style="font-family:標楷體; font-size:15pt">查報時間：</span>
      <span style="font-family:標楷體; font-size:15pt"><?= date('n', strtotime($activeTeamInfo['sending_time'])) ?> 月</span>
      <span style="font-family:標楷體; font-size:15pt"><?= date('d', strtotime($activeTeamInfo['sending_time'])) ?> 日</span>
      <span style="font-family:標楷體; font-size:15pt"><?= date('H', strtotime($activeTeamInfo['sending_time'])) ?> 時</span>
      <span style="font-family:標楷體; font-size:15pt"><?= date('i', strtotime($activeTeamInfo['sending_time'])) ?> 分, </span>
      <span style="font-family:標楷體; font-size:15pt"><?= $activeTeamInfo['car_count'] ?>車次, </span>
      <span style="font-family:標楷體; font-size:15pt"><?= $activeTeamInfo['people_count'] ?>人次, </span>
      <span style="font-family:標楷體; font-size:15pt">回傳人：</span>
    </p>
    <table cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin-left:0pt">
      <tr>
        <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:6pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:6pt; padding-left:2.4pt; padding-right:5.03pt; vertical-align:top; width:42.6pt">
          <p style="line-height:23pt; margin:0pt; orphans:0; text-align:center; widows:0">
            <span style="font-family:標楷體; font-size:18pt">編號</span>
          </p>
        </td>
        <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:6pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:115.2pt">
          <p style="line-height:23pt; margin:0pt; orphans:0; text-align:center; widows:0">
            <span style="font-family:標楷體; font-size:18pt">地點</span>
          </p>
        </td>
        <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:6pt; border-top-color:#000000; border-top-style:solid; border-top-width:6pt; padding-left:5.03pt; padding-right:2.4pt; vertical-align:top; width:294.5pt">
          <p style="line-height:23pt; margin:0pt; orphans:0; text-align:center; widows:0">
            <span style="font-family:標楷體; font-size:18pt">狀況</span>
          </p>
        </td>
      </tr>
      <?php foreach ($placeStatus as $index=>$place) { ?>
      <tr>
        <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:6pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:2.4pt; padding-right:5.03pt; vertical-align:middle; width:42.6pt">
          <p style="line-height:20pt; margin:0pt; orphans:0; text-align:center; widows:0">
            <span style="font-family:標楷體; font-size:18pt"><?= $index+1 ?></span>
          </p>
        </td>
        <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:middle; width:115.2pt">
          <p style="line-height:12pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:12pt"><?= $place['name'] ?></span>
          </p>
        </td>
        <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:6pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:2.4pt; vertical-align:middle; width:294.5pt">
          <p style="line-height:20pt; margin:0pt; orphans:0; text-align:justify; widows:0">
            <span style="font-family:標楷體; font-size:15pt"><?= isHappenedDisaster($place['is_happened_disaster']) ?></span>
          </p>
          <p style="line-height:20pt; margin:0pt; orphans:0; text-align:justify; widows:0">
            <span style="font-family:標楷體; font-size:15pt"><?= isEffectTraffic($place['is_effect_traffic']) ?></span>
          </p>
          <p style="line-height:20pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:15pt">回報災情時間：<?= $place['return_time'] ?></span>
          </p>
        </td>
      </tr>
      <?php } ?>

      <tr style="height:138.9pt">
        <td colspan="3" style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:6pt; border-left-color:#000000; border-left-style:solid; border-left-width:6pt; border-right-color:#000000; border-right-style:solid; border-right-width:6pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:2.4pt; padding-right:2.4pt; vertical-align:top; width:473.9pt">
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:14pt">備註:</span>
          </p>
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:14pt">一、請各分隊事先就轄內歷史災害潛勢地點、低窪地區、鄰近重要道路地區，</span>
          </p>
          <p style="line-height:23pt; margin:0pt 0pt 0pt 30pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:14pt">綜合考量擇定五處以上之災情查報地點，並視需要更新。</span>
          </p>
          <p style="line-height:20pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:14pt">二、各分隊請於4時出勤前往上述擇定地點災情查報，於4時45分前送各大</span>
          </p>
          <p style="line-height:20pt; margin:0pt 0pt 0pt 30pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:14pt">隊彙整，各大隊5時前以資料交換櫃送本局指揮科執勤官彙整後，送局長提供縣府作為是否宣布停止辦公上課之參考。</span>
          </p>
          <p style="line-height:20pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:14pt">&#xa0;</span>
          </p>
        </td>
      </tr>
      <tr style="height:0pt">
        <td style="width:53.4pt; border:none"></td>
        <td style="width:126pt; border:none"></td>
        <td style="width:305.3pt; border:none"></td>
      </tr>
    </table>
    <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
      <span style="font-family:標楷體; font-size:15pt">&#xa0;</span>
    </p>
  </div>

  <p style="page-break-after:always"></p>

  <?php foreach ($disasterStatus as $disaster) { ?>
  <div style="margin-left: 35px">
    <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
      <span style="background-color:#d8d8d8; font-family:標楷體; font-size:22pt; font-weight:bold">表二</span>
      <span style="font-family:標楷體; font-size:22pt; font-weight:bold">(本表僅於有災情時回報)</span>
    </p>
    <table cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin-left:0pt">
      <tr>
        <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:6pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:6pt; padding-left:2.4pt; padding-right:5.03pt; vertical-align:top; width:198.6pt">
          <p style="line-height:23pt; margin:0pt; orphans:0; text-align:center; widows:0">
            <span style="font-family:標楷體; font-size:15pt">編號 <?= $placeIds[$disaster['place_status_id']] ?> 地點狀況</span>
          </p>
        </td>
        <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:6pt; border-top-color:#000000; border-top-style:solid; border-top-width:6pt; padding-left:5.03pt; padding-right:2.4pt; vertical-align:top; width:264.5pt">
          <p style="line-height:23pt; margin:0pt; orphans:0; text-align:center; widows:0">
            <span style="font-family:標楷體; font-size:14pt"><?= $disaster['city'] ?></span>
            <span style="font-family:標楷體; font-size:14pt">災情描述內容</span>
          </p>
        </td>
      </tr>
      <tr>
        <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:6pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:2.4pt; padding-right:5.03pt; vertical-align:top; width:198.6pt">
          <p style="margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:14pt"><?= selectDisaster("路樹災情", $disaster['place_status_id'], $placeDisasters) ?>路樹災情</span>
            <span style="font-family:標楷體; font-size:14pt"><?= selectDisaster("廣告招牌災情", $disaster['place_status_id'], $placeDisasters) ?>廣告招牌災情</span>
          </p>
          <p style="margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:14pt"><?= selectDisaster("道路、隧道災情", $disaster['place_status_id'], $placeDisasters) ?>道路、隧道災情</span>
            <span style="font-family:標楷體; font-size:14pt"><?= selectDisaster("橋梁災情", $disaster['place_status_id'], $placeDisasters) ?>橋梁災情</span>
          </p>
          <p style="margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:14pt"><?= selectDisaster("鐵路、高鐵災情", $disaster['place_status_id'], $placeDisasters) ?>鐵路、高鐵災情</span>
            <span style="font-family:標楷體; font-size:14pt"><?= selectDisaster("積淹水災情", $disaster['place_status_id'], $placeDisasters) ?>積淹水災情</span>
          </p>
          <p style="margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:14pt"><?= selectDisaster("土石災情", $disaster['place_status_id'], $placeDisasters) ?>土石災情</span>
            <span style="font-family:標楷體; font-size:14pt"><?= selectDisaster("建物毀損", $disaster['place_status_id'], $placeDisasters) ?>建物毀損</span>
          </p>
          <p style="margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:14pt"><?= selectDisaster("水利設施災害", $disaster['place_status_id'], $placeDisasters) ?>水利設施災害</span>
          </p>
          <p style="margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:14pt"><?= selectDisaster("民生、基礎設施災情", $disaster['place_status_id'], $placeDisasters) ?>民生、基礎設施災情</span>
          </p>
          <p style="margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:14pt"><?= selectDisaster("車輛及交通事故災情", $disaster['place_status_id'], $placeDisasters) ?>車輛及交通事故災情</span>
            <span style="font-family:標楷體; font-size:14pt"><?= selectDisaster("火災", $disaster['place_status_id'], $placeDisasters) ?>火災</span>
          </p>
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:14pt"><?= selectDisaster("other", $disaster['place_status_id'], $placeDisasters) ?>其他災害情形：</span>
            <!-- <span style="font-family:標楷體; font-size:14pt">：</span> -->
            <span style="font-family:標楷體; font-size:14pt"><?= showOtherDisasterName("other", $disaster['place_status_id'], $placeDisasters) ?></span>
          </p>
        </td>
        <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:6pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:2.4pt; vertical-align:top; width:264.5pt">
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:15pt">一、地點：</span>
            <span style="font-family:標楷體; font-size:15pt"><?= $disaster['address'] ?></span>
          </p>
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:15pt">&#xa0;</span>
          </p>
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:15pt">二、災情概述(人、事、時、地、物簡單明確敘述清楚)：</span>
            <span style="font-family:標楷體; font-size:15pt"><?= $disaster['description'] ?></span>
          </p>
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:15pt">&#xa0;</span>
          </p>
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <?php if (isset($disaster['hurt_people'])) { ?>
              <span style="font-family:標楷體; font-size:14pt">三、現場造成 <?= $disaster['hurt_people'] ?> 人受傷</span>
            <?php } else { ?>
              <span style="font-family:標楷體; font-size:14pt">三、現場造成&#xa0;&#xa0;&#xa0;&#xa0;人受傷</span>
            <?php } ?>
          </p>
          <p style="line-height:23pt; margin:0pt 0pt 0pt 84.6pt; orphans:0; widows:0">
            <?php if (isset($disaster['dead_people'])) { ?>
              <span style="font-family:標楷體; font-size:14pt">&#xa0;<?= $disaster['dead_people'] ?> 人死亡</span>
            <?php } else { ?>
              <span style="font-family:標楷體; font-size:14pt">&#xa0;&#xa0;&#xa0;&#xa0;人死亡</span>
            <?php } ?>
          </p>
          <p style="line-height:23pt; margin:0pt 0pt 0pt 84.6pt; orphans:0; widows:0">
            <?php if (isset($disaster['trapped_people'])) { ?>
              <span style="font-family:標楷體; font-size:14pt">&#xa0;<?= $disaster['trapped_people'] ?> 人受困</span>
            <?php } else { ?>
              <span style="font-family:標楷體; font-size:14pt">&#xa0;&#xa0;&#xa0;&#xa0;人受困</span>
            <?php } ?>
          </p>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:6pt; border-left-color:#000000; border-left-style:solid; border-left-width:6pt; border-right-color:#000000; border-right-style:solid; border-right-width:6pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:2.4pt; padding-right:2.4pt; vertical-align:top; width:473.9pt">
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:15pt">回報流程</span>
            <span style="font-family:標楷體; font-size:15pt">：</span>
          </p>
          <p style="line-height:23pt; margin:0pt 0pt 0pt 4.8pt; orphans:0; padding-left:19.2pt; text-indent:-19.2pt; widows:0">
            <span style="font-family:標楷體; font-size:15pt">一、請各分隊於</span>
            <span style="background-color:#d8d8d8; border-color:#000000; border-style:solid; border-width:0.75pt; font-size:16pt">
              <span style="font-family:標楷體; font-size:15pt; font-weight:bold">T(指揮中心派遣出勤查報時間)+30分</span>
            </span>
            <span style="font-family:標楷體; font-size:15pt">前將</span>
            <span style="background-color:#d8d8d8; border-color:#000000; border-style:solid; border-width:0.75pt; font-family:標楷體; font-size:15pt; font-weight:bold">表一(有災情時連同表二)</span>
            <span style="font-family:標楷體; font-size:15pt">回報各所屬大隊，請各大隊於</span>
            <span style="background-color:#d8d8d8; border-color:#000000; border-style:solid; border-width:0.75pt; font-family:標楷體; font-size:15pt; font-weight:bold">T+45分</span>
            <span style="font-family:標楷體; font-size:15pt">前將資料彙整回報指揮科值勤官。</span>
          </p>
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:15pt">二、各大隊暨指揮中心傳真電話：</span>
          </p>
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:15pt">第一大隊：7264807</span>
          </p>
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:15pt">第二大隊：8378521、8379119</span>
          </p>
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:15pt">第三大隊：7740119</span>
          </p>
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:15pt">第四大隊：8910119</span>
          </p>
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:15pt">指揮中心：7631914</span>
          </p>
          <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
            <span style="font-family:標楷體; font-size:15pt">&#xa0;</span>
          </p>
        </td>
      </tr>
      <tr style="height:0pt">
        <td style="width:209.4pt; border:none"></td>
        <td style="width:275.3pt; border:none"></td>
      </tr>
    </table>
    <p style="line-height:23pt; margin:0pt; orphans:0; widows:0">
      <span style="font-family:標楷體; font-size:15pt">&#xa0;</span>
    </p>
  </div>
  <p style="page-break-after:always"></p>
  <?php } ?>
</body>
</html>