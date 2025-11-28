<?php
/**
 * Server Environment Checker
 * Upload this to your server to check configuration
 */

header('Content-Type: text/plain');

echo "=== Server Environment Check ===\n\n";

// Check PHP Version
echo "PHP Version: " . phpversion() . "\n";

// Check Upload Limits
echo "\n=== Upload Limits ===\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";

// Check Directories
echo "\n=== Directories ===\n";
echo "Current Directory: " . __DIR__ . "\n";
$uploadDir = __DIR__ . '/../public/uploads/';
echo "Target Upload Directory: " . $uploadDir . "\n";

if (file_exists($uploadDir)) {
    echo "Upload Directory Exists: YES\n";
    echo "Writable: " . (is_writable($uploadDir) ? "YES" : "NO") . "\n";
    echo "Permissions: " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "\n";
} else {
    echo "Upload Directory Exists: NO\n";
    echo "Attempting to create...\n";
    if (mkdir($uploadDir, 0755, true)) {
        echo "Created successfully.\n";
    } else {
        echo "Failed to create. Check parent directory permissions.\n";
    }
}

// Check GD Library (for images)
echo "\n=== Extensions ===\n";
echo "GD Library: " . (extension_loaded('gd') ? "Installed" : "Not Installed") . "\n";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? "Installed" : "Not Installed") . "\n";

echo "\n=== End Check ===";
?>