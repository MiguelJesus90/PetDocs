<?php
/**
 * Quick Diagnostic Script - Tests API endpoints
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

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => $_SERVER['SERVER_NAME'] ?? 'unknown',
    'tests' => []
];

// Test 1: Buffer cleaning
$results['tests']['buffer_cleaning'] = [
    'name' => 'Output Buffer Cleaning',
    'status' => 'success',
    'message' => 'Buffer cleaned successfully'
];

// Test 2: Config file
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
    $results['tests']['config'] = [
        'name' => 'Configuration',
        'status' => 'success',
        'message' => 'Config file loaded'
    ];

    // Test 3: Database connection
    try {
        $pdo = getDBConnection();
        $results['tests']['database'] = [
            'name' => 'Database Connection',
            'status' => 'success',
            'message' => 'Connected to ' . DB_NAME
        ];

        // Test 4: Pets table
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM pets");
            $count = $stmt->fetch()['count'];
            $results['tests']['pets_table'] = [
                'name' => 'Pets Table',
                'status' => 'success',
                'message' => "Found {$count} pets"
            ];
        } catch (PDOException $e) {
            $results['tests']['pets_table'] = [
                'name' => 'Pets Table',
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        // Test 5: Documents table
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM documents");
            $count = $stmt->fetch()['count'];
            $results['tests']['documents_table'] = [
                'name' => 'Documents Table',
                'status' => 'success',
                'message' => "Found {$count} documents"
            ];
        } catch (PDOException $e) {
            $results['tests']['documents_table'] = [
                'name' => 'Documents Table',
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

    } catch (Exception $e) {
        $results['tests']['database'] = [
            'name' => 'Database Connection',
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
} else {
    $results['tests']['config'] = [
        'name' => 'Configuration',
        'status' => 'error',
        'message' => 'Config file not found'
    ];
}

// Calculate summary
$total = count($results['tests']);
$passed = 0;
$failed = 0;

foreach ($results['tests'] as $test) {
    if ($test['status'] === 'success') {
        $passed++;
    } else {
        $failed++;
    }
}

$results['summary'] = [
    'total' => $total,
    'passed' => $passed,
    'failed' => $failed,
    'overall' => $failed === 0 ? 'ALL TESTS PASSED ✅' : 'SOME TESTS FAILED ❌'
];

// Clean buffer and send response
ob_end_clean();

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>