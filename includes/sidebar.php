<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="/dashboard.php">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'medicines.php' ? 'active' : ''; ?>" href="/medicines.php">
                    <i class="bi bi-capsule"></i> Medicines
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'active' : ''; ?>" href="/sales.php">
                    <i class="bi bi-cart"></i> Sales
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'suppliers.php' ? 'active' : ''; ?>" href="/suppliers.php">
                    <i class="bi bi-truck"></i> Suppliers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'purchase_orders.php' ? 'active' : ''; ?>" href="/purchase_orders.php">
                    <i class="bi bi-box-seam"></i> Purchase Orders
                </a>
            </li>
            
            <?php if ($auth->hasRole('admin')): ?>
            <li class="nav-item">
                <hr class="my-2">
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="/users.php">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" href="/reports.php">
                    <i class="bi bi-graph-up"></i> Reports
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
