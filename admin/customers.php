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

// Handle enable/disable/delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_action'], $_POST['customer_id'])) {
    $customer_id = (int)$_POST['customer_id'];
    if ($_POST['customer_action'] === 'toggle_active') {
        // Toggle is_active
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$customer_id]);
        $success = "Customer status updated successfully!";
    } elseif ($_POST['customer_action'] === 'delete') {
        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$customer_id]);
        $success = "Customer deleted successfully!";
    }
}

// --- SEARCH FUNCTIONALITY ---
$search = trim($_GET['search'] ?? '');
$params = [];
$search_sql = '';
if ($search !== '') {
    $search_sql = "WHERE (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}

// Fetch customers with search
$stmt = $pdo->prepare("SELECT * FROM users $search_sql ORDER BY id DESC");
$stmt->execute($params);
$customers = $stmt->fetchAll();

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
                <a href="../pages/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </header>

    <aside class="admin-sidebar">
        <nav class="admin-menu">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Manage Categories</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Manage Products</a></li>
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
                <h1>Customer Management</h1>

                <?php if (isset($success)): ?>
                    <div class="admin-alert success" style="margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <!-- Search and Filters -->
                <div class="admin-card" style="margin-bottom: 30px;">
                    <div class="admin-card-header">
                        <i class="fas fa-search"></i> Search Customers
                    </div>
                    <div class="admin-card-body">
                        <form method="get" style="display: flex; gap: 15px; align-items: center;">
                            <div style="flex: 1;">
                                <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <?php if ($search): ?>
                                <a href="customers.php" class="btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Customers Table -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <i class="fas fa-users"></i> All Customers
                        <?php if ($search): ?>
                            <span style="font-weight: normal; font-size: 0.9rem; margin-left: 10px;">
                                (Search results for: "<?php echo htmlspecialchars($search); ?>")
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="admin-card-body">
                        <div class="table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($customers)): ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center; color: #7f8c8d; font-style: italic; padding: 40px;">
                                                <?php if ($search): ?>
                                                    No customers found matching your search criteria.
                                                <?php else: ?>
                                                    No customers found.
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($customers as $customer): ?>
                                            <tr>
                                                <td><strong><?php echo $customer['id']; ?></strong></td>
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                        <div style="width: 35px; height: 35px; background: linear-gradient(135deg, #3498db, #2980b9); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                                            <?php echo strtoupper(substr($customer['first_name'] ?? 'U', 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')); ?></strong>
                                                            <?php if (($customer['user_type'] ?? '') === 'admin'): ?>
                                                                <br><small style="color: #e74c3c; font-weight: bold;">ADMIN</small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($customer['email'] ?? ''); ?></td>
                                                <td>
                                                    <?php if (($customer['is_active'] ?? 0)): ?>
                                                        <span class="badge paid">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge unpaid">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($customer['created_at'] ?? 'now')); ?></td>
                                                <td>
                                                    <?php if (($customer['user_type'] ?? '') !== 'admin'): ?>
                                                        <form method="POST" style="display: inline-block; margin-right: 5px;">
                                                            <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                                            <button type="submit" name="customer_action" value="toggle_active" class="btn-small" style="background: <?php echo ($customer['is_active'] ?? 0) ? '#e74c3c' : '#27ae60'; ?>;">
                                                                <i class="fas fa-<?php echo ($customer['is_active'] ?? 0) ? 'ban' : 'check'; ?>"></i>
                                                                <?php echo ($customer['is_active'] ?? 0) ? 'Disable' : 'Enable'; ?>
                                                            </button>
                                                        </form>
                                                        <form method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this customer account? This action cannot be undone.');">
                                                            <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                                            <button type="submit" name="customer_action" value="delete" class="btn-danger">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span style="color: #7f8c8d; font-style: italic;">Admin Account</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (!empty($customers)): ?>
                            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                                <strong style="color: #2c3e50;">
                                    <i class="fas fa-users"></i> Total Customers: <?php echo count($customers); ?>
                                </strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
