<?php if (isset($_SESSION['flash']) && $_SESSION['flash']) { ?>
<div class="alert alert-danger"><?= session_flash('error', null) ?></div>
<?php } ?>