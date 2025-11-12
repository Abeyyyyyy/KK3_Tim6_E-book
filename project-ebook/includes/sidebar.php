<?php
// Sidebar navigation berdasarkan role user
function getSidebarMenu($role) {
    $menus = [
        'admin' => [
            ['icon' => '📊', 'name' => 'Dashboard', 'url' => 'dashboard.php'],
            ['icon' => '📚', 'name' => 'Kelola Buku', 'url' => 'books.php'],
            ['icon' => '👥', 'name' => 'Manajemen User', 'url' => 'users.php'],
            ['icon' => '💰', 'name' => 'Transaksi', 'url' => 'transactions.php'],
            ['icon' => '🏪', 'name' => 'Toko Buku', 'url' => 'store.php'],
            ['icon' => '📖', 'name' => 'Library Saya', 'url' => 'library.php']
        ],
        'teacher' => [
            ['icon' => '📊', 'name' => 'Dashboard', 'url' => 'dashboard.php'],
            ['icon' => '📚', 'name' => 'Buku Saya', 'url' => 'my-books.php'],
            ['icon' => '➕', 'name' => 'Tambah Buku', 'url' => 'add-book.php'],
            ['icon' => '🏪', 'name' => 'Toko Buku', 'url' => 'store.php'],
            ['icon' => '📖', 'name' => 'Library Saya', 'url' => 'library.php'],
            ['icon' => '💰', 'name' => 'Penjualan', 'url' => 'sales.php']
        ],
        'student' => [
            ['icon' => '📊', 'name' => 'Dashboard', 'url' => 'dashboard.php'],
            ['icon' => '🏪', 'name' => 'Toko Buku', 'url' => 'store.php'],
            ['icon' => '📖', 'name' => 'Library Saya', 'url' => 'library.php'],
            ['icon' => '🛒', 'name' => 'Pembelian', 'url' => 'purchases.php']
        ]
    ];
    
    return $menus[$role] ?? [];
}
?>

<!-- Sidebar Navigation -->
<aside class="w-64 bg-blue-600 text-white min-h-screen fixed left-0 top-0">
    <div class="p-6">
        <!-- Logo -->
        <div class="flex items-center space-x-3 mb-8">
            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                <span class="text-blue-600 font-bold text-lg">EB</span>
            </div>
            <div>
                <h1 class="text-xl font-bold">E-Book System</h1>
                <p class="text-blue-200 text-sm"><?php echo ucfirst($_SESSION['role']); ?></p>
            </div>
        </div>

        <!-- User Info -->
        <div class="bg-blue-500 rounded-lg p-4 mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-blue-400 rounded-full flex items-center justify-center">
                    <span class="font-bold"><?php echo substr($_SESSION['full_name'], 0, 1); ?></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium truncate"><?php echo $_SESSION['full_name']; ?></p>
                    <p class="text-blue-200 text-sm truncate">@<?php echo $_SESSION['username']; ?></p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="space-y-2">
            <?php foreach (getSidebarMenu($_SESSION['role']) as $menu): ?>
            <a href="<?php echo $menu['url']; ?>" 
               class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-500 transition duration-200 <?php echo (basename($_SERVER['PHP_SELF']) == $menu['url']) ? 'bg-blue-500' : ''; ?>">
                <span class="text-lg"><?php echo $menu['icon']; ?></span>
                <span class="font-medium"><?php echo $menu['name']; ?></span>
            </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- Logout Button -->
    <div class="absolute bottom-0 left-0 right-0 p-6">
        <a href="../../logout.php" 
           class="flex items-center space-x-3 p-3 rounded-lg bg-blue-500 hover:bg-blue-400 transition duration-200">
            <span class="text-lg">🚪</span>
            <span class="font-medium">Logout</span>
        </a>
    </div>
</aside>