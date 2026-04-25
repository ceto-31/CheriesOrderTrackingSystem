<?php
/**
 * Admin Debug Tool
 * This will help identify issues with the admin panel
 */
require_once '../config/config.php';

// Force display errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

$errors = [];
$warnings = [];
$success = [];

// Check 1: Session
if (session_status() === PHP_SESSION_ACTIVE) {
    $success[] = "✓ Session is active";
    if (isset($_SESSION['user_id'])) {
        $success[] = "✓ User is logged in (ID: " . $_SESSION['user_id'] . ")";
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
            $success[] = "✓ User has admin privileges";
        } else {
            $errors[] = "✗ User is NOT admin (is_admin: " . ($_SESSION['is_admin'] ?? 'not set') . ")";
        }
    } else {
        $errors[] = "✗ User is NOT logged in";
    }
} else {
    $errors[] = "✗ Session is not active";
}

// Check 2: Database Connection
try {
    $database = new Database();
    $db = $database->getConnection();
    $success[] = "✓ Database connection successful";
    
    // Test query
    $test = $db->query("SELECT COUNT(*) as count FROM products")->fetch();
    $success[] = "✓ Database query works (" . $test['count'] . " products found)";
    
    // Check categories
    $cats = $db->query("SELECT COUNT(*) as count FROM categories")->fetch();
    $success[] = "✓ Categories table accessible (" . $cats['count'] . " categories found)";
} catch (Exception $e) {
    $errors[] = "✗ Database error: " . $e->getMessage();
}

// Check 3: File Permissions
$upload_path = __DIR__ . '/../assets/images/products/';
if (file_exists($upload_path)) {
    $success[] = "✓ Upload directory exists: " . $upload_path;
    if (is_writable($upload_path)) {
        $success[] = "✓ Upload directory is writable";
    } else {
        $warnings[] = "⚠ Upload directory is NOT writable (may affect image uploads)";
    }
} else {
    $errors[] = "✗ Upload directory does NOT exist: " . $upload_path;
}

// Check 4: Required Files
$required_files = [
    'product_save.php' => __DIR__ . '/product_save.php',
    'product_delete.php' => __DIR__ . '/product_delete.php',
    'products.php' => __DIR__ . '/products.php',
    '../config/config.php' => __DIR__ . '/../config/config.php',
    '../includes/header.php' => __DIR__ . '/../includes/header.php',
    '../includes/footer.php' => __DIR__ . '/../includes/footer.php',
];

foreach ($required_files as $name => $path) {
    if (file_exists($path)) {
        $success[] = "✓ File exists: " . $name;
    } else {
        $errors[] = "✗ File MISSING: " . $name . " at " . $path;
    }
}

// Check 5: PHP Configuration
$success[] = "✓ PHP Version: " . phpversion();
$upload_max = ini_get('upload_max_filesize');
$post_max = ini_get('post_max_size');
$success[] = "✓ Upload max filesize: " . $upload_max;
$success[] = "✓ Post max size: " . $post_max;

// Check 6: jQuery and SweetAlert availability (test connection)
$jquery_test = @file_get_contents('https://code.jquery.com/jquery-3.6.0.min.js', false, stream_context_create(['http' => ['timeout' => 2]]));
if ($jquery_test !== false) {
    $success[] = "✓ jQuery CDN accessible";
} else {
    $warnings[] = "⚠ Cannot reach jQuery CDN (check internet connection)";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineClick - Admin Debugger</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #B76E09;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }
        .subtitle {
            color: #7D6E6E;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 10px;
            background: #f8f9fa;
        }
        .section h2 {
            margin-bottom: 15px;
            color: #333;
            font-size: 1.5rem;
            border-bottom: 2px solid #B76E09;
            padding-bottom: 10px;
        }
        .item {
            padding: 10px;
            margin: 8px 0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        .test-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .btn {
            padding: 15px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-align: center;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-primary {
            background: #B76E09;
            color: #fff;
        }
        .btn-success {
            background: #28a745;
            color: #fff;
        }
        .btn-info {
            background: #17a2b8;
            color: #fff;
        }
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        .btn-danger {
            background: #dc3545;
            color: #fff;
        }
        #console-output {
            background: #1e1e1e;
            color: #00ff00;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            max-height: 400px;
            overflow-y: auto;
            margin-top: 20px;
            white-space: pre-wrap;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .summary-card {
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            color: #fff;
        }
        .summary-card h3 {
            font-size: 2.5rem;
            margin-bottom: 5px;
        }
        .summary-card p {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .card-success { background: linear-gradient(135deg, #28a745, #20c997); }
        .card-error { background: linear-gradient(135deg, #dc3545, #c82333); }
        .card-warning { background: linear-gradient(135deg, #ffc107, #ff9800); }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 DineClick Admin Debugger</h1>
        <p class="subtitle">Comprehensive system diagnostic tool</p>
        
        <div class="summary">
            <div class="summary-card card-success">
                <h3><?php echo count($success); ?></h3>
                <p>Passed Checks</p>
            </div>
            <div class="summary-card card-error">
                <h3><?php echo count($errors); ?></h3>
                <p>Critical Errors</p>
            </div>
            <div class="summary-card card-warning">
                <h3><?php echo count($warnings); ?></h3>
                <p>Warnings</p>
            </div>
        </div>

        <?php if (count($errors) > 0): ?>
        <div class="section">
            <h2>❌ Critical Errors</h2>
            <?php foreach ($errors as $error): ?>
                <div class="item error"><?php echo $error; ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (count($warnings) > 0): ?>
        <div class="section">
            <h2>⚠️ Warnings</h2>
            <?php foreach ($warnings as $warning): ?>
                <div class="item warning"><?php echo $warning; ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="section">
            <h2>✅ Successful Checks</h2>
            <?php foreach ($success as $item): ?>
                <div class="item success"><?php echo $item; ?></div>
            <?php endforeach; ?>
        </div>

        <div class="section">
            <h2>🧪 Interactive Tests</h2>
            <p style="margin-bottom: 15px; color: #7D6E6E;">Click buttons below to test specific functionality. Watch the console output for results.</p>
            
            <div class="test-buttons">
                <button class="btn btn-primary" id="testJQuery">Test jQuery</button>
                <button class="btn btn-success" id="testSweetAlert">Test SweetAlert</button>
                <button class="btn btn-info" id="testAjaxSave">Test AJAX Save</button>
                <button class="btn btn-warning" id="testAjaxDelete">Test AJAX Delete</button>
                <button class="btn btn-danger" id="testButtonClick">Test Button Click</button>
                <button class="btn btn-primary" id="testFormData">Test FormData</button>
            </div>
            
            <div id="console-output">Console output will appear here...</div>
        </div>

        <div class="section">
            <h2>📋 Quick Actions</h2>
            <div class="test-buttons">
                <a href="products.php" class="btn btn-primary">Go to Products Page</a>
                <a href="dashboard.php" class="btn btn-success">Go to Dashboard</a>
                <a href="../setup_check.php" class="btn btn-info">Run Setup Check</a>
                <button class="btn btn-warning" id="clearCache">Clear Browser Cache</button>
            </div>
        </div>

        <div class="section">
            <h2>💡 Recommendations</h2>
            <?php if (count($errors) > 0): ?>
                <div class="item error">
                    <strong>Action Required:</strong> Fix the critical errors listed above before proceeding.
                </div>
            <?php elseif (count($warnings) > 0): ?>
                <div class="item warning">
                    <strong>Optional:</strong> Address the warnings for optimal performance.
                </div>
            <?php else: ?>
                <div class="item success">
                    <strong>System Status:</strong> All checks passed! Your system should be working correctly.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const consoleOutput = $('#console-output');
        
        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const colors = {
                'info': '#00ff00',
                'success': '#00ff00',
                'error': '#ff0000',
                'warning': '#ffff00'
            };
            consoleOutput.append(
                `<span style="color: ${colors[type]}">[${timestamp}] ${message}</span>\n`
            );
            consoleOutput.scrollTop(consoleOutput[0].scrollHeight);
        }

        // Test jQuery
        $('#testJQuery').click(function() {
            log('Testing jQuery...', 'info');
            if (typeof jQuery !== 'undefined') {
                log('✓ jQuery is loaded (version: ' + jQuery.fn.jquery + ')', 'success');
                log('✓ $ selector works', 'success');
                log('✓ Click event works', 'success');
            } else {
                log('✗ jQuery is NOT loaded', 'error');
            }
        });

        // Test SweetAlert
        $('#testSweetAlert').click(function() {
            log('Testing SweetAlert2...', 'info');
            if (typeof Swal !== 'undefined') {
                log('✓ SweetAlert2 is loaded', 'success');
                Swal.fire({
                    title: 'Success!',
                    text: 'SweetAlert2 is working correctly',
                    icon: 'success',
                    confirmButtonColor: '#B76E09'
                });
                log('✓ SweetAlert dialog opened', 'success');
            } else {
                log('✗ SweetAlert2 is NOT loaded', 'error');
            }
        });

        // Test AJAX Save
        $('#testAjaxSave').click(function() {
            log('Testing AJAX connection to product_save.php...', 'info');
            
            const testData = new FormData();
            testData.append('name', 'Test Product');
            testData.append('description', 'Debug test');
            testData.append('category_id', '1');
            testData.append('price', '100');
            testData.append('stock', '50');
            testData.append('is_available', '1');
            
            $.ajax({
                url: 'product_save.php',
                type: 'POST',
                data: testData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    log('✓ AJAX request successful', 'success');
                    log('Response: ' + JSON.stringify(response), 'success');
                },
                error: function(xhr, status, error) {
                    log('✗ AJAX request failed', 'error');
                    log('Status: ' + status, 'error');
                    log('Error: ' + error, 'error');
                    log('Response: ' + xhr.responseText, 'error');
                }
            });
        });

        // Test AJAX Delete
        $('#testAjaxDelete').click(function() {
            log('Testing AJAX connection to product_delete.php...', 'info');
            $.ajax({
                url: 'product_delete.php',
                type: 'POST',
                data: { product_id: 999999 },
                dataType: 'json',
                success: function(response) {
                    log('✓ AJAX connection works', 'success');
                    log('Response: ' + JSON.stringify(response), 'info');
                },
                error: function(xhr, status, error) {
                    log('✗ AJAX request failed', 'error');
                    log('Status: ' + status, 'error');
                    log('Error: ' + error, 'error');
                }
            });
        });

        // Test Button Click
        $('#testButtonClick').click(function() {
            log('Testing button click events...', 'info');
            log('✓ This button click works!', 'success');
            
            // Test if buttons are being blocked
            const button = $(this);
            if (button.is(':visible')) {
                log('✓ Button is visible', 'success');
            }
            if (!button.is(':disabled')) {
                log('✓ Button is not disabled', 'success');
            }
            
            log('Testing if any CSS is blocking clicks...', 'info');
            const zIndex = button.css('z-index');
            const pointerEvents = button.css('pointer-events');
            log('z-index: ' + zIndex, 'info');
            log('pointer-events: ' + pointerEvents, 'info');
        });

        // Test FormData
        $('#testFormData').click(function() {
            log('Testing FormData support...', 'info');
            if (typeof FormData !== 'undefined') {
                log('✓ FormData is supported', 'success');
                const fd = new FormData();
                fd.append('test', 'value');
                log('✓ Can create FormData object', 'success');
            } else {
                log('✗ FormData is NOT supported', 'error');
            }
        });

        // Clear Cache
        $('#clearCache').click(function() {
            log('Attempting to clear cache...', 'info');
            if (caches) {
                caches.keys().then(names => {
                    names.forEach(name => {
                        caches.delete(name);
                    });
                });
                log('✓ Cache cleared', 'success');
            }
            log('Please also press Ctrl+F5 to hard refresh', 'warning');
        });

        // Initial log
        log('Debugger loaded successfully', 'success');
        log('System checks complete. Click buttons above to run tests.', 'info');
    </script>
</body>
</html>
