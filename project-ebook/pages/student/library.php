<?php
require_once '../../includes/auth.php';
require_once '../../includes/database.php';

$auth = new Auth();
$auth->redirectIfNotAuthorized(['student']);

$db = new Database();
$conn = $db->getConnection();

// Get student's purchased books
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT b.*, t.transaction_date 
    FROM transactions t 
    JOIN books b ON t.book_id = b.id 
    WHERE t.user_id = ? AND t.payment_status = 'completed'
    ORDER BY t.transaction_date DESC
");
$stmt->execute([$user_id]);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/sidebar.php'; ?>

<div class="p-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Library Saya</h1>
        <p class="text-gray-600">Buku-buku yang telah Anda beli</p>
    </div>

    <?php if (count($books) > 0): ?>
        <div class="mb-6 flex justify-between items-center">
            <p class="text-gray-600">Total <span class="font-semibold"><?php echo count($books); ?> buku</span> dalam library Anda</p>
            <div class="flex gap-2">
                <input type="text" placeholder="Cari di library..." 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($books as $book): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-200">
                <div class="h-48 bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center">
                    <i class="fas fa-book-open text-4xl text-green-600"></i>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php echo $book['title']; ?></h3>
                    <p class="text-sm text-gray-600 mb-2">by <?php echo $book['author']; ?></p>
                    <p class="text-sm text-gray-500 mb-3"><?php echo $book['category_name'] ?? 'Uncategorized'; ?></p>
                    <p class="text-xs text-gray-400 mb-4">Dibeli: <?php echo date('d M Y', strtotime($book['transaction_date'])); ?></p>
                    
                    <div class="mt-4 flex gap-2">
                        <button class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition duration-200">
                            <i class="fas fa-download mr-2"></i>Download
                        </button>
                        <button class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    
                    <div class="mt-3 flex justify-between text-xs text-gray-500">
                        <span><?php echo number_format($book['price'], 0, ',', '.'); ?> halaman</span>
                        <span>PDF</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-book-open text-4xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Library Anda kosong</h3>
            <p class="text-gray-500 mb-6">Anda belum membeli buku apapun. Mulai jelajahi toko buku untuk menemukan buku yang menarik!</p>
            <a href="store.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200 text-lg">
                <i class="fas fa-shopping-cart mr-2"></i>Jelajahi Toko Buku
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>