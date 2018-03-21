<?php
  require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/session.php');
  require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/isLogin.php');

  $session();
  if (!$isLogin()) {
    header('Location: /disaster_report/index.php');
  }
?>

<!DOCTYPE html>
<html lang="en">

<?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_header.php'); ?>

<body>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_navbar.php'); ?>
  <div class="container">
    <div class="d-flex justify-content-center">
      <a href="/disaster_report/public/views/rainTopic.php">
        <button class="mainmenu__button m-1">豪大雨</br>災情查報</button>
      </a>
      <button class="mainmenu__button m-1">烏溪、濁水溪</br>災情查報</button>
    </div>
    <div class="d-flex justify-content-center">
      <button class="mainmenu__button m-1">地震</br>災情查報</button>
      <button class="mainmenu__button m-1">其他</br>災情查報</button>
    </div>
  </div>

</body>

<?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_script.php'); ?>

</html>