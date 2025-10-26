<?php
/**
 * System Test Script
 * This script tests the basic functionality of the application
 * Access via: http://YOUR_SERVER/test.php
 * 
 * WARNING: Remove this file in production!
 */

// Prevent direct access in production
// Check multiple conditions for production environment
if (getenv('PRODUCTION') === 'true' || 
    (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] !== 'localhost' && 
     !preg_match('/^(127\.|192\.168\.|10\.)/', $_SERVER['SERVER_ADDR'] ?? ''))) {
    http_response_code(403);
    die('Access denied. This diagnostic tool is only available in development environments.');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Test - Kottackal Medical Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4">System Test Results</h1>
        <div class="alert alert-warning">
            <strong>Warning:</strong> This test file should be removed in production!
        </div>
        
        <?php
        $tests = [];
        
        // Test 1: PHP Version
        $phpVersion = phpversion();
        $tests[] = [
            'name' => 'PHP Version',
            'status' => version_compare($phpVersion, '8.0', '>='),
            'message' => "PHP $phpVersion " . (version_compare($phpVersion, '8.0', '>=') ? '✓' : '✗ (Requires PHP 8.0+)'),
            'type' => version_compare($phpVersion, '8.0', '>=') ? 'success' : 'danger'
        ];
        
        // Test 2: Required PHP Extensions
        $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            $tests[] = [
                'name' => "PHP Extension: $ext",
                'status' => $loaded,
                'message' => $loaded ? "✓ Loaded" : "✗ Not loaded",
                'type' => $loaded ? 'success' : 'danger'
            ];
        }
        
        // Test 3: Configuration File
        $configExists = file_exists(__DIR__ . '/config/config.php');
        $tests[] = [
            'name' => 'Configuration File',
            'status' => $configExists,
            'message' => $configExists ? "✓ Found" : "✗ Not found",
            'type' => $configExists ? 'success' : 'danger'
        ];
        
        // Test 4: Database Connection
        if ($configExists) {
            require_once __DIR__ . '/config/config.php';
            require_once __DIR__ . '/includes/database.php';
            
            try {
                $database = new Database();
                $db = $database->getConnection();
                
                $tests[] = [
                    'name' => 'Database Connection',
                    'status' => true,
                    'message' => "✓ Connected to " . DB_NAME,
                    'type' => 'success'
                ];
                
                // Test 5: Tables Exist
                $requiredTables = ['users', 'medicines', 'sales', 'suppliers'];
                foreach ($requiredTables as $table) {
                    // Use prepared statement to safely check table existence
                    $stmt = $db->prepare("SELECT 1 FROM information_schema.tables 
                                          WHERE table_schema = :dbname AND table_name = :tablename LIMIT 1");
                    $stmt->execute([':dbname' => DB_NAME, ':tablename' => $table]);
                    $exists = $stmt->rowCount() > 0;
                    $tests[] = [
                        'name' => "Table: $table",
                        'status' => $exists,
                        'message' => $exists ? "✓ Exists" : "✗ Not found",
                        'type' => $exists ? 'success' : 'warning'
                    ];
                }
                
                // Test 6: Admin User Exists
                $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
                $adminCount = $stmt->fetch()['count'];
                $tests[] = [
                    'name' => 'Admin User',
                    'status' => $adminCount > 0,
                    'message' => $adminCount > 0 ? "✓ Found ($adminCount)" : "✗ Not found",
                    'type' => $adminCount > 0 ? 'success' : 'warning'
                ];
                
            } catch (Exception $e) {
                $tests[] = [
                    'name' => 'Database Connection',
                    'status' => false,
                    'message' => "✗ Error: " . $e->getMessage(),
                    'type' => 'danger'
                ];
            }
        }
        
        // Test 7: Write Permissions
        $logsDir = __DIR__ . '/logs';
        $logsWritable = is_writable($logsDir);
        $tests[] = [
            'name' => 'Logs Directory Writable',
            'status' => $logsWritable,
            'message' => $logsWritable ? "✓ Writable" : "✗ Not writable",
            'type' => $logsWritable ? 'success' : 'warning'
        ];
        
        // Test 8: .htaccess
        $htaccessExists = file_exists(__DIR__ . '/.htaccess');
        $tests[] = [
            'name' => '.htaccess File',
            'status' => $htaccessExists,
            'message' => $htaccessExists ? "✓ Found" : "✗ Not found",
            'type' => $htaccessExists ? 'success' : 'warning'
        ];
        
        // Display Results
        foreach ($tests as $test) {
            echo '<div class="card mb-2">';
            echo '<div class="card-body d-flex justify-content-between align-items-center">';
            echo '<strong>' . htmlspecialchars($test['name']) . '</strong>';
            echo '<span class="badge bg-' . $test['type'] . '">' . htmlspecialchars($test['message']) . '</span>';
            echo '</div>';
            echo '</div>';
        }
        
        // Summary
        $passed = count(array_filter($tests, fn($t) => $t['status']));
        $total = count($tests);
        $percentage = round(($passed / $total) * 100);
        
        echo '<div class="alert alert-' . ($percentage === 100 ? 'success' : ($percentage >= 80 ? 'warning' : 'danger')) . ' mt-4">';
        echo "<h5>Summary: $passed / $total tests passed ($percentage%)</h5>";
        if ($percentage === 100) {
            echo '<p class="mb-0">All tests passed! System is ready.</p>';
        } else {
            echo '<p class="mb-0">Some tests failed. Please check the configuration.</p>';
        }
        echo '</div>';
        ?>
        
        <div class="mt-4">
            <h5>System Information</h5>
            <table class="table table-sm">
                <tr>
                    <th>PHP Version:</th>
                    <td><?php echo phpversion(); ?></td>
                </tr>
                <tr>
                    <th>Server Software:</th>
                    <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                </tr>
                <tr>
                    <th>Document Root:</th>
                    <td><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></td>
                </tr>
                <tr>
                    <th>Current Time:</th>
                    <td><?php echo date('Y-m-d H:i:s'); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="mt-4">
            <a href="/index.php" class="btn btn-primary">Go to Application</a>
            <a href="#" onclick="location.reload()" class="btn btn-secondary">Refresh Tests</a>
        </div>
        
        <div class="alert alert-danger mt-4">
            <strong>Important:</strong> Delete this file (test.php) before deploying to production!
        </div>
    </div>
</body>
</html>
