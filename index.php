<?php

require($_SERVER['DOCUMENT_ROOT'] . '/modules/csrfTokenModules.php');

$lifetime = 7200;
$path = '/';
$domain = '; samesite=lax;';
$secure = false;
$httpOnly = true;
session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);

session_start();
$setCsrfTokenToSession($generateRandStr);
$setCsrfTokenToCookie($_SESSION['csrfToken']);
session_write_close();

?>

<!DOCTYPE html>
<html lang="en">

<?php include($_SERVER['DOCUMENT_ROOT'] . '/public/views/partials/_header.php'); ?>

<body>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/public/views/partials/_navbar.php'); ?>
  <div class="container">
    <div class="row">
      <div class="col-md-4 offset-md-4">
          <div class="card">
              <!-- <div class="card-title card-header">登入</div> -->
              <div class="card-body">
                  <form method="POST" action="/routes/auth/login.php">

                      <div class="form-group">
                        <label for="account" class="">臂章號碼：</label>
                        <input id="account" type="text" class="form-control" name="account" >
                      </div>

                      <div class="form-group">
                        <label for="password">密碼：</label>
                        <input id="password" type="password" class="form-control" name="password" >
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

<script src="/public/bootstrap-v4.0.0/js/jquery-3.2.1.slim.min.js"></script>
<script src="/public/bootstrap-v4.0.0/js/popper.min.js"></script>
<script src="/public/bootstrap-v4.0.0/js/bootstrap.min.js"></script>
<script src="/public/js/index.js"></script>
</html>