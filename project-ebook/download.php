<?php
session_start();
require_once 'includes/database.php';

$db = new Database();
$conn = $db->getConnection();

if (!isset($_GET['book_id']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$book_id = $_GET['book_id'];
$user_id = $_SESSION['user_id'];

// Check if user has purchased this book
$stmt = $conn->prepare("
    SELECT b.file_path, b.title, b.author 
    FROM books b 
    JOIN transactions t ON b.id = t.book_id 
    WHERE b.id = ? AND t.user_id = ? AND t.payment_status = 'completed'
");
$stmt->execute([$book_id, $user_id]);

if ($stmt->rowCount() > 0) {
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    $file_path = "uploads/books/" . $book['file_path'];
    
    if (file_exists($file_path)) {
        // Set headers for download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $book['title'] . ' - ' . $book['author'] . '.' . pathinfo($file_path, PATHINFO_EXTENSION) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        flush();
        readfile($file_path);
        exit;
    } else {
        // File not found
        echo "<script>alert('File buku tidak ditemukan.'); window.history.back();</script>";
    }
} else {
    // User doesn't have access
    echo "<script>alert('Anda tidak memiliki akses untuk mendownload buku ini.'); window.history.back();</script>";
}
?>