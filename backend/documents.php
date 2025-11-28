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
        // Allow DELETE via POST for hosting compatibility
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            handleDelete($pdo);
        } else {
            handlePost($pdo);
        }
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
    // Check for upload errors first
    if (isset($_FILES['file']['error']) && $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
        ];
        $errorMessage = $uploadErrors[$_FILES['file']['error']] ?? 'Unknown upload error';
        sendResponse(['success' => false, 'message' => 'Upload failed: ' . $errorMessage], 400);
    }

    if (!isset($_POST['pet_id']) || !isset($_FILES['file'])) {
        sendResponse(['success' => false, 'message' => 'Missing required fields'], 400);
    }

    $petId = $_POST['pet_id'];
    $documentType = $_POST['document_type'] ?? 'general';
    $notes = $_POST['notes'] ?? '';
    $file = $_FILES['file'];

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
    if (!in_array($file['type'], $allowedTypes)) {
        sendResponse(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and PDF allowed'], 400);
    }

    // Max 5MB (Server might have lower limit, checked above)
    if ($file['size'] > 5 * 1024 * 1024) {
        sendResponse(['success' => false, 'message' => 'File too large. Max 5MB'], 400);
    }

    // Create upload directory if it doesn't exist
    // Use __DIR__ to get absolute path relative to this script
    // Changed to '../uploads/' because index.html is at root (htdocs), so uploads should be at root too (htdocs/uploads)
    $uploadDir = __DIR__ . '/../uploads/';

    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            sendResponse(['success' => false, 'message' => 'Failed to create upload directory'], 500);
        }
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '_' . time() . '.' . $extension;
    $filePath = $uploadDir . $fileName;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        $error = error_get_last();
        sendResponse(['success' => false, 'message' => 'Failed to move uploaded file: ' . ($error['message'] ?? '')], 500);
    }

    // Save to database
    // Store relative path for frontend use
    $dbFilePath = 'uploads/' . $fileName;

    $stmt = $pdo->prepare("
        INSERT INTO documents (pet_id, document_type, file_name, file_path, file_size, notes) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    try {
        $stmt->execute([
            $petId,
            $documentType,
            $file['name'],
            $dbFilePath,
            $file['size'],
            $notes
        ]);

        $docId = $pdo->lastInsertId();
        sendResponse(['success' => true, 'message' => 'Document uploaded', 'id' => $docId], 201);
    } catch (PDOException $e) {
        // If DB insert fails, delete the uploaded file to avoid orphans
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        sendResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

// DELETE: Remove a document
function handleDelete($pdo)
{
    // Enable error reporting for debugging this request
    ini_set('display_errors', 0); // Don't output errors to HTML, we want JSON

    $data = json_decode(file_get_contents('php://input'), true);

    // Try to get ID from JSON body or URL parameters (query string)
    // Some servers strip the body from DELETE requests
    $id = $data['id'] ?? $_GET['id'] ?? null;

    if (!$id) {
        sendResponse(['success' => false, 'message' => 'Document ID required'], 400);
    }

    try {
        // Get file path before deleting from DB
        $stmt = $pdo->prepare("SELECT file_path FROM documents WHERE id = ?");
        $stmt->execute([$id]);
        $doc = $stmt->fetch();

        if ($doc) {
            // 1. Try to delete from NEW location (root/uploads)
            $filePathNew = __DIR__ . '/../' . $doc['file_path'];
            if (file_exists($filePathNew)) {
                unlink($filePathNew);
            }

            // 2. Try to delete from OLD location (root/public/uploads) - just in case
            // If file_path is 'uploads/foo.jpg', this becomes '.../public/uploads/foo.jpg'
            $filePathOld = __DIR__ . '/../public/' . $doc['file_path'];
            if (file_exists($filePathOld)) {
                unlink($filePathOld);
            }

            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
            $stmt->execute([$id]);

            sendResponse(['success' => true, 'message' => 'Document deleted']);
        } else {
            sendResponse(['success' => false, 'message' => 'Document not found'], 404);
        }
    } catch (Exception $e) {
        sendResponse(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()], 500);
    }
}
?>