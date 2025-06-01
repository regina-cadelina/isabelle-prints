<div class="product-modal-content">
    <div class="product-modal-image">
        <?php if ($product['image_url']): ?>
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        <?php else: ?>
            <div class="placeholder-image">
                <i class="fas fa-mug-hot"></i>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="product-modal-details">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
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
            
            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="addToCartModal()">ADD TO CART</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">VIEW DETAILS</button>
            </div>
        </form>
    </div>
</div>