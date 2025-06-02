<?php
session_start();

if (isset($_SESSION['user'])) {
    echo "Welcome, " . $_SESSION['user']['name'];
    // Show logout button, etc.
} else {
    echo "You are not logged in.";
    // Show login button
}
$pageTitle = "Home";
include 'includes/header.php';
require_once 'config/database.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Premium Quality Printing Solutions</h1>
            <p>Bringing your ideas to life with exceptional printing services</p>
            <a href="/isabelle-prints/pages/products.php" class="btn btn-primary">Explore Collection</a>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
        <div class="container">
            <h2>Our Categories</h2>
            <div class="categories-grid">
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name LIMIT 4");
                    $categories = $stmt->fetchAll();
                    
                    if (count($categories) > 0) {
                        foreach ($categories as $category) {
                            ?>
                            <div class="category-card">
                                <a href="/isabelle-prints/pages/products.php?category=<?php echo $category['id']; ?>">
                                    <div class="category-image">
                                        <i class="fas fa-<?php echo $category['icon'] ?? 'image'; ?>"></i>
                                    </div>
                                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                                </a>
                            </div>
                            <?php
                        }
                    } else {
                        // Default categories if none in database
                        $defaultCategories = [
                            ['name' => 'Books & Magazines', 'icon' => 'book'],
                            ['name' => 'Business Cards', 'icon' => 'id-card'],
                            ['name' => 'Posters & Banners', 'icon' => 'image'],
                            ['name' => 'Custom Apparel', 'icon' => 'tshirt']
                        ];
                        
                        foreach ($defaultCategories as $index => $category) {
                            ?>
                            <div class="category-card">
                                <a href="/isabelle-prints/pages/products.php?category=<?php echo $index + 1; ?>">
                                    <div class="category-image">
                                        <i class="fas fa-<?php echo $category['icon']; ?>"></i>
                                    </div>
                                    <h3><?php echo $category['name']; ?></h3>
                                </a>
                            </div>
                            <?php
                        }
                    }
                } catch (PDOException $e) {
                    // Default categories if database error
                    $defaultCategories = [
                        ['name' => 'Books & Magazines', 'icon' => 'book'],
                        ['name' => 'Business Cards', 'icon' => 'id-card'],
                        ['name' => 'Posters & Banners', 'icon' => 'image'],
                        ['name' => 'Custom Apparel', 'icon' => 'tshirt']
                    ];
                    
                    foreach ($defaultCategories as $index => $category) {
                        ?>
                        <div class="category-card">
                            <a href="/isabelle-prints/pages/products.php?category=<?php echo $index + 1; ?>">
                                <div class="category-image">
                                    <i class="fas fa-<?php echo $category['icon']; ?>"></i>
                                </div>
                                <h3><?php echo $category['name']; ?></h3>
                            </a>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="products-section">
        <div class="container">
            <h2>Featured Products</h2>
            <div class="products-grid">
                <?php
                try {
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE featured = 1 LIMIT 8");
                    $stmt->execute();
                    $featuredProducts = $stmt->fetchAll();

                    if (count($featuredProducts) > 0) {
                        foreach ($featuredProducts as $product) {
                            ?>
                            <div class="product-card" onclick="openProductModal(<?php echo $product['id']; ?>)">
                                <div class="product-image">
                                    <?php if (!empty($product['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <div class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'General'); ?></div>
                                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div class="product-price">₱<?php echo number_format($product['base_price'], 2); ?></div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        // Display sample products
                        $sampleProducts = [
                            ['name' => 'Premium Business Cards', 'price' => 500, 'category' => 'Business Cards'],
                            ['name' => 'Custom Poster Print', 'price' => 800, 'category' => 'Posters'],
                            ['name' => 'Magazine Printing', 'price' => 1200, 'category' => 'Magazines'],
                            ['name' => 'Banner Design', 'price' => 1500, 'category' => 'Banners'],
                            ['name' => 'Brochure Printing', 'price' => 600, 'category' => 'Brochures'],
                            ['name' => 'Custom T-Shirt', 'price' => 750, 'category' => 'Apparel'],
                            ['name' => 'Flyer Design', 'price' => 400, 'category' => 'Flyers'],
                            ['name' => 'Book Printing', 'price' => 2000, 'category' => 'Books']
                        ];
                        
                        foreach ($sampleProducts as $index => $product) {
                            ?>
                            <div class="product-card" onclick="openProductModal(<?php echo $index + 1; ?>)">
                                <div class="product-image">
                                    <div class="placeholder-image">
                                        <i class="fas fa-image"></i>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <div class="product-category"><?php echo $product['category']; ?></div>
                                    <div class="product-name"><?php echo $product['name']; ?></div>
                                    <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                } catch (PDOException $e) {
                    // Display sample products if database error
                    $sampleProducts = [
                        ['name' => 'Premium Business Cards', 'price' => 500, 'category' => 'Business Cards'],
                        ['name' => 'Custom Poster Print', 'price' => 800, 'category' => 'Posters'],
                        ['name' => 'Magazine Printing', 'price' => 1200, 'category' => 'Magazines'],
                        ['name' => 'Banner Design', 'price' => 1500, 'category' => 'Banners'],
                        ['name' => 'Brochure Printing', 'price' => 600, 'category' => 'Brochures'],
                        ['name' => 'Custom T-Shirt', 'price' => 750, 'category' => 'Apparel'],
                        ['name' => 'Flyer Design', 'price' => 400, 'category' => 'Flyers'],
                        ['name' => 'Book Printing', 'price' => 2000, 'category' => 'Books']
                    ];
                    
                    foreach ($sampleProducts as $index => $product) {
                        ?>
                        <div class="product-card" onclick="openProductModal(<?php echo $index + 1; ?>)">
                            <div class="product-image">
                                <div class="placeholder-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            </div>
                            <div class="product-info">
                                <div class="product-category"><?php echo $product['category']; ?></div>
                                <div class="product-name"><?php echo $product['name']; ?></div>
                                <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>
</main>

<!-- Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeProductModal()">&times;</span>
        <div id="modalContent">
            <div class="loading">
                <i class="fas fa-spinner"></i>
                Loading product details...
            </div>
        </div>
    </div>
</div>

<script>
// Product Modal Functions
function openProductModal(productId) {
    const modal = document.getElementById('productModal');
    const modalContent = document.getElementById('modalContent');
    
    modal.style.display = 'block';
    modalContent.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i> Loading product details...</div>';
    
    // Fetch product details
    fetch(`/isabelle-prints/api/get-product.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayProductModal(data.product);
            } else {
                modalContent.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-triangle"></i><h3>Product not found</h3><p>Sorry, this product could not be loaded.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalContent.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-triangle"></i><h3>Error loading product</h3><p>Please try again later.</p></div>';
        });
}

function displayProductModal(product) {
    const modalContent = document.getElementById('modalContent');
    
    modalContent.innerHTML = `
        <div class="product-modal-content">
            <div class="product-modal-image">
                ${product.image_url ? 
                    `<img src="${product.image_url}" alt="${product.name}">` : 
                    '<div class="placeholder-image"><i class="fas fa-image"></i></div>'
                }
            </div>
            <div class="product-modal-details">
                <h2>${product.name}</h2>
                <div class="product-category">${product.category_name || 'General'}</div>
                <div class="product-price">₱${parseFloat(product.base_price).toLocaleString('en-US', {minimumFractionDigits: 2})}</div>
                
                <div class="product-description">
                    <p>${product.description || 'High-quality printing service with professional results.'}</p>
                </div>
                
                <ul class="product-features">
                    <li><i class="fas fa-check"></i> Premium quality materials</li>
                    <li><i class="fas fa-check"></i> Fast turnaround time</li>
                    <li><i class="fas fa-check"></i> Professional design support</li>
                    <li><i class="fas fa-check"></i> Satisfaction guaranteed</li>
                </ul>
                
                <form class="product-form" onsubmit="addToCart(event, ${product.id})">
                    <div class="product-options">
                        <div class="option-group">
                            <label>Size:</label>
                            <select name="size" class="form-control" required>
                                <option value="">Select Size</option>
                                <option value="small">Small</option>
                                <option value="medium">Medium</option>
                                <option value="large">Large</option>
                                <option value="custom">Custom Size</option>
                            </select>
                        </div>
                        
                        <div class="option-group">
                            <label>Paper Type:</label>
                            <select name="paper_type" class="form-control" required>
                                <option value="">Select Paper Type</option>
                                <option value="standard">Standard</option>
                                <option value="premium">Premium</option>
                                <option value="glossy">Glossy</option>
                                <option value="matte">Matte</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="quantity-controls">
                        <label>Quantity:</label>
                        <button type="button" class="qty-btn" onclick="changeQuantity(-1)">-</button>
                        <input type="number" name="quantity" class="qty-input" value="1" min="1" max="1000" required>
                        <button type="button" class="qty-btn" onclick="changeQuantity(1)">+</button>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-full">Add to Cart</button>
                    </div>
                </form>
            </div>
        </div>
    `;
}

function closeProductModal() {
    document.getElementById('productModal').style.display = 'none';
}

function changeQuantity(change) {
    const qtyInput = document.querySelector('.qty-input');
    let currentQty = parseInt(qtyInput.value);
    let newQty = currentQty + change;
    
    if (newQty >= 1 && newQty <= 1000) {
        qtyInput.value = newQty;
    }
}

function addToCart(event, productId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    formData.append('product_id', productId);
    formData.append('action', 'add_to_cart');
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Adding to Cart...';
    submitBtn.disabled = true;
    
    fetch('/isabelle-prints/api/cart-actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count in header
            updateCartCount();
            
            // Show success message
            submitBtn.textContent = 'Added to Cart!';
            submitBtn.style.backgroundColor = '#28a745';
            
            // Close modal after 1 second
            setTimeout(() => {
                closeProductModal();
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                submitBtn.style.backgroundColor = '';
            }, 1000);
        } else {
            alert(data.message || 'Error adding item to cart');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding item to cart');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function updateCartCount() {
    fetch('/isabelle-prints/api/get-cart-count.php')
        .then(response => response.json())
        .then(data => {
            const cartCountElement = document.querySelector('.cart-count');
            if (data.count > 0) {
                if (cartCountElement) {
                    cartCountElement.textContent = data.count;
                } else {
                    // Create cart count element if it doesn't exist
                    const cartIcon = document.querySelector('.cart-icon');
                    const countSpan = document.createElement('span');
                    countSpan.className = 'cart-count';
                    countSpan.textContent = data.count;
                    cartIcon.appendChild(countSpan);
                }
            } else {
                if (cartCountElement) {
                    cartCountElement.remove();
                }
            }
        })
        .catch(error => console.error('Error updating cart count:', error));
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('productModal');
    if (event.target == modal) {
        closeProductModal();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
