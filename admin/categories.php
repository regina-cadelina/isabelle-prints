<?php
require_once '../config/database.php';

// Handle add, edit, delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && !empty($_POST['category_name'])) {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([trim($_POST['category_name'])]);
        }
        if ($_POST['action'] === 'edit' && !empty($_POST['category_id']) && !empty($_POST['category_name'])) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->execute([trim($_POST['category_name']), $_POST['category_id']]);
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
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <a href="dashboard.php" class="btn" style="display:inline-block;margin-bottom:20px;">&larr; Back to Dashboard</a>
        <h2>Manage Categories</h2>
        <!-- Add Category Form -->
        <form method="post" style="margin-bottom:20px;">
            <input type="text" name="category_name" placeholder="New Category Name" required>
            <button type="submit" name="action" value="add">Add Category</button>
        </form>

        <!-- Categories Table -->
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <form method="post">
                        <td><?php echo $cat['id']; ?></td>
                        <td>
                            <input type="text" name="category_name" value="<?php echo htmlspecialchars($cat['name']); ?>" required>
                            <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                        </td>
                        <td>
                            <button type="submit" name="action" value="edit">update</button>
                            <button type="submit" name="action" value="delete" onclick="return confirm('Delete this category?');">Delete</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>