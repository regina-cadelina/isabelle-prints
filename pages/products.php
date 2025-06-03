<?php
$pageTitle = "Collection";
include '../includes/header.php';
require_once '../config/database.php';

// Get category filter
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : null;

// Get sort option
$sortOption = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

// Prepare sorting parameters
$sortField = 'name';
$sortDirection = 'ASC';

switch ($sortOption) {
    case 'price_asc':
        $sortField = 'base_price';
        $sortDirection = 'ASC';
        break;
    case 'price_desc':
        $sortField = 'base_price';
        $sortDirection = 'DESC';
        break;
    case 'name_desc':
        $sortField = 'name';
        $sortDirection = 'DESC';
        break;
    case 'newest':
        $sortField = 'created_at';
        $sortDirection = 'DESC';
        break;
}

// Get all categories
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Get products with filtering and sorting
try {
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.is_active = 1";
    
    $params = [];
    
    if ($categoryFilter) {
        $query .= " AND p.category_id = ?";
        $params[] = $categoryFilter;
    }
    
    $query .= " ORDER BY p.$sortField $sortDirection";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}
?>

<main class="products-page">
    <div class="container">
        <div class="page-header">
            <h1>Our Collection</h1>
            <p>Discover our wide range of premium printing products</p>
        </div>

        <div class="products-filters">
            <div class="filter-section">
                <label for="category-filter">Category:</label>
                <select id="category-filter" onchange="filterByCategory(this.value)">
                    <option value="">All Categories</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $categoryFilter == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="1" <?php echo $categoryFilter == '1' ? 'selected' : ''; ?>>Business Cards</option>
                        <option value="2" <?php echo $categoryFilter == '2' ? 'selected' : ''; ?>>Posters</option>
                        <option value="3" <?php echo $categoryFilter == '3' ? 'selected' : ''; ?>>Magazines</option>
                        <option value="4" <?php echo $categoryFilter == '4' ? 'selected' : ''; ?>>Banners</option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="filter-section">
                <label for="sort-select">Sort by:</label>
                <select id="sort-select" onchange="sortProducts(this.value)">
                    <option value="name_asc" <?php echo $sortOption == 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                    <option value="name_desc" <?php echo $sortOption == 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                    <option value="price_asc" <?php echo $sortOption == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                    <option value="price_desc" <?php echo $sortOption == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                    <option value="newest" <?php echo $sortOption == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                </select>
            </div>
        </div>

        <?php if (empty($products)): ?>
            <div class="no-products">
                <p>No products found. Please try a different category or check back later.</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" onclick="openProductModal(<?php echo $product['id']; ?>)">
                        <div class="product-image">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="../uploads/products/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <i class="fas fa-image"></i>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'General'); ?></div>
                            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-price">₱<?php echo number_format($product['base_price'], 2); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
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
    // Fix: prepend the correct path to the image_url
    const imagePath = product.image_url 
        ? `/isabelle-prints/uploads/products/${product.image_url}` 
        : '';

    modalContent.innerHTML = `
        <div class="product-modal-content">
            <div class="product-modal-image">
                ${product.image_url ? 
                    `<img src="${imagePath}" alt="${product.name}">` : 
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

function filterByCategory(categoryId) {
    const currentUrl = new URL(window.location.href);
    if (categoryId) {
        currentUrl.searchParams.set('category', categoryId);
    } else {
        currentUrl.searchParams.delete('category');
    }
    window.location.href = currentUrl.toString();
}

function sortProducts(sortValue) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('sort', sortValue);
    window.location.href = currentUrl.toString();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('productModal');
    if (event.target == modal) {
        closeProductModal();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
