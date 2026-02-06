<?php
header('Content-Type: text/plain');

function checkFile($paths, $searchString = null, $searchName = '')
{
    $foundPath = null;
    if (!is_array($paths))
        $paths = [$paths];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            $foundPath = $path;
            break;
        }
    }

    if (!$foundPath) {
        echo "Checking: " . implode(' OR ', $paths) . "\n";
        echo "  ❌ File not found in any location\n\n";
        return;
    }

    echo "Checking: $foundPath\n";
    $perms = substr(sprintf('%o', fileperms($foundPath)), -4);
    echo "  ✅ Exists. Permissions: $perms\n";

    if ($searchString) {
        $content = file_get_contents($foundPath);
        if (strpos($content, $searchString) !== false) {
            echo "  ✅ Logic found: $searchName\n";
        } else {
            echo "  ❌ Logic MISSING: $searchName\n";
        }
    }

    // Show snippet for CSS and HTML
    if (strpos($foundPath, '.css') !== false || strpos($foundPath, '.html') !== false) {
        $content = file_get_contents($foundPath);
        echo "  📄 Content Snippet (First 100 chars):\n";
        echo "  " . substr(htmlspecialchars($content), 0, 100) . "...\n";
    }
    echo "\n";
}

echo "=== FILE VERIFICATION V3 ===\n";

// Check Config
checkFile('config.php', "ini_set('display_errors', 0)", "display_errors = 0");

// Check Pets (Method Spoofing)
checkFile('pets.php', "if (\$_GET['action'] === 'delete')", "Method Spoofing Logic");

// Check CSS (Try both public/css and root css)
checkFile(['../public/css/style.css', '../css/style.css']);

// Check JS (Try both public/js and root js)
checkFile(['../public/js/app.js', '../js/app.js'], "action=delete", "Delete Fix in JS");

// Check Index HTML (Try root and public)
checkFile(['../index.html', '../public/index.html'], "style.css?v=1.0", "Cache Busting v=1.0");
?>