<?php
/**
 * Database Seeding Script for PetDocs
 * Adds sample data to test the application
 * Developed by Miguel Jesús Arias Cañete
 */

// CRITICAL: Clean ALL output buffers before anything else
while (ob_get_level()) {
    ob_end_clean();
}

// Start fresh output buffering
ob_start();

require_once 'config.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    $pdo = getDBConnection();

    // Check if there's already data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM pets");
    $count = $stmt->fetch()['count'];

    if ($count > 0) {
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'message' => 'La base de datos ya contiene datos',
            'existing_pets' => $count
        ]);
        exit;
    }

    // Insert sample pets
    $samplePets = [
        [
            'name' => 'Max',
            'species' => 'Perro',
            'breed' => 'Labrador',
            'birth_date' => '2020-05-15',
            'owner_name' => 'Juan Pérez'
        ],
        [
            'name' => 'Luna',
            'species' => 'Gato',
            'breed' => 'Siamés',
            'birth_date' => '2021-03-20',
            'owner_name' => 'María García'
        ],
        [
            'name' => 'Rocky',
            'species' => 'Perro',
            'breed' => 'Pastor Alemán',
            'birth_date' => '2019-11-10',
            'owner_name' => 'Carlos Rodríguez'
        ]
    ];

    $inserted = 0;
    $stmt = $pdo->prepare("
        INSERT INTO pets (name, species, breed, birth_date, owner_name) 
        VALUES (:name, :species, :breed, :birth_date, :owner_name)
    ");

    foreach ($samplePets as $pet) {
        $stmt->execute($pet);
        $inserted++;
    }

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Datos de ejemplo insertados correctamente',
        'inserted_pets' => $inserted,
        'pets' => $samplePets
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Error al insertar datos: ' . $e->getMessage()
    ]);
}
?>