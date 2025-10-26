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
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
                $medicine_id = $_POST['medicine_id'] ?? null;
                $name = sanitize_input($_POST['name']);
                $generic_name = sanitize_input($_POST['generic_name']);
                $category = sanitize_input($_POST['category']);
                $manufacturer = sanitize_input($_POST['manufacturer']);
                $unit_price = floatval($_POST['unit_price']);
                $quantity_in_stock = intval($_POST['quantity_in_stock']);
                $reorder_level = intval($_POST['reorder_level']);
                $expiry_date = $_POST['expiry_date'];
                $description = sanitize_input($_POST['description']);
                
                if ($_POST['action'] === 'add') {
                    $query = "INSERT INTO medicines (name, generic_name, category, manufacturer, unit_price, 
                              quantity_in_stock, reorder_level, expiry_date, description) 
                              VALUES (:name, :generic_name, :category, :manufacturer, :unit_price, 
                              :quantity_in_stock, :reorder_level, :expiry_date, :description)";
                } else {
                    $query = "UPDATE medicines SET name = :name, generic_name = :generic_name, 
                              category = :category, manufacturer = :manufacturer, unit_price = :unit_price, 
                              quantity_in_stock = :quantity_in_stock, reorder_level = :reorder_level, 
                              expiry_date = :expiry_date, description = :description 
                              WHERE medicine_id = :medicine_id";
                }
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':generic_name', $generic_name);
                $stmt->bindParam(':category', $category);
                $stmt->bindParam(':manufacturer', $manufacturer);
                $stmt->bindParam(':unit_price', $unit_price);
                $stmt->bindParam(':quantity_in_stock', $quantity_in_stock);
                $stmt->bindParam(':reorder_level', $reorder_level);
                $stmt->bindParam(':expiry_date', $expiry_date);
                $stmt->bindParam(':description', $description);
                
                if ($_POST['action'] === 'edit') {
                    $stmt->bindParam(':medicine_id', $medicine_id);
                }
                
                if ($stmt->execute()) {
                    redirect_with_message('/medicines.php', 'Medicine ' . ($_POST['action'] === 'add' ? 'added' : 'updated') . ' successfully!', 'success');
                }
            } elseif ($_POST['action'] === 'delete') {
                $medicine_id = intval($_POST['medicine_id']);
                $stmt = $db->prepare("UPDATE medicines SET is_active = 0 WHERE medicine_id = :id");
                $stmt->bindParam(':id', $medicine_id);
                
                if ($stmt->execute()) {
                    redirect_with_message('/medicines.php', 'Medicine deleted successfully!', 'success');
                }
            }
        }
    }
    
    // Get all active medicines
    $search = $_GET['search'] ?? '';
    $category_filter = $_GET['category'] ?? '';
    
    $query = "SELECT * FROM medicines WHERE is_active = 1";
    $params = [];
    
    if (!empty($search)) {
        $query .= " AND (name LIKE :search OR generic_name LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    if (!empty($category_filter)) {
        $query .= " AND category = :category";
        $params[':category'] = $category_filter;
    }
    
    $query .= " ORDER BY name ASC";
    
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $medicines = $stmt->fetchAll();
    
    // Get categories for filter
    $stmt = $db->query("SELECT DISTINCT category FROM medicines WHERE is_active = 1 AND category IS NOT NULL ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    error_log("Medicines Error: " . $e->getMessage());
    die("System error. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Medicines</title>
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
                    <h1 class="h2">Medicines Inventory</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMedicineModal">
                        <i class="bi bi-plus-circle"></i> Add Medicine
                    </button>
                </div>
                
                <?php display_flash_message(); ?>
                
                <!-- Search and Filter -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="GET" class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Search medicines..." 
                                   value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="GET">
                            <select class="form-select" name="category" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>" 
                                            <?php echo $category_filter === $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>
                
                <!-- Medicines Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Generic Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Expiry</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($medicines)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No medicines found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($medicines as $medicine): ?>
                                            <tr>
                                                <td><?php echo $medicine['medicine_id']; ?></td>
                                                <td><?php echo htmlspecialchars($medicine['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($medicine['generic_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><span class="badge bg-info"><?php echo htmlspecialchars($medicine['category'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td><?php echo format_currency($medicine['unit_price']); ?></td>
                                                <td>
                                                    <?php if ($medicine['quantity_in_stock'] <= $medicine['reorder_level']): ?>
                                                        <span class="badge bg-danger"><?php echo $medicine['quantity_in_stock']; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success"><?php echo $medicine['quantity_in_stock']; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo format_date($medicine['expiry_date']); ?></td>
                                                <td>
                                                    <?php if (strtotime($medicine['expiry_date']) < time()): ?>
                                                        <span class="badge bg-danger">Expired</span>
                                                    <?php elseif ($medicine['quantity_in_stock'] <= $medicine['reorder_level']): ?>
                                                        <span class="badge bg-warning">Low Stock</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">OK</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick='editMedicine(<?php echo json_encode($medicine); ?>)'>
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteMedicine(<?php echo $medicine['medicine_id']; ?>, '<?php echo htmlspecialchars($medicine['name'], ENT_QUOTES, 'UTF-8'); ?>')">
                                                        <i class="bi bi-trash"></i>
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
    
    <!-- Add/Edit Medicine Modal -->
    <div class="modal fade" id="addMedicineModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Medicine</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="medicineForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="medicine_id" id="medicine_id">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Medicine Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="generic_name" class="form-label">Generic Name</label>
                                <input type="text" class="form-control" id="generic_name" name="generic_name">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="manufacturer" class="form-label">Manufacturer</label>
                                <input type="text" class="form-control" id="manufacturer" name="manufacturer">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="unit_price" class="form-label">Unit Price (₹) *</label>
                                <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="quantity_in_stock" class="form-label">Quantity in Stock *</label>
                                <input type="number" class="form-control" id="quantity_in_stock" name="quantity_in_stock" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="reorder_level" class="form-label">Reorder Level *</label>
                                <input type="number" class="form-control" id="reorder_level" name="reorder_level" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="expiry_date" class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Medicine</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Form -->
    <form method="POST" id="deleteForm" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="medicine_id" id="delete_medicine_id">
    </form>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editMedicine(medicine) {
            document.getElementById('modalTitle').textContent = 'Edit Medicine';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('medicine_id').value = medicine.medicine_id;
            document.getElementById('name').value = medicine.name;
            document.getElementById('generic_name').value = medicine.generic_name || '';
            document.getElementById('category').value = medicine.category || '';
            document.getElementById('manufacturer').value = medicine.manufacturer || '';
            document.getElementById('unit_price').value = medicine.unit_price;
            document.getElementById('quantity_in_stock').value = medicine.quantity_in_stock;
            document.getElementById('reorder_level').value = medicine.reorder_level;
            document.getElementById('expiry_date').value = medicine.expiry_date || '';
            document.getElementById('description').value = medicine.description || '';
            
            new bootstrap.Modal(document.getElementById('addMedicineModal')).show();
        }
        
        function deleteMedicine(id, name) {
            if (confirm('Are you sure you want to delete "' + name + '"?')) {
                document.getElementById('delete_medicine_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
        
        // Reset form when modal is hidden
        document.getElementById('addMedicineModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('medicineForm').reset();
            document.getElementById('modalTitle').textContent = 'Add Medicine';
            document.getElementById('formAction').value = 'add';
        });
    </script>
</body>
</html>
