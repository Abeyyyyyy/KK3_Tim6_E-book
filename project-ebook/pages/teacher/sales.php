<?php
require_once '../../includes/auth.php';
require_once '../../includes/database.php';

$auth = new Auth();
$auth->redirectIfNotAuthorized(['teacher']);

$db = new Database();
$conn = $db->getConnection();

// Get teacher's sales data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT t.*, b.title as book_title, u.username as buyer 
    FROM transactions t 
    JOIN books b ON t.book_id = b.id 
    JOIN users u ON t.user_id = u.id 
    WHERE b.created_by = ? 
    ORDER BY t.transaction_date DESC
");
$stmt->execute([$user_id]);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total sales
$total_sales = $conn->prepare("
    SELECT COUNT(*) as total_count, SUM(amount) as total_amount 
    FROM transactions t 
    JOIN books b ON t.book_id = b.id 
    WHERE b.created_by = ? AND t.payment_status = 'completed'
");
$total_sales->execute([$user_id]);
$sales_stats = $total_sales->fetch(PDO::FETCH_ASSOC);
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/sidebar.php'; ?>

<div class="p-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Penjualan Saya</h1>
        <p class="text-gray-600">Lihat riwayat penjualan buku Anda</p>
    </div>

    <!-- Sales Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-shopping-cart text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Total Penjualan</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $sales_stats['total_count'] ?? 0; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-dollar-sign text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Total Pendapatan</h3>
                    <p class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($sales_stats['total_amount'] ?? 0, 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-book text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Buku Terjual</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo count($sales); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <?php if (count($sales) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembeli</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buku</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo $sale['buyer']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $sale['book_title']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rp <?php echo number_format($sale['amount'], 0, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $sale['payment_status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                          ($sale['payment_status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                    <?php echo ucfirst($sale['payment_status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d M Y H:i', strtotime($sale['transaction_date'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-receipt text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada penjualan</h3>
                <p class="text-gray-500">Buku Anda belum ada yang terjual</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>