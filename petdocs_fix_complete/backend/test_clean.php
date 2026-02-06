<?php
/**
 * Clean Test Script - Tests if output buffer cleaning works
 * Developed by Miguel Jesús Arias Cañete
 */

// CRITICAL: Clean ALL output buffers before anything else
while (ob_get_level()) {
    ob_end_clean();
}

// Start fresh output buffering
ob_start();

// Set headers
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

// Clean buffer and send response
ob_end_clean();

echo json_encode([
    'success' => true,
    'message' => 'Clean JSON response test',
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => $_SERVER['SERVER_NAME'] ?? 'unknown'
], JSON_PRETTY_PRINT);
?>