<?php
/**
 * Advanced Diagnostic Script for PetDocs
 * Tests all operations and identifies the exact error
 */

require_once 'config.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<h1>PetDocs - Diagnóstico Avanzado</h1>";
echo "<pre>";

try {
    $pdo = getDBConnection();
    echo "✅ Conexión a base de datos exitosa\n\n";

    // 1. Verificar estructura de la tabla
    echo "=== ESTRUCTURA DE LA TABLA PETS ===\n";
    $stmt = $pdo->query("DESCRIBE pets");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "{$col['Field']} - {$col['Type']} - {$col['Null']} - {$col['Key']}\n";
    }

    // 2. Contar registros existentes
    echo "\n=== DATOS EXISTENTES ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM pets");
    $count = $stmt->fetch()['count'];
    echo "Total de mascotas: $count\n";

    if ($count > 0) {
        echo "\nÚltimas 3 mascotas:\n";
        $stmt = $pdo->query("SELECT id, name, species, owner_name, created_at FROM pets ORDER BY created_at DESC LIMIT 3");
        $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($pets as $pet) {
            echo "  - ID: {$pet['id']}, Nombre: {$pet['name']}, Especie: {$pet['species']}, Dueño: {$pet['owner_name']}, Creado: {$pet['created_at']}\n";
        }
    }

    // 3. Probar INSERT
    echo "\n=== PRUEBA DE INSERT ===\n";
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO pets (name, species, breed, birth_date, owner_name, photo) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            'TestDiagnostico',
            'Perro',
            'Test',
            '2020-01-01',
            'Test Owner',
            null
        ]);

        $insertId = $pdo->lastInsertId();

        echo "✅ INSERT exitoso - ID: $insertId\n";

        // Eliminar el registro de prueba
        $stmt = $pdo->prepare("DELETE FROM pets WHERE id = ?");
        $stmt->execute([$insertId]);
        echo "✅ DELETE de prueba exitoso\n";

        $pdo->commit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "❌ ERROR en INSERT: " . $e->getMessage() . "\n";
        echo "Código de error: " . $e->getCode() . "\n";
    }

    // 4. Verificar permisos del usuario
    echo "\n=== PERMISOS DE BASE DE DATOS ===\n";
    $stmt = $pdo->query("SHOW GRANTS");
    $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($grants as $grant) {
        echo "$grant\n";
    }

    // 5. Verificar variables del servidor
    echo "\n=== INFORMACIÓN DEL SERVIDOR ===\n";
    echo "PHP Version: " . phpversion() . "\n";
    echo "PDO Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    echo "Server Version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";

} catch (Exception $e) {
    echo "❌ ERROR FATAL: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "</pre>";
?>