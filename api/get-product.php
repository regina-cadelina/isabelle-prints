<?php
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit;
}

$productId = $_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if ($product) {
        echo json_encode(['success' => true, 'product' => $product]);
    } else {
        // Return sample product data if not found in database
        $sampleProducts = [
            1 => ['id' => 1, 'name' => 'Premium Business Cards', 'base_price' => 500, 'category_name' => 'Business Cards', 'description' => 'High-quality business cards with premium finish.'],
            2 => ['id' => 2, 'name' => 'Custom Poster Print', 'base_price' => 800, 'category_name' => 'Posters', 'description' => 'Large format poster printing with vibrant colors.'],
            3 => ['id' => 3, 'name' => 'Magazine Printing', 'base_price' => 1200, 'category_name' => 'Magazines', 'description' => 'Professional magazine printing with binding options.'],
            4 => ['id' => 4, 'name' => 'Banner Design', 'base_price' => 1500, 'category_name' => 'Banners', 'description' => 'Eye-catching banners for events and promotions.'],
            5 => ['id' => 5, 'name' => 'Brochure Printing', 'base_price' => 600, 'category_name' => 'Brochures', 'description' => 'Tri-fold and bi-fold brochures with professional design.'],
            6 => ['id' => 6, 'name' => 'Custom T-Shirt', 'base_price' => 750, 'category_name' => 'Apparel', 'description' => 'Custom printed t-shirts with your design.'],
            7 => ['id' => 7, 'name' => 'Flyer Design', 'base_price' => 400, 'category_name' => 'Flyers', 'description' => 'Promotional flyers with attractive designs.'],
            8 => ['id' => 8, 'name' => 'Book Printing', 'base_price' => 2000, 'category_name' => 'Books', 'description' => 'Complete book printing with binding and cover options.']
        ];
        
        if (isset($sampleProducts[$productId])) {
            echo json_encode(['success' => true, 'product' => $sampleProducts[$productId]]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
