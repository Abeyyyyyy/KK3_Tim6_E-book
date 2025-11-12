<?php
echo "<h1>🔧 PHP Configuration Check</h1>";

// Check file upload settings
echo "<h3>File Upload Settings:</h3>";
echo "file_uploads: " . ini_get('file_uploads') . "<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "memory_limit: " . ini_get('memory_limit') . "<br>";

// Check if uploads are enabled
if (ini_get('file_uploads')) {
    echo "<p style='color: green;'>✅ File uploads are enabled</p>";
} else {
    echo "<p style='color: red;'>❌ File uploads are disabled</p>";
}

// Check session
echo "<h3>Session Check:</h3>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
echo "Username: " . ($_SESSION['username'] ?? 'NOT SET') . "<br>";
echo "Role: " . ($_SESSION['role'] ?? 'NOT SET') . "<br>";

// Check upload directory
echo "<h3>Upload Directory Check:</h3>";
$upload_path = $_SERVER['DOCUMENT_ROOT'] . '/project-ebook/uploads/';
echo "Upload Path: " . $upload_path . "<br>";
echo "Exists: " . (is_dir($upload_path) ? 'YES' : 'NO') . "<br>";
echo "Writable: " . (is_writable($upload_path) ? 'YES' : 'NO') . "<br>";

echo "<hr>";
echo "<a href='pages/teacher/add-book.php'>Test Upload Form</a>";
?>