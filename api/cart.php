<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_to_cart') {
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $customizationNotes = $_POST['customization_notes'] ?? '';
        
        // Collect selected options
        $options = [];
        if (isset($_POST['size']) && !empty($_POST['size'])) {
            $options['size'] = $_POST['size'];
        }
        if (isset($_POST['color']) && !empty($_POST['color'])) {
            $options['color'] = $_POST['color'];
        }
        if (isset($_POST['finish']) && !empty($_POST['finish'])) {
            $options['finish'] = $_POST['finish'];
        }
        if (isset($_POST['material']) && !empty($_POST['material'])) {
            $options['material'] = $_POST['material'];
        }
        
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            exit;
        }
        
        // Check if product exists
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        
        if ($product['stock_quantity'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
            exit;
        }
        
        // Handle custom image upload
        $customImageFile = null;
        if (isset($_FILES['custom_image']) && $_FILES['custom_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/custom-images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['custom_image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                $fileName = 'custom_' . time() . '_' . uniqid() . '.' . $fileExtension;
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['custom_image']['tmp_name'], $uploadPath)) {
                    $customImageFile = $fileName;
                }
            }
        }
        
        // Add to cart with custom data
        addToCartWithCustomization($productId, $quantity, $options, $customizationNotes, $customImageFile);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Product added to cart successfully',
            'cart_count' => getCartItemCount()
        ]);
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
?>
