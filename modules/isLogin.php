<?php

$isLogin = function () {
  if (empty($_SESSION['is_login'])){
    return false;
  } else if ($_SESSION['is_login'] == true) {
    return true;
  }
  return false;
};
