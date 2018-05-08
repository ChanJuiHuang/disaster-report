<?php

require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/session.php');
require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/isLogin.php');
require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/csrfTokenModules.php');

session();

if ($isLogin()) {
  header('Location: /disaster_report/public/views/mainMenu.php');
  return;
}

setCsrfTokenToSession();

?>

<!DOCTYPE html>
<html lang="en">

<?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_header.php'); ?>

<body>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_navbar.php'); ?>
  <div class="container">
    <div class="row">
      <div class="col-md-6 offset-md-3">
          <div class="card">
              <div class="card-title card-header" style="font-size: 6vmin; text-align: center;">登入</div>
              <div class="card-body">
                  <?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_messages.php'); ?>
                  <form method="POST" action="/disaster_report/routes/auth/login.php">
                      <input type="hidden" class="form-control" name="csrfToken" value="<?= $_SESSION['csrfToken'] ?>">
                      <div class="form-group">
                        <label for="account">臂章號碼：</label>
                        <input id="account" type="text" class="form-control" name="account" required>
                      </div>

                      <div class="form-group">
                        <label for="password">密碼：</label>
                        <input id="password" type="password" class="form-control" name="password" required>
                      </div>

                      <div class="form-group">
						<label for="captcha">驗證碼：</label>
						<p>
                            <input type="text" name="captcha" id="captcha" class="form-control" required>
						    <img src="/disaster_report/modules/showCaptchaImg.php" id="captcha-img">
                            <a href="#" id="reload-captcha" style="font-size: 12px">重新產生</a>
                        </p>
                      </div>
                      
                      <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            登入
                        </button>
                      </div>
                  </form>
              </div>
          </div>
      </div>
  </div>
  </div>
</body>

<?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_script.php'); ?>
<script src="/disaster_report/public/js/queryString.js"></script>
<script src="/disaster_report/public/js/reloadCaptchaImg.js"></script>

</html>