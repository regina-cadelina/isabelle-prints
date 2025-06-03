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
        if ($_POST['action'] === 'add' && !empty($_POST['category_name'])) {
            $icon = trim($_POST['icon'] ?? '');
            $stmt = $pdo->prepare("INSERT INTO categories (name, icon) VALUES (?, ?)");
            $stmt->execute([trim($_POST['category_name']), $icon]);
            header("Location: categories.php");
            exit;
        }
        if ($_POST['action'] === 'edit' && !empty($_POST['category_id']) && !empty($_POST['category_name'])) {
            $icon = trim($_POST['icon'] ?? '');
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, icon = ? WHERE id = ?");
            $stmt->execute([trim($_POST['category_name']), $icon, $_POST['category_id']]);
        }
        if ($_POST['action'] === 'delete' && !empty($_POST['category_id'])) {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$_POST['category_id']]);
        }
        header("Location: categories.php");
        exit;
    }
}

// Fetch all categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
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
                    <form method="post" class="admin-form">
                        <div class="form-group">
                            <label for="category_name">Category Name</label>
                            <input type="text" id="category_name" name="category_name" class="form-control" placeholder="Enter category name" required>
                        </div>
                        <div class="form-group">
                            <label for="icon">Icon (Font Awesome class name)</label>
                            <input type="text" id="icon" name="icon" class="form-control" placeholder="e.g. tag, box, print">
                            <small>Enter the Font Awesome icon name without the "fa-" prefix</small>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <form method="post">
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
                                            <button type="submit" name="action" value="edit" class="btn-small">Update</button>
                                            <button type="submit" name="action" value="delete" class="btn-danger" onclick="return confirm('Are you sure you want to delete this category?');">Delete</button>
                                        </td>
                                    </form>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
</body>
</html>