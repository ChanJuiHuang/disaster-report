<?php

$createUserSession = function () {
  $lifetime = 7200;
  $path = '/';
  $domain = '; samesite=lax;';
  $secure = false;
  $httpOnly = true;
  session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
};