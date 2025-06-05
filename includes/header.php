<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
$is_admin = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Isabelle Concept & Prints</title>

    <!-- CSS Files -->
    <link rel="stylesheet" href="/isabelle-prints/assets/css/header-fix.css">
    <link rel="stylesheet" href="/isabelle-prints/assets/css/style.css">
    <link rel="stylesheet" href="/isabelle-prints/assets/css/login.css">
    <link rel="stylesheet" href="/isabelle-prints/assets/css/account.css">
    <link rel="stylesheet" href="/isabelle-prints/assets/css/checkout-orders.css">
    <link rel="stylesheet" href="/isabelle-prints/assets/css/products.css">
    <link rel="stylesheet" href="/isabelle-prints/assets/css/admin-floating-button.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<!-- Admin Floating Button - Only visible to admin users -->
<?php if ($is_admin): ?>
    <div id="admin-floating-btn" class="admin-floating-button">
        <a href="/isabelle-prints/admin/dashboard.php" title="Back to Admin Dashboard">
            <i class="fas fa-cog"></i>
            <span class="admin-btn-text">Admin Dashboard</span>
        </a>
    </div>
<?php endif; ?>

<!-- Dropdown overlay -->
<div class="dropdown-overlay" id="dropdownOverlay"></div>

<header>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="/isabelle-prints/index.php">Isabelle Concept & Prints</a>
            </div>
            
            <div class="nav-container">
                <a href="/isabelle-prints/index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">HOME</a>
                <a href="/isabelle-prints/pages/products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">COLLECTION</a>
                <a href="/isabelle-prints/pages/faqs.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'faqs.php' ? 'active' : ''; ?>">FAQS</a>
                
                <div class="nav-icons">
                    <a href="/isabelle-prints/pages/cart.php" class="nav-icon cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <?php 
                        $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                        if ($cartCount > 0): 
                        ?>
                        <span class="cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="user-menu">
                            <button class="nav-icon user-icon" onclick="toggleUserMenu(event)">
                                <i class="fas fa-user"></i>
                            </button>
                            
                            <div class="user-dropdown" id="userDropdown">
                                <div class="user-info">
                                    <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['first_name'] ?? 'User'); ?></span>
                                    <small><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></small>
                                </div>
                                <hr>
                                <a href="/isabelle-prints/pages/account.php">
                                    <i class="fas fa-user-cog"></i> Account Details
                                </a>
                                <a href="/isabelle-prints/pages/orders.php">
                                    <i class="fas fa-shopping-bag"></i> My Orders
                                </a>
                                <?php if ($is_admin): ?>
                                <hr>
                                <a href="/isabelle-prints/admin/dashboard.php">
                                    <i class="fas fa-cog"></i> Admin Dashboard
                                </a>
                                <?php endif; ?>
                                <hr>
                                <a href="/isabelle-prints/pages/logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/isabelle-prints/pages/login.php" class="nav-icon user-icon">
                            <i class="fas fa-user"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<!-- Add this script right before the closing </header> tag -->
<script>
function toggleUserMenu(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('userDropdown');
    const overlay = document.getElementById('dropdownOverlay');
    const userIcon = event.currentTarget; // Get the clicked user icon
    
    const isShowing = dropdown.classList.contains('show');
    
    if (isShowing) {
        dropdown.classList.remove('show');
        overlay.classList.remove('show');
    } else {
        // Calculate position based on user icon
        const iconRect = userIcon.getBoundingClientRect();
        const dropdownWidth = 220; // Match the min-width from CSS
        
        // Position dropdown right under the icon
        dropdown.style.top = (iconRect.bottom + 5) + 'px';
        
        // Position horizontally - align right edge of dropdown with right edge of icon
        dropdown.style.left = (iconRect.right - dropdownWidth) + 'px';
        
        // Ensure dropdown doesn't go off-screen
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        // Adjust horizontal position if it goes off-screen
        if (iconRect.right - dropdownWidth < 10) {
            // If it would go off the left edge, align with left edge of icon
            dropdown.style.left = iconRect.left + 'px';
        } else if (iconRect.right > viewportWidth - 10) {
            // If icon is too close to right edge, align dropdown to right edge of viewport
            dropdown.style.left = (viewportWidth - dropdownWidth - 10) + 'px';
        }
        
        // Adjust vertical position if it goes off-screen
        const dropdownHeight = 200; // Approximate height
        if (iconRect.bottom + dropdownHeight > viewportHeight - 20) {
            // If it would go off the bottom, position above the icon
            dropdown.style.top = (iconRect.top - dropdownHeight - 5) + 'px';
        }
        
        dropdown.classList.add('show');
        overlay.classList.add('show');
    }
}

// Update position on window resize
window.addEventListener('resize', function() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown && dropdown.classList.contains('show')) {
        // Re-trigger positioning
        const userIcon = document.querySelector('.user-icon');
        if (userIcon) {
            // Create a fake event to reuse the positioning logic
            const fakeEvent = { currentTarget: userIcon, stopPropagation: () => {} };
            toggleUserMenu(fakeEvent);
        }
    }
});

// Update position on scroll (for sticky header)
window.addEventListener('scroll', function() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown && dropdown.classList.contains('show')) {
        const userIcon = document.querySelector('.user-icon');
        if (userIcon) {
            const iconRect = userIcon.getBoundingClientRect();
            dropdown.style.top = (iconRect.bottom + 5) + 'px';
        }
    }
});
</script>
</header>

<!-- Main content wrapper to ensure proper spacing -->
<div class="main-content-wrapper">

</div> <!-- Close main-content-wrapper -->
