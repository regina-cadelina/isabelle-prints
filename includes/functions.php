<?php
// Helper functions for the website

function getCartItemCount() {
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    return array_sum(array_column($_SESSION['cart'], 'quantity'));
}

function formatPrice($price) {
    return '₱' . number_format($price, 2);
}

function addToCart($productId, $quantity, $options = [], $notes = '') {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $cartKey = $productId . '_' . md5(serialize($options));
    
    if (isset($_SESSION['cart'][$cartKey])) {
        $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cartKey] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'options' => $options,
            'notes' => $notes
        ];
    }
}

function addToCartWithCustomization($productId, $quantity, $options = [], $customizationNotes = '', $customImageFile = null) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $cartKey = $productId . '_' . md5(serialize($options) . $customizationNotes . $customImageFile);
    
    if (isset($_SESSION['cart'][$cartKey])) {
        $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cartKey] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'options' => $options,
            'notes' => $customizationNotes,
            'customization_notes' => $customizationNotes,
            'custom_image_file' => $customImageFile
        ];
    }
}

function getCartItems($pdo) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $items = [];
    foreach ($_SESSION['cart'] as $cartKey => $cartItem) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$cartItem['product_id']]);
        $product = $stmt->fetch();
        
        if ($product) {
            $items[] = [
                'cart_key' => $cartKey,
                'product' => $product,
                'quantity' => $cartItem['quantity'],
                'options' => $cartItem['options'] ?? [],
                'notes' => $cartItem['notes'] ?? '',
                'customization_notes' => $cartItem['customization_notes'] ?? '',
                'custom_image_file' => $cartItem['custom_image_file'] ?? null,
                'selected_options' => json_encode($cartItem['options'] ?? [])
            ];
        }
    }
    
    return $items;
}

function calculateCartTotal($pdo) {
    $items = getCartItems($pdo);
    $subtotal = 0;
    
    foreach ($items as $item) {
        $price = $item['product']['base_price'];
        $subtotal += $price * $item['quantity'];
    }
    
    $shipping = 5.99;
    $tax = $subtotal * 0.07; // 7% tax
    $total = $subtotal + $shipping + $tax;
    
    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total
    ];
}

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'],
        'name' => $_SESSION['user_name'],
        'first_name' => $_SESSION['first_name'],
        'last_name' => $_SESSION['last_name'],
        'user_type' => $_SESSION['user_type'] ?? 'customer'
    ];
}

function isAdmin() {
    return isLoggedIn() && ($_SESSION['user_type'] ?? '') === 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /isabelle-prints/pages/login.php');
        exit;
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /isabelle-prints/pages/login.php');
        exit;
    }
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function clearCart($pdo, $user_id) {
    // Clear session cart
    unset($_SESSION['cart']);

    // Optional: Clear cart from database if you store cart items in DB
    // Example:
    // $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
    // $stmt->execute([$user_id]);
}
?>
