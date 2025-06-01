<div class="product-modal-content">
    <div class="product-modal-image">
        <?php if ($product['image_url']): ?>
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        <?php else: ?>
            <div class="placeholder-image">
                <i class="fas fa-bath"></i>
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
                <li><i class="fas fa-check"></i> 100% cotton terry cloth</li>
                <li><i class="fas fa-check"></i> Highly absorbent</li>
                <li><i class="fas fa-check"></i> Machine washable</li>
                <li><i class="fas fa-check"></i> Soft and comfortable</li>
                <li><i class="fas fa-check"></i> Durable construction</li>
            </ul>
        </div>
        
        <form id="addToCartForm" class="product-form">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            
            <div class="form-group">
                <label>Size:</label>
                <div class="size-options">
                    <button type="button" class="size-option active" data-size="hand">Hand Towel</button>
                    <button type="button" class="size-option" data-size="bath">Bath Towel</button>
                    <button type="button" class="size-option" data-size="beach">Beach Towel</button>
                </div>
                <input type="hidden" name="size" value="hand">
            </div>
            
            <div class="form-group">
                <label>Color:</label>
                <div class="color-options">
                    <button type="button" class="color-option white active" data-color="white" title="White"></button>
                    <button type="button" class="color-option" data-color="blue" style="background: #0074d9;" title="Blue"></button>
                    <button type="button" class="color-option" data-color="green" style="background: #2ecc40;" title="Green"></button>
                    <button type="button" class="color-option" data-color="pink" style="background: #f012be;" title="Pink"></button>
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
                <textarea name="notes" class="form-control" placeholder="Add any special instructions for your towel design..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="addToCartModal()">ADD TO CART</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">VIEW DETAILS</button>
            </div>
        </form>
    </div>
</div>