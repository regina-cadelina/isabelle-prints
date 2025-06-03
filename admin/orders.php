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
                $success = "Order status updated!";
                break;
            case 'update_payment_status':
                $stmt = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
                $stmt->execute([$_POST['payment_status'], $_POST['order_id']]);
                $success = "Payment status updated!";
                break;
            case 'delete_order':
                $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                $stmt->execute([$_POST['order_id']]);
                $success = "Order deleted!";
                break;
        }
    }
}

// --- SEARCH FUNCTIONALITY ---
$search = trim($_GET['search'] ?? '');
$search_sql = '';
$params = [];
if ($search !== '') {
    $search_sql = "WHERE (o.id LIKE :search OR o.status LIKE :search OR o.payment_status LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%$search%";
}

// Fetch orders with search
$query = "
    SELECT o.id AS order_id, o.total_amount, o.status, o.payment_status, o.created_at, u.first_name, u.last_name, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    $search_sql
    ORDER BY o.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
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
                    <li><a href="products.php"><i class="fas fa-box"></i> Manage Products</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Manage Categories</a></li>
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
                <h1>Manage Orders</h1>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <!-- Search Form -->
                <form method="get" style="margin-bottom:15px;display:flex;gap:10px;align-items:center;">
                    <input type="text" name="search" class="form-control" placeholder="Search by order #, customer, email, status..." value="<?php echo htmlspecialchars($search); ?>" style="max-width:250px;">
                    <button type="submit" class="btn-secondary"><i class="fas fa-search"></i> Search</button>
                    <?php if ($search): ?>
                        <a href="orders.php" class="btn-secondary" style="background:#eee;color:#333;">Clear</a>
                    <?php endif; ?>
                </form>

                <div class="admin-form" style="margin-top: 30px;">
                    <h2>All Orders</h2>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($orders as $order): ?>
<tr>
    <td><?php echo htmlspecialchars($order['order_id']); ?></td>
    <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
    <td><?php echo htmlspecialchars($order['email']); ?></td>
    <td><?php echo number_format($order['total_amount'], 2); ?></td>
    <td>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
            <select name="status" onchange="if(confirm('Change order status?')) this.form.submit();">
                <?php foreach ($statusOptions as $status): ?>
                    <option value="<?php echo $status; ?>" <?php if($order['status'] == $status) echo 'selected'; ?>>
                        <?php echo ucfirst($status); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </td>
    <td>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="update_payment_status">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
            <select name="payment_status" onchange="if(confirm('Change payment status?')) this.form.submit();">
                <?php foreach (['unpaid', 'pending', 'paid'] as $payStatus): ?>
                    <option value="<?php echo $payStatus; ?>" <?php if($order['payment_status'] == $payStatus) echo 'selected'; ?>>
                        <?php echo ucfirst($payStatus); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </td>
    <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
    <td>
        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this order?');">
            <input type="hidden" name="action" value="delete_order">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
            <button type="submit" class="btn-small btn-danger">Delete</button>
        </form>
        <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn-small">View</a>
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
        .admin-table th, .admin-table td { padding: 8px 10px; }
        .btn-small { background: #3498db; color: #fff; border: none; padding: 4px 8px; border-radius: 3px; font-size: 12px; text-decoration: none; }
        .btn-danger { background: #e74c3c; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</body>
</html>
<script>
    // Handle status change confirmation
    document.querySelectorAll('select[name="status"]').forEach(select => {
        select.addEventListener('change', function() {
            if (!confirm('Change order status?')) {
                this.value = this.dataset.originalValue; // Reset to original value
            }
        });
    });
    // Initialize original values for status selects
    document.querySelectorAll('select[name="status"]').forEach(select => {
        select.dataset.originalValue = select.value; // Store original value
    });
</script>