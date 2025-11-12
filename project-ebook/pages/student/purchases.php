<?php
require_once '../../includes/auth.php';
require_once '../../includes/database.php';

$auth = new Auth();
$auth->redirectIfNotAuthorized(['student']);

$db = new Database();
$conn = $db->getConnection();

// Get student's purchase history
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT t.*, b.title as book_title, b.author, b.category_id, c.name as category_name 
    FROM transactions t 
    JOIN books b ON t.book_id = b.id 
    LEFT JOIN categories c ON b.category_id = c.id 
    WHERE t.user_id = ? 
    ORDER BY t.transaction_date DESC
");
$stmt->execute([$user_id]);
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total spent
$total_stmt = $conn->prepare("SELECT SUM(amount) as total_spent FROM transactions WHERE user_id = ? AND payment_status = 'completed'");
$total_stmt->execute([$user_id]);
$total_data = $total_stmt->fetch(PDO::FETCH_ASSOC);
$total_spent = $total_data['total_spent'] ?? 0;
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/sidebar.php'; ?>

<div class="p-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Riwayat Pembelian</h1>
        <p class="text-gray-600">Lihat semua transaksi pembelian Anda</p>
    </div>

    <!-- Purchase Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-shopping-cart text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Total Pembelian</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo count($purchases); ?></p>
                </div>
            </div>
        </div>

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

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-book text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Buku Dimiliki</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo count($purchases); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchases Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <?php if (count($purchases) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buku</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($purchases as $purchase): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $purchase['book_title']; ?></div>
                                <div class="text-sm text-gray-500">by <?php echo $purchase['author']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $purchase['category_name'] ?? 'Uncategorized'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rp <?php echo number_format($purchase['amount'], 0, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $purchase['payment_status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                          ($purchase['payment_status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                    <?php echo ucfirst($purchase['payment_status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d M Y H:i', strtotime($purchase['transaction_date'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if ($purchase['payment_status'] == 'completed'): ?>
                                    <a href="../download.php?book_id=<?php echo $purchase['book_id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-download mr-1"></i>Download
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">Tunggu konfirmasi</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-receipt text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada pembelian</h3>
                <p class="text-gray-500 mb-4">Anda belum melakukan pembelian buku apapun</p>
                <a href="store.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                    Mulai Berbelanja
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>