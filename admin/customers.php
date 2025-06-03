<?php

// Check if user is logged in and is an admin
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();
}

// Fetch all customers
$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$customers = $stmt->fetchAll();

// Handle enable/disable/delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_action'], $_POST['customer_id'])) {
    $customer_id = (int)$_POST['customer_id'];
    if ($_POST['customer_action'] === 'toggle_active') {
        // Toggle is_active
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$customer_id]);
    } elseif ($_POST['customer_action'] === 'delete') {
        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$customer_id]);
    }
    // Refresh to avoid resubmission
    header("Location: customers.php");
    exit;
}

$page_title = "Customers";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="admin-body">
   <!-- Admin Header -->
   <header class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">
                <h2><i class="fas fa-cog"></i> Admin Panel</h2>
            </div>
            <div class="admin-user">
                <span>&nbsp Welcome, <?php echo htmlspecialchars($current_user['first_name'] ?? 'Admin'); ?></span>
                <a href="../pages/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </header>

    <aside class="admin-sidebar">
        <nav class="admin-menu">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Manage Products</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Manage Categories</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Manage Orders</a></li>
                <li><a href="customers.php" class="active"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="faqs.php"><i class="fas fa-question-circle"></i> Manage FAQs</a></li>
                <li><a href="../index.php"><i class="fas fa-globe"></i> View Website</a></li>
            </ul>
        </nav>
    </aside>

    <div class="admin-container">
        <main class="admin-main">
            <div class="admin-content">
                <h1>Customers</h1>
                <p><a href="dashboard.php">&larr; Back to Dashboard</a></p>
                <div class="admin-form" style="margin-top: 30px;">
                    <table class="admin-table">
                        <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Registered</th>
        <th>Actions</th>
    </tr>
</thead>
                        <tbody>
                            <?php if (empty($customers)): ?>
                                <tr>
                                    <td colspan="5">No customers found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><?php echo $customer['id']; ?></td>
                                        <td><?php echo htmlspecialchars(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars($customer['email'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($customer['created_at'] ?? ''); ?></td>
                                        <td>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                                <button type="submit" name="customer_action" value="toggle_active" class="btn-action">
                                                    <?php echo ($customer['is_active'] ?? 0) ? 'Disable' : 'Enable'; ?>
                                                </button>
                                            </form>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this account? This action cannot be undone.');">
                                                <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                                <button type="submit" name="customer_action" value="delete" class="btn-action btn-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>