// Confirmation for delete actions
function confirmDelete(message = 'Yakin ingin menghapus? Tindakan ini tidak dapat dibatalkan.') {
    return confirm(message);
}

// Confirmation for purchase
function confirmPurchase(bookTitle, price) {
    return confirm(`Yakin ingin membeli "${bookTitle}" seharga Rp ${price}?`);
}

// Confirmation for logout
function confirmLogout() {
    return confirm('Yakin ingin logout?');
}

// Confirmation for publish/unpublish
function confirmPublish(action, bookTitle) {
    if (action === 'publish') {
        return confirm(`Yakin ingin mempublikasikan "${bookTitle}"? Buku akan tersedia di toko.`);
    } else {
        return confirm(`Yakin ingin menyembunyikan "${bookTitle}"? Buku tidak akan lagi tersedia di toko.`);
    }
}

// Add confirmation to all delete links
document.addEventListener('DOMContentLoaded', function() {
    const deleteLinks = document.querySelectorAll('a[href*="delete"], a[onclick*="delete"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirmDelete()) {
                e.preventDefault();
            }
        });
    });

    // Add confirmation to logout links
    const logoutLinks = document.querySelectorAll('a[href*="logout"]');
    logoutLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirmLogout()) {
                e.preventDefault();
            }
        });
    });
});