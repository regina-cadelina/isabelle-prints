<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_to_cart') {
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $options = isset($_POST['options']) ? json_decode($_POST['options'], true) : [];
        $notes = $_POST['notes'] ?? '';
        
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            exit;
        }
        
        // Check if product exists
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        
        // Add to cart
        addToCart($productId, $quantity, $options, $notes);
        
        echo json_encode(['success' => true, 'message' => 'Product added to cart']);
        exit;
    }
    
    if ($action === 'update_cart') {
        $cartKey = $_POST['cart_key'] ?? '';
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        
        if (!$cartKey || !isset($_SESSION['cart'][$cartKey])) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
            exit;
        }
        
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$cartKey]);
        } else {
            $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
        }
        
        echo json_encode(['success' => true, 'message' => 'Cart updated']);
        exit;
    }
    
    if ($action === 'remove_item') {
        $cartKey = $_POST['cart_key'] ?? '';
        
        if (!$cartKey || !isset($_SESSION['cart'][$cartKey])) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
            exit;
        }
        
        unset($_SESSION['cart'][$cartKey]);
        
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
        exit;
    }
    
    if ($action === 'clear_cart') {
        unset($_SESSION['cart']);
        
        echo json_encode(['success' => true, 'message' => 'Cart cleared']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);