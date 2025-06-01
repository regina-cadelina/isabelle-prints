<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$cartCount = getCartItemCount();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Isabelle Concept & Prints</title>
    <link rel="stylesheet" href="/isabelle-prints/assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="/isabelle-prints/">Isabelle Concept & Prints</a>
                </div>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="/isabelle-prints/" class="<?php echo $currentPage == 'index' ? 'active' : ''; ?>">HOME</a></li>
                        <li><a href="/isabelle-prints/pages/products.php" class="<?php echo $currentPage == 'products' ? 'active' : ''; ?>">COLLECTION</a></li>
                        <li><a href="/isabelle-prints/pages/faqs.php" class="<?php echo $currentPage == 'faqs' ? 'active' : ''; ?>">FAQS</a></li>
                        <li><a href="/isabelle-prints/pages/about.php" class="<?php echo $currentPage == 'about' ? 'active' : ''; ?>">ABOUT US</a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <a href="/isabelle-prints/pages/login.php" class="user-icon">
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="/isabelle-prints/pages/cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </header>