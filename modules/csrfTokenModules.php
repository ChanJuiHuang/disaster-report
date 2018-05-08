<?php

function generateRandStr ($length) {
  $alphanum = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $str = '';
  for ($i = 0; $i < $length; $i++) {
    $str .= $alphanum[rand(0, 61)];
  }

  return $str;
};

function setCsrfTokenToSession () {
  $_SESSION['csrfToken'] = generateRandStr(40);
};
