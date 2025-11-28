<?php
/**
 * Manual Delete Debug Tool (ROOT VERSION)
 * Upload this to your ROOT folder (htdocs), NOT in api.
 */

// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Adjusted path to config (since we are in root now)
require_once 'api/config.php';

// Override JSON header from config.php
header('Content-Type: text/html; charset=UTF-8');

echo "<h1>Debug Document Deletion (Root Version)</h1>";

try {
    $pdo = getDBConnection();

    // Handle Delete Request
    if (isset($_GET['delete_id'])) {
        $id = $_GET['delete_id'];
        echo "<div style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
        echo "<strong>Attempting to delete ID: $id</strong><br>";

        // 1. Get info
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
        $stmt->execute([$id]);
        $doc = $stmt->fetch();

        if (!$doc) {
            echo "❌ Document not found in DB.<br>";
        } else {
            echo "✅ Found in DB: " . htmlspecialchars($doc['file_name']) . " (Path: " . htmlspecialchars($doc['file_path']) . ")<br>";

            // 2. Check Paths
            // We are in root, so we don't need /../
            $path1 = __DIR__ . '/' . $doc['file_path'];
            $path2 = __DIR__ . '/public/' . $doc['file_path'];

            echo "Checking Path 1 (New): $path1 <br>";
            if (file_exists($path1)) {
                echo "✅ File exists at Path 1. Deleting... ";
                if (unlink($path1))
                    echo "✅ Deleted.<br>";
                else
                    echo "❌ Failed to unlink (Check Permissions).<br>";
            } else {
                echo "⚠️ File not found at Path 1.<br>";
            }

            echo "Checking Path 2 (Old): $path2 <br>";
            if (file_exists($path2)) {
                echo "✅ File exists at Path 2. Deleting... ";
                if (unlink($path2))
                    echo "✅ Deleted.<br>";
                else
                    echo "❌ Failed to unlink (Check Permissions).<br>";
            } else {
                echo "⚠️ File not found at Path 2.<br>";
            }

            // 3. Delete from DB
            echo "Deleting from DB... ";
            $del = $pdo->prepare("DELETE FROM documents WHERE id = ?");
            if ($del->execute([$id])) {
                echo "✅ Database record deleted.<br>";
            } else {
                echo "❌ Database delete failed.<br>";
                print_r($del->errorInfo());
            }
        }
        echo "</div><hr>";
    }

    // List Documents
    echo "<h2>Current Documents</h2>";
    $stmt = $pdo->query("SELECT * FROM documents ORDER BY id DESC");
    $docs = $stmt->fetchAll();

    if (empty($docs)) {
        echo "No documents found.";
    } else {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>File Name</th><th>Path</th><th>Action</th></tr>";
        foreach ($docs as $doc) {
            echo "<tr>";
            echo "<td>" . $doc['id'] . "</td>";
            echo "<td>" . htmlspecialchars($doc['file_name']) . "</td>";
            echo "<td>" . htmlspecialchars($doc['file_path']) . "</td>";
            echo "<td><a href='?delete_id=" . $doc['id'] . "' onclick='return confirm(\"Are you sure?\")' style='color: red;'>[DELETE DEBUG]</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }

} catch (Exception $e) {
    echo "<h1>CRITICAL ERROR</h1>";
    echo $e->getMessage();
}
?>