<?php
require_once '../../includes/auth.php';
require_once '../../includes/database.php';

$auth = new Auth();
$auth->redirectIfNotAuthorized(['teacher']);

$db = new Database();
$conn = $db->getConnection();

// Get stats for teacher
$user_id = $_SESSION['user_id'];
$my_books_count = $conn->query("SELECT COUNT(*) FROM books WHERE created_by = $user_id")->fetchColumn();
$published_books_count = $conn->query("SELECT COUNT(*) FROM books WHERE created_by = $user_id AND is_published = TRUE")->fetchColumn();
$sales_count = $conn->query("SELECT COUNT(*) FROM transactions WHERE book_id IN (SELECT id FROM books WHERE created_by = $user_id) AND payment_status = 'completed'")->fetchColumn();
$revenue = $conn->query("SELECT SUM(amount) FROM transactions WHERE book_id IN (SELECT id FROM books WHERE created_by = $user_id) AND payment_status = 'completed'")->fetchColumn() ?? 0;
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/sidebar.php'; ?>

<div class="p-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard Teacher</h1>
        <p class="text-gray-600">Selamat datang, <?php echo $_SESSION['full_name']; ?>!</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Buku Saya -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-book text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Total Buku Saya</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $my_books_count; ?></p>
                </div>
            </div>
        </div>

        <!-- Buku Published -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Buku Published</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $published_books_count; ?></p>
                </div>
            </div>
        </div>

        <!-- Total Penjualan -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-shopping-cart text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Total Penjualan</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $sales_count; ?></p>
                </div>
            </div>
        </div>

        <!-- Total Pendapatan -->
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

    <!-- Recent Books -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Buku Terbaru Saya</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    $stmt = $conn->prepare("SELECT title, price, is_published, created_at FROM books WHERE created_by = ? ORDER BY created_at DESC LIMIT 5");
                    $stmt->execute([$user_id]);
                    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($books as $book):
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $book['title']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp <?php echo number_format($book['price'], 0, ',', '.'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $book['is_published'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo $book['is_published'] ? 'Published' : 'Draft'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y', strtotime($book['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>