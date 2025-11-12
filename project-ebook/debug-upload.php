<?php
echo "<h1>🔧 Debug Upload System</h1>";

// Test paths
$paths = [
    'document_root' => $_SERVER['DOCUMENT_ROOT'],
    'project_path' => $_SERVER['DOCUMENT_ROOT'] . '/project-ebook/',
    'uploads_path' => $_SERVER['DOCUMENT_ROOT'] . '/project-ebook/uploads/',
    'covers_path' => $_SERVER['DOCUMENT_ROOT'] . '/project-ebook/uploads/covers/',
    'books_path' => $_SERVER['DOCUMENT_ROOT'] . '/project-ebook/uploads/books/'
];

foreach ($paths as $name => $path) {
    echo "<h3>Checking: {$name}</h3>";
    echo "Path: <code>{$path}</code><br>";
    echo "Exists: " . (file_exists($path) ? '✅ YES' : '❌ NO') . "<br>";
    echo "Is Directory: " . (is_dir($path) ? '✅ YES' : '❌ NO') . "<br>";
    echo "Writable: " . (is_writable($path) ? '✅ YES' : '❌ NO') . "<br>";
    echo "<hr>";
}

// Test file upload capabilities
echo "<h3>Testing File Upload Capabilities</h3>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";
echo "Max File Uploads: " . ini_get('max_file_uploads') . "<br>";

// Test creating directories
echo "<h3>Testing Directory Creation</h3>";
$testDirs = ['uploads', 'uploads/covers', 'uploads/books'];
foreach ($testDirs as $dir) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/project-ebook/' . $dir;
    if (!is_dir($fullPath)) {
        if (mkdir($fullPath, 0777, true)) {
            echo "✅ Created: {$dir}<br>";
        } else {
            echo "❌ Failed to create: {$dir}<br>";
        }
    } else {
        echo "✅ Already exists: {$dir}<br>";
    }
}

echo "<hr><h2>🎯 Quick Fix</h2>";
echo "<p>Jika folder tidak ada atau tidak writable, coba:</p>";
echo "<ol>";
echo "<li>Buat manual folder <code>uploads/covers</code> dan <code>uploads/books</code></li>";
echo "<li>Klik kanan folder → Properties → Hilangkan centang 'Read-only'</li>";
echo "<li>Atau jalankan XAMPP sebagai Administrator</li>";
echo "</ol>";

echo "<a href='pages/teacher/add-book.php'>➡️ Test Upload Kembali</a>";
?>