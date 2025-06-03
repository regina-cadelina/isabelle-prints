<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

session_start(); // Make sure session is started

$current_user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();
}

// Get total orders and total sales
$stmt = $pdo->query("SELECT COUNT(*) AS total_orders, SUM(total_amount) AS total_sales FROM orders");
$summary = $stmt->fetch();

// Get order count by status
$stmt = $pdo->query("SELECT status, COUNT(*) AS count FROM orders GROUP BY status");
$statusCounts = $stmt->fetchAll();

$page_title = "Sales Report";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
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
    <?php
require_once '../config/database.php';

// Get total orders and total sales
$stmt = $pdo->query("SELECT COUNT(*) AS total_orders, SUM(total_amount) AS total_sales FROM orders");
$summary = $stmt->fetch();
$totalOrders = $summary['total_orders'] ?? 0;
$totalSales = $summary['total_sales'] ?? 0;

// Get order count by status
$stmt = $pdo->query("SELECT status, COUNT(*) AS count FROM orders GROUP BY status");
$statusCounts = $stmt->fetchAll();

$page_title = "Sales Report";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="admin-body">

    <aside class="admin-sidebar">
        <nav class="admin-menu">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Manage Products</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Manage Orders</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="faqs.php"><i class="fas fa-question-circle"></i> Manage FAQs</a></li>
                <li><a href="../index.php"><i class="fas fa-globe"></i> View Website</a></li>
            </ul>
        </nav>
    </aside>

    <div class="admin-container">
        <main class="admin-main">
            <div class="admin-content">
                <h1>Sales Report</h1>
                <p><a href="dashboard.php">&larr; Back to Dashboard</a></p>
                <div class="admin-form" style="margin-top: 30px;">
                    <h2>Summary</h2>
                    <ul>
                        <li><strong>Total Orders:</strong> <?php echo $totalOrders; ?></li>
                        <li><strong>Total Sales:</strong> $<?php echo number_format($totalSales, 2); ?></li>
                    </ul>

                    <h2>Orders by Status</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Order Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($statusCounts)): ?>
                                <tr>
                                    <td colspan="2">No orders found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($statusCounts as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                                        <td><?php echo $row['count']; ?></td>
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