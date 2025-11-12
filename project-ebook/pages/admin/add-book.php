<?php
require_once '../../includes/auth.php';
require_once '../../includes/database.php';

$auth = new Auth();
$auth->redirectIfNotAuthorized(['admin']);

$db = new Database();
$conn = $db->getConnection();

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    
    // Handle file uploads (simplified - for demo)
    $cover_image = 'default_cover.jpg';
    $file_path = 'sample.pdf';
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO books (title, author, description, price, file_path, cover_image, category_id, created_by, is_published) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title, $author, $description, $price, $file_path, $cover_image, 
            $category_id, $_SESSION['user_id'], $is_published
        ]);
        
        $success = "Buku berhasil ditambahkan!";
        
        // Reset form
        $_POST = array();
        
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/sidebar.php'; ?>

<div class="p-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Tambah Buku Baru</h1>
        <p class="text-gray-600">Tambahkan buku baru ke katalog</p>
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

    <!-- Book Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Judul Buku *</label>
                        <input type="text" name="title" required 
                               value="<?php echo $_POST['title'] ?? ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Author -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Penulis *</label>
                        <input type="text" name="author" required
                               value="<?php echo $_POST['author'] ?? ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                        <select name="category_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                    <?php echo (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Harga (Rp) *</label>
                        <input type="number" name="price" required min="0" step="0.01"
                               value="<?php echo $_POST['price'] ?? ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea name="description" rows="6"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo $_POST['description'] ?? ''; ?></textarea>
                    </div>

                    <!-- Publish Option -->
                    <div class="flex items-center">
                        <input type="checkbox" name="is_published" id="is_published" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                               <?php echo isset($_POST['is_published']) ? 'checked' : ''; ?>>
                        <label for="is_published" class="ml-2 block text-sm text-gray-700">
                            Publikasikan buku ini
                        </label>
                    </div>

                    <!-- File Upload (Placeholder for now) -->
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-600">Upload cover dan file buku (akan diimplementasi kemudian)</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex justify-end space-x-3">
                <a href="books.php" 
                   class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition duration-200">
                    Batal
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-save mr-2"></i>Simpan Buku
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>