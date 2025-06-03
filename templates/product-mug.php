<div class="product-modal-content">
    <div class="product-modal-image">
        <?php if ($product['image_url']): ?>
            <img src="../uploads/products/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['product_name'] ?? $product['name']); ?>">
        <?php else: ?>
            <div class="placeholder-image">
                <i class="fas fa-image"></i>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="product-modal-details">
        <!-- Product Name -->
        <h2>
            <?php echo htmlspecialchars($product['product_name'] ?? $product['name']); ?>
        </h2>
        <!-- Product Quantity/Stock -->
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
                <li><i class="fas fa-check"></i> 11oz ceramic mug</li>
                <li><i class="fas fa-check"></i> Dishwasher and microwave safe</li>
                <li><i class="fas fa-check"></i> High-quality sublimation printing</li>
                <li><i class="fas fa-check"></i> Vibrant colors that won't fade</li>
                <li><i class="fas fa-check"></i> Perfect for hot and cold beverages</li>
            </ul>
        </div>
        
        <form id="addToCartForm" class="product-form">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            
            <div class="form-group">
                <label>Size:</label>
                <div class="size-options">
                    <button type="button" class="size-option active" data-size="11oz">11oz</button>
                    <button type="button" class="size-option" data-size="15oz">15oz</button>
                </div>
                <input type="hidden" name="size" value="11oz">
            </div>
            
            <div class="form-group">
                <label>Color:</label>
                <div class="color-options">
                    <button type="button" class="color-option white active" data-color="white" title="White"></button>
                    <button type="button" class="color-option" data-color="black" style="background: #000;" title="Black"></button>
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
                <textarea name="notes" class="form-control" placeholder="Add any special instructions for your mug design..."></textarea>
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
                    <button type="button" onclick="uploadCustomFile()" class="btn btn-outline-primary">Upload</button>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="addToCartModal()">ADD TO CART</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">VIEW DETAILS</button>
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