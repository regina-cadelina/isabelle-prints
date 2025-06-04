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
$totalOrders = $summary['total_orders'] ?? 0;
$totalSales = $summary['total_sales'] ?? 0;

// Get order count by status
$stmt = $pdo->query("SELECT status, COUNT(*) AS count FROM orders GROUP BY status");
$statusCounts = $stmt->fetchAll();

// Get total products
$stmt = $pdo->query("SELECT COUNT(*) AS total_products FROM products");
$totalProducts = $stmt->fetchColumn();

// Get pending orders
$stmt = $pdo->query("SELECT COUNT(*) AS pending_orders FROM orders WHERE status = 'pending'");
$pendingOrders = $stmt->fetchColumn();

// Get best seller product
$stmt = $pdo->query("
    SELECT p.name, SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 1
");
$bestSeller = $stmt->fetch();

// Get least seller product
$stmt = $pdo->query("
    SELECT p.name, SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY total_sold ASC
    LIMIT 1
");
$leastSeller = $stmt->fetch();

// Get orders per month (last 12 months)
$stmt = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS order_count, SUM(total_amount) AS month_sales
    FROM orders
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12
");
$ordersPerMonth = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get inventory report (total stock per product)
$stmt = $pdo->query("SELECT product_name, stock_quantity FROM products WHERE is_active = 1 ORDER BY product_name ASC");
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Sales Report";
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
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <h1>Sales Report</h1>
                    <button type="button" class="btn-primary" onclick="window.location.href='../tcpdf6/examples/print-reports.php'">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>

                <!-- Summary Cards Section -->
                <div class="admin-card" style="margin-bottom: 30px;">
                    <div class="admin-card-header">
                        <i class="fas fa-chart-line"></i> Business Summary
                    </div>
                    <div class="admin-card-body">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon products">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $totalProducts; ?></h3>
                                    <p>Total Products</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon orders">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $totalOrders; ?></h3>
                                    <p>Total Orders</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon pending">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $pendingOrders; ?></h3>
                                    <p>Pending Orders</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-peso-sign"></i>
                                </div>
                                <div class="stat-info">
                                    <h3>₱<?php echo number_format($totalSales, 2); ?></h3>
                                    <p>Total Sales</p>
                                </div>
                            </div>
                        </div>

                        <!-- Best and Least Seller Cards -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 25px;">
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #27ae60;">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                    <i class="fas fa-trophy" style="color: #f39c12; font-size: 1.2rem;"></i>
                                    <h4 style="margin: 0; color: #2c3e50;">Best Seller</h4>
                                </div>
                                <p style="margin: 0; font-weight: 600; color: #27ae60;">
                                    <?php
                                    if ($bestSeller && $bestSeller['total_sold'] > 0) {
                                        echo htmlspecialchars($bestSeller['name']) . " ({$bestSeller['total_sold']} sold)";
                                    } else {
                                        echo "No sales yet";
                                    }
                                    ?>
                                </p>
                            </div>

                            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #e74c3c;">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                    <i class="fas fa-chart-line-down" style="color: #e74c3c; font-size: 1.2rem;"></i>
                                    <h4 style="margin: 0; color: #2c3e50;">Least Seller</h4>
                                </div>
                                <p style="margin: 0; font-weight: 600; color: #e74c3c;">
                                    <?php
                                    if ($leastSeller && $leastSeller['total_sold'] > 0) {
                                        echo htmlspecialchars($leastSeller['name']) . " ({$leastSeller['total_sold']} sold)";
                                    } else {
                                        echo "No sales yet";
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders by Status Section -->
                <div class="admin-card" style="margin-bottom: 30px;">
                    <div class="admin-card-header">
                        <i class="fas fa-list-alt"></i> Orders by Status
                    </div>
                    <div class="admin-card-body">
                        <div class="table-container">
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
                                            <td colspan="2" style="text-align: center; color: #7f8c8d; font-style: italic;">No orders found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($statusCounts as $row): ?>
                                            <tr>
                                                <td>
                                                    <span class="status-badge <?php echo strtolower($row['status']); ?>">
                                                        <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                                    </span>
                                                </td>
                                                <td><strong><?php echo $row['count']; ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Monthly Report Section -->
                <div class="admin-card" style="margin-bottom: 30px;">
                    <div class="admin-card-header">
                        <i class="fas fa-calendar-alt"></i> Monthly Report (Last 12 Months)
                    </div>
                    <div class="admin-card-body">
                        <div class="table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Order Count</th>
                                        <th>Monthly Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($ordersPerMonth)): ?>
                                        <tr>
                                            <td colspan="3" style="text-align: center; color: #7f8c8d; font-style: italic;">No orders found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach (array_reverse($ordersPerMonth) as $row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars(date('F Y', strtotime($row['month'] . '-01'))); ?></td>
                                                <td><strong><?php echo $row['order_count']; ?></strong></td>
                                                <td class="price-cell">₱<?php echo number_format($row['month_sales'] ?? 0, 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Inventory Report Section -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <i class="fas fa-warehouse"></i> Inventory Report (Current Stocks)
                    </div>
                    <div class="admin-card-body">
                        <div class="table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Current Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($inventory)): ?>
                                        <tr>
                                            <td colspan="2" style="text-align: center; color: #7f8c8d; font-style: italic;">No products found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php 
                                        $totalStocks = 0;
                                        foreach ($inventory as $item): 
                                            $stock = (int)$item['stock_quantity'];
                                            $totalStocks += $stock;
                                            
                                            // Determine stock level class
                                            $stockClass = '';
                                            if ($stock <= 5) {
                                                $stockClass = 'stock-low';
                                            } elseif ($stock <= 20) {
                                                $stockClass = 'stock-medium';
                                            } else {
                                                $stockClass = 'stock-good';
                                            }
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                <td>
                                                    <span class="<?php echo $stockClass; ?>">
                                                        <?php echo $stock; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (!empty($inventory)): ?>
                            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #3498db;">
                                <strong style="color: #2c3e50; font-size: 1.1rem;">
                                    <i class="fas fa-boxes"></i> Total Stocks (All Products): <?php echo $totalStocks; ?>
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
