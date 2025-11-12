<?php
class ErrorHandler {
    public static function displayError($message, $type = 'error') {
        $bgColor = $type === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700';
        $icon = $type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
        
        return "
            <div class='{$bgColor} border px-4 py-3 rounded mb-4 flex items-center'>
                <i class='fas {$icon} mr-2'></i>
                <span>{$message}</span>
            </div>
        ";
    }
    
    public static function logError($message, $file = '', $line = '') {
        $logMessage = "[" . date('Y-m-d H:i:s') . "] ";
        $logMessage .= $file ? "File: {$file} " : "";
        $logMessage .= $line ? "Line: {$line} " : "";
        $logMessage .= "Error: {$message}\n";
        
        error_log($logMessage, 3, "../../logs/error.log");
    }
    
    public static function handleDatabaseError(PDOException $e) {
        self::logError($e->getMessage(), $e->getFile(), $e->getLine());
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['error'] = "Terjadi kesalahan sistem. Silakan coba lagi.";
        header("Location: ../error.php");
        exit();
    }
    
    public static function handleUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi ukuran maksimal server).',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi ukuran maksimal form).',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian.',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP.'
        ];
        
        return $errors[$errorCode] ?? 'Unknown upload error.';
    }
}

// Custom error handler function
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $errorTypes = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice'
    ];
    
    $errorType = $errorTypes[$errno] ?? 'Unknown Error';
    $message = "{$errorType}: {$errstr} in {$errfile} on line {$errline}";
    
    ErrorHandler::logError($message);
    
    if (in_array($errno, [E_USER_ERROR, E_ERROR])) {
        ErrorHandler::displayError('Terjadi kesalahan sistem. Silakan coba lagi.');
    }
    
    return true;
}

// Set custom error handler
set_error_handler('customErrorHandler');

// Set exception handler
function customExceptionHandler($exception) {
    ErrorHandler::logError($exception->getMessage(), $exception->getFile(), $exception->getLine());
    ErrorHandler::displayError('Terjadi kesalahan sistem. Silakan coba lagi.');
}

set_exception_handler('customExceptionHandler');
?>