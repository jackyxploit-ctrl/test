<?php
$path = isset($_GET['path']) ? $_GET['path'] : getcwd();
$path = realpath($path); // resolve symbolic paths
$files = scandir($path);

function isEditable($file) {
    return is_file($file) && is_writable($file);
}

function sanitize($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Handle file save
if (isset($_POST['save_file']) && isset($_POST['file_path'])) {
    file_put_contents($_POST['file_path'], $_POST['file_content']);
    echo "<p style='color:green'>✅ File saved successfully!</p>";
}

// Handle file view/edit
if (isset($_GET['edit']) && is_file($_GET['edit'])) {
    $fileToEdit = $_GET['edit'];
    $content = file_get_contents($fileToEdit);
    echo "<h2>Editing: " . sanitize($fileToEdit) . "</h2>";
    echo "<form method='post'>";
    echo "<textarea name='file_content' rows='20' style='width:100%'>" . sanitize($content) . "</textarea>";
    echo "<input type='hidden' name='file_path' value='" . sanitize($fileToEdit) . "' />";
    echo "<br><button type='submit' name='save_file'>💾 Save File</button>";
    echo "</form>";
    echo "<hr><a href='?path=" . urlencode($path) . "'>⬅️ Back to File List</a>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP File Manager (Local)</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        table { width: 100%; background: #fff; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; }
        th { background: #eee; }
        a { text-decoration: none; }
    </style>
</head>
<body>
    <h2>📁 Directory: <?= sanitize($path) ?></h2>
    <table>
        <thead>
            <tr><th>Name</th><th>Type</th><th>Size</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($files as $file): ?>
            <?php
                if ($file === '.') continue;
                $fullPath = $path . DIRECTORY_SEPARATOR . $file;
                $type = is_dir($fullPath) ? '📂 Dir' : '📄 File';
                $size = is_file($fullPath) ? filesize($fullPath) : '-';
                $linkPath = urlencode($fullPath);
            ?>
            <tr>
                <td><?= sanitize($file) ?></td>
                <td><?= $type ?></td>
                <td><?= $size ?></td>
                <td>
                    <?php if (is_dir($fullPath)): ?>
                        <a href="?path=<?= $linkPath ?>">🔍 Open</a>
                    <?php elseif (isEditable($fullPath)): ?>
                        <a href="?edit=<?= $linkPath ?>">✏️ Edit</a>
                    <?php else: ?>
                        ⚠️ Not editable
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
