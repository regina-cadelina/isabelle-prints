<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
// Check if user is admin
if (!isLoggedIn() || getCurrentUser()['user_type'] !== 'admin') {
    redirect('../login.php');
}

$current_user = getCurrentUser();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_product':
                $stmt = $pdo->prepare("INSERT INTO products (category_id, product_name, product_slug, description, short_description, base_price, sku, stock_quantity, is_bestseller, is_new) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $slug = strtolower(str_replace(' ', '-', $_POST['product_name']));
                $stmt->execute([
                    $_POST['category_id'],
                    $_POST['product_name'],
                    $slug,
                    $_POST['description'],
                    $_POST['short_description'],
                    $_POST['base_price'],
                    $_POST['sku'],
                    $_POST['stock_quantity'],
                    isset($_POST['is_featured']) ? 1 : 0,
                    isset($_POST['is_bestseller']) ? 1 : 0,
                    isset($_POST['is_new']) ? 1 : 0
                ]);
                $success = "Product added successfully!";
                break;
                
            case 'update_product':
                $stmt = $pdo->prepare("UPDATE products SET category_id = ?, product_name = ?, description = ?, short_description = ?, base_price = ?, sku = ?, stock_quantity = ?, is_bestseller = ?, is_new = ? WHERE product_id = ?");
                $stmt->execute([
                    $_POST['category_id'],
                    $_POST['product_name'],
                    $_POST['description'],
                    $_POST['short_description'],
                    $_POST['base_price'],
                    $_POST['sku'],
                    $_POST['stock_quantity'],
                    isset($_POST['is_featured']) ? 1 : 0,
                    isset($_POST['is_bestseller']) ? 1 : 0,
                    isset($_POST['is_new']) ? 1 : 0,
                    $_POST['product_id']
                ]);
                $success = "Product updated successfully!";
                break;
                
            case 'delete_product':
                $stmt = $pdo->prepare("UPDATE products SET is_active = 0 WHERE product_id = ?");
                $stmt->execute([$_POST['product_id']]);
                $success = "Product deleted successfully!";
                break;
        }
    }
}

// Get products
$stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'active' ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();

// Get categories for dropdown
$stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
$categories = $stmt->fetchAll();

// Get product for editing if edit parameter is set
$edit_product = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_product = $stmt->fetch();
}

$page_title = "Manage Products";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="admin-body">
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">
                <h2><i class="fas fa-cog"></i> Admin Panel</h2>
            </div>
            <div class="admin-user">
                <span>Welcome, <?php echo htmlspecialchars($current_user['first_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </header>

    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <nav class="admin-menu">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="products.php" class="active"><i class="fas fa-box"></i> Manage Products</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Manage Orders</a></li>
                    <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="faqs.php"><i class="fas fa-question-circle"></i> Manage FAQs</a></li>
                    <li><a href="../index.php"><i class="fas fa-globe"></i> View Website</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-content">
                <h1>Manage Products</h1>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <!-- Add/Edit Product Form -->
                <div class="admin-form">
                    <h2><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="<?php echo $edit_product ? 'update_product' : 'add_product'; ?>">
                        <?php if ($edit_product): ?>
                            <input type="hidden" name="product_id" value="<?php echo $edit_product['product_id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="product_name">Product Name</label>
                                <input type="text" id="product_name" name="product_name" class="form-control" 
                                       value="<?php echo $edit_product ? htmlspecialchars($edit_product['product_name']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="category_id">Category</label>
                                <select id="category_id" name="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo ($edit_product && $edit_product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="short_description">Short Description</label>
                            <input type="text" id="short_description" name="short_description" class="form-control" 
                                   value="<?php echo $edit_product ? htmlspecialchars($edit_product['short_description']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Full Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4"><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="base_price">Price ($)</label>
                                <input type="number" id="base_price" name="base_price" class="form-control" step="0.01" 
                                       value="<?php echo $edit_product ? $edit_product['base_price'] : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="sku">SKU</label>
                                <input type="text" id="sku" name="sku" class="form-control" 
                                       value="<?php echo $edit_product ? htmlspecialchars($edit_product['sku']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="stock_quantity">Stock Quantity</label>
                                <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" 
                                       value="<?php echo $edit_product ? $edit_product['stock_quantity'] : '0'; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Product Flags</label>
                            <div class="checkbox-group">
                                
                                <label class="checkbox-label">
                                    <input type="checkbox" name="is_bestseller" <?php echo ($edit_product && $edit_product['is_bestseller']) ? 'checked' : ''; ?>>
                                    Bestseller
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="is_new" <?php echo ($edit_product && $edit_product['is_new']) ? 'checked' : ''; ?>>
                                    New Product
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">
                                <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
                            </button>
                            <?php if ($edit_product): ?>
                                <a href="products.php" class="btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Products List -->
                <div class="admin-form" style="margin-top: 30px;">
                    <h2>All Products</h2>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($product['is_bestseller'])): ?><span class="badge bestseller">Bestseller</span><?php endif; ?>
                                        <?php if (!empty($product['is_new'])): ?><span class="badge new">New</span><?php endif; ?>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <br><small><?php echo htmlspecialchars($product['slug']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td><?php echo formatPrice($product['base_price']); ?></td>
                                    <td>
                                        <span class="<?php echo $product['stock_quantity'] < 10 ? 'stock-low' : ($product['stock_quantity'] < 50 ? 'stock-medium' : 'stock-good'); ?>">
                                            <?php echo $product['stock_quantity']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        
                                        <?php if ($product['is_bestseller']): ?><span class="badge bestseller">Bestseller</span><?php endif; ?>
                                        <?php if ($product['is_new']): ?><span class="badge new">New</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="products.php?edit=<?php echo $product['id']; ?>" class="btn-small">Edit</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                            <input type="hidden" name="action" value="delete_product">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" class="btn-small btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }
        
        .checkbox-group {
            display: flex;
            gap: 20px;
        }
        
        
        
        .form-actions {
            margin-top: 20px;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin-left: 10px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .badge {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
            margin-right: 5px;
        }
        
        .badge.featured { background: #3498db; color: white; }
        .badge.bestseller { background: #f39c12; color: white; }
        .badge.new { background: #27ae60; color: white; }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 5px;
        }
    </style>
</body>
</html>