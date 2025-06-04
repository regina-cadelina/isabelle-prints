<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Get product ID from request
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

try {
    // Get product details
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // Get product customization options
    $optionsStmt = $pdo->prepare("
        SELECT * FROM product_customization_options 
        WHERE product_id = ? AND is_active = 1 
        ORDER BY option_type, sort_order, option_name
    ");
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
    
    // Parse features from JSON or text
    $features = [];
    if (!empty($product['features'])) {
        $decodedFeatures = json_decode($product['features'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedFeatures)) {
            $features = $decodedFeatures;
        } else {
            // Fallback for non-JSON features
            $features = array_filter(explode("\n", $product['features']));
        }
    }
    
    // Return JSON response for AJAX requests
    if (isset($_GET['format']) && $_GET['format'] === 'json') {
        echo json_encode([
            'success' => true,
            'product' => $product,
            'options' => $groupedOptions,
            'features' => $features
        ]);
        exit;
    }
    
    // Generate HTML for modal
    $imagePath = '';
    if (!empty($product['image_url'])) {
        // Check if the image_url already contains the full path
        if (strpos($product['image_url'], 'uploads/') === 0) {
            $imagePath = "/isabelle-prints/" . htmlspecialchars($product['image_url']);
        } else {
            $imagePath = "/isabelle-prints/uploads/products/" . htmlspecialchars($product['image_url']);
        }
    }
    
    ?>
    <div class="product-modal-content">
        <div class="product-modal-image">
            <?php if ($imagePath): ?>
                <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div class="placeholder-image" style="display:none;"><i class="fas fa-image"></i></div>
            <?php else: ?>
                <div class="placeholder-image"><i class="fas fa-image"></i></div>
            <?php endif; ?>
        </div>
        <div class="product-modal-details">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            
            <div class="product-stock" style="margin-bottom:10px;">
                <strong>Available Stock:</strong> <?php echo (int)$product['stock_quantity']; ?>
            </div>
            
            <div class="product-price">
                <span class="price">₱<?php echo number_format($product['base_price'], 2); ?></span>
            </div>
            
            <div class="product-description">
                <p><?php echo htmlspecialchars($product['modal_description'] ?: $product['description'] ?: 'High-quality product with professional results.'); ?></p>
            </div>
            
            <?php if (!empty($features)): ?>
            <ul class="product-features">
                <?php foreach ($features as $feature): ?>
                    <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($feature); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            
            <form class="product-form" onsubmit="addToCart(event, <?php echo $product['id']; ?>)">
                <div class="product-options">
                    <?php foreach ($groupedOptions as $optionType => $typeOptions): ?>
                        <div class="option-group">
                            <label><?php echo ucfirst($optionType); ?>:</label>
                            
                            <?php if ($optionType === 'color'): ?>
                                <div class="color-options">
                                    <?php foreach ($typeOptions as $option): ?>
                                        <div class="color-option" 
                                             data-color="<?php echo htmlspecialchars($option['option_value']); ?>"
                                             data-price="<?php echo $option['price_modifier']; ?>"
                                             style="background-color: <?php echo htmlspecialchars($option['option_value']); ?>"
                                             title="<?php echo htmlspecialchars($option['option_name']); ?>"
                                             <?php echo $option['is_default'] ? 'class="color-option active"' : ''; ?>>
                                        </div>
                                    <?php endforeach; ?>
                                    <input type="hidden" name="color" value="<?php 
                                        $defaultColor = array_filter($typeOptions, function($opt) { return $opt['is_default']; });
                                        echo $defaultColor ? htmlspecialchars(reset($defaultColor)['option_value']) : '';
                                    ?>">
                                </div>
                            <?php else: ?>
                                <select name="<?php echo htmlspecialchars($optionType); ?>" class="form-control" required>
                                    <option value="">Select <?php echo ucfirst($optionType); ?></option>
                                    <?php foreach ($typeOptions as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['option_value']); ?>" 
                                                data-price="<?php echo $option['price_modifier']; ?>"
                                                <?php echo $option['is_default'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['option_name']); ?>
                                            <?php if ($option['price_modifier'] != 0): ?>
                                                (<?php echo $option['price_modifier'] > 0 ? '+' : ''; ?>₱<?php echo number_format($option['price_modifier'], 2); ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="quantity-controls">
                    <label>Quantity:</label>
                    <div class="qty-wrapper">
                        <button type="button" class="qty-btn" onclick="changeQuantity(-1)">-</button>
                        <input type="number" name="quantity" class="qty-input" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" readonly>
                        <button type="button" class="qty-btn" onclick="changeQuantity(1)">+</button>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-full">Add to Cart</button>
                </div>
            </form>
        </div>
    </div>
    <?php
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
