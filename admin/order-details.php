<?php
require_once '../config/database.php';

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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Details #<?php echo htmlspecialchars($order['id']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        .order-summary { margin-bottom: 30px; }
        .order-items { border-collapse: collapse; width: 100%; }
        .order-items th, .order-items td { border: 1px solid #ccc; padding: 8px; }
        .order-items th { background: #f5f5f5; }
        .badge { padding: 3px 8px; border-radius: 3px; color: #fff; font-size: 12px; }
        .badge.paid { background: #27ae60; }
        .badge.unpaid { background: #e74c3c; }
        .badge.pending { background: #f39c12; }
    </style>
</head>
<body>
    <p><a href="orders.php">&larr; Back to Orders</a></p>
    <h1>Order Details</h1>
    <div class="order-summary">
        <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['id']); ?></p>
        <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?> (<?php echo htmlspecialchars($order['email']); ?>)</p>
        <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
        <p>
            <strong>Payment Status:</strong>
            <span class="badge <?php echo htmlspecialchars($order['payment_status']); ?>">
                <?php echo ucfirst($order['payment_status']); ?>
            </span>
        </p>
        <p><strong>Order Date:</strong> <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
        <p><strong>Total Amount:</strong> <?php echo number_format($order['total_amount'], 2); ?></p>
        <?php if (isset($order['downpayment_amount'])): ?>
            <p><strong>Downpayment:</strong> <?php echo number_format($order['downpayment_amount'], 2); ?></p>
            <p><strong>Remaining Balance:</strong> <?php echo number_format($order['total_amount'] - $order['downpayment_amount'], 2); ?></p>
        <?php endif; ?>
    </div>
    <h2>Items</h2>
    <table class="order-items">
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo number_format($item['unit_price'], 2); ?></td>
                <td><?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>