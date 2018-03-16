<?php

include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/config/db_config.php');

if (!(empty($_POST['account']) || empty($_POST['password']) || empty($_POST['captcha']))) {
  $account = null;
  $password = null;
  $captcha = null;
  extract($_POST, EXTR_IF_EXISTS);

  try {
    $conn = new PDO($dsn, $db_user, $db_password);
    $userStmt = $conn->prepare("SELECT MemName, MemID, UnitID from MemberData WHERE MemID=? AND Pwd=? AND PosID!='406';");
    $userStmt->execute([$account, $password]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    session_start();
    if (!$conn) {
      throw new Exception("Connection Error!", 0);
    }
    if (!(($userStmt->rowCount() === 1) && $user)) {
      throw new Exception("Account or Password is wrong!", 1);
    }
    if ($captcha !== $_SESSION['captcha']) {
      throw new Exception("Captcha is wrong!", 2);
    }
    if ($_COOKIE['x_csrf_token'] !== $_SESSION['csrfToken']) {
      throw new Exception("CSRF Token is wrong!", 3);
    }
    $_SESSION['name'] = $user['memname'];
    $_SESSION['account'] = $account;
    $_SESSION['is_login'] = true;
    $_SESSION['is_center'] = $user['unitid'] === '01' ? true : false;

    // $conn = odbc_connect($odbc_dsn, $odbc_user, $odbc_password);
    // $userStmt = odbc_prepare($conn, 'SELECT MemName, MemID, UnitID from MemberData WHERE MemID=? AND Pwd=?;');
    // $result = odbc_execute($userStmt, [$account, $password]);
    // $user = odbc_fetch_array($userStmt);

    // session_start();
    // if (!$conn) {
    //   throw new Exception("Connection Error!", 0);
    // }
    // if (!(odbc_num_rows($userStmt) === 1 && $userStmt && $result)) {
    //   throw new Exception("Account or Password is wrong!", 1);
    // }
    // if ($captcha !== $_SESSION['captcha']) {
    //   throw new Exception("Captcha is wrong!", 2);
    // }
    // if ($_COOKIE['x_csrf_token'] !== $_SESSION['csrfToken']) {
    //   throw new Exception("CSRF Token is wrong!", 3);
    // }
    // $_SESSION['name'] = $user['MemName'];
    // $_SESSION['account'] = $account;
    // $_SESSION['is_login'] = true;
    // $_SESSION['is_center'] = $user['UnitID'] === '01' ? true : false;
    header('Location: /disaster_report/public/views/mainMenu.php');
  } catch (Exception $e) {
    header('Location: /disaster_report/index.php?fail=' . $e->getCode());
  }
} else {
  header('Location: /disaster_report/index.php?fail=1');
}
session_write_close();