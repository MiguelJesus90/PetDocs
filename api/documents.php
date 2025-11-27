<?php
/**
 * Documents API Endpoint
 * Developed by Miguel Jesús Arias Cañete
 */

require_once 'config.php';

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet($pdo);
        break;
    case 'POST':
        handlePost($pdo);
        break;
    case 'DELETE':
        handleDelete($pdo);
        break;
    default:
        sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// GET: Retrieve documents for a pet
function handleGet($pdo)
{
    if (!isset($_GET['pet_id'])) {
        sendResponse(['success' => false, 'message' => 'Pet ID required'], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM documents WHERE pet_id = ? ORDER BY upload_date DESC");
    $stmt->execute([$_GET['pet_id']]);
    $documents = $stmt->fetchAll();

    sendResponse(['success' => true, 'data' => $documents]);
}

// POST: Upload a new document
function handlePost($pdo)
{
    if (!isset($_POST['pet_id']) || !isset($_FILES['file'])) {
        sendResponse(['success' => false, 'message' => 'Missing required fields'], 400);
    }

    $petId = $_POST['pet_id'];
    $documentType = $_POST['document_type'] ?? 'general';
    $notes = $_POST['notes'] ?? '';
    $file = $_FILES['file'];

    // Validate file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
    if (!in_array($file['type'], $allowedTypes)) {
        sendResponse(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and PDF allowed'], 400);
    }

    // Max 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        sendResponse(['success' => false, 'message' => 'File too large. Max 5MB'], 400);
    }

    // Create upload directory if it doesn't exist
    $uploadDir = '../public/uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '_' . time() . '.' . $extension;
    $filePath = $uploadDir . $fileName;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        sendResponse(['success' => false, 'message' => 'Failed to upload file'], 500);
    }

    // Save to database
    $stmt = $pdo->prepare("
        INSERT INTO documents (pet_id, document_type, file_name, file_path, file_size, notes) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $petId,
        $documentType,
        $file['name'],
        'uploads/' . $fileName,
        $file['size'],
        $notes
    ]);

    $docId = $pdo->lastInsertId();
    sendResponse(['success' => true, 'message' => 'Document uploaded', 'id' => $docId], 201);
}

// DELETE: Remove a document
function handleDelete($pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'])) {
        sendResponse(['success' => false, 'message' => 'Document ID required'], 400);
    }

    // Get file path before deleting from DB
    $stmt = $pdo->prepare("SELECT file_path FROM documents WHERE id = ?");
    $stmt->execute([$data['id']]);
    $doc = $stmt->fetch();

    if ($doc) {
        // Delete file from server
        $filePath = '../public/' . $doc['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
        $stmt->execute([$data['id']]);

        sendResponse(['success' => true, 'message' => 'Document deleted']);
    } else {
        sendResponse(['success' => false, 'message' => 'Document not found'], 404);
    }
}
?>