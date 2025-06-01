<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Get product ID from request
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    http_response_code(400);
    echo "Invalid product ID";
    exit;
}

try {
    // Get product details
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        http_response_code(404);
        echo "Product not found";
        exit;
    }
    
    // Get product options
    $optionsStmt = $pdo->prepare("SELECT * FROM product_options WHERE product_id = ? ORDER BY option_type, option_name");
    $optionsStmt->execute([$productId]);
    $options = $optionsStmt->fetchAll();
    
    // Group options by type
    $groupedOptions = [];
    foreach ($options as $option) {
        if (!isset($groupedOptions[$option['option_type']])) {
            $groupedOptions[$option['option_type']] = [];
        }
        $groupedOptions[$option['option_type']][] = $option;
    }
    
    // Determine product type to show appropriate template
    $categoryStmt = $pdo->prepare("SELECT c.slug FROM categories c JOIN products p ON c.id = p.category_id WHERE p.id = ?");
    $categoryStmt->execute([$productId]);
    $category = $categoryStmt->fetch();
    $productType = $category ? $category['slug'] : 'default';
    
    // Include the appropriate template based on product type
    switch ($productType) {
        case 'tshirts':
            include '../templates/product-tshirt.php';
            break;
        case 'mugs':
            include '../templates/product-mug.php';
            break;
        case 'towels':
            include '../templates/product-towel.php';
            break;
        case 'tumblers':
            include '../templates/product-tumbler.php';
            break;
        default:
            include '../templates/product-default.php';
            break;
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database error: " . $e->getMessage();
}
?>