<?php
/**
 * Test POST operation to pets.php
 * Simulates creating a pet to see the exact error
 */

$url = 'https://petdocs-miguel.lovestoblog.com/backend/pets.php';

$data = [
    'name' => 'TestPet',
    'species' => 'Perro',
    'breed' => 'Test Breed',
    'birth_date' => '2020-01-01',
    'owner_name' => 'Test Owner'
];

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = @file_get_contents($url, false, $context);

echo "=== Response ===\n";
if ($result === false) {
    echo "Error: Could not connect to server\n";
    echo "HTTP Response Headers:\n";
    print_r($http_response_header ?? 'No headers');
} else {
    echo $result . "\n";
}

echo "\n=== HTTP Response Headers ===\n";
if (isset($http_response_header)) {
    foreach ($http_response_header as $header) {
        echo $header . "\n";
    }
}
?>