<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

$current_user = getCurrentUser();

// Dashboard statistics
$stats = [];

// Total products
$stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
$stats['total_products'] = $stmt->fetch()['count'];

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
$stats['total_orders'] = $stmt->fetch()['count'];

// Pending orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $stmt->fetch()['count'];

// Total customers
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'customer'");
$stats['total_customers'] = $stmt->fetch()['count'];

// Recent orders (latest 5)
$stmt = $pdo->query("
    SELECT o.order_number, o.status, o.total_amount, o.created_at, u.first_name, u.last_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
");
$recent_orders = $stmt->fetchAll();

// Low stock products (stock < 10, show all)
$stmt = $pdo->query("
    SELECT 
        p.id as product_id,
        p.product_name,
        p.sku,
        p.base_price,
        p.stock_quantity,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
      AND p.stock_quantity < 10
    ORDER BY p.stock_quantity ASC
");
$low_stock = $stmt->fetchAll();

$page_title = "Admin Dashboard";
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
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Manage Categories</a></li>
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
                <h1>Dashboard Overview</h1>
                
                <!-- Statistics Cards -->
                <div class="admin-card" style="margin-bottom: 30px;">
                    <div class="admin-card-header">
                        <i class="fas fa-chart-line"></i> Quick Statistics
                    </div>
                    <div class="admin-card-body">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon products">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $stats['total_products']; ?></h3>
                                    <p>Products Offered</p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon orders">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $stats['total_orders']; ?></h3>
                                    <p>Total Orders</p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon pending">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $stats['pending_orders']; ?></h3>
                                    <p>Pending Orders</p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon customers">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $stats['total_customers']; ?></h3>
                                    <p>Total Customers</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders and Low Stock -->
                <div class="dashboard-grid">
                    <!-- Recent Orders -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <i class="fas fa-shopping-cart"></i> Recent Orders
                        </div>
                        <div class="admin-card-body">
                            <div class="table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recent_orders)): ?>
                                            <tr>
                                                <td colspan="5" style="text-align: center; color: #7f8c8d; font-style: italic;">No recent orders</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td>#<?php echo htmlspecialchars($order['order_number']); ?></td>
                                                <td><?php echo htmlspecialchars(trim($order['first_name'] . ' ' . $order['last_name'])); ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo htmlspecialchars($order['status']); ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="price-cell"><?php echo formatPrice($order['total_amount']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div style="margin-top: 15px; text-align: center;">
                                <a href="orders.php" class="view-all-btn">View All Orders</a>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <i class="fas fa-exclamation-triangle"></i> Low Stock Alert
                        </div>
                        <div class="admin-card-body">
                            <div class="table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Category</th>
                                            <th>Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($low_stock)): ?>
                                            <tr>
                                                <td colspan="3" style="text-align: center; color: #7f8c8d; font-style: italic;">All products have sufficient stock</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($low_stock as $product): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                                    <?php if (!empty($product['sku'])): ?>
                                                        <br><small style="color: #7f8c8d;">SKU: <?php echo htmlspecialchars($product['sku']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                <td>
                                                    <span class="stock-low"><?php echo (int)$product['stock_quantity']; ?></span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div style="margin-top: 15px; text-align: center;">
                                <a href="products.php" class="view-all-btn">Manage Products</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
