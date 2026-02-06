<?php
/**
 * Debug Response Script
 * Shows exactly what the backend is sending
 */

// Capture all output
ob_start();

// Include config and get pets
require_once 'config.php';

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM pets ORDER BY created_at DESC");
$pets = $stmt->fetchAll();

$response = ['success' => true, 'data' => $pets];

// Get the output buffer (any errors or warnings)
$buffer = ob_get_clean();

// Show debug info
header('Content-Type: text/html; charset=UTF-8');
echo "<h1>Debug Response</h1>";
echo "<h2>Output Buffer (should be empty):</h2>";
echo "<pre>";
echo htmlspecialchars($buffer);
echo "</pre>";
echo "<p>Length: " . strlen($buffer) . " bytes</p>";

echo "<h2>JSON Response:</h2>";
echo "<pre>";
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";

echo "<h2>Raw Response (hex):</h2>";
echo "<pre>";
$json = json_encode($response);
for ($i = 0; $i < min(200, strlen($json)); $i++) {
    printf("%02X ", ord($json[$i]));
    if (($i + 1) % 16 == 0)
        echo "\n";
}
echo "</pre>";
?>