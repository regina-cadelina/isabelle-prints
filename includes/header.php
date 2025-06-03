<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Isabelle Concept & Prints</title>

    <!-- CSS Files -->
    <link rel="stylesheet" href="/isabelle-prints/assets/css/style.css">
    <link rel="stylesheet" href="/isabelle-prints/assets/css/login.css">
    <link rel="stylesheet" href="/isabelle-prints/assets/css/account.css">
    <link rel="stylesheet" href="/isabelle-prints/assets/css/checkout-orders.css">
    <link rel="stylesheet" href="/isabelle-prints/assets/css/products.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
    /* Header alignment fix - keeping your original styles but fixing alignment */
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
    }
    
    .nav-container {
        display: flex;
        align-items: center;
        gap: 2rem;
    }
    
    .nav-link {
        color: #555;
        font-weight: 500;
        transition: color 0.3s;
        text-decoration: none;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
    }
    
    .nav-link:hover, 
    .nav-link.active {
        color: #f4c430;
    }
    
    .nav-icons {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-left: 1rem;
    }
    
    .nav-icon {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.1rem;
        color: #555;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem;
        transition: color 0.3s;
        text-decoration: none;
    }
    
    .nav-icon:hover {
        color: #f4c430;
    }
    </style>
</head>
<body>
<header>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="/isabelle-prints/index.php">Isabelle Concept & Prints</a>
            </div>
            
            <div class="nav-container">
                <a href="/isabelle-prints/index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">HOME</a>
                <a href="/isabelle-prints/pages/products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">COLLECTION</a>
                <a href="/isabelle-prints/pages/faqs.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'faq.php' ? 'active' : ''; ?>">FAQS</a>
                
                <div class="nav-icons">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="user-menu">
                            <button class="nav-icon user-icon" onclick="toggleUserMenu()">
                                <i class="fas fa-user"></i>
                            </button>
                            <div class="user-dropdown" id="userDropdown">
                                <div class="user-info">
                                    <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                                    <small><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></small>
                                </div>
                                <hr>
                                <a href="/isabelle-prints/pages/account.php">
                                    <i class="fas fa-user-cog"></i> Account Details
                                </a>
                                <a href="/isabelle-prints/pages/orders.php">
                                    <i class="fas fa-shopping-bag"></i> My Orders
                                </a>
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
                    
                    <a href="/isabelle-prints/pages/cart.php" class="nav-icon cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <?php 
                        $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                        if ($cartCount > 0): 
                        ?>
                        <span class="cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
window.onclick = function(event) {
    if (!event.target.matches('.user-icon') && !event.target.matches('.user-icon i')) {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
}
</script>
