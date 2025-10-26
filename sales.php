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
    
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add_sale') {
            // Start transaction
            $db->beginTransaction();
            
            try {
                $customer_name = sanitize_input($_POST['customer_name'] ?? '');
                $customer_phone = sanitize_input($_POST['customer_phone'] ?? '');
                $payment_method = $_POST['payment_method'] ?? 'cash';
                $discount = floatval($_POST['discount'] ?? 0);
                $tax = floatval($_POST['tax'] ?? 0);
                $items = json_decode($_POST['items'], true);
                
                if (empty($items)) {
                    throw new Exception('No items in cart');
                }
                
                // Calculate totals
                $total_amount = 0;
                foreach ($items as $item) {
                    $total_amount += $item['quantity'] * $item['price'];
                }
                
                $final_amount = $total_amount - $discount + $tax;
                
                // Insert sale
                $stmt = $db->prepare("INSERT INTO sales (customer_name, customer_phone, total_amount, discount, tax, final_amount, payment_method, cashier_id)
                                      VALUES (:customer_name, :customer_phone, :total_amount, :discount, :tax, :final_amount, :payment_method, :cashier_id)");
                $cashier_id = $auth->getUserId();
                $stmt->bindParam(':customer_name', $customer_name);
                $stmt->bindParam(':customer_phone', $customer_phone);
                $stmt->bindParam(':total_amount', $total_amount);
                $stmt->bindParam(':discount', $discount);
                $stmt->bindParam(':tax', $tax);
                $stmt->bindParam(':final_amount', $final_amount);
                $stmt->bindParam(':payment_method', $payment_method);
                $stmt->bindParam(':cashier_id', $cashier_id);
                $stmt->execute();
                
                $sale_id = $db->lastInsertId();
                
                // Insert sale items and update stock
                $stmt_item = $db->prepare("INSERT INTO sale_items (sale_id, medicine_id, quantity, unit_price, total_price)
                                           VALUES (:sale_id, :medicine_id, :quantity, :unit_price, :total_price)");
                $stmt_update = $db->prepare("UPDATE medicines SET quantity_in_stock = quantity_in_stock - :quantity WHERE medicine_id = :medicine_id");
                
                foreach ($items as $item) {
                    $total_price = $item['quantity'] * $item['price'];
                    
                    $stmt_item->bindParam(':sale_id', $sale_id);
                    $stmt_item->bindParam(':medicine_id', $item['medicine_id']);
                    $stmt_item->bindParam(':quantity', $item['quantity']);
                    $stmt_item->bindParam(':unit_price', $item['price']);
                    $stmt_item->bindParam(':total_price', $total_price);
                    $stmt_item->execute();
                    
                    $stmt_update->bindParam(':quantity', $item['quantity']);
                    $stmt_update->bindParam(':medicine_id', $item['medicine_id']);
                    $stmt_update->execute();
                }
                
                $db->commit();
                
                redirect_with_message('/sales.php', 'Sale completed successfully! Sale ID: #' . $sale_id, 'success');
            } catch (Exception $e) {
                $db->rollBack();
                error_log("Sale Error: " . $e->getMessage());
                redirect_with_message('/sales.php', 'Error processing sale: ' . $e->getMessage(), 'error');
            }
        }
    }
    
    // Get recent sales
    $stmt = $db->query("SELECT s.sale_id, s.sale_date, s.customer_name, s.customer_phone, s.final_amount, s.payment_method, u.full_name as cashier
                        FROM sales s
                        LEFT JOIN users u ON s.cashier_id = u.user_id
                        ORDER BY s.sale_date DESC
                        LIMIT 20");
    $sales = $stmt->fetchAll();
    
    // Get medicines for sale
    $stmt = $db->query("SELECT medicine_id, name, unit_price, quantity_in_stock 
                        FROM medicines 
                        WHERE is_active = 1 AND quantity_in_stock > 0
                        ORDER BY name ASC");
    $medicines = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Sales Page Error: " . $e->getMessage());
    die("System error. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Sales</title>
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
                    <h1 class="h2">Sales Management</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newSaleModal">
                        <i class="bi bi-plus-circle"></i> New Sale
                    </button>
                </div>
                
                <?php display_flash_message(); ?>
                
                <!-- Recent Sales -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Sales</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Sale ID</th>
                                        <th>Date & Time</th>
                                        <th>Customer</th>
                                        <th>Phone</th>
                                        <th>Amount</th>
                                        <th>Payment</th>
                                        <th>Cashier</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($sales)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No sales recorded yet</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($sales as $sale): ?>
                                            <tr>
                                                <td><strong>#<?php echo $sale['sale_id']; ?></strong></td>
                                                <td><?php echo format_datetime($sale['sale_date']); ?></td>
                                                <td><?php echo htmlspecialchars($sale['customer_name'] ?: 'Walk-in', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($sale['customer_phone'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><strong><?php echo format_currency($sale['final_amount']); ?></strong></td>
                                                <td><span class="badge bg-info"><?php echo strtoupper($sale['payment_method']); ?></span></td>
                                                <td><?php echo htmlspecialchars($sale['cashier'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewSale(<?php echo $sale['sale_id']; ?>)">
                                                        <i class="bi bi-eye"></i> View
                                                    </button>
                                                </td>
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
    
    <!-- New Sale Modal -->
    <div class="modal fade" id="newSaleModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="saleForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_sale">
                        <input type="hidden" name="items" id="cartItems">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customer_name" class="form-label">Customer Name</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Optional">
                            </div>
                            <div class="col-md-6">
                                <label for="customer_phone" class="form-label">Customer Phone</label>
                                <input type="text" class="form-control" id="customer_phone" name="customer_phone" placeholder="Optional">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="medicine_select" class="form-label">Select Medicine</label>
                                <select class="form-select" id="medicine_select">
                                    <option value="">-- Select Medicine --</option>
                                    <?php foreach ($medicines as $med): ?>
                                        <option value="<?php echo $med['medicine_id']; ?>" 
                                                data-name="<?php echo htmlspecialchars($med['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-price="<?php echo $med['unit_price']; ?>"
                                                data-stock="<?php echo $med['quantity_in_stock']; ?>">
                                            <?php echo htmlspecialchars($med['name'], ENT_QUOTES, 'UTF-8'); ?> 
                                            (Stock: <?php echo $med['quantity_in_stock']; ?>, Price: <?php echo format_currency($med['unit_price']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="quantity_input" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity_input" min="1" value="1">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-success w-100" onclick="addToCart()">
                                    <i class="bi bi-plus"></i> Add
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive mb-3">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Medicine</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="cartTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No items added</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                <table class="table table-sm">
                                    <tr>
                                        <td>Subtotal:</td>
                                        <td class="text-end"><strong id="subtotalDisplay">₹0.00</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Discount:</td>
                                        <td class="text-end">
                                            <input type="number" class="form-control form-control-sm" name="discount" id="discount" value="0" step="0.01" onchange="updateTotal()">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Tax:</td>
                                        <td class="text-end">
                                            <input type="number" class="form-control form-control-sm" name="tax" id="tax" value="0" step="0.01" onchange="updateTotal()">
                                        </td>
                                    </tr>
                                    <tr class="table-success">
                                        <td><strong>Final Total:</strong></td>
                                        <td class="text-end"><strong id="finalTotalDisplay">₹0.00</strong></td>
                                    </tr>
                                </table>
                                
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select mb-2" name="payment_method" id="payment_method" required>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="upi">UPI</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="completeSaleBtn" disabled>
                            <i class="bi bi-check-circle"></i> Complete Sale
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let cart = [];
        
        function addToCart() {
            const select = document.getElementById('medicine_select');
            const quantity = parseInt(document.getElementById('quantity_input').value);
            
            if (!select.value) {
                alert('Please select a medicine');
                return;
            }
            
            const option = select.options[select.selectedIndex];
            const medicine_id = parseInt(select.value);
            const name = option.dataset.name;
            const price = parseFloat(option.dataset.price);
            const stock = parseInt(option.dataset.stock);
            
            if (quantity > stock) {
                alert('Quantity exceeds available stock!');
                return;
            }
            
            // Check if item already in cart
            const existingIndex = cart.findIndex(item => item.medicine_id === medicine_id);
            if (existingIndex !== -1) {
                cart[existingIndex].quantity += quantity;
            } else {
                cart.push({ medicine_id, name, price, quantity });
            }
            
            updateCartDisplay();
            select.value = '';
            document.getElementById('quantity_input').value = 1;
        }
        
        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartDisplay();
        }
        
        function updateCartDisplay() {
            const tbody = document.getElementById('cartTableBody');
            
            if (cart.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No items added</td></tr>';
                document.getElementById('completeSaleBtn').disabled = true;
            } else {
                let html = '';
                cart.forEach((item, index) => {
                    const total = item.price * item.quantity;
                    html += `<tr>
                        <td>${item.name}</td>
                        <td>₹${item.price.toFixed(2)}</td>
                        <td>${item.quantity}</td>
                        <td>₹${total.toFixed(2)}</td>
                        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeFromCart(${index})"><i class="bi bi-trash"></i></button></td>
                    </tr>`;
                });
                tbody.innerHTML = html;
                document.getElementById('completeSaleBtn').disabled = false;
            }
            
            updateTotal();
        }
        
        function updateTotal() {
            let subtotal = 0;
            cart.forEach(item => {
                subtotal += item.price * item.quantity;
            });
            
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const tax = parseFloat(document.getElementById('tax').value) || 0;
            const finalTotal = subtotal - discount + tax;
            
            document.getElementById('subtotalDisplay').textContent = '₹' + subtotal.toFixed(2);
            document.getElementById('finalTotalDisplay').textContent = '₹' + finalTotal.toFixed(2);
        }
        
        document.getElementById('saleForm').addEventListener('submit', function(e) {
            if (cart.length === 0) {
                e.preventDefault();
                alert('Please add items to cart');
                return;
            }
            
            document.getElementById('cartItems').value = JSON.stringify(cart);
        });
        
        // Reset modal when closed
        document.getElementById('newSaleModal').addEventListener('hidden.bs.modal', function () {
            cart = [];
            document.getElementById('saleForm').reset();
            updateCartDisplay();
        });
        
        function viewSale(saleId) {
            window.location.href = '/sale_details.php?id=' + saleId;
        }
    </script>
</body>
</html>
