<?php
/**
 * Pets API Endpoint
 * Developed by Miguel Jesús Arias Cañete
 */

// CRITICAL: Clean ALL output buffers before anything else
// InfinityFree injects HTML/ads that corrupt JSON responses
while (ob_get_level()) {
    ob_end_clean();
}

// Start fresh output buffering
ob_start();

require_once 'config.php';

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Method spoofing for free hosting that blocks DELETE/PUT
if ($method === 'POST' && isset($_GET['action'])) {
    if ($_GET['action'] === 'delete') {
        $method = 'DELETE';
    } elseif ($_GET['action'] === 'put') {
        $method = 'PUT';
    }
}

switch ($method) {
    case 'GET':
        handleGet($pdo);
        break;
    case 'POST':
        handlePost($pdo);
        break;
    case 'PUT':
        handlePut($pdo);
        break;
    case 'DELETE':
        handleDelete($pdo);
        break;
    default:
        sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// GET: Retrieve all pets or a specific pet
function handleGet($pdo)
{
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM pets WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $pet = $stmt->fetch();

        if ($pet) {
            sendResponse(['success' => true, 'data' => $pet]);
        } else {
            sendResponse(['success' => false, 'message' => 'Pet not found'], 404);
        }
    } else {
        $stmt = $pdo->query("SELECT * FROM pets ORDER BY created_at DESC");
        $pets = $stmt->fetchAll();
        sendResponse(['success' => true, 'data' => $pets]);
    }
}

// POST: Create a new pet
function handlePost($pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['name']) || !isset($data['species']) || !isset($data['owner_name'])) {
        sendResponse(['success' => false, 'message' => 'Missing required fields'], 400);
    }

    $stmt = $pdo->prepare("
        INSERT INTO pets (name, species, breed, birth_date, owner_name, photo) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['name'],
        $data['species'],
        $data['breed'] ?? null,
        $data['birth_date'] ?? null,
        $data['owner_name'],
        $data['photo'] ?? null
    ]);

    $petId = $pdo->lastInsertId();
    sendResponse(['success' => true, 'message' => 'Pet created', 'id' => $petId], 201);
}

// PUT: Update a pet
function handlePut($pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'])) {
        sendResponse(['success' => false, 'message' => 'Pet ID required'], 400);
    }

    $stmt = $pdo->prepare("
        UPDATE pets 
        SET name = ?, species = ?, breed = ?, birth_date = ?, owner_name = ?, photo = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $data['name'],
        $data['species'],
        $data['breed'] ?? null,
        $data['birth_date'] ?? null,
        $data['owner_name'],
        $data['photo'] ?? null,
        $data['id']
    ]);

    sendResponse(['success' => true, 'message' => 'Pet updated']);
}

// DELETE: Remove a pet
function handleDelete($pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'])) {
        sendResponse(['success' => false, 'message' => 'Pet ID required'], 400);
    }

    $stmt = $pdo->prepare("DELETE FROM pets WHERE id = ?");
    $stmt->execute([$data['id']]);

    sendResponse(['success' => true, 'message' => 'Pet deleted']);
}
?>