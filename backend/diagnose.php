<?php
/**
 * Diagnostic Script for PetDocs
 * Tests database connection, tables, and basic operations
 * Developed by Miguel Jesús Arias Cañete
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => []
];

// Test 1: PHP Version
$results['tests']['php_version'] = [
    'name' => 'PHP Version',
    'status' => 'success',
    'value' => phpversion(),
    'message' => 'PHP is running'
];

// Test 2: Required Extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json'];
$extensions_ok = true;
$missing = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $extensions_ok = false;
        $missing[] = $ext;
    }
}

$results['tests']['extensions'] = [
    'name' => 'Required Extensions',
    'status' => $extensions_ok ? 'success' : 'error',
    'value' => $extensions_ok ? 'All required extensions loaded' : 'Missing: ' . implode(', ', $missing),
    'message' => $extensions_ok ? 'PDO, PDO_MySQL, and JSON extensions available' : 'Some extensions are missing'
];

// Test 3: Config File
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
    
    $results['tests']['config_file'] = [
        'name' => 'Configuration File',
        'status' => 'success',
        'value' => 'config.php found',
        'message' => 'Configuration file exists and loaded'
    ];
    
    // Test 4: Database Constants
    $constants_defined = defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS');
    
    $results['tests']['db_constants'] = [
        'name' => 'Database Constants',
        'status' => $constants_defined ? 'success' : 'error',
        'value' => $constants_defined ? 'All constants defined' : 'Some constants missing',
        'details' => [
            'DB_HOST' => defined('DB_HOST') ? DB_HOST : 'NOT DEFINED',
            'DB_NAME' => defined('DB_NAME') ? DB_NAME : 'NOT DEFINED',
            'DB_USER' => defined('DB_USER') ? DB_USER : 'NOT DEFINED',
            'DB_PASS' => defined('DB_PASS') ? '***' : 'NOT DEFINED'
        ]
    ];
    
    // Test 5: Database Connection
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
        $results['tests']['db_connection'] = [
            'name' => 'Database Connection',
            'status' => 'success',
            'value' => 'Connected successfully',
            'message' => 'PDO connection established to ' . DB_HOST
        ];
        
        // Test 6: Check Tables
        $tables = ['pets', 'documents'];
        $tables_status = [];
        
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $count = $stmt->fetch()['count'];
                $tables_status[$table] = [
                    'exists' => true,
                    'count' => $count,
                    'status' => 'success'
                ];
            } catch (PDOException $e) {
                $tables_status[$table] = [
                    'exists' => false,
                    'error' => $e->getMessage(),
                    'status' => 'error'
                ];
            }
        }
        
        $all_tables_ok = array_reduce($tables_status, function($carry, $item) {
            return $carry && $item['status'] === 'success';
        }, true);
        
        $results['tests']['tables'] = [
            'name' => 'Database Tables',
            'status' => $all_tables_ok ? 'success' : 'error',
            'value' => $tables_status,
            'message' => $all_tables_ok ? 'All required tables exist' : 'Some tables are missing or inaccessible'
        ];
        
        // Test 7: Table Structure - Pets
        try {
            $stmt = $pdo->query("DESCRIBE pets");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $results['tests']['pets_structure'] = [
                'name' => 'Pets Table Structure',
                'status' => 'success',
                'value' => $columns,
                'message' => 'Pets table structure retrieved'
            ];
        } catch (PDOException $e) {
            $results['tests']['pets_structure'] = [
                'name' => 'Pets Table Structure',
                'status' => 'error',
                'value' => null,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
        
        // Test 8: Table Structure - Documents
        try {
            $stmt = $pdo->query("DESCRIBE documents");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $results['tests']['documents_structure'] = [
                'name' => 'Documents Table Structure',
                'status' => 'success',
                'value' => $columns,
                'message' => 'Documents table structure retrieved'
            ];
        } catch (PDOException $e) {
            $results['tests']['documents_structure'] = [
                'name' => 'Documents Table Structure',
                'status' => 'error',
                'value' => null,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
        
        // Test 9: Test Query
        try {
            $stmt = $pdo->query("SELECT * FROM pets LIMIT 1");
            $sample = $stmt->fetch();
            
            $results['tests']['test_query'] = [
                'name' => 'Test Query',
                'status' => 'success',
                'value' => $sample ? 'Sample data retrieved' : 'No data in pets table',
                'sample' => $sample,
                'message' => 'SELECT query executed successfully'
            ];
        } catch (PDOException $e) {
            $results['tests']['test_query'] = [
                'name' => 'Test Query',
                'status' => 'error',
                'value' => null,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
        
        // Test 10: Uploads Directory
        $uploads_dir = __DIR__ . '/../public/uploads';
        $uploads_exists = is_dir($uploads_dir);
        $uploads_writable = $uploads_exists && is_writable($uploads_dir);
        
        $results['tests']['uploads_directory'] = [
            'name' => 'Uploads Directory',
            'status' => ($uploads_exists && $uploads_writable) ? 'success' : 'warning',
            'value' => [
                'path' => $uploads_dir,
                'exists' => $uploads_exists,
                'writable' => $uploads_writable
            ],
            'message' => $uploads_writable ? 'Uploads directory is writable' : 'Uploads directory not writable or missing'
        ];
        
    } catch (PDOException $e) {
        $results['tests']['db_connection'] = [
            'name' => 'Database Connection',
            'status' => 'error',
            'value' => null,
            'message' => 'Connection failed: ' . $e->getMessage(),
            'error_code' => $e->getCode()
        ];
    }
    
} else {
    $results['tests']['config_file'] = [
        'name' => 'Configuration File',
        'status' => 'error',
        'value' => 'config.php not found',
        'message' => 'Configuration file is missing'
    ];
}

// Summary
$total_tests = count($results['tests']);
$passed = 0;
$failed = 0;
$warnings = 0;

foreach ($results['tests'] as $test) {
    if ($test['status'] === 'success') {
        $passed++;
    } elseif ($test['status'] === 'error') {
        $failed++;
    } else {
        $warnings++;
    }
}

$results['summary'] = [
    'total' => $total_tests,
    'passed' => $passed,
    'failed' => $failed,
    'warnings' => $warnings,
    'overall_status' => $failed > 0 ? 'FAILED' : ($warnings > 0 ? 'WARNING' : 'PASSED')
];

// Output results
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
