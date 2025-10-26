<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);
    
    $auth->requireLogin();
    
    // Get all purchase orders
    $stmt = $db->query("SELECT po.*, s.name as supplier_name, u.full_name as created_by_name
                        FROM purchase_orders po
                        LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
                        LEFT JOIN users u ON po.created_by = u.user_id
                        ORDER BY po.order_date DESC
                        LIMIT 20");
    $orders = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Purchase Orders Error: " . $e->getMessage());
    die("System error. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Purchase Orders</title>
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
                    <h1 class="h2">Purchase Orders</h1>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Order Date</th>
                                        <th>Supplier</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($orders)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No purchase orders found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                                                <td><?php echo format_date($order['order_date']); ?></td>
                                                <td><?php echo htmlspecialchars($order['supplier_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo format_currency($order['total_amount']); ?></td>
                                                <td>
                                                    <?php
                                                    $badge_class = match($order['status']) {
                                                        'received' => 'bg-success',
                                                        'cancelled' => 'bg-danger',
                                                        default => 'bg-warning'
                                                    };
                                                    ?>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo strtoupper($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($order['created_by_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
