<?php
// Helper functions for the website

function getCartItemCount() {
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    return array_sum(array_column($_SESSION['cart'], 'quantity'));
}

function formatPrice($price) {
    return '$' . number_format($price, 2);
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
                'options' => $cartItem['options'],
                'notes' => $cartItem['notes']
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
        // Add option price modifiers here if needed
        $subtotal += $price * $item['quantity'];
    }
    
    $shipping = 5.99;
    $tax = $subtotal * 0.08; // 8% tax
    $total = $subtotal + $shipping + $tax;
    
    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total
    ];
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser($pdo) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
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