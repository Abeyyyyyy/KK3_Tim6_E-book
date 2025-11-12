<?php
require_once '../../includes/auth.php';
require_once '../../includes/database.php';

$auth = new Auth();
$auth->redirectIfNotAuthorized(['teacher']);

$db = new Database();
$conn = $db->getConnection();

$user_id = $_SESSION['user_id'];

// Handle delete book
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Check if book belongs to user
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ? AND created_by = ?");
    $stmt->execute([$delete_id, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        $delete_stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
        $delete_stmt->execute([$delete_id]);
        $success = "Buku berhasil dihapus!";
    } else {
        $error = "Anda tidak memiliki akses untuk menghapus buku ini!";
    }
}

// Handle toggle publish status
if (isset($_GET['toggle_publish'])) {
    $book_id = $_GET['toggle_publish'];
    
    $stmt = $conn->prepare("SELECT is_published FROM books WHERE id = ? AND created_by = ?");
    $stmt->execute([$book_id, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
        $new_status = $book['is_published'] ? 0 : 1;
        
        $update_stmt = $conn->prepare("UPDATE books SET is_published = ? WHERE id = ?");
        $update_stmt->execute([$new_status, $book_id]);
        
        $success = "Status buku berhasil diubah!";
    }
}

// Get user's books
$stmt = $conn->prepare("
    SELECT b.*, c.name as category_name 
    FROM books b 
    LEFT JOIN categories c ON b.category_id = c.id 
    WHERE b.created_by = ? 
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/sidebar.php'; ?>

<div class="p-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Kelola Buku Saya</h1>
            <p class="text-gray-600">Kelola buku yang Anda terbitkan</p>
        </div>
        <a href="add-book.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
            <i class="fas fa-plus mr-2"></i>Tambah Buku
        </a>
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

    <!-- Books Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <?php if (count($books) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cover</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($books as $book): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img src="../../uploads/<?php echo $book['cover_image']; ?>" 
                                     alt="<?php echo $book['title']; ?>" 
                                     class="w-12 h-16 object-cover rounded">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $book['title']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $book['author']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $book['category_name'] ?? 'Uncategorized'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rp <?php echo number_format($book['price'], 0, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $book['is_published'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo $book['is_published'] ? 'Published' : 'Draft'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="edit-book.php?id=<?php echo $book['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="?toggle_publish=<?php echo $book['id']; ?>" 
                                       class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-toggle-<?php echo $book['is_published'] ? 'on' : 'off'; ?>"></i> 
                                        <?php echo $book['is_published'] ? 'Unpublish' : 'Publish'; ?>
                                    </a>
                                    <a href="?delete_id=<?php echo $book['id']; ?>" 
                                       class="text-red-600 hover:text-red-900"
                                       onclick="return confirm('Yakin ingin menghapus buku ini?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-book-open text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada buku</h3>
                <p class="text-gray-500 mb-4">Mulai dengan menambahkan buku pertama Anda</p>
                <a href="add-book.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Tambah Buku Pertama
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>