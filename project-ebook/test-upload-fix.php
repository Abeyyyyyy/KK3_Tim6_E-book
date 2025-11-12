<?php
echo "<h1>🛠 Testing Upload Fix</h1>";

// Test paths
$base_path = $_SERVER['DOCUMENT_ROOT'] . '/project-ebook/';
$uploads_path = $base_path . 'uploads/';
$covers_path = $uploads_path . 'covers/';
$books_path = $uploads_path . 'books/';

echo "<h3>Checking Paths:</h3>";
echo "Base: " . $base_path . "<br>";
echo "Uploads: " . $uploads_path . "<br>";
echo "Covers: " . $covers_path . "<br>";
echo "Books: " . $books_path . "<br>";

echo "<h3>Checking Folder Existence:</h3>";
echo "Uploads exists: " . (is_dir($uploads_path) ? '✅ YES' : '❌ NO') . "<br>";
echo "Covers exists: " . (is_dir($covers_path) ? '✅ YES' : '❌ NO') . "<br>";
echo "Books exists: " . (is_dir($books_path) ? '✅ YES' : '❌ NO') . "<br>";

echo "<h3>Checking Folder Permissions:</h3>";
echo "Uploads writable: " . (is_writable($uploads_path) ? '✅ YES' : '❌ NO') . "<br>";
echo "Covers writable: " . (is_writable($covers_path) ? '✅ YES' : '❌ NO') . "<br>";
echo "Books writable: " . (is_writable($books_path) ? '✅ YES' : '❌ NO') . "<br>";

// Test creating a file
echo "<h3>Testing File Creation:</h3>";
$test_file = $covers_path . 'test.txt';
if (file_put_contents($test_file, 'test')) {
    echo "✅ Bisa buat file di covers folder<br>";
    unlink($test_file);
} else {
    echo "❌ GAGAL buat file di covers folder<br>";
}

$test_file2 = $books_path . 'test.txt';
if (file_put_contents($test_file2, 'test')) {
    echo "✅ Bisa buat file di books folder<br>";
    unlink($test_file2);
} else {
    echo "❌ GAGAL buat file di books folder<br>";
}

echo "<hr>";
echo "<h2>📝 Instructions:</h2>";
echo "<ol>";
echo "<li>Pastikan folder <code>uploads/covers/</code> dan <code>uploads/books/</code> ada</li>";
echo "<li>Pastikan folder tersebut bisa ditulisi (writable)</li>";
echo "<li>Jika masih error, buka XAMPP sebagai Administrator</li>";
echo "</ol>";

echo "<a href='pages/teacher/add-book.php' style='background: blue; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🎯 Test Upload Buku Sekarang</a>";
?>