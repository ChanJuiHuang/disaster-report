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
  setCsrfTokenToSession();
  setCsrfTokenToCookie($_SESSION['csrfToken']);
};