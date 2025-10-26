<nav class="navbar navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="/dashboard.php">
            <i class="bi bi-hospital"></i> <?php echo APP_NAME; ?>
        </a>
        
        <div class="d-flex align-items-center">
            <span class="navbar-text text-white me-3">
                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8'); ?>
                <span class="badge bg-secondary"><?php echo strtoupper($_SESSION['role']); ?></span>
            </span>
            <a href="/logout.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</nav>
