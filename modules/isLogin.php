<?php

$isLogin = function () {
  if (empty($_SESSION['is_login'])){
    return false;
  } else {
    return true;
  }
};
