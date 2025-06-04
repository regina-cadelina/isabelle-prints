<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || getCurrentUser()['user_type'] !== 'admin') {
    redirect('../login.php');
}

$current_user = getCurrentUser();

// Handle status update or delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$_POST['status'], $_POST['order_id']]);
                $success = "Order status updated successfully!";
                break;
            case 'update_payment_status':
                $stmt = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
                $stmt->execute([$_POST['payment_status'], $_POST['order_id']]);
                $success = "Payment status updated successfully!";
                break;
            case 'delete_order':
                $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                $stmt->execute([$_POST['order_id']]);
                $success = "Order deleted successfully!";
                break;
        }
    }
}

// --- SEARCH FUNCTIONALITY ---
$search = trim($_GET['search'] ?? '');
$search_sql = '';
$params = [];
if ($search !== '') {
    $search_sql = "WHERE (
        o.id LIKE :search
        OR o.order_number LIKE :search
        OR o.status LIKE :search
        OR o.payment_status LIKE :search
        OR u.first_name LIKE :search
        OR u.last_name LIKE :search
        OR u.email LIKE :search
    )";
    $params[':search'] = "%$search%";
}

// Pagination setup
$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $perPage;

// Count total orders for pagination (with search)
$count_sql = "
    SELECT COUNT(*) FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    " . ($search ? $search_sql : "");
$count_stmt = $pdo->prepare($count_sql);
if ($search) {
    $count_stmt->execute([':search' => "%$search%"]);
} else {
    $count_stmt->execute();
}
$totalOrders = $count_stmt->fetchColumn();
$totalPages = ceil($totalOrders / $perPage);

// Fetch orders with search and pagination
$query = "
    SELECT 
        o.id AS order_id, 
        o.order_number, 
        o.total_amount, 
        o.status, 
        o.payment_status, 
        o.created_at, 
        o.reference_number,
        u.first_name, 
        u.last_name, 
        u.email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    $search_sql
    ORDER BY o.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($query);
if ($search) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$orders = $stmt->fetchAll();

$page_title = "Manage Orders";
$statusOptions = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="admin-body">
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
        <aside class="admin-sidebar">
            <nav class="admin-menu">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Manage Categories</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Manage Products</a></li>
                    <li><a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> Manage Orders</a></li>
                    <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="faqs.php"><i class="fas fa-question-circle"></i> Manage FAQs</a></li>
                    <li><a href="../index.php"><i class="fas fa-globe"></i> View Website</a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-main">
            <div class="admin-content">
                <h1>Order Management</h1>

                <?php if (isset($success)): ?>
                    <div class="admin-alert success" style="margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <!-- Search Form -->
                <div class="admin-card" style="margin-bottom: 30px;">
                    <div class="admin-card-header">
                        <i class="fas fa-search"></i> Search Orders
                    </div>
                    <div class="admin-card-body">
                        <form method="get" style="display: flex; gap: 15px; align-items: center;">
                            <div style="flex: 1;">
                                <input type="text" name="search" class="form-control" placeholder="Search by order ID, order #, customer, email, status..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <?php if ($search): ?>
                                <a href="orders.php" class="btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <i class="fas fa-shopping-cart"></i> All Orders
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
                                        <th>Order ID</th>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($orders)): ?>
                                        <tr>
                                            <td colspan="8" style="text-align: center; color: #7f8c8d; font-style: italic; padding: 40px;">
                                                <?php if ($search): ?>
                                                    No orders found matching your search criteria.
                                                <?php else: ?>
                                                    No orders found.
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($order['order_id']); ?></strong></td>
                                            <td>
                                                <span style="font-family: monospace; background: #f8f9fa; padding: 2px 6px; border-radius: 4px;">
                                                    #<?php echo htmlspecialchars($order['order_number']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong>
                                                    <br><small style="color: #7f8c8d;"><?php echo htmlspecialchars($order['email']); ?></small>
                                                </div>
                                            </td>
                                            <td class="price-cell"><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                                    <select name="status" onchange="if(confirm('Change order status?')) this.form.submit();" class="form-control" style="font-size: 0.85rem;">
                                                        <?php foreach ($statusOptions as $status): ?>
                                                            <option value="<?php echo $status; ?>" <?php if($order['status'] == $status) echo 'selected'; ?>>
                                                                <?php echo ucfirst($status); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                            </td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_payment_status">
                                                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                                    <select name="payment_status" onchange="if(confirm('Change payment status?')) this.form.submit();" class="form-control" style="font-size: 0.85rem;">
                                                        <?php foreach (['unpaid', 'pending', 'paid'] as $payStatus): ?>
                                                            <option value="<?php echo $payStatus; ?>" <?php if($order['payment_status'] == $payStatus) echo 'selected'; ?>>
                                                                <?php echo ucfirst($payStatus); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn-small" style="margin-right: 5px;">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this order? This action cannot be undone.');">
                                                    <input type="hidden" name="action" value="delete_order">
                                                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                                    <button type="submit" class="btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls -->
                        <?php if ($totalPages > 1): ?>
                            <div style="margin-top: 20px; text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                                <?php if ($page > 1): ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn-small" style="margin-right: 10px;">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                <?php endif; ?>
                                <span style="color: #2c3e50; font-weight: 600;">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                                <?php if ($page < $totalPages): ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn-small" style="margin-left: 10px;">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($orders)): ?>
                            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                                <strong style="color: #2c3e50;">
                                    <i class="fas fa-shopping-cart"></i> Total Orders: <?php echo $totalOrders; ?>
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
