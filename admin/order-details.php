<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

$current_user = getCurrentUser();

// Get order ID from query string
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch order details
$stmt = $pdo->prepare("
    SELECT o.*, u.first_name, u.last_name, u.email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<h2>Order not found.</h2>";
    exit;
}

// Fetch order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name AS product_name
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

$page_title = "Order Details #" . $order['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">
                <h2><i class="fas fa-cog"></i> Admin Panel</h2>
            </div>
            <div class="admin-user">
                <span>Welcome, <?php echo htmlspecialchars($current_user['first_name']); ?></span>
                <div class="logout-container">
                <a href="../pages/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <nav class="admin-menu">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Manage Products</a></li>
                    <li><a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> Manage Orders</a></li>
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
                <a href="orders.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
                
                <h1><i class="fas fa-receipt"></i> Order Details #<?php echo htmlspecialchars($order['id']); ?></h1>
                
                <!-- Order Details Grid -->
                <div class="order-details-grid">
                    <!-- Order Summary -->
                    <div class="order-summary-card">
                        <div class="order-summary-header">
                            <i class="fas fa-info-circle"></i> Order Information
                        </div>
                        <div class="order-summary-body">
                            <div class="order-info-grid">
                                <div class="order-info-item">
                                    <div class="order-info-label">Order Number</div>
                                    <div class="order-info-value">#<?php echo htmlspecialchars($order['id']); ?></div>
                                </div>
                                
                                <div class="order-info-item">
                                    <div class="order-info-label">Order Date</div>
                                    <div class="order-info-value"><?php echo date('M j, Y H:i', strtotime($order['created_at'])); ?></div>
                                </div>
                                
                                <div class="order-info-item">
                                    <div class="order-info-label">Customer</div>
                                    <div class="order-info-value"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></div>
                                </div>
                                
                                <div class="order-info-item">
                                    <div class="order-info-label">Email</div>
                                    <div class="order-info-value"><?php echo htmlspecialchars($order['email']); ?></div>
                                </div>
                                
                                <div class="order-info-item">
                                    <div class="order-info-label">Status</div>
                                    <div class="order-info-value">
                                        <span class="status-badge <?php echo htmlspecialchars($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="order-info-item">
                                    <div class="order-info-label">Payment Status</div>
                                    <div class="order-info-value">
                                        <span class="badge <?php echo htmlspecialchars($order['payment_status']); ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="order-info-item">
                                    <div class="order-info-label">Total Amount</div>
                                    <div class="order-info-value price-cell">₱<?php echo number_format($order['total_amount'], 2); ?></div>
                                </div>
                                
                                <?php if (isset($order['downpayment_amount']) && $order['downpayment_amount'] > 0): ?>
                                <div class="order-info-item">
                                    <div class="order-info-label">Downpayment</div>
                                    <div class="order-info-value">₱<?php echo number_format($order['downpayment_amount'], 2); ?></div>
                                </div>
                                
                                <div class="order-info-item">
                                    <div class="order-info-label">Remaining Balance</div>
                                    <div class="order-info-value price-cell">₱<?php echo number_format($order['total_amount'] - $order['downpayment_amount'], 2); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Proof of Payment -->
                    <div class="payment-proof-section">
                        <div class="payment-proof-header">
                            <i class="fas fa-receipt"></i> Proof of Payment
                        </div>
                        <div class="payment-proof-body">
                            <?php if (!empty($order['proof_of_payment'])): ?>
                                <?php $proofUrl = '../uploads/proof/' . htmlspecialchars($order['proof_of_payment']); ?>
                                <a href="<?php echo $proofUrl; ?>" target="_blank" class="proof-link">
                                    <i class="fas fa-external-link-alt"></i> View Full Size
                                </a>
                                <br>
                                <img src="<?php echo $proofUrl; ?>" alt="Proof of Payment" class="proof-image">
                            <?php else: ?>
                                <div class="no-proof-message">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    No proof of payment uploaded yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

<<<<<<< HEAD
                <!-- Order Items -->
                <div class="order-items-section">
                    <div class="order-items-header">
                        <i class="fas fa-shopping-bag"></i> Order Items
                    </div>
                    <div class="table-container">
                        <table class="order-items-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-box"></i> Product</th>
                                    <th><i class="fas fa-sort-numeric-up"></i> Quantity</th>
                                    <th><i class="fas fa-tag"></i> Unit Price</th>
                                    <th><i class="fas fa-calculator"></i> Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td>
                                        <span class="quantity-badge"><?php echo $item['quantity']; ?></span>
                                    </td>
                                    <td class="price-cell">₱<?php echo number_format($item['unit_price'], 2); ?></td>
                                    <td class="price-cell">₱<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
=======
        <?php if (!empty($order['payment_proof_file'])): ?>
            <p><strong>Proof of Payment:</strong></p>
            <p>
                <img src="../uploads/payment-proofs/<?php echo htmlspecialchars($order['payment_proof_file']); ?>"
                     alt="Proof of Payment"
                     style="max-width: 400px; border: 1px solid #ccc; padding: 5px;">
            </p>
        <?php else: ?>
            <p><strong>Proof of Payment:</strong> Not uploaded.</p>
        <?php endif; ?>
>>>>>>> 7e4279db8f049c79ffcb3e44e6ac05beeff2a859
    </div>
</body>
</html>