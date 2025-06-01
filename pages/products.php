<?php
$pageTitle = "Our Products";
include '../includes/header.php';

// Get filter parameters
$categoryFilter = $_GET['category'] ?? '';
$sortBy = $_GET['sort'] ?? 'name';

// Build query
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active'";
$params = [];

if ($categoryFilter) {
    $query .= " AND c.slug = ?";
    $params[] = $categoryFilter;
}

// Add sorting
switch ($sortBy) {
    case 'price_low':
        $query .= " ORDER BY p.base_price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.base_price DESC";
        break;
    default:
        $query .= " ORDER BY p.name ASC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get all categories for filter
$categoriesStmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
$categories = $categoriesStmt->fetchAll();
?>

<main class="products-page">
    <div class="container">
        <div class="page-header">
            <h1>OUR PRODUCTS</h1>
            <nav class="breadcrumb">
                <a href="/isabelle-prints/">Home</a> / Products
            </nav>
        </div>

        <div class="products-content">
            <aside class="sidebar">
                <div class="filter-section">
                    <h3>Categories</h3>
                    <form method="GET" id="filterForm">
                        <?php foreach ($categories as $category): ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="category" value="<?php echo $category['slug']; ?>" 
                                       <?php echo $categoryFilter == $category['slug'] ? 'checked' : ''; ?>
                                       onchange="document.getElementById('filterForm').submit();">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </form>
                </div>
            </aside>

            <div class="products-main">
                <div class="products-header">
                    <div class="sort-controls">
                        <select name="sort" onchange="location.href='?sort=' + this.value + '<?php echo $categoryFilter ? '&category=' . $categoryFilter : ''; ?>'">
                            <option value="name" <?php echo $sortBy == 'name' ? 'selected' : ''; ?>>ALPHABETICALLY, A-Z</option>
                            <option value="price_low" <?php echo $sortBy == 'price_low' ? 'selected' : ''; ?>>PRICE, LOW TO HIGH</option>
                            <option value="price_high" <?php echo $sortBy == 'price_high' ? 'selected' : ''; ?>>PRICE, HIGH TO LOW</option>
                        </select>
                    </div>
                </div>

                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if ($product['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-image"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="product-price">
                                    <?php if ($product['is_sale'] && $product['sale_price']): ?>
                                        <span class="original-price"><?php echo formatPrice($product['base_price']); ?></span>
                                        <span class="sale-price"><?php echo formatPrice($product['sale_price']); ?></span>
                                    <?php else: ?>
                                        <span class="price"><?php echo formatPrice($product['base_price']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-primary" onclick="openProductModal(<?php echo $product['id']; ?>)">
                                    View Details
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="modalContent">
            <!-- Content will be loaded via AJAX -->
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>