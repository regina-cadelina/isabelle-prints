<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

$current_user = getCurrentUser();

// Handle add, edit, delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Handle file upload for add and edit
        $image_url = '';
        if (!empty($_FILES['image']['name'])) {
            $target_dir = "../uploads/categories/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $filename = uniqid('cat_', true) . '.' . $ext;
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = $filename;
            }
        }

        if ($_POST['action'] === 'add' && !empty($_POST['category_name'])) {
            $icon = trim($_POST['icon'] ?? '');
            $name = trim($_POST['category_name']);
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            // Ensure slug is unique
            $check = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
            $baseSlug = $slug;
            $i = 1;
            while (true) {
                $check->execute([$slug]);
                if ($check->fetchColumn() == 0) break;
                $slug = $baseSlug . '-' . $i++;
            }
            $stmt = $pdo->prepare("INSERT INTO categories (name, icon, slug, image_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $icon, $slug, $image_url]);
            header("Location: categories.php");
            exit;
        }
        if ($_POST['action'] === 'edit' && !empty($_POST['category_id']) && !empty($_POST['category_name'])) {
            $icon = trim($_POST['icon'] ?? '');
            $name = trim($_POST['category_name']);
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            // Ensure slug is unique except for this category
            $check = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ? AND id != ?");
            $baseSlug = $slug;
            $i = 1;
            while (true) {
                $check->execute([$slug, $_POST['category_id']]);
                if ($check->fetchColumn() == 0) break;
                $slug = $baseSlug . '-' . $i++;
            }
            // If a new image is uploaded, update it, else keep the old one
            if ($image_url) {
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, icon = ?, slug = ?, image_url = ? WHERE id = ?");
                $stmt->execute([$name, $icon, $slug, $image_url, $_POST['category_id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, icon = ?, slug = ? WHERE id = ?");
                $stmt->execute([$name, $icon, $slug, $_POST['category_id']]);
            }
        }
        if ($_POST['action'] === 'delete' && !empty($_POST['category_id'])) {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$_POST['category_id']]);
        }
        header("Location: categories.php");
        exit;
    }
}

// Pagination setup
$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $perPage;

// Get total count
$totalStmt = $pdo->query("SELECT COUNT(*) FROM categories");
$totalCategories = $totalStmt->fetchColumn();
$totalPages = ceil($totalCategories / $perPage);

// Fetch paginated categories
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY id ASC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$categories = $stmt->fetchAll();

$page_title = "Manage Categories";
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
                    <li><a href="categories.php" class="active"><i class="fas fa-tags"></i> Manage Categories</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Manage Products</a></li>
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
                <h1>Manage Categories</h1>
                
                <!-- Add Category Form -->
                <div class="dashboard-section">
                    <h2>Add New Category</h2>
                    <form method="post" class="admin-form" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="category_name">Category Name</label>
                            <input type="text" id="category_name" name="category_name" class="form-control" placeholder="Enter category name" required>
                        </div>
                        <div class="form-group">
                            <label for="icon">Icon (Font Awesome class name)</label>
                            <input type="text" id="icon" name="icon" class="form-control" placeholder="e.g. tag, box, print">
                            <small>Enter the Font Awesome icon name without the "fa-" prefix</small>
                        </div>
                        <div class="form-group">
                            <label for="image">Category Image</label>
                            <input type="file" id="image" name="image" class="form-control" accept="image/*">
                            <small>Optional. Upload a category image (jpg, png, etc.)</small>
                        </div>
                        <button type="submit" name="action" value="add" class="btn-primary">Add Category</button>
                    </form>
                </div>
                
                <!-- Categories Table -->
                <div class="dashboard-section">
                    <h2>Existing Categories</h2>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Icon</th>
                                    <th>Image</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <form method="post" enctype="multipart/form-data">
                                        <td><?php echo $cat['id']; ?></td>
                                        <td>
                                            <input type="text" name="category_name" class="form-control" value="<?php echo htmlspecialchars($cat['name']); ?>" required>
                                            <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                        </td>
                                        <td>
                                            <input type="text" name="icon" class="form-control" value="<?php echo htmlspecialchars($cat['icon'] ?? ''); ?>" placeholder="Icon name">
                                            <?php if (!empty($cat['icon'])): ?>
                                                <i class="fas fa-<?php echo htmlspecialchars($cat['icon']); ?>"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($cat['image_url'])): ?>
                                                <img src="../uploads/categories/<?php echo htmlspecialchars($cat['image_url']); ?>" alt="Category Image" style="width:40px;height:40px;object-fit:cover;">
                                            <?php else: ?>
                                                <span style="color:#aaa;">No image</span>
                                            <?php endif; ?>
                                            <input type="file" name="image" class="form-control" accept="image/*" style="margin-top:5px;">
                                        </td>
                                        <td>
                                            <button type="submit" name="action" value="edit" class="btn-small">Update</button>
                                            <button type="submit" name="action" value="delete" class="btn-danger" onclick="return confirm('Are you sure you want to delete this category?');">Delete</button>
                                        </td>
                                    </form>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <!-- Pagination Controls -->
                        <div style="margin-top:15px; text-align:center;">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="btn-small" style="margin-right:10px;">&larr; Prev</a>
                            <?php endif; ?>
                            <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="btn-small" style="margin-left:10px;">Next &rarr;</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>