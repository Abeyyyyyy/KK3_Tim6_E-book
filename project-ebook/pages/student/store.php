<?php
require_once '../../includes/auth.php';
require_once '../../includes/database.php';

$auth = new Auth();
$auth->redirectIfNotAuthorized(['student']);

$db = new Database();
$conn = $db->getConnection();

// Get all published books
$stmt = $conn->prepare("
    SELECT b.*, c.name as category_name, u.username as seller 
    FROM books b 
    LEFT JOIN categories c ON b.category_id = c.id 
    LEFT JOIN users u ON b.created_by = u.id 
    WHERE b.is_published = TRUE 
    ORDER BY b.created_at DESC
");
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle book purchase
if (isset($_GET['buy_book'])) {
    $book_id = $_GET['buy_book'];
    $user_id = $_SESSION['user_id'];
    
    // Check if already purchased
    $check_stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? AND book_id = ?");
    $check_stmt->execute([$user_id, $book_id]);
    
    if ($check_stmt->rowCount() == 0) {
        // Get book price
        $book_stmt = $conn->prepare("SELECT price FROM books WHERE id = ?");
        $book_stmt->execute([$book_id]);
        $book_data = $book_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Create transaction
        $insert_stmt = $conn->prepare("
            INSERT INTO transactions (user_id, book_id, amount, payment_status) 
            VALUES (?, ?, ?, 'completed')
        ");
        $insert_stmt->execute([$user_id, $book_id, $book_data['price']]);
        
        // Add to user library
        $library_stmt = $conn->prepare("INSERT INTO user_library (user_id, book_id) VALUES (?, ?)");
        $library_stmt->execute([$user_id, $book_id]);
        
        $success = "Buku berhasil dibeli! Silakan cek di Library Anda.";
    } else {
        $error = "Anda sudah membeli buku ini!";
    }
}
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/sidebar.php'; ?>

<div class="p-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Toko Buku</h1>
        <p class="text-gray-600">Jelajahi dan beli buku yang tersedia</p>
    </div>

    <!-- Notifications -->
    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" placeholder="Cari buku berdasarkan judul atau penulis..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-2">
                <select class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option>Semua Kategori</option>
                    <option>Programming</option>
                    <option>Mathematics</option>
                    <option>Science</option>
                    <option>Literature</option>
                    <option>Business</option>
                </select>
                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($books as $book): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-200">
            <div class="h-48 bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                <i class="fas fa-book-open text-4xl text-blue-600"></i>
            </div>
            <div class="p-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-2 truncate"><?php echo $book['title']; ?></h3>
                <p class="text-sm text-gray-600 mb-2">by <?php echo $book['author']; ?></p>
                <p class="text-sm text-gray-500 mb-3"><?php echo $book['category_name'] ?? 'Uncategorized'; ?></p>
                <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?php echo $book['description'] ? substr($book['description'], 0, 100) . '...' : 'Tidak ada deskripsi'; ?></p>
                
                <div class="flex justify-between items-center mb-4">
                    <span class="text-lg font-bold text-blue-600">Rp <?php echo number_format($book['price'], 0, ',', '.'); ?></span>
                    <span class="text-sm text-gray-500">by <?php echo $book['seller']; ?></span>
                </div>
                
                <div class="flex gap-2">
                    <a href="?buy_book=<?php echo $book['id']; ?>" 
                       class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-200 text-center"
                       onclick="return confirm('Yakin ingin membeli buku ini?')">
                        <i class="fas fa-shopping-cart mr-2"></i>Beli Sekarang
                    </a>
                    <button class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-300 transition duration-200">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (count($books) == 0): ?>
        <div class="text-center py-12">
            <i class="fas fa-book text-4xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada buku tersedia</h3>
            <p class="text-gray-500">Belum ada buku yang dipublikasikan untuk dijual</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>