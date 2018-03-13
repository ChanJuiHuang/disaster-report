<?php

include($_SERVER['DOCUMENT_ROOT'] . '/config/db_config.php');

if (!(empty($_POST['account']) || empty($_POST['password']))) {
  $account = null;
  $password = null;
  extract($_POST, EXTR_IF_EXISTS);

  try {
    $conn = new PDO($dsn, $db_user, $db_password);
    $userStmt = $conn->prepare("SELECT MemName, MemID, UnitID from MemberData WHERE MemID=? AND Pwd=? AND PosID!='406';");
    $userStmt->execute([$account, $password]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    session_start();
    if (!$conn) {
      throw new Exception("Connection Error!");
    }
    if (!($userStmt->rowCount() === 1) && $user) {
      throw new Exception("Account or Password is wrong!");
    }
    if ($_COOKIE['x_csrf_token'] !== $_SESSION['csrfToken']) {
      throw new Exception("CSRF Token is wrong!");
    }

    // $conn = odbc_connect($odbc_dsn, $odbc_user, $odbc_password);
    // $userStmt = odbc_prepare($conn, 'SELECT MemName, MemID, UnitID from MemberData WHERE MemID=? AND Pwd=?;');
    // $result = odbc_execute($userStmt, [$account, $password]);
    // $user = odbc_fetch_array($userStmt);

    // session_start();
    // if (!$conn) {
    //   throw new Exception("Connection Error!");
    // }
    // if (!(odbc_num_rows($userStmt) === 1 && $userStmt && $result)) {
    //   throw new Exception("Account or Password is wrong!");
    // }
    // if ($_COOKIE['x_csrf_token'] !== $_SESSION['csrfToken']) {
    //   throw new Exception("CSRF Token is wrong!");
    // }
    $_SESSION['name'] = $user['MemName'];
    $_SESSION['account'] = $account;
    $_SESSION['is_login'] = true;
    $_SESSION['is_center'] = $user['UnitID'] === '01' ? true : false;
    session_write_close();
  } catch (Exception $e) {
    header('Location: /index.php?fail=0');
  }
  header('Location: /testLogin.php');
} else {
  header('Location: /index.php?fail=0');
}
