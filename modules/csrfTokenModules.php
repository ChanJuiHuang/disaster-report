<?php

function generateRandStr ($length) {
  $alphanum = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $str = '';
  for ($i = 0; $i < $length; $i++) {
    $str .= $alphanum[rand(0, 61)];
  }

  return $str;
};

function generateSalt () {
    return $salt = '$2a$10$' . generateRandStr(22) . '$';
};

function setCsrfTokenToSession () {
  if (empty($_SESSION['csrfToken'])) {
    $_SESSION['csrfToken'] = generateRandStr(40);
  }
};

function setCsrfTokenToCookie ($csrfToken) {
  if (empty($_COOKIE['x_csrf_token'])) {
    $expire = time() + 7200;
    $path = '/';
    $domain = '; samesite=lax;';
    $secure = false;
    $httpOnly = true;
    setcookie('x_csrf_token', $csrfToken, $expire, $path, $domain, $secure, $httpOnly);
  }
};