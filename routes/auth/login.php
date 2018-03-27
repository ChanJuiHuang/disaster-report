<?php

require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/config/db_config.php');
require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/session.php');
require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/isLogin.php');

session();

if ($isLogin()) {
  header('Location: /disaster_report/public/views/mainMenu.php');
}

if (empty($_POST['account']) || empty($_POST['password'])) {
  header('Location: /disaster_report/index.php');
  session_flash('error', '您的帳號或密碼輸入錯誤！');
} elseif (empty($_POST['captcha'])) {
  header('Location: /disaster_report/index.php');
  session_flash('error', '您的驗證碼輸入錯誤！');
} else {
  $account = null;
  $password = null;
  $captcha = null;
  extract($_POST, EXTR_IF_EXISTS);

  try {
    $conn = new PDO($dsn, $db_user, $db_password);
    $userStmt = $conn->prepare("SELECT MemName, MemID, UnitID from MemberData WHERE MemID=? AND Pwd=? AND PosID!='406';");
    $userStmt->execute([$account, $password]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!(($userStmt->rowCount() === 1) && $user)) {
      throw new Exception("您的帳號或密碼輸入錯誤！", 1);
    }
    if ($captcha !== $_SESSION['captcha']) {
      throw new Exception("您的驗證碼輸入錯誤！", 2);
    }
    if ($_COOKIE['x_csrf_token'] !== $_SESSION['csrfToken']) {
      throw new Exception(null, 3);
    }
    $_SESSION['name'] = $user['memname'];
    $_SESSION['account'] = $account;
    $_SESSION['is_login'] = true;
    $_SESSION['is_center'] = $user['unitid'] === '01' ? true : false;
    $_SESSION['team_NO'] = $user['unitid'];

    // $conn = odbc_connect($odbc_dsn, $odbc_user, $odbc_password);
    // $userStmt = odbc_prepare($conn, "SELECT MemName, MemID, UnitID from MemberData WHERE MemID=? AND Pwd=? AND PosID!='406';");
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
    // $_SESSION['team_NO'] = $user['UnitID'];
    header('Location: /disaster_report/public/views/mainMenu.php');
  } catch (Exception $e) {
    if ($e->getCode() !== 7) {
      header('Location: /disaster_report/index.php');
      session_flash('error', $e->getMessage());
    } else {
      header("Location: /disaster_report/routes/auth/logout.php?fail={$e->getCode()}");
    }
  }
}
