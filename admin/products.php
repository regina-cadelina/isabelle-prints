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
                $slug = strtolower(str_replace(' ', '-', $_POST['product_name']));
                // Check for duplicate slug
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
                $stmt->execute([$slug]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "A product with this name/slug already exists. Please use a different name.";
                    break;
                }
                
                $imageFileName = null;
                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (in_array($ext, $allowed)) {
                        $imageFileName = uniqid('prod_', true) . '.' . $ext;
                        move_uploaded_file($_FILES['product_image']['tmp_name'], __DIR__ . '/../uploads/products/' . $imageFileName);
                    }
                }
                
                // Process features
                $features = [];
                if (!empty($_POST['features'])) {
                    $features = array_filter(array_map('trim', explode("\n", $_POST['features'])));
                }
                
                $stmt = $pdo->prepare("INSERT INTO products (category_id, product_name, name, slug, description, modal_description, short_description, base_price, sku, stock_quantity, is_bestseller, is_new, image_url, features) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['category_id'],
                    $_POST['product_name'],
                    $_POST['product_name'], // name field
                    $slug,
                    $_POST['description'] ?? '',
                    $_POST['modal_description'] ?? '',
                    $_POST['short_description'] ?? '',
                    $_POST['base_price'],
                    $_POST['sku'] ?? '',
                    $_POST['stock_quantity'],
                    isset($_POST['is_bestseller']) ? 1 : 0,
                    isset($_POST['is_new']) ? 1 : 0,
                    $imageFileName,
                    json_encode($features)
                ]);
                
                $productId = $pdo->lastInsertId();
                
                // Add customization options
                if (!empty($_POST['customization_options'])) {
                    foreach ($_POST['customization_options'] as $optionType => $options) {
                        $sortOrder = 0;
                        foreach ($options as $option) {
                            if (!empty($option['name']) && !empty($option['value'])) {
                                $stmt = $pdo->prepare("INSERT INTO product_customization_options (product_id, option_type, option_name, option_value, price_modifier, is_default, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                $stmt->execute([
                                    $productId,
                                    $optionType,
                                    $option['name'],
                                    $option['value'],
                                    $option['price_modifier'] ?? 0,
                                    isset($option['is_default']) ? 1 : 0,
                                    $sortOrder++
                                ]);
                            }
                        }
                    }
                }
                
                $success = "Product added successfully!";
                break;

            case 'update_product':
                $imageFileName = $edit_product['image_url'] ?? null;
                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (in_array($ext, $allowed)) {
                        $imageFileName = uniqid('prod_', true) . '.' . $ext;
                        move_uploaded_file($_FILES['product_image']['tmp_name'], __DIR__ . '/../uploads/products/' . $imageFileName);
                    }
                }
                
                // Process features
                $features = [];
                if (!empty($_POST['features'])) {
                    $features = array_filter(array_map('trim', explode("\n", $_POST['features'])));
                }
                
                $stmt = $pdo->prepare("UPDATE products SET category_id = ?, product_name = ?, name = ?, description = ?, modal_description = ?, short_description = ?, base_price = ?, sku = ?, stock_quantity = ?, is_bestseller = ?, is_new = ?, image_url = ?, features = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['category_id'],
                    $_POST['product_name'],
                    $_POST['product_name'], // name field
                    $_POST['description'] ?? '',
                    $_POST['modal_description'] ?? '',
                    $_POST['short_description'] ?? '',
                    $_POST['base_price'],
                    $_POST['sku'] ?? '',
                    $_POST['stock_quantity'],
                    isset($_POST['is_bestseller']) ? 1 : 0,
                    isset($_POST['is_new']) ? 1 : 0,
                    $imageFileName,
                    json_encode($features),
                    $_POST['product_id']
                ]);
                
                // Update customization options
                // First, delete existing options
                $stmt = $pdo->prepare("DELETE FROM product_customization_options WHERE product_id = ?");
                $stmt->execute([$_POST['product_id']]);
                
                // Add new options
                if (!empty($_POST['customization_options'])) {
                    foreach ($_POST['customization_options'] as $optionType => $options) {
                        $sortOrder = 0;
                        foreach ($options as $option) {
                            if (!empty($option['name']) && !empty($option['value'])) {
                                $stmt = $pdo->prepare("INSERT INTO product_customization_options (product_id, option_type, option_name, option_value, price_modifier, is_default, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                $stmt->execute([
                                    $_POST['product_id'],
                                    $optionType,
                                    $option['name'],
                                    $option['value'],
                                    $option['price_modifier'] ?? 0,
                                    isset($option['is_default']) ? 1 : 0,
                                    $sortOrder++
                                ]);
                            }
                        }
                    }
                }
                
                $success = "Product updated successfully!";
                break;

            case 'delete_product':
                $stmt = $pdo->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
                $stmt->execute([$_POST['product_id']]);
                $success = "Product deleted successfully!";
                break;
        }
    }
}

// Handle search
$search = trim($_GET['search'] ?? '');
$search_sql = '';
$params = [];
if ($search !== '') {
    $search_sql = "AND (p.product_name LIKE :search OR p.sku LIKE :search OR c.name LIKE :search)";
    $params[':search'] = "%$search%";
}

// Pagination setup
$perPage = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $perPage;

// Count total products for pagination (with search)
$count_sql = "
    SELECT COUNT(*) FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1 " . ($search ? "AND (p.product_name LIKE :search OR p.sku LIKE :search OR c.name LIKE :search)" : "");
$count_stmt = $pdo->prepare($count_sql);
if ($search) {
    $count_stmt->execute([':search' => "%$search%"]);
} else {
    $count_stmt->execute();
}
$totalProducts = $count_stmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

// Get products with search and pagination
$product_sql = "
    SELECT p.*, c.name AS category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 $search_sql
    ORDER BY p.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($product_sql);
if ($search) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for dropdown
$stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
$categories = $stmt->fetchAll();

// Get product for editing if edit parameter is set
$edit_product = null;
$edit_options = [];
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_product = $stmt->fetch();
    
    if ($edit_product) {
        // Get existing customization options
        $stmt = $pdo->prepare("SELECT * FROM product_customization_options WHERE product_id = ? ORDER BY option_type, sort_order");
        $stmt->execute([$_GET['edit']]);
        $options = $stmt->fetchAll();
        
        foreach ($options as $option) {
            $edit_options[$option['option_type']][] = $option;
        }
    }
}

$page_title = "Manage Products";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .customization-section {
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .option-type {
            margin-bottom: 20px;
        }
        .option-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        .option-item input, .option-item select {
            flex: 1;
        }
        .add-option-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        .remove-option-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        .features-textarea {
            width: 100%;
            min-height: 100px;
            resize: vertical;
        }
    </style>
</head>
<body class="admin-body">
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">
                <h2><i class="fas fa-cog"></i> Admin Panel</h2>
            </div>
            <div class="admin-user">
                <a href="../pages/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </header>

    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <nav class="admin-menu">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Manage Categories</a></li>
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
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Add/Edit Product Form -->
                <div class="admin-form">
                    <h2><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $edit_product ? 'update_product' : 'add_product'; ?>" />
                        <?php if ($edit_product): ?>
                            <input type="hidden" name="product_id" value="<?php echo (int)$edit_product['id']; ?>" />
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="product_name">Product Name</label>
                                <input type="text" id="product_name" name="product_name" class="form-control" 
                                    value="<?php echo $edit_product ? htmlspecialchars($edit_product['product_name'] ?? '') : ''; ?>" required />
                            </div>

                            <div class="form-group">
                                <label for="category_id">Category</label>
                                <select id="category_id" name="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo (int)$category['id']; ?>" 
                                            <?php echo ($edit_product && $edit_product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="stock_quantity">Stock Quantity</label>
                                <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" 
                                    value="<?php echo $edit_product ? (int)$edit_product['stock_quantity'] : '0'; ?>" required />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="short_description">Short Description</label>
                            <input type="text" id="short_description" name="short_description" class="form-control" 
                                value="<?php echo $edit_product ? htmlspecialchars($edit_product['short_description'] ?? '') : ''; ?>" />
                        </div>

                        <div class="form-group">
                            <label for="description">Full Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4"><?php echo $edit_product ? htmlspecialchars($edit_product['description'] ?? '') : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="modal_description">Modal Description (shown in product popup)</label>
                            <textarea id="modal_description" name="modal_description" class="form-control" rows="3"><?php echo $edit_product ? htmlspecialchars($edit_product['modal_description'] ?? '') : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="features">Product Features (one per line)</label>
                            <textarea id="features" name="features" class="form-control features-textarea" rows="5" placeholder="Premium quality materials&#10;Fast turnaround time&#10;Professional design support"><?php 
                                if ($edit_product && !empty($edit_product['features'])) {
                                    $features = json_decode($edit_product['features'], true);
                                    if (is_array($features)) {
                                        echo htmlspecialchars(implode("\n", $features));
                                    } else {
                                        echo htmlspecialchars($edit_product['features']);
                                    }
                                }
                            ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="base_price">Price (₱)</label>
                                <input type="number" id="base_price" name="base_price" class="form-control" step="0.01" 
                                    value="<?php echo $edit_product ? htmlspecialchars($edit_product['base_price'] ?? '') : ''; ?>" required />
                            </div>

                            <div class="form-group">
                                <label for="sku">SKU</label>
                                <input type="text" id="sku" name="sku" class="form-control" 
                                    value="<?php echo $edit_product ? htmlspecialchars($edit_product['sku'] ?? '') : ''; ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Product Flags</label>
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="is_bestseller" <?php echo ($edit_product && $edit_product['is_bestseller']) ? 'checked' : ''; ?> />
                                    Bestseller
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="is_new" <?php echo ($edit_product && $edit_product['is_new']) ? 'checked' : ''; ?> />
                                    New Product
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="product_image">Product Image</label>
                            <input type="file" id="product_image" name="product_image" accept="image/*" class="form-control" />
                            <?php if ($edit_product && $edit_product['image_url']): ?>
                                <div style="margin-top:10px;">
                                    <img src="../uploads/products/<?php echo htmlspecialchars($edit_product['image_url']); ?>" alt="Product Image" style="max-width:100px;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Customization Options Section -->
                        <div class="customization-section">
                            <h3>Customization Options</h3>
                            <p>Configure the options customers can choose from (sizes, colors, finishes, materials)</p>
                            
                            <?php 
                            $option_types = ['size', 'color', 'finish', 'material'];
                            foreach ($option_types as $type): 
                            ?>
                                <div class="option-type">
                                    <h4><?php echo ucfirst($type); ?> Options</h4>
                                    <div id="<?php echo $type; ?>-options">
                                        <?php 
                                        $existing_options = $edit_options[$type] ?? [];
                                        if (empty($existing_options)) {
                                            $existing_options = [null]; // Show one empty row
                                        }
                                        foreach ($existing_options as $index => $option): 
                                        ?>
                                            <div class="option-item">
                                                <input type="text" name="customization_options[<?php echo $type; ?>][<?php echo $index; ?>][name]" 
                                                       placeholder="Option Name (e.g., Small, Red)" 
                                                       value="<?php echo $option ? htmlspecialchars($option['option_name']) : ''; ?>">
                                                <input type="text" name="customization_options[<?php echo $type; ?>][<?php echo $index; ?>][value]" 
                                                       placeholder="<?php echo $type === 'color' ? 'Color Value (e.g., #FF0000)' : 'Option Value'; ?>" 
                                                       value="<?php echo $option ? htmlspecialchars($option['option_value']) : ''; ?>">
                                                <input type="number" name="customization_options[<?php echo $type; ?>][<?php echo $index; ?>][price_modifier]" 
                                                       placeholder="Price +/-" step="0.01" 
                                                       value="<?php echo $option ? $option['price_modifier'] : '0'; ?>">
                                                <label>
                                                    <input type="checkbox" name="customization_options[<?php echo $type; ?>][<?php echo $index; ?>][is_default]" 
                                                           <?php echo ($option && $option['is_default']) ? 'checked' : ''; ?>>
                                                    Default
                                                </label>
                                                <button type="button" class="remove-option-btn" onclick="removeOption(this)">Remove</button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="button" class="add-option-btn" onclick="addOption('<?php echo $type; ?>')">Add <?php echo ucfirst($type); ?> Option</button>
                                </div>
                            <?php endforeach; ?>
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
                    <form method="get" style="margin-bottom:15px;display:flex;gap:10px;align-items:center;">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, SKU, or category" value="<?php echo htmlspecialchars($search); ?>" style="max-width:250px;">
                        <button type="submit" class="btn-secondary"><i class="fas fa-search"></i> Search</button>
                        <?php if ($search): ?>
                            <a href="products.php" class="btn-secondary" style="background:#eee;color:#333;">Clear</a>
                        <?php endif; ?>
                    </form>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>SKU</th> <!-- Added SKU column -->
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($products): ?>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['product_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                            <td>$<?php echo number_format($product['base_price'], 2); ?></td>
                                            <td><?php echo (int)$product['stock_quantity']; ?></td>
                                            <td>
                                                <?php if ($product['is_bestseller']): ?>
                                                    <span class="label label-success">Bestseller</span>
                                                <?php endif; ?>
                                                <?php if ($product['is_new']): ?>
                                                    <span class="label label-info">New</span>
                                                <?php endif; ?>
                                                <?php if (!$product['is_bestseller'] && !$product['is_new']): ?>
                                                    <span class="label label-default">Normal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="products.php?edit=<?php echo (int)$product['id']; ?>" class="btn-action btn-edit" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                    <input type="hidden" name="action" value="delete_product" />
                                                    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>" />
                                                    <button type="submit" class="btn-action btn-delete" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" style="text-align:center;">No products found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <!-- Pagination Controls -->
                        <div style="margin-top:15px; text-align:center;">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn-small" style="margin-right:10px;">&larr; Prev</a>
                            <?php endif; ?>
                            <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                            <?php if ($page < $totalPages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn-small" style="margin-left:10px;">Next &rarr;</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        let optionCounters = {
            size: <?php echo count($edit_options['size'] ?? []) ?: 1; ?>,
            color: <?php echo count($edit_options['color'] ?? []) ?: 1; ?>,
            finish: <?php echo count($edit_options['finish'] ?? []) ?: 1; ?>,
            material: <?php echo count($edit_options['material'] ?? []) ?: 1; ?>
        };

        function addOption(type) {
            const container = document.getElementById(type + '-options');
            const index = optionCounters[type]++;
            
            const div = document.createElement('div');
            div.className = 'option-item';
            div.innerHTML = `
                <input type="text" name="customization_options[${type}][${index}][name]" 
                       placeholder="Option Name (e.g., Small, Red)">
                <input type="text" name="customization_options[${type}][${index}][value]" 
                       placeholder="${type === 'color' ? 'Color Value (e.g., #FF0000)' : 'Option Value'}">
                <input type="number" name="customization_options[${type}][${index}][price_modifier]" 
                       placeholder="Price +/-" step="0.01" value="0">
                <label>
                    <input type="checkbox" name="customization_options[${type}][${index}][is_default]">
                    Default
                </label>
                <button type="button" class="remove-option-btn" onclick="removeOption(this)">Remove</button>
            `;
            
            container.appendChild(div);
        }

        function removeOption(button) {
            button.parentElement.remove();
        }
    </script>
</body>
</html>
