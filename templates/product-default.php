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
        </div>
        
        <form id="addToCartForm" class="product-form">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            
            <?php if (isset($groupedOptions['size'])): ?>
            <div class="form-group">
                <label>Size:</label>
                <select name="size" class="form-control">
                    <?php foreach ($groupedOptions['size'] as $option): ?>
                        <option value="<?php echo htmlspecialchars($option['option_value']); ?>" 
                                <?php echo $option['is_default'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option['option_name']); ?>
                            <?php if ($option['price_modifier'] > 0): ?>
                                (+<?php echo formatPrice($option['price_modifier']); ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <?php if (isset($groupedOptions['color'])): ?>
            <div class="form-group">
                <label>Color:</label>
                <select name="color" class="form-control">
                    <?php foreach ($groupedOptions['color'] as $option): ?>
                        <option value="<?php echo htmlspecialchars($option['option_value']); ?>" 
                                <?php echo $option['is_default'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option['option_name']); ?>
                            <?php if ($option['price_modifier'] > 0): ?>
                                (+<?php echo formatPrice($option['price_modifier']); ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <?php if (isset($groupedOptions['finish'])): ?>
            <div class="form-group">
                <label>Finish:</label>
                <select name="finish" class="form-control">
                    <?php foreach ($groupedOptions['finish'] as $option): ?>
                        <option value="<?php echo htmlspecialchars($option['option_value']); ?>" 
                                <?php echo $option['is_default'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option['option_name']); ?>
                            <?php if ($option['price_modifier'] > 0): ?>
                                (+<?php echo formatPrice($option['price_modifier']); ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
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
                <textarea name="notes" class="form-control" placeholder="Add any special instructions or customization details here..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="addToCartModal()">ADD TO CART</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">VIEW DETAILS</button>
            </div>
        </form>
    </div>
</div>