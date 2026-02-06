<?php
/**
 * Database Fix Script for PetDocs
 * Adds missing 'photo' column if it doesn't exist
 * Developed by Miguel Jesús Arias Cañete
 */

require_once 'config.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    $pdo = getDBConnection();

    $results = [
        'timestamp' => date('Y-m-d H:i:s'),
        'fixes_applied' => []
    ];

    // Check if 'photo' column exists in pets table
    $stmt = $pdo->query("DESCRIBE pets");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $results['existing_columns'] = $columns;

    if (!in_array('photo', $columns)) {
        // Add photo column
        $pdo->exec("ALTER TABLE pets ADD COLUMN photo VARCHAR(255) AFTER owner_name");
        $results['fixes_applied'][] = 'Added photo column to pets table';
    } else {
        $results['fixes_applied'][] = 'Photo column already exists';
    }

    // Check if 'updated_at' column exists
    if (!in_array('updated_at', $columns)) {
        $pdo->exec("ALTER TABLE pets ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        $results['fixes_applied'][] = 'Added updated_at column to pets table';
    } else {
        $results['fixes_applied'][] = 'Updated_at column already exists';
    }

    // Verify final structure
    $stmt = $pdo->query("DESCRIBE pets");
    $finalColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results['final_structure'] = $finalColumns;
    $results['success'] = true;
    $results['message'] = 'Database schema verified and fixed';

    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fixing database: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ], JSON_PRETTY_PRINT);
}
?>