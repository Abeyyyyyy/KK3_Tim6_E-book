<?php
class FileUpload {
    private $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $allowedDocumentTypes = ['pdf', 'doc', 'docx', 'txt'];
    private $maxImageSize = 2 * 1024 * 1024; // 2MB
    private $maxDocumentSize = 10 * 1024 * 1024; // 10MB
    private $basePath;

    public function __construct() {
        // Gunakan absolute path yang lebih reliable
        $this->basePath = $_SERVER['DOCUMENT_ROOT'] . '/project-ebook/uploads/';
        
        // Ensure base directory exists
        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0777, true);
        }
    }

    public function uploadCover($file) {
        return $this->uploadFile($file, 'covers', $this->allowedImageTypes, $this->maxImageSize);
    }

    public function uploadBookFile($file) {
        return $this->uploadFile($file, 'books', $this->allowedDocumentTypes, $this->maxDocumentSize);
    }

    private function uploadFile($file, $folder, $allowedTypes, $maxSize) {
        // Check upload error first
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi ukuran maksimal server).',
                UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi ukuran maksimal form).',
                UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian.',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload.',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan.',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
                UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP.'
            ];
            return ['success' => false, 'error' => $errorMessages[$file['error']] ?? 'Unknown upload error.'];
        }

        // Check file size
        if ($file['size'] > $maxSize) {
            $sizeInMB = round($maxSize / (1024 * 1024), 2);
            return ['success' => false, 'error' => "Ukuran file terlalu besar. Maksimal {$sizeInMB}MB."];
        }

        // Check file type
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedTypes)) {
            $allowedTypesStr = implode(', ', $allowedTypes);
            return ['success' => false, 'error' => "Tipe file tidak diizinkan. Hanya: {$allowedTypesStr}"];
        }

        // Create folder if not exists
        $folderPath = $this->basePath . $folder . '/';
        if (!is_dir($folderPath)) {
            if (!mkdir($folderPath, 0777, true)) {
                return ['success' => false, 'error' => "Gagal membuat folder {$folder}."];
            }
        }

        // Check if folder is writable
        if (!is_writable($folderPath)) {
            return ['success' => false, 'error' => "Folder {$folder} tidak bisa ditulisi."];
        }

        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $fileExtension;
        $uploadPath = $folderPath . $filename;

        // Debug info
        error_log("Attempting to upload file to: " . $uploadPath);
        error_log("Temp file: " . $file['tmp_name']);
        error_log("Folder writable: " . (is_writable($folderPath) ? 'yes' : 'no'));

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => true, 'filename' => $filename];
        } else {
            $lastError = error_get_last();
            error_log("Move uploaded file failed: " . print_r($lastError, true));
            return ['success' => false, 'error' => 'Gagal menyimpan file. Error: ' . ($lastError['message'] ?? 'Unknown error')];
        }
    }

    public function deleteFile($filename, $folder) {
        $filePath = $this->basePath . $folder . '/' . $filename;
        if (file_exists($filePath) && $filename !== 'default_cover.jpg') {
            return unlink($filePath);
        }
        return true;
    }
}
?>