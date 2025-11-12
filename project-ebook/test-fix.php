<?php
echo "<h1>🔧 Test Setelah Perbaikan Manual</h1>";

$base_path = $_SERVER['DOCUMENT_ROOT'] . '/project-ebook/uploads/';
$covers_path = $base_path . 'covers/';
$books_path = $base_path . 'books/';

echo "<h3>Checking Paths:</h3>";
echo "Base: " . $base_path . "<br>";
echo "Covers: " . $covers_path . "<br>";
echo "Books: " . $books_path . "<br>";

echo "<h3>Folder Existence (After Manual Creation):</h3>";
echo "Uploads: " . (is_dir($base_path) ? '✅ ADA' : '❌ TIDAK ADA') . "<br>";
echo "Covers: " . (is_dir($covers_path) ? '✅ ADA' : '❌ TIDAK ADA') . "<br>";
echo "Books: " . (is_dir($books_path) ? '✅ ADA' : '❌ TIDAK ADA') . "<br>";

echo "<h3>Folder Permissions:</h3>";
echo "Uploads writable: " . (is_writable($base_path) ? '✅ BISA DITULIS' : '❌ TIDAK BISA DITULIS') . "<br>";
echo "Covers writable: " . (is_writable($covers_path) ? '✅ BISA DITULIS' : '❌ TIDAK BISA DITULIS') . "<br>";
echo "Books writable: " . (is_writable($books_path) ? '✅ BISA DITULIS' : '❌ TIDAK BISA DITULIS') . "<br>";

// Test file creation
echo "<h3>Testing File Creation:</h3>";
$test_file1 = $covers_path . 'test.txt';
if (file_put_contents($test_file1, 'test')) {
    echo "✅ Bisa buat file di covers<br>";
    unlink($test_file1);
} else {
    echo "❌ GAGAL buat file di covers<br>";
    echo "Error: " . error_get_last()['message'] . "<br>";
}

$test_file2 = $books_path . 'test.txt';
if (file_put_contents($test_file2, 'test')) {
    echo "✅ Bisa buat file di books<br>";
    unlink($test_file2);
} else {
    echo "❌ GAGAL buat file di books<br>";
    echo "Error: " . error_get_last()['message'] . "<br>";
}

echo "<hr>";
echo "<h2>📋 Checklist Penyelesaian:</h2>";
echo "<ul>";
echo "<li>" . (is_dir($covers_path) ? '✅' : '❌') . " Folder uploads/covers ADA</li>";
echo "<li>" . (is_dir($books_path) ? '✅' : '❌') . " Folder uploads/books ADA</li>";
echo "<li>" . (is_writable($covers_path) ? '✅' : '❌') . " Folder covers BISA DITULIS</li>";
echo "<li>" . (is_writable($books_path) ? '✅' : '❌') . " Folder books BISA DITULIS</li>";
echo "</ul>";

if (is_dir($covers_path) && is_dir($books_path) && is_writable($covers_path) && is_writable($books_path)) {
    echo "<div style='background: green; color: white; padding: 20px; border-radius: 10px; text-align: center;'>";
    echo "<h2>🎉 SEMUA CHECKLIST TERPENUHI!</h2>";
    echo "<p>Sekarang upload buku harus berhasil!</p>";
    echo "<a href='pages/teacher/add-book.php' style='color: white; font-weight: bold; font-size: 18px;'>➡️ TEST UPLOAD BUKU SEKARANG</a>";
    echo "</div>";
} else {
    echo "<div style='background: red; color: white; padding: 20px; border-radius: 10px;'>";
    echo "<h2>⚠️ MASIH ADA MASALAH!</h2>";
    echo "<p>Ikuti instruksi di bawah:</p>";
    echo "<ol>";
    echo "<li>Buat folder UPLOADS/COVERS/BOOKS manual via Windows Explorer</li>";
    echo "<li>Klik kanan folder UPLOADS → Properties → Security → Edit → Pilih Users/Everyone → Centang Full Control → Apply</li>";
    echo "<li>Jalankan XAMPP sebagai Administrator</li>";
    echo "</ol>";
    echo "</div>";
}
?>