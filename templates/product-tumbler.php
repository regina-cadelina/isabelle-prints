<div class="product-modal-content">
    <div class="product-modal-image">
        <?php if ($product['image_url']): ?>
            <img src="uploads/products/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        <?php else: ?>
            <div class="placeholder-image">
                <i class="fas fa-image"></i>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="product-modal-details">
        <h2>
            <?php echo htmlspecialchars($product['product_name'] ?? $product['name']); ?>
        </h2>
        <div class="product-stock" style="margin-bottom:10px;">
            <div class="product-stock" style="margin-bottom:10px;">
    <strong>Available Stock:</strong>
    <?php echo isset($product['stock_quantity']) ? (int)$product['stock_quantity'] : 'N/A'; ?>
</div>

<form id="addToCartForm" class="product-form" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

    <!-- ...existing form fields... -->

    <div class="form-actions">
        <button type="button" class="btn btn-primary"
            onclick="addToCartModal()"
            <?php if (empty($product['stock_quantity']) || $product['stock_quantity'] <= 0) echo 'disabled style="opacity:0.5;cursor:not-allowed;"'; ?>>
            ADD TO CART
        </button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">VIEW DETAILS</button>
    </div>
</form>

<?php if (empty($product['stock_quantity']) || $product['stock_quantity'] <= 0): ?>
    <div class="out-of-stock-message" style="color:red; margin-top:10px;">
        This product is out of stock and cannot be ordered.
    </div>
<?php endif; ?>
        </div>
        <div class="product-price">
            <?php if ($product['is_sale'] && $product['sale_price']): ?>
                <span class="original-price"><?php echo formatPrice($product['base_price']); ?></span>
                <span class="sale-price"><?php echo formatPrice($product['sale_price']); ?></span>
            <?php else: ?>
                <span class="price"><?php echo formatPrice($product['base_price']); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="product-description">
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            
            <ul class="product-features">
                <li><i class="fas fa-check"></i> Double-wall insulation</li>
                <li><i class="fas fa-check"></i> Keeps drinks hot or cold for hours</li>
                <li><i class="fas fa-check"></i> Spill-proof lid included</li>
                <li><i class="fas fa-check"></i> BPA-free materials</li>
                <li><i class="fas fa-check"></i> Easy to clean</li>
            </ul>
        </div>
        
        <form id="addToCartForm" class="product-form" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            
            <div class="form-group">
                <label>Size:</label>
                <div class="size-options">
                    <button type="button" class="size-option active" data-size="20oz">20oz</button>
                    <button type="button" class="size-option" data-size="30oz">30oz</button>
                </div>
                <input type="hidden" name="size" value="20oz">
            </div>
            
            <div class="form-group">
                <label>Color:</label>
                <div class="color-options">
                    <button type="button" class="color-option white active" data-color="white" title="White"></button>
                    <button type="button" class="color-option" data-color="black" style="background: #000;" title="Black"></button>
                    <button type="button" class="color-option" data-color="silver" style="background: #c0c0c0;" title="Silver"></button>
                    <button type="button" class="color-option" data-color="blue" style="background: #0074d9;" title="Blue"></button>
                </div>
                <input type="hidden" name="color" value="white">
            </div>
            
            <div class="form-group">
                <label>Quantity:</label>
                <div class="quantity-controls">
                    <button type="button" class="qty-btn" onclick="changeQuantity(this, -1)">-</button>
                    <input type="number" name="quantity" value="1" min="1" class="qty-input">
                    <button type="button" class="qty-btn" onclick="changeQuantity(this, 1)">+</button>
                </div>
            </div>
            
            <div class="form-group">
                <label>Customization Notes:</label>
                <textarea name="notes" class="form-control" placeholder="Add any special instructions for your tumbler design..."></textarea>
            </div>
            
            <div class="form-group">
    <label for="custom_file">Upload Custom Image (JPG, PNG, PDF):</label>
    <div style="display: flex; align-items: center; gap: 10px;">
        <!-- Image Preview -->
        <div id="customImagePreview" style="margin-right:10px;">
            <img id="previewImg" src="#" alt="Preview" style="display:none; max-width:60px; max-height:60px; border:1px solid #ccc; border-radius:8px;"/>
        </div>
        <!-- File Input -->
        <input type="file" class="form-control" name="custom_file" id="custom_file" accept=".jpg,.jpeg,.png,.pdf">
    </div>
</div>
            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="addToCartModal()">ADD TO CART</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('custom_file').addEventListener('change', function(event) {
    const preview = document.getElementById('previewImg');
    const file = event.target.files[0];
    if (file && file.type.match('image.*')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.src = '#';
        preview.style.display = 'none';
    }
});
</script>

<?php
// Handle the form submission for adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = $_POST['product_id'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $quantity = $_POST['quantity'];
    $notes = $_POST['notes'];
    $imageFileName = null;

    // Handle file upload if a custom file is provided
    if (isset($_FILES['custom_file']) && $_FILES['custom_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/products/';
        $imageFileName = uniqid('product_', true) . '.' . pathinfo($_FILES['custom_file']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['custom_file']['tmp_name'], $uploadDir . $imageFileName);
    }

    // Add the product to the cart (implement your cart logic here)
    // Example: Cart::add($productId, $size, $color, $quantity, $notes, $imageFileName);

    // For demo, just show an alert
    echo '<script>alert("Product added to cart! (ID: ' . $productId . ', Size: ' . $size . ', Color: ' . $color . ', Quantity: ' . $quantity . ', Notes: ' . $notes . ', Image: ' . $imageFileName . ')");</script>';
}

$customImageFile = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['custom_file']) && $_FILES['custom_file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/custom/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileExtension = strtolower(pathinfo($_FILES['custom_file']['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

    if (in_array($fileExtension, $allowedExtensions)) {
        $fileName = 'custom_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['custom_file']['tmp_name'], $uploadPath)) {
            $customImageFile = $fileName;
            // Save $customImageFile to your cart/session/order as needed
        } else {
            $error = 'Failed to upload custom file.';
        }
    } else {
        $error = 'Invalid custom file type. Only JPG, PNG, and PDF are allowed.';
    }
}
?>