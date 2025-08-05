<?php
// ==================== FILE MANAGER CONFIG ====================
ini_set('display_errors', 1);
error_reporting(E_ALL);

function sanitize($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function isEditable($file) {
    return is_file($file) && is_writable($file);
}

// Get path from URL or default to current
$path = isset($_GET['path']) ? realpath($_GET['path']) : getcwd();
$files = scandir($path);

// Save edited file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_path'], $_POST['file_content'])) {
    file_put_contents($_POST['file_path'], $_POST['file_content']);
    echo "<p style='color:green'>✅ File saved!</p>";
}

// Edit file view
if (isset($_GET['edit']) && is_file($_GET['edit'])) {
    $fileToEdit = $_GET['edit'];
    $content = file_get_contents($fileToEdit);
    ?>
    <h2>📝 Editing: <?= sanitize($fileToEdit) ?></h2>
    <form method="post">
        <textarea name="file_content" rows="20" style="width:100%"><?= sanitize($content) ?></textarea><br>
        <input type="hidden" name="file_path" value="<?= sanitize($fileToEdit) ?>">
        <button type="submit">💾 Save</button>
    </form>
    <hr>
    <a href="?path=<?= urlencode($path) ?>">⬅️ Back</a>
    <?php exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP File Manager</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        table { width: 100%; background: #fff; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; }
        th { background: #eee; }
        a { text-decoration: none; color: #0066cc; }
        .not-editable { color: #999; }
    </style>
</head>
<body>
    <h2>📂 Directory: <?= sanitize($path) ?></h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($files as $file): ?>
            <?php
                if ($file === '.') continue;
                $fullPath = realpath($path . DIRECTORY_SEPARATOR . $file);
                $isDir = is_dir($fullPath);
                $type = $isDir ? '📁 Folder' : '📄 File';
                $size = $isDir ? '-' : filesize($fullPath);
                $encoded = urlencode($fullPath);
            ?>
            <tr>
                <td><?= sanitize($file) ?></td>
                <td><?= $type ?></td>
                <td><?= $size ?></td>
                <td>
                    <?php if ($isDir): ?>
                        <a href="?path=<?= $encoded ?>">📂 Open</a>
                    <?php elseif (isEditable($fullPath)): ?>
                        <a href="?edit=<?= $encoded ?>">✏️ Edit</a>
                    <?php else: ?>
                        <span class="not-editable">🚫 Not editable</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
