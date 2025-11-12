<?php
echo "<h1>🛠 Test Paths</h1>";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Expected Path: " . $_SERVER['DOCUMENT_ROOT'] . '/project-ebook/uploads/covers/' . "<br>";

$test_path = $_SERVER['DOCUMENT_ROOT'] . '/project-ebook/uploads/covers/';
echo "Path exists: " . (is_dir($test_path) ? 'YES' : 'NO') . "<br>";
echo "Path writable: " . (is_writable($test_path) ? 'YES' : 'NO') . "<br>";

// Try to create test file
$test_file = $test_path . 'test.txt';
if (file_put_contents($test_file, 'test')) {
    echo "✅ Bisa create file<br>";
    unlink($test_file);
} else {
    echo "❌ GAGAL create file<br>";
}
?>