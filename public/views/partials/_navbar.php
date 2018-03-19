<nav class="navbar navbar-expand-lg navbar-light bg-light mb-3" style="background-color: #e3f2fd;">
    <a class="navbar-brand">彰化縣消防局災情查報系統</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
        aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
        <!-- <ul class="navbar-nav mr-auto"> -->
            <!-- <li class="nav-item">
                <a class="nav-link" href="/">Home
                    <span class="sr-only">(current)</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/about">About</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/contact">Contact</a>
            </li> -->
        <!-- </ul> -->
        <?php if (!empty($_SESSION['is_login']) && $_SESSION['is_login'] === true) { ?>
        <ul class="navbar-nav">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    <?= $_SESSION['name'] ?>
                    <!-- <?= mb_convert_encoding($_SESSION['name'], 'utf-8', 'big-5'); ?> -->
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="">Posts</a>
                    <a class="dropdown-item" href="">Categories</a>
                    <a class="dropdown-item" href="">Tags</a>
                    <div class="dropdown-divider"></div>
                    <form action="/disaster_report/routes/auth/logout.php" method="POST">
                        <button class="dropdown-item" type="submit" style="curs-or: pointer;">Logout</button>
                    </form>
                </div>
            </li>
        </ul>
        <?php } ?>
    </div>
</nav>
