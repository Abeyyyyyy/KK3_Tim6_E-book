<?php
require_once '../../includes/auth.php';
require_once '../../includes/database.php';

$auth = new Auth();
$auth->redirectIfNotAuthorized(['student']);

$db = new Database();
$conn = $db->getConnection();

// Get stats for student
$user_id = $_SESSION['user_id'];
$purchased_books_count = $conn->query("SELECT COUNT(*) FROM user_library WHERE user_id = $user_id")->fetchColumn();
$total_spent = $conn->query("SELECT SUM(amount) FROM transactions WHERE user_id = $user_id AND payment_status = 'completed'")->fetchColumn() ?? 0;
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/sidebar.php'; ?>

<div class="p-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard Student</h1>
        <p class="text-gray-600">Selamat datang, <?php echo $_SESSION['full_name']; ?>!</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Total Buku yang Dibeli -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-book text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Buku yang Dibeli</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $purchased_books_count; ?></p>
                </div>
            </div>
        </div>

        <!-- Total Pengeluaran -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-dollar-sign text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Total Pengeluaran</h3>
                    <p class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($total_spent, 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-rocket text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Aksi Cepat</h3>
                    <a href="store.php" class="text-blue-600 hover:text-blue-800 font-medium">Belanja Buku →</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Purchases -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Pembelian Terbaru</h2>
        <div class="space-y-4">
            <?php
            $stmt = $conn->prepare("
                SELECT b.title, b.author, t.amount, t.transaction_date 
                FROM transactions t 
                JOIN books b ON t.book_id = b.id 
                WHERE t.user_id = ? AND t.payment_status = 'completed'
                ORDER BY t.transaction_date DESC 
                LIMIT 5
            ");
            $stmt->execute([$user_id]);
            $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($purchases) > 0):
                foreach ($purchases as $purchase):
            ?>
            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-book text-blue-600"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800"><?php echo $purchase['title']; ?></p>
                    <p class="text-sm text-gray-600">by <?php echo $purchase['author']; ?></p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-800">Rp <?php echo number_format($purchase['amount'], 0, ',', '.'); ?></p>
                    <p class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($purchase['transaction_date'])); ?></p>
                </div>
            </div>
            <?php 
                endforeach;
            else:
            ?>
            <div class="text-center py-4">
                <p class="text-gray-500">Belum ada pembelian.</p>
                <a href="store.php" class="text-blue-600 hover:text-blue-800 font-medium">Mulai berbelanja →</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>