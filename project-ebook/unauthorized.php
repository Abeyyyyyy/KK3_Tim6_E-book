<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized - E-book System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md text-center">
        <div class="text-6xl text-red-500 mb-4">🚫</div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Akses Ditolak</h1>
        <p class="text-gray-600 mb-4">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        <a href="../login.php" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
            Kembali ke Login
        </a>
    </div>
</body>
</html>