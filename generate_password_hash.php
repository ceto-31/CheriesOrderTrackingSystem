<?php
/**
 * Password Hash Generator
 * Use this to generate secure password hashes for admin accounts
 */

// Generate hash for "admin123"
$password = "admin123";
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<!DOCTYPE html>";
echo "<html><head><title>Password Hash Generator</title>";
echo "<style>body{font-family:Arial;padding:40px;background:#f5f5f5;}";
echo ".container{background:#fff;padding:30px;border-radius:8px;max-width:800px;margin:0 auto;}";
echo "h1{color:#B76E09;}";
echo ".hash{background:#f8f9fa;padding:15px;border-radius:5px;word-break:break-all;border-left:4px solid #28a745;margin:20px 0;}";
echo ".sql{background:#f8f9fa;padding:15px;border-radius:5px;border-left:4px solid #007bff;margin:20px 0;}";
echo "code{color:#dc3545;}</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔐 Password Hash Generated</h1>";
echo "<p><strong>Password:</strong> <code>admin123</code></p>";
echo "<p><strong>Generated Hash:</strong></p>";
echo "<div class='hash'>" . htmlspecialchars($hash) . "</div>";

echo "<h2>SQL Update Command:</h2>";
echo "<p>Run this SQL in phpMyAdmin to update the admin password:</p>";
echo "<div class='sql'>";
echo "<code>UPDATE users SET password = '" . $hash . "' WHERE email = 'admin@dineclick.com';</code>";
echo "</div>";

echo "<h2>Quick Fix Instructions:</h2>";
echo "<ol>";
echo "<li>Copy the SQL command above</li>";
echo "<li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
echo "<li>Select 'dineclick_db' database</li>";
echo "<li>Click 'SQL' tab</li>";
echo "<li>Paste the SQL command</li>";
echo "<li>Click 'Go'</li>";
echo "<li>Try logging in again with: admin@dineclick.com / admin123</li>";
echo "</ol>";

echo "<h2>Alternative: Create New Admin</h2>";
echo "<p>If the above doesn't work, you can register a new account and manually make it admin:</p>";
echo "<ol>";
echo "<li>Register at: <a href='register.php'>register.php</a></li>";
echo "<li>In phpMyAdmin, run: <code>UPDATE users SET is_admin = 1 WHERE email = 'your_email@example.com';</code></li>";
echo "</ol>";

echo "<hr style='margin:30px 0;'>";
echo "<p><a href='login.php' style='display:inline-block;padding:12px 24px;background:#B76E09;color:#fff;text-decoration:none;border-radius:5px;'>Go to Login Page</a></p>";
echo "</div></body></html>";
?>
