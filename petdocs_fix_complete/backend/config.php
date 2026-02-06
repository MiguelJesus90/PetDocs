<?php
/**
 * Database Configuration
 * Developed by Miguel Jesús Arias Cañete
 */

// CRITICAL: Disable display_errors to prevent output corruption
// InfinityFree may inject content that breaks JSON responses
error_reporting(E_ALL);
ini_set('display_errors', 0);  // Changed from 1 to 0
ini_set('log_errors', 1);

// Clean any output that might have been generated before this point
if (ob_get_level())
    ob_end_clean();
ob_start();

// CORS Headers (allow frontend to access API)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();  // Clean buffer before exit
    http_response_code(200);
    exit();
}

// Database configuration
// InfinityFree credentials
define('DB_HOST', 'sql108.infinityfree.com');     // InfinityFree MySQL host
define('DB_NAME', 'if0_40530495_petdocs_db');     // Your database name
define('DB_USER', 'if0_40530495');                // Your database username
define('DB_PASS', 'yTc4a2AkMlYyicJ');          // InfinityFree password
define('DB_CHARSET', 'utf8mb4');

// Create PDO connection
function getDBConnection()
{
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $e->getMessage()
        ]);
        exit();
    }
}

// Helper function to send JSON response
function sendResponse($data, $statusCode = 200)
{
    // Clean any buffered output to prevent corruption
    if (ob_get_level())
        ob_end_clean();

    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}
?>