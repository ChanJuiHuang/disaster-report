<?php

require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/session.php');

$session();
session_unset();
session_destroy();

foreach ($_COOKIE as $name => $value) {
  setcookie($name, null, -1, '/');
}

if (isset($_SERVER['QUERY_STRING'])) {
  parse_str($_SERVER['QUERY_STRING'], $queryString);
}

if ($queryString['fail']) {
  $fail = $queryString['fail'];
} else {
  $fail = '';
}

header("Location: /disaster_report/index.php?fail={$fail}");