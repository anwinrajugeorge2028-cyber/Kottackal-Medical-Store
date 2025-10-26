<?php
session_start();
include 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != "admin") {
    header("Location: login.html");
    exit();
}

// Get statistics
$total_medicines = 0;
$low_stock = 0;
$out_of_stock = 0;

// Total medicines count
$total_sql = "SELECT COUNT(*) as total FROM medicines";
$total_result = mysqli_query($conn, $total_sql);
if ($total_result) {
    $total_data = mysqli_fetch_assoc($total_result);
    $total_medicines = $total_data['total'];
}

// Low stock count (less than 10 but greater than 0)
$low_stock_sql = "SELECT COUNT(*) as low FROM medicines WHERE quantity < 10 AND quantity > 0";
$low_stock_result = mysqli_query($conn, $low_stock_sql);
if ($low_stock_result) {
    $low_stock_data = mysqli_fetch_assoc($low_stock_result);
    $low_stock = $low_stock_data['low'];
}

// Out of stock count (FIXED - using backticks for reserved keyword)
$out_of_stock_sql = "SELECT COUNT(*) as `out` FROM medicines WHERE quantity = 0";
$out_of_stock_result = mysqli_query($conn, $out_of_stock_sql);
if ($out_of_stock_result) {
    $out_of_stock_data = mysqli_fetch_assoc($out_of_stock_result);
    $out_of_stock = $out_of_stock_data['out'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Medical Store</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
        }

        .sidebar-header {
            text-align: center;
            padding: 20px;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            color: #666;
            font-size: 14px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0 15px;
        }

        .sidebar-menu li {
            margin-bottom: 8px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #555;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar-menu a i {
            margin-right: 12px;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        .sidebar-menu a:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-menu a.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2em;
        }

        .header p {
            color: #666;
            font-size: 1.1em;
        }

        /* Stats Container */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            color: #7f8c8d;
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #2c3e50;
        }

        .stat-card.total { border-top: 4px solid #3498db; }
        .stat-card.low-stock { border-top: 4px solid #f39c12; }
        .stat-card.out-of-stock { border-top: 4px solid #e74c3c; }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Medical Store</h2>
                <p>Admin Panel</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="add_medicine.php"><i class="fas fa-plus-circle"></i> Add Medicine</a></li>
                <li><a href="view_medicines.php"><i class="fas fa-capsules"></i> View Medicines</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <p>Welcome back, <?php echo $_SESSION['username'] ?? 'Admin'; ?>!</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card total">
                    <h3>Total Medicines</h3>
                    <div class="stat-number"><?php echo $total_medicines; ?></div>
                </div>
                <div class="stat-card low-stock">
                    <h3>Low Stock</h3>
                    <div class="stat-number"><?php echo $low_stock; ?></div>
                </div>
                <div class="stat-card out-of-stock">
                    <h3>Out of Stock</h3>
                    <div class="stat-number"><?php echo $out_of_stock; ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php mysqli_close($conn); ?>