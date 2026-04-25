<?php
/**
 * Setup Verification Page
 * Check if DineClick is properly configured
 * Access: http://localhost/DineClick/setup_check.php
 */

$checks = [];

// Check 1: PHP Version
$php_version = phpversion();
$checks['php_version'] = [
    'name' => 'PHP Version',
    'status' => version_compare($php_version, '7.4.0', '>='),
    'message' => $php_version,
    'required' => 'PHP 7.4 or higher'
];

// Check 2: Database Connection
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $checks['database'] = [
            'name' => 'Database Connection',
            'status' => true,
            'message' => 'Connected successfully',
            'required' => 'MySQL connection'
        ];
        
        // Check 3: Tables exist
        $tables = ['users', 'products', 'categories', 'orders', 'order_items', 'cart', 'notifications'];
        $existing_tables = [];
        
        foreach ($tables as $table) {
            $query = "SHOW TABLES LIKE '$table'";
            $stmt = $db->query($query);
            if ($stmt->rowCount() > 0) {
                $existing_tables[] = $table;
            }
        }
        
        $checks['tables'] = [
            'name' => 'Database Tables',
            'status' => count($existing_tables) === count($tables),
            'message' => count($existing_tables) . ' of ' . count($tables) . ' tables found',
            'required' => 'All 7 tables must exist'
        ];
        
        // Check 4: Admin account exists
        $admin_query = "SELECT COUNT(*) as count FROM users WHERE is_admin = 1";
        $admin_stmt = $db->query($admin_query);
        $admin_count = $admin_stmt->fetch()['count'];
        
        $checks['admin'] = [
            'name' => 'Admin Account',
            'status' => $admin_count > 0,
            'message' => $admin_count > 0 ? 'Admin account exists' : 'No admin account found',
            'required' => 'At least one admin account'
        ];
        
    }
} catch (Exception $e) {
    $checks['database'] = [
        'name' => 'Database Connection',
        'status' => false,
        'message' => $e->getMessage(),
        'required' => 'MySQL connection'
    ];
}

// Check 5: Upload Directory
$upload_dir = __DIR__ . '/assets/images/products/';
$upload_exists = is_dir($upload_dir);
$upload_writable = $upload_exists && is_writable($upload_dir);

$checks['upload_dir'] = [
    'name' => 'Upload Directory',
    'status' => $upload_writable,
    'message' => $upload_writable ? 'Directory exists and writable' : ($upload_exists ? 'Not writable' : 'Directory not found'),
    'required' => 'assets/images/products/ must be writable'
];

// Check 6: Session Support
$checks['sessions'] = [
    'name' => 'Session Support',
    'status' => function_exists('session_start'),
    'message' => function_exists('session_start') ? 'Sessions enabled' : 'Sessions not available',
    'required' => 'PHP sessions must be enabled'
];

// Check 7: PDO MySQL Extension
$checks['pdo'] = [
    'name' => 'PDO MySQL Extension',
    'status' => extension_loaded('pdo_mysql'),
    'message' => extension_loaded('pdo_mysql') ? 'PDO MySQL loaded' : 'Extension not loaded',
    'required' => 'PDO MySQL extension required'
];

// Check 8: GD Library (for image processing)
$checks['gd'] = [
    'name' => 'GD Library',
    'status' => extension_loaded('gd'),
    'message' => extension_loaded('gd') ? 'GD Library available' : 'Not available',
    'required' => 'GD Library for image processing'
];

// Calculate overall status
$all_passed = true;
foreach ($checks as $check) {
    if (!$check['status']) {
        $all_passed = false;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineClick Setup Check</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            background: #fff;
            padding: 30px;
            border-radius: 12px 12px 0 0;
            text-align: center;
        }
        
        .header h1 {
            color: #B76E09;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #7D6E6E;
            font-size: 1rem;
        }
        
        .status-banner {
            padding: 20px;
            text-align: center;
            font-weight: 600;
            font-size: 1.2rem;
            color: #fff;
        }
        
        .status-banner.success {
            background: #28a745;
        }
        
        .status-banner.error {
            background: #dc3545;
        }
        
        .checks {
            background: #fff;
            padding: 30px;
            border-radius: 0 0 12px 12px;
        }
        
        .check-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            background: #f8f9fa;
            border-left: 4px solid #ddd;
        }
        
        .check-item.passed {
            background: #d4edda;
            border-left-color: #28a745;
        }
        
        .check-item.failed {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        
        .check-info h3 {
            font-size: 1.1rem;
            color: #000;
            margin-bottom: 5px;
        }
        
        .check-info p {
            font-size: 0.9rem;
            color: #7D6E6E;
            margin-bottom: 3px;
        }
        
        .check-status {
            font-size: 2rem;
        }
        
        .check-status.passed {
            color: #28a745;
        }
        
        .check-status.failed {
            color: #dc3545;
        }
        
        .actions {
            background: #fff;
            padding: 20px 30px;
            margin-top: 20px;
            border-radius: 12px;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 5px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #B76E09;
            color: #fff;
        }
        
        .btn-primary:hover {
            background: #8C5608;
        }
        
        .btn-secondary {
            background: #7D6E6E;
            color: #fff;
        }
        
        .btn-secondary:hover {
            background: #5a5353;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍴 DineClick Setup Check</h1>
            <p>Verifying system configuration and requirements</p>
        </div>
        
        <div class="status-banner <?php echo $all_passed ? 'success' : 'error'; ?>">
            <?php if ($all_passed): ?>
                ✓ All checks passed! Your system is ready.
            <?php else: ?>
                ⚠ Some checks failed. Please review below.
            <?php endif; ?>
        </div>
        
        <div class="checks">
            <?php foreach ($checks as $key => $check): ?>
                <div class="check-item <?php echo $check['status'] ? 'passed' : 'failed'; ?>">
                    <div class="check-info">
                        <h3><?php echo $check['name']; ?></h3>
                        <p><strong>Status:</strong> <?php echo $check['message']; ?></p>
                        <p><strong>Required:</strong> <?php echo $check['required']; ?></p>
                    </div>
                    <div class="check-status <?php echo $check['status'] ? 'passed' : 'failed'; ?>">
                        <?php echo $check['status'] ? '✓' : '✗'; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="actions">
            <h3 style="margin-bottom: 20px; color: #000;">Next Steps:</h3>
            
            <?php if ($all_passed): ?>
                <a href="login.php" class="btn btn-primary">Go to Login Page</a>
                <a href="register.php" class="btn btn-secondary">Create Account</a>
            <?php else: ?>
                <p style="color: #7D6E6E; margin-bottom: 15px;">
                    Please fix the failed checks and refresh this page.
                </p>
                <a href="setup_check.php" class="btn btn-secondary">Refresh Checks</a>
            <?php endif; ?>
            
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                <p style="color: #7D6E6E; font-size: 0.9rem;">
                    <strong>Need help?</strong> Check the INSTALLATION_GUIDE.txt file
                </p>
            </div>
        </div>
    </div>
</body>
</html>
