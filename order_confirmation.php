<?php
include 'config.php';

// Check if user is logged in and has order details
if (!isset($_SESSION['user_id']) || !isset($_SESSION['order_details'])) {
    header("Location: medicines.php");
    exit();
}

$order_details = $_SESSION['order_details'];
unset($_SESSION['order_details']); // Clear order details after displaying
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Medical Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo">
                <h1>🏥 Kottackal Medical Store</h1>
            </div>
            <nav class="navbar">
                <a href="user_dashboard.php" class="nav-link">Dashboard</a>
                <a href="medicines.php" class="nav-link">Shop Medicines</a>
                <a href="cart.php" class="nav-link">🛒 Cart</a>
                <a href="logout.php" class="nav-link" style="color: #e74c3c;">
                    Logout (<?php echo $_SESSION['username']; ?>)
                </a>
            </nav>
        </header>

        <div class="table-container">
            <div style="text-align: center; padding: 2rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">✅</div>
                <h2 style="color: #27ae60;">Order Placed Successfully!</h2>
                <p style="color: #7f8c8d; font-size: 1.2rem;">Thank you for your purchase</p>
            </div>

            <div style="background: #e8f4fd; padding: 2rem; border-radius: 10px; margin: 2rem 0; border: 1px solid #3498db;">
                <h3>📋 Order Details</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1rem;">
                    <div>
                        <h4>Order Information</h4>
                        <p><strong>Order ID:</strong> <?php echo $order_details['order_id']; ?></p>
                        <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a'); ?></p>
                        <p><strong>Customer:</strong> <?php echo $_SESSION['username']; ?></p>
                    </div>
                    <div>
                        <h4>Order Summary</h4>
                        <p><strong>Total Items:</strong> <?php echo count($order_details['items']); ?></p>
                        <p><strong>Total Amount:</strong> <span style="font-size: 1.5rem; color: #27ae60; font-weight: bold;">₹<?php echo number_format($order_details['total'], 2); ?></span></p>
                    </div>
                </div>
            </div>

            <div style="background: white; padding: 1.5rem; border-radius: 10px; border: 1px solid #e9ecef;">
                <h4>🛍️ Ordered Items</h4>
                <table style="width: 100%; margin-top: 1rem;">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_details['items'] as $item): ?>
                        <tr>
                            <td style="width: 80%;"><?php echo $item; ?></td>
                            <td style="color: #27ae60; font-weight: bold;">✅ Ordered</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="background: #fff3cd; padding: 1.5rem; border-radius: 10px; margin-top: 2rem; border: 1px solid #ffeaa7;">
                <h4>📦 What's Next?</h4>
                <ul>
                    <li>Your order has been confirmed and will be processed shortly</li>
                    <li>You will receive a confirmation email with delivery details</li>
                    <li>Expected delivery: 2-3 business days</li>
                    <li>For any queries, contact our customer support</li>
                </ul>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="medicines.php" class="btn btn-primary">🛒 Continue Shopping</a>
                <a href="user_dashboard.php" class="btn btn-secondary">📊 Back to Dashboard</a>
                <a href="javascript:window.print()" class="btn" style="background: #34495e; color: white;">🖨️ Print Receipt</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php mysqli_close($conn); ?>