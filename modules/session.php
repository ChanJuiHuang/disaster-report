<?php

require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/csrfTokenModules.php');

function session () {
  $lifetime = 7200;
  $path = '/';
  $domain = '; samesite=lax;';
  $secure = false;
  $httpOnly = true;
  session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
  session_start();
  $isset = setCsrfTokenToSession();
  setCsrfTokenToCookie($isset, $_SESSION['csrfToken']);
};

function session_flash ($key, $value) {
  if (empty($value)) {
    echo isset($_SESSION[$key]) ? $_SESSION[$key] : '';
    unset($_SESSION[$key]);
    $_SESSION['flash'] = false;
  } else {
    $_SESSION['flash'] = true;
    $_SESSION[$key] = $value;
  }
}