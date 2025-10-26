<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Medical Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo">
                    <h1>🏥 Kottackal Medical Store - CUSTOMER PORTAL</h1>
            </div>
            <nav class="navbar">
                <a href="user_dashboard.php" class="nav-link active">Dashboard</a>
                <a href="medicines.php" class="nav-link">View Medicines</a>
                <a href="logout.php" class="nav-link" style="color: #e74c3c;">
                    Logout (<?php echo $_SESSION['username']; ?>)
                </a>
            </nav>
        </header>

        <div class="table-container">
            <h2>Welcome, <?php echo $_SESSION['username']; ?>! 👋</h2>
            <p>Customer Portal - Browse and purchase medicines</p>

            <div class="dashboard-cards">
                <div class="dashboard-card" style="background: #3498db; color: white;">
                    <h3>💊 Available Medicines</h3>
                    <p style="font-size: 2.5rem; font-weight: bold;">
                        <?php 
                        $total_sql = "SELECT COUNT(*) as total FROM medicines";
                        $total_result = mysqli_query($conn, $total_sql);
                        $total_data = mysqli_fetch_assoc($total_result);
                        echo $total_data['total'];
                        ?>
                    </p>
                    <small>Browse our medicine catalog</small>
                </div>
                
                <a href="medicines.php" class="dashboard-card" style="background: #2ecc71; color: white; text-decoration: none;">
                    <h3>🛒 Start Shopping</h3>
                    <p style="font-size: 2.5rem; font-weight: bold;">→</p>
                    <small>View all available medicines</small>
                </a>
            </div>

            <div style="background: white; padding: 1.5rem; border-radius: 10px; margin-top: 2rem; border: 1px solid #e9ecef;">
                <h3>📋 Your Permissions</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="padding: 10px; border-bottom: 1px solid #eee;">✅ View all medicines</li>
                    <li style="padding: 10px; border-bottom: 1px solid #eee;">✅ Purchase medicines</li>
                    <li style="padding: 10px; border-bottom: 1px solid #eee;">❌ Cannot add medicines</li>
                    <li style="padding: 10px; border-bottom: 1px solid #eee;">❌ Cannot delete medicines</li>
                    <li style="padding: 10px;">❌ Cannot edit medicines</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
<?php mysqli_close($conn); ?>