<?php
include 'config.php';

// Check if user is logged in and is a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'user') {
    header("Location: login.html");
    exit();
}

// Handle remove from cart
if (isset($_GET['remove_id'])) {
    $remove_id = mysqli_real_escape_string($conn, $_GET['remove_id']);
    if (isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
        $success = "Item removed from cart!";
    }
}

// Handle update quantity
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $medicine_id => $quantity) {
        if ($quantity == 0) {
            unset($_SESSION['cart'][$medicine_id]);
        } else {
            $_SESSION['cart'][$medicine_id] = $quantity;
        }
    }
    $success = "Cart updated successfully!";
}

// Handle place order
if (isset($_POST['place_order'])) {
    if (!empty($_SESSION['cart'])) {
        // Create order record (you can expand this with a proper orders table)
        $order_items = array();
        $total_amount = 0;
        
        foreach ($_SESSION['cart'] as $medicine_id => $quantity) {
            $medicine_sql = "SELECT name, price FROM medicines WHERE id = '$medicine_id'";
            $medicine_result = mysqli_query($conn, $medicine_sql);
            if ($medicine_result && mysqli_num_rows($medicine_result) > 0) {
                $medicine = mysqli_fetch_assoc($medicine_result);
                $subtotal = $medicine['price'] * $quantity;
                $total_amount += $subtotal;
                $order_items[] = "{$medicine['name']} (Qty: $quantity) - ₹" . number_format($subtotal, 2);
            }
        }
        
        // Store order in session for confirmation
        $_SESSION['order_details'] = array(
            'items' => $order_items,
            'total' => $total_amount,
            'order_id' => 'ORD' . time() . rand(100, 999)
        );
        
        // Clear cart
        $_SESSION['cart'] = array();
        
        // Redirect to order confirmation
        header("Location: order_confirmation.php");
        exit();
    } else {
        $error = "Your cart is empty!";
    }
}

// Get cart items with details
$cart_items = array();
$total_amount = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $medicine_id => $quantity) {
        $sql = "SELECT * FROM medicines WHERE id = '$medicine_id'";
        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $medicine = mysqli_fetch_assoc($result);
            $subtotal = $medicine['price'] * $quantity;
            $total_amount += $subtotal;
            
            $cart_items[] = array(
                'id' => $medicine['id'],
                'name' => $medicine['name'],
                'price' => $medicine['price'],
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'max_quantity' => $medicine['quantity']
            );
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Medical Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo">
                    <h1>🏥 Kottackal Medical Store - SHOPPING CART</h1>
            </div>
            <nav class="navbar">
                <a href="user_dashboard.php" class="nav-link">Dashboard</a>
                <a href="medicines.php" class="nav-link">Shop Medicines</a>
                <a href="cart.php" class="nav-link active">🛒 Cart 
                    (<?php echo array_sum($_SESSION['cart'] ?? array()); ?>)
                </a>
                <a href="logout.php" class="nav-link" style="color: #e74c3c;">
                    Logout (<?php echo $_SESSION['username']; ?>)
                </a>
            </nav>
        </header>

        <div class="table-container">
            <h2>🛒 Your Shopping Cart</h2>
            <p>Review your items and proceed to checkout</p>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($cart_items)): ?>
                <form method="POST">
                    <table>
                        <thead>
                            <tr>
                                <th>Medicine Name</th>
                                <th>Price (₹)</th>
                                <th>Quantity</th>
                                <th>Subtotal (₹)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <input type="number" name="quantities[<?php echo $item['id']; ?>]" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="<?php echo $item['max_quantity']; ?>"
                                           style="width: 70px; padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
                                </td>
                                <td>₹<?php echo number_format($item['subtotal'], 2); ?></td>
                                <td>
                                    <a href="cart.php?remove_id=<?php echo $item['id']; ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Remove <?php echo htmlspecialchars($item['name']); ?> from cart?')">
                                       ❌ Remove
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr style="background: #f8f9fa; font-weight: bold;">
                                <td colspan="3" style="text-align: right;">Total Amount:</td>
                                <td>₹<?php echo number_format($total_amount, 2); ?></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                        <button type="submit" name="update_cart" class="btn btn-secondary">
                            🔄 Update Cart
                        </button>
                        <button type="submit" name="place_order" class="btn btn-primary">
                            ✅ Place Order
                        </button>
                        <a href="medicines.php" class="btn" style="background: #3498db; color: white;">
                            ➕ Continue Shopping
                        </a>
                    </div>
                </form>

                <div style="background: #e8f4fd; padding: 1.5rem; border-radius: 10px; margin-top: 2rem; border: 1px solid #3498db;">
                    <h3>📦 Order Summary</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                        <div>
                            <h4>Items in Cart:</h4>
                            <ul>
                                <?php foreach ($cart_items as $item): ?>
                                    <li><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div>
                            <h4>Total: ₹<?php echo number_format($total_amount, 2); ?></h4>
                            <p style="color: #7f8c8d;">Inclusive of all taxes</p>
                            <p style="color: #27ae60; font-weight: bold;">✅ Free delivery</p>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div style="text-align: center; padding: 60px; background: #f8f9fa; border-radius: 10px;">
                    <h3>🛒 Your cart is empty</h3>
                    <p>Add some medicines to your cart to get started</p>
                    <a href="medicines.php" class="btn btn-primary" style="margin-top: 15px;">
                        🏪 Start Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php mysqli_close($conn); ?>