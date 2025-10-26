<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);
    
    // Require login
    $auth->requireLogin();
    
    // Get dashboard statistics
    $stats = [];
    
    // Total medicines
    $stmt = $db->query("SELECT COUNT(*) as count FROM medicines WHERE is_active = 1");
    $stats['total_medicines'] = $stmt->fetch()['count'];
    
    // Low stock items
    $stmt = $db->query("SELECT COUNT(*) as count FROM medicines WHERE quantity_in_stock <= reorder_level AND is_active = 1");
    $stats['low_stock'] = $stmt->fetch()['count'];
    
    // Expired medicines
    $stmt = $db->query("SELECT COUNT(*) as count FROM medicines WHERE expiry_date < CURDATE() AND is_active = 1");
    $stats['expired'] = $stmt->fetch()['count'];
    
    // Today's sales
    $stmt = $db->query("SELECT COUNT(*) as count, COALESCE(SUM(final_amount), 0) as total 
                        FROM sales WHERE DATE(sale_date) = CURDATE()");
    $today_sales = $stmt->fetch();
    $stats['today_sales_count'] = $today_sales['count'];
    $stats['today_sales_total'] = $today_sales['total'];
    
    // Recent sales
    $stmt = $db->query("SELECT s.sale_id, s.sale_date, s.customer_name, s.final_amount, s.payment_method, u.full_name as cashier
                        FROM sales s
                        LEFT JOIN users u ON s.cashier_id = u.user_id
                        ORDER BY s.sale_date DESC
                        LIMIT 5");
    $recent_sales = $stmt->fetchAll();
    
    // Low stock medicines
    $stmt = $db->query("SELECT medicine_id, name, quantity_in_stock, reorder_level, unit_price
                        FROM medicines 
                        WHERE quantity_in_stock <= reorder_level AND is_active = 1
                        ORDER BY quantity_in_stock ASC
                        LIMIT 10");
    $low_stock_items = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    die("System error. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-calendar"></i> Today
                            </button>
                        </div>
                    </div>
                </div>
                
                <?php display_flash_message(); ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Medicines</h6>
                                        <h2 class="mb-0"><?php echo $stats['total_medicines']; ?></h2>
                                    </div>
                                    <i class="bi bi-capsule fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Low Stock</h6>
                                        <h2 class="mb-0"><?php echo $stats['low_stock']; ?></h2>
                                    </div>
                                    <i class="bi bi-exclamation-triangle fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Expired Items</h6>
                                        <h2 class="mb-0"><?php echo $stats['expired']; ?></h2>
                                    </div>
                                    <i class="bi bi-x-circle fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Today's Sales</h6>
                                        <h2 class="mb-0"><?php echo format_currency($stats['today_sales_total']); ?></h2>
                                        <small><?php echo $stats['today_sales_count']; ?> transactions</small>
                                    </div>
                                    <i class="bi bi-cart-check fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Recent Sales -->
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Sales</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Date</th>
                                                <th>Customer</th>
                                                <th>Amount</th>
                                                <th>Payment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recent_sales)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">No sales yet</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($recent_sales as $sale): ?>
                                                    <tr>
                                                        <td>#<?php echo $sale['sale_id']; ?></td>
                                                        <td><?php echo format_datetime($sale['sale_date']); ?></td>
                                                        <td><?php echo htmlspecialchars($sale['customer_name'] ?: 'Walk-in', ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo format_currency($sale['final_amount']); ?></td>
                                                        <td><span class="badge bg-info"><?php echo strtoupper($sale['payment_method']); ?></span></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Low Stock Items -->
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Low Stock Alert</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Medicine</th>
                                                <th>Stock</th>
                                                <th>Reorder</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($low_stock_items)): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">All items well stocked</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($low_stock_items as $item): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><span class="badge bg-warning"><?php echo $item['quantity_in_stock']; ?></span></td>
                                                        <td><?php echo $item['reorder_level']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
