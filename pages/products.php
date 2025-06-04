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
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="modalContent">
            <div class="loading">
                <i class="fas fa-spinner"></i>
                Loading product details...
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
