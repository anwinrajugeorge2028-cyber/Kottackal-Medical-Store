<?php
session_start();
include 'config.php';

// Allow public viewing of medicines. If a user is logged in, detect if they're admin.
$is_admin = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
    $is_admin = true;
}

// Handle add-to-cart action (simple session-based cart)
$msg_success = '';
$msg_error = '';
if (isset($_GET['add_id'])) {
    $add_id = intval($_GET['add_id']);
    // Only logged-in regular users can add to cart
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
        header("Location: login.html?error=Please+login+as+user+to+add+to+cart");
        exit();
    }

    // Check medicine exists and has stock
    $check_sql = mysqli_prepare($conn, "SELECT quantity FROM medicines WHERE id = ? LIMIT 1");
    if ($check_sql) {
        mysqli_stmt_bind_param($check_sql, 'i', $add_id);
        mysqli_stmt_execute($check_sql);
        mysqli_stmt_bind_result($check_sql, $available_qty);
        if (mysqli_stmt_fetch($check_sql)) {
            if ($available_qty > 0) {
                // Add to session cart
                if (!isset($_SESSION['cart'])) $_SESSION['cart'] = array();
                if (!isset($_SESSION['cart'][$add_id])) {
                    $_SESSION['cart'][$add_id] = 1;
                } else {
                    $_SESSION['cart'][$add_id]++;
                }
                $msg_success = 'Added to cart';
            } else {
                $msg_error = 'This medicine is out of stock';
            }
        } else {
            $msg_error = 'Medicine not found';
        }
        mysqli_stmt_close($check_sql);
    } else {
        $msg_error = 'Server error';
    }
}

// Fetch medicines (show all, including zero stock so we can show out-of-stock badges)
$sql = "SELECT * FROM medicines ORDER BY name ASC";
$result = mysqli_query($conn, $sql);

$cart_count = 0;
if (isset($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicines - Kottackal Medical Store</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
        <script src="script.js"></script>
</head>
<body>
    <?php if ($is_admin): ?>
    <!-- ADMIN LAYOUT -->
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Kottackal Medical</h2>
                <p>Admin Panel</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="add_medicine.php"><i class="fas fa-plus-circle"></i> Add Medicine</a></li>
                    <li><a href="medicines.php" class="active"><i class="fas fa-capsules"></i> View Medicines</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> View Orders</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Manage Medicines</h1>
                <p>View and manage all medicines in inventory</p>
            </div>
            <!-- Rest of admin content... -->
        </div>
    </div>

    <?php else: ?>
    <!-- USER LAYOUT -->
    <div class="public-container">
        <!-- Brand Header -->
        <header class="brand-header">
            <div class="brand-container">
                <h1 class="store-name">KOTTACKAL MEDICAL STORE</h1>
                <p class="store-tagline">YOUR HEALTH, OUR PRIORITY</p>
                <div class="store-location">Kottackal, Kerala</div>
            </div>
        </header>

        <!-- Navigation -->
        <nav class="navbar">
            <div class="navbar-content">
                <div class="nav-logo">
                    <h2>Kottackal Medical</h2>
                </div>
                <ul class="nav-links">
                    <li><a href="index.html"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="medicines.php" class="active"><i class="fas fa-capsules"></i> Medicines</a></li>
                    <li><a href="about.html"><i class="fas fa-info-circle"></i> About</a></li>
                    <li><a href="contact.html"><i class="fas fa-phone"></i> Contact</a></li>
                    <li>
                        <a href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Cart 
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-badge"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="logout.php" style="color: var(--error-red);">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a></li>
                </ul>
            </div>
        </nav>

        <main class="main-content-public">
            <div class="page-header">
                <h1>Our Medicines</h1>
                <p>Quality healthcare products for your well-being</p>
            </div>

            <?php if ($msg_success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg_success); ?></div>
            <?php endif; ?>
            <?php if ($msg_error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($msg_error); ?></div>
            <?php endif; ?>

            <div class="medicine-grid">
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): 
                        $status_class = 'stock-in';
                        $status_text = 'In Stock';
                        if ($row['quantity'] == 0) {
                            $status_class = 'stock-out';
                            $status_text = 'Out of Stock';
                        } elseif ($row['quantity'] < 10) {
                            $status_class = 'stock-low';
                            $status_text = 'Low Stock';
                        }
                    ?>
                    <div class="medicine-card">
                        <i class="fas fa-pills" style="font-size: 2.2em; color: var(--primary-blue); margin-bottom: 0.8rem;"></i>
                        <div class="medicine-name"><?php echo htmlspecialchars($row['name']); ?></div>
                        <div class="medicine-description"><?php echo htmlspecialchars($row['description']); ?></div>
                        <div class="medicine-price">₹<?php echo number_format($row['price'], 2); ?></div>
                        <div style="margin-top: 0.5rem;">
                            <span class="stock-status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </div>
                        <div style="margin-top: 1rem; display:flex; gap:0.5rem;">
                            <?php if ($row['quantity'] > 0): ?>
                                <a href="medicines.php?add_id=<?php echo $row['id']; ?>" class="btn btn-primary">Add to Cart</a>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>Out of stock</button>
                            <?php endif; ?>
                            <a href="#" class="btn">Details</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align:center; padding: 2rem;">
                        <h3>No medicines available right now</h3>
                        <p>Please check back later or contact us for assistance.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <?php endif; ?>
</body>
</html>