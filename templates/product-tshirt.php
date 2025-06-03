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
            <strong>Available Stock:</strong>
            <?php echo isset($product['stock_quantity']) ? (int)$product['stock_quantity'] : 'N/A'; ?>
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
                <li><i class="fas fa-check"></i> 100% cotton material</li>
                <li><i class="fas fa-check"></i> Pre-shrunk fabric</li>
                <li><i class="fas fa-check"></i> Machine washable</li>
                <li><i class="fas fa-check"></i> Available in multiple sizes</li>
                <li><i class="fas fa-check"></i> High-quality print that won't fade</li>
            </ul>
        </div>
        
        <form id="addToCartForm" class="product-form" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            
            <div class="form-group">
                <label>Size:</label>
                <div class="size-options">
                    <button type="button" class="size-option active" data-size="small">Small</button>
                    <button type="button" class="size-option" data-size="medium">Medium</button>
                    <button type="button" class="size-option" data-size="large">Large</button>
                    <button type="button" class="size-option" data-size="xl">XL</button>
                </div>
                <input type="hidden" name="size" value="small">
            </div>
            
            <div class="form-group">
                <label>Color:</label>
                <div class="color-options">
                    <button type="button" class="color-option white active" data-color="white" title="White"></button>
                    <button type="button" class="color-option" data-color="black" style="background: #000;" title="Black"></button>
                    <button type="button" class="color-option" data-color="navy" style="background: #001f3f;" title="Navy"></button>
                    <button type="button" class="color-option" data-color="red" style="background: #ff4136;" title="Red"></button>
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
                <textarea name="notes" class="form-control" placeholder="Add any special instructions for your t-shirt design..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="custom_file">Upload Custom Image:</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <!-- Image Preview -->
                    <div id="customImagePreview" style="margin-right:10px;">
                        <img id="previewImg" src="#" alt="Preview" style="display:none; max-width:60px; max-height:60px; border:1px solid #ccc; border-radius:8px;"/>
                    </div>
                    <!-- File Input and Upload Button -->
                    <input type="file" name="custom_file" id="custom_file" accept=".jpg,.jpeg,.png,.pdf" style="margin-right:5px;">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="addToCartModal()">ADD TO CART</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">VIEW DETAILS</button>
            </div>
        </form>
    </div>
</div>

<?php
function handleCustomizationUpload($fileInputName = 'custom_file') {
    $uploadDir = __DIR__ . '/../uploads/customization-uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $fileExtension = strtolower(pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            return false; // Invalid file type
        }
        $fileName = 'custom_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $uploadPath)) {
            return $fileName;
        }
    }
    return false;
}
?>

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

// Dummy upload handler for modal demo (replace with AJAX if needed)
function uploadCustomFile() {
    const fileInput = document.getElementById('custom_file');
    if (!fileInput.files.length) {
        alert('Please select a file to upload.');
        return;
    }
    alert('File ready to upload! (Implement AJAX upload as needed)');
}
</script>