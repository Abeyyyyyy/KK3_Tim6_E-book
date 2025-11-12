<?php
require_once '../../includes/auth.php';
require_once '../../includes/database.php';

$auth = new Auth();
$auth->redirectIfNotAuthorized(['admin']);

$db = new Database();
$conn = $db->getConnection();

// Get stats
$users_count = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$books_count = $conn->query("SELECT COUNT(*) FROM books")->fetchColumn();
$transactions_count = $conn->query("SELECT COUNT(*) FROM transactions WHERE payment_status = 'completed'")->fetchColumn();
$revenue = $conn->query("SELECT SUM(amount) FROM transactions WHERE payment_status = 'completed'")->fetchColumn() ?? 0;
?>

<?php include '../../includes/header.php'; ?>

<!-- Include Sidebar -->
<?php include '../../includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="p-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard Admin</h1>
        <p class="text-gray-600">Selamat datang, <?php echo $_SESSION['full_name']; ?>!</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Total Users</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $users_count; ?></p>
                </div>
            </div>
        </div>

        <!-- Total Books -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-book text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Total Buku</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $books_count; ?></p>
                </div>
            </div>
        </div>

        <!-- Total Transactions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-shopping-cart text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Total Transaksi</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $transactions_count; ?></p>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-dollar-sign text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Total Pendapatan</h3>
                    <p class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($revenue, 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Terbaru</h2>
        <div class="space-y-4">
            <?php
            $recent_activity = $conn->query("
                SELECT 'book' as type, title as name, created_at FROM books 
                UNION ALL 
                SELECT 'user' as type, username as name, created_at FROM users 
                ORDER BY created_at DESC LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($recent_activity as $activity):
            ?>
            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                <div class="w-8 h-8 rounded-full flex items-center justify-center 
                    <?php echo $activity['type'] == 'book' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600'; ?>">
                    <i class="fas fa-<?php echo $activity['type'] == 'book' ? 'book' : 'user'; ?> text-sm"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800">
                        <?php echo $activity['type'] == 'book' ? 'Buku baru ditambahkan' : 'User baru terdaftar'; ?>
                    </p>
                    <p class="text-sm text-gray-600"><?php echo $activity['name']; ?></p>
                </div>
                <span class="text-xs text-gray-500">
                    <?php echo date('d M Y', strtotime($activity['created_at'])); ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>