<?php
require_once '../config/database.php';

$order = null;
$order_items = [];

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);  // safer to cast as int
    // Fetch order by id
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    // DEBUG: Check if order was found
    if (!$order) {
        echo "<pre>DEBUG: Order number used: " . htmlspecialchars($order_number) . "</pre>";
    }

    // Fetch order items
    if ($order) {
        $stmt = $pdo->prepare("SELECT oi.*, p.product_name 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?");
        $stmt->execute([$order['id']]);
        $order_items = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .confirmation { max-width: 600px; margin: auto; padding: 30px; border: 1px solid #ddd; border-radius: 8px; }
        .confirmation h1 { color: #27ae60; }
        .order-summary { margin-top: 20px; }
        .order-summary th, .order-summary td { padding: 8px 12px; }
        .order-summary th { background: #f5f5f5; }
    </style>
</head>
<body>
<div class="confirmation">
    <?php if ($order): ?>
        <h1>Thank you for your order!</h1>
        <p>Your order has been placed successfully.</p>
        <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
        <div class="order-summary">
            <h2>Order Summary</h2>
            <table border="1" width="100%" class="order-summary">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td>₱<?php echo number_format($item['total_price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p style="text-align:right; margin-top:10px;">
                <strong>Subtotal:</strong> ₱<?php echo number_format($order['subtotal'], 2); ?><br>
                <strong>Shipping:</strong> ₱<?php echo number_format($order['shipping_cost'], 2); ?><br>
                <strong>Total:</strong> ₱<?php echo number_format($order['total_amount'], 2); ?>
            </p>
        </div>
        <p style="margin-top:30px;">A confirmation email has been sent to you. If you have any questions, please <a href="../contact.php">contact us</a>.</p>
    <?php else: ?>
        <h1>Order Not Found</h1>
        <p>Sorry, we couldn't find your order. Please check your order number or contact support.</p>
    <?php endif; ?>
</div>
</body>

</html>