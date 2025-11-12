<?php
require_once '../../includes/auth.php';
require_once '../../includes/database.php';

$auth = new Auth();
$auth->redirectIfNotAuthorized(['teacher']);

$db = new Database();
$conn = $db->getConnection();

// Get categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Debug: Check if form is being submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("FORM SUBMITTED - Starting processing");
    
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    
    error_log("Form data received - Title: $title, Author: $author");

    // Define upload paths
    $base_upload_path = $_SERVER['DOCUMENT_ROOT'] . '/project-ebook/uploads/';
    $covers_path = $base_upload_path . 'covers/';
    $books_path = $base_upload_path . 'books/';

    // Create directories if they don't exist
    if (!is_dir($covers_path)) {
        mkdir($covers_path, 0777, true);
        error_log("Created covers directory");
    }
    if (!is_dir($books_path)) {
        mkdir($books_path, 0777, true);
        error_log("Created books directory");
    }

    // Check if directories are writable
    if (!is_writable($covers_path)) {
        $error = "Folder covers tidak bisa ditulisi. Pastikan permissions benar.";
        error_log("Covers folder not writable");
    }
    if (!is_writable($books_path)) {
        $error = "Folder books tidak bisa ditulisi. Pastikan permissions benar.";
        error_log("Books folder not writable");
    }

    $cover_image = 'default_cover.jpg';
    $file_path = '';

    // Handle cover upload
    if (!empty($_FILES['cover_image']['name']) && !isset($error)) {
        $coverFile = $_FILES['cover_image'];
        error_log("Cover file detected: " . $coverFile['name']);
        
        $coverExt = strtolower(pathinfo($coverFile['name'], PATHINFO_EXTENSION));
        $coverAllowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($coverExt, $coverAllowed)) {
            $cover_image = uniqid() . '_cover.' . $coverExt;
            $coverPath = $covers_path . $cover_image;
            
            error_log("Attempting to move cover to: " . $coverPath);
            
            if (move_uploaded_file($coverFile['tmp_name'], $coverPath)) {
                error_log("Cover upload SUCCESS: " . $cover_image);
            } else {
                $error = "Gagal upload cover image. Error: " . error_get_last()['message'];
                error_log("Cover upload FAILED: " . $error);
            }
        } else {
            $error = "Format cover tidak didukung. Gunakan JPG, PNG, atau GIF.";
            error_log("Invalid cover format: " . $coverExt);
        }
    }

    // Handle book file upload
    if (!empty($_FILES['book_file']['name']) && !isset($error)) {
        $bookFile = $_FILES['book_file'];
        error_log("Book file detected: " . $bookFile['name']);
        
        $bookExt = strtolower(pathinfo($bookFile['name'], PATHINFO_EXTENSION));
        $bookAllowed = ['pdf', 'doc', 'docx', 'txt'];
        
        if (in_array($bookExt, $bookAllowed)) {
            $file_path = uniqid() . '_book.' . $bookExt;
            $bookPath = $books_path . $file_path;
            
            error_log("Attempting to move book file to: " . $bookPath);
            
            if (move_uploaded_file($bookFile['tmp_name'], $bookPath)) {
                error_log("Book file upload SUCCESS: " . $file_path);
            } else {
                $error = "Gagal upload file buku. Error: " . error_get_last()['message'];
                error_log("Book file upload FAILED: " . $error);
                // Delete cover if book upload failed
                if ($cover_image !== 'default_cover.jpg' && file_exists($covers_path . $cover_image)) {
                    unlink($covers_path . $cover_image);
                    error_log("Deleted cover due to book upload failure");
                }
            }
        } else {
            $error = "Format file buku tidak didukung. Gunakan PDF, DOC, DOCX, atau TXT.";
            error_log("Invalid book file format: " . $bookExt);
        }
    } else if (!isset($error)) {
        $error = "File buku harus diupload.";
        error_log("No book file uploaded");
    }

    // If no errors, save to database
    if (!isset($error) && !empty($file_path)) {
        try {
            error_log("Attempting to save to database...");
            
            $stmt = $conn->prepare("
                INSERT INTO books (title, author, description, price, file_path, cover_image, category_id, created_by, is_published) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $title, $author, $description, $price, $file_path, $cover_image, 
                $category_id, $_SESSION['user_id'], $is_published
            ]);
            
            if ($result) {
                $success = "Buku berhasil ditambahkan!";
                error_log("Database save SUCCESS - Book ID: " . $conn->lastInsertId());
                
                // Reset form
                $_POST = array();
                $_FILES = array();
            } else {
                $error = "Gagal menyimpan ke database.";
                error_log("Database save FAILED");
            }
            
        } catch (PDOException $e) {
            $error = "Error database: " . $e->getMessage();
            error_log("Database ERROR: " . $e->getMessage());
            
            // Delete uploaded files if database error
            if ($cover_image !== 'default_cover.jpg' && file_exists($covers_path . $cover_image)) {
                unlink($covers_path . $cover_image);
            }
            if (!empty($file_path) && file_exists($books_path . $file_path)) {
                unlink($books_path . $file_path);
            }
        }
    } else if (!isset($error) && empty($file_path)) {
        $error = "File buku harus diupload.";
    }
    
    error_log("Form processing COMPLETED - Success: " . (isset($success) ? 'YES' : 'NO') . ", Error: " . ($error ?? 'NONE'));
}
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/sidebar.php'; ?>

<div class="p-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Tambah Buku Baru</h1>
        <p class="text-gray-600">Tambahkan buku baru ke katalog Anda</p>
    </div>

    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
            <p class="text-sm mt-2">Buku telah berhasil ditambahkan ke sistem dan siap dijual!</p>
        </div>
        
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            <p><strong>Next Steps:</strong></p>
            <ul class="list-disc list-inside mt-2">
                <li><a href="my-books.php" class="underline">Lihat daftar buku Anda</a></li>
                <li><a href="add-book.php" class="underline">Tambah buku lain</a></li>
                <li><a href="../student/store.php" class="underline">Lihat di toko buku</a></li>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Book Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="" enctype="multipart/form-data" id="bookForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Judul Buku *</label>
                        <input type="text" name="title" required 
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Masukkan judul buku">
                    </div>

                    <!-- Author -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Penulis *</label>
                        <input type="text" name="author" required
                               value="<?php echo htmlspecialchars($_POST['author'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Nama penulis">
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
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Harga (Rp) *</label>
                        <input type="number" name="price" required min="0" step="0.01"
                               value="<?php echo $_POST['price'] ?? ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Contoh: 50000">
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea name="description" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Deskripsi tentang buku ini"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <!-- Cover Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cover Buku</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                            <input type="file" name="cover_image" accept="image/*" 
                                   class="w-full" id="cover-upload">
                            <label for="cover-upload" class="cursor-pointer">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-600">Klik untuk upload cover buku</p>
                                <p class="text-xs text-gray-500">Format: JPG, PNG, GIF (Maks. 2MB)</p>
                            </label>
                            <div id="cover-preview" class="mt-2 hidden">
                                <img id="cover-preview-img" class="mx-auto h-32 object-cover rounded">
                            </div>
                        </div>
                    </div>

                    <!-- Book File Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">File Buku *</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                            <input type="file" name="book_file" accept=".pdf,.doc,.docx,.txt" required
                                   class="w-full" id="file-upload">
                            <label for="file-upload" class="cursor-pointer">
                                <i class="fas fa-file-upload text-3xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-600">Klik untuk upload file buku</p>
                                <p class="text-xs text-gray-500">Format: PDF, DOC, DOCX, TXT (Maks. 10MB)</p>
                            </label>
                            <div id="file-preview" class="mt-2 hidden">
                                <p id="file-name" class="text-sm font-medium text-gray-700"></p>
                                <p id="file-size" class="text-xs text-gray-500"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Publish Option -->
                    <div class="flex items-center">
                        <input type="checkbox" name="is_published" id="is_published" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                               <?php echo isset($_POST['is_published']) ? 'checked' : ''; ?>>
                        <label for="is_published" class="ml-2 block text-sm text-gray-700">
                            Publikasikan buku ini (tampilkan di toko)
                        </label>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex justify-end space-x-3">
                <a href="my-books.php" 
                   class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition duration-200">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200"
                        id="submitBtn">
                    <i class="fas fa-save mr-2"></i>Simpan Buku
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Cover image preview
document.getElementById('cover-upload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('cover-preview');
    const previewImg = document.getElementById('cover-preview-img');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
});

// File info preview
document.getElementById('file-upload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('file-preview');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    
    if (file) {
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
});

// Form submission handler
document.getElementById('bookForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const fileInput = document.getElementById('file-upload');
    
    // Validate file
    if (!fileInput.files.length) {
        alert('File buku harus diupload!');
        e.preventDefault();
        return;
    }
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    submitBtn.disabled = true;
});
</script>

<?php include '../../includes/footer.php'; ?>