
<?php
require_once '../includes/functions.php';
// Check if user is logged in and is an admin
require_once '../config/database.php';

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
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-body"><?php
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
</head>
<body class="admin-body">
    <div class="admin-container">
        <main class="admin-main">
            <div class="admin-content">
                <h1>Sales Report</h1>
                <p><a href="dashboard.php">&larr; Back to Dashboard</a></p> <!-- Optional -->
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

    <div class="admin-container">
        <main class="admin-main">
            <div class="admin-content">
                <h1>Sales Report</h1>
                <div class="admin-form" style="margin-top: 30px;">
                    <h2>Summary</h2>
                    <ul>
                        <li><strong>Total Orders:</strong> <?php echo $summary['total_orders']; ?></li>
                        <li><strong>Total Sales:</strong> $<?php echo number_format($summary['total_sales'], 2); ?></li>
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
                            <?php foreach ($statusCounts as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td><?php echo $row['count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>