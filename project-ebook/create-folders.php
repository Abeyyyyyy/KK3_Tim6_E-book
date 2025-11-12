<?php
// Script untuk membuat folder uploads secara otomatis
echo "<h1>Membuat Folder Uploads...</h1>";

$folders = [
    'uploads',
    'uploads/covers', 
    'uploads/books',
    'logs'
];

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        if (mkdir($folder, 0777, true)) {
            echo "✅ Folder '<strong>{$folder}</strong>' berhasil dibuat.<br>";
        } else {
            echo "❌ Gagal membuat folder '{$folder}'.<br>";
        }
    } else {
        echo "✅ Folder '<strong>{$folder}</strong>' sudah ada.<br>";
    }
}

// Test permissions
echo "<h2>Testing Permissions:</h2>";
$test_file = 'uploads/test.txt';
if (file_put_contents($test_file, 'test')) {
    echo "✅ Folder uploads bisa ditulisi<br>";
    unlink($test_file);
} else {
    echo "❌ Folder uploads TIDAK bisa ditulisi<br>";
}

echo "<hr><h3>🎉 Selesai! Sekarang coba upload buku lagi.</h3>";
echo "<a href='pages/teacher/add-book.php'>Kembali ke Tambah Buku</a>";
?>