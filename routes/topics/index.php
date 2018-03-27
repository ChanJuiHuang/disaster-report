<?php
  require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/session.php');
  require($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/modules/isLogin.php');

  $session();
  if (!$isLogin()) {
    header('Location: /disaster_report/index.php');
  }
?>

<!DOCTYPE html>
<html lang="en">

<?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_header.php'); ?>

<body>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_navbar.php'); ?>
  <div class="container">
    <div class="row">
      <div class="col-md-10 offset-md-1">
        <div class="card">
          <div class="card-title card-header" style="font-size: 6vmin; text-align: center;">主題列表</div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-10 offset-md-1">
                <?php if ($_SESSION['is_center']) { ?>
                <a href="/disaster_report/routes/topics/create.php<?= isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '' ?>">
                  <button type="button" class="btn btn-primary">新增主題</button>
                </a>
                <?php } ?>
              </div>
            </div>
            <div class="row">
              <div class="col-md-10 offset-md-1">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th scope="col">主題名稱</th>
                      <th scope="col">建立時間</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <th scope="row"><?= $topic['name'] ?></th>
                      <td><?= date('Y-m-d H:i:s', strtotime($topic['created_at'])) ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

<?php include($_SERVER['DOCUMENT_ROOT'] . '/disaster_report/public/views/partials/_script.php'); ?>

</html>