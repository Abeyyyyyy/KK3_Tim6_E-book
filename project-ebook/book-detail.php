<?php
session_start();
require_once 'includes/database.php';

$db = new Database();
$conn = $db->getConnection();

$book_id = $_GET['id'] ?? 0;

// Get book details
$stmt = $conn->prepare("
    SELECT b.*, c.name as category_name, u.username as seller_name, u.full_name as seller_full_name 
    FROM books b 
    LEFT JOIN categories c ON b.category_id = c.id 
    LEFT JOIN users u ON b.created_by = u.id 
    WHERE b.id = ? AND b.is_published = TRUE
");
$stmt->execute([$book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    die("Buku tidak ditemukan.");
}

// Check if user already purchased this book
$has_purchased = false;
if (isset($_SESSION['user_id'])) {
    $check_stmt = $conn->prepare("
        SELECT id FROM transactions 
        WHERE user_id = ? AND book_id = ? AND payment_status = 'completed'
    ");
    $check_stmt->execute([$_SESSION['user_id'], $book_id]);
    $has_purchased = $check_stmt->rowCount() > 0;
}

// Check if user is the owner
$is_owner = isset($_SESSION['user_id']) && $book['created_by'] == $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - E-book System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-xl font-bold">E-book System</a>
            <div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="pages/<?php echo $_SESSION['role']; ?>/dashboard.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition duration-200">
                        Dashboard
                    </a>
                <?php else: ?>
                    <a href="login.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition duration-200">
                        Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-4">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden max-w-4xl mx-auto">
            <div class="md:flex">
                <!-- Book Cover -->
                <div class="md:w-1/3 p-6">
                    <div class="bg-gray-200 rounded-lg h-64 flex items-center justify-center">
                        <?php if ($book['cover_image'] && $book['cover_image'] !== 'default_cover.jpg'): ?>
                            <img src="uploads/covers/<?php echo $book['cover_image']; ?>" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                 class="h-full w-full object-cover rounded-lg">
                        <?php else: ?>
                            <i class="fas fa-book-open text-6xl text-gray-400"></i>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Book Details -->
                <div class="md:w-2/3 p-6">
                    <div class="mb-4">
                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                            <?php echo $book['category_name'] ?? 'Uncategorized'; ?>
                        </span>
                    </div>

                    <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($book['title']); ?></h1>
                    <p class="text-gray-600 mb-4">by <?php echo htmlspecialchars($book['author']); ?></p>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Deskripsi</h3>
                        <p class="text-gray-700 leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($book['description'] ?: 'Tidak ada deskripsi tersedia.')); ?>
                        </p>
                    </div>

                    <div class="border-t border-gray-200 pt-4 mb-6">
                        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                            <div>
                                <span class="font-semibold">Penjual:</span>
                                <p><?php echo htmlspecialchars($book['seller_full_name']); ?> (<?php echo $book['seller_name']; ?>)</p>
                            </div>
                            <div>
                                <span class="font-semibold">Tanggal Terbit:</span>
                                <p><?php echo date('d M Y', strtotime($book['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-3xl font-bold text-green-600">
                                Rp <?php echo number_format($book['price'], 0, ',', '.'); ?>
                            </span>
                        </div>
                        
                        <div class="flex gap-3">
                            <?php if ($has_purchased): ?>
                                <a href="download.php?book_id=<?php echo $book['id']; ?>" 
                                   class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-200 font-semibold">
                                    <i class="fas fa-download mr-2"></i>Download
                                </a>
                            <?php elseif ($is_owner): ?>
                                <span class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold">
                                    <i class="fas fa-book mr-2"></i>Buku Anda
                                </span>
                            <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] == 'student'): ?>
                                <a href="pages/student/store.php?buy_book=<?php echo $book['id']; ?>" 
                                   class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200 font-semibold"
                                   onclick="return confirm('Yakin ingin membeli buku ini?')">
                                    <i class="fas fa-shopping-cart mr-2"></i>Beli Sekarang
                                </a>
                            <?php elseif (!isset($_SESSION['user_id'])): ?>
                                <a href="login.php" 
                                   class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200 font-semibold">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Login untuk Membeli
                                </a>
                            <?php endif; ?>
                            
                            <a href="javascript:history.back()" 
                               class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition duration-200 font-semibold">
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>