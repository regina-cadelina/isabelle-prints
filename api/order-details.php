<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    http_response_code(400);
    echo "Invalid order ID";
    exit;
}

try {
    // Get order details
    $stmt = $pdo->prepare("
        SELECT o.* FROM orders o 
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        http_response_code(404);
        echo "Order not found";
        exit;
    }
    
    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.base_price 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll();
    
    ?>
    <div class="order-details-modal">
        <h2>Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
        
        <div class="order-info-grid">
            <div class="info-section">
                <h3>Order Information</h3>
                <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                <p><strong>Status:</strong> <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></p>
                <p><strong>Payment Method:</strong> Bank Transfer</p>
                <p><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
            </div>
            
            <div class="info-section">
                <h3>Shipping Address</h3>
                <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            </div>
            
            <div class="info-section">
                <h3>Billing Address</h3>
                <p><?php echo nl2br(htmlspecialchars($order['billing_address'])); ?></p>
            </div>
            
            <div class="info-section">
                <h3>Payment Information</h3>
                <p><strong>Bank:</strong> <?php echo htmlspecialchars($order['bank_name']); ?></p>
                <p><strong>Reference Number:</strong> <?php echo htmlspecialchars($order['reference_number']); ?></p>
                <p><strong>Account Holder:</strong> <?php echo htmlspecialchars($order['bank_owner_name']); ?></p>
            </div>
        </div>
        
        <div class="order-items-section">
            <h3>Order Items</h3>
            <div class="items-list">
                <?php foreach ($orderItems as $item): ?>
                    <div class="order-item-detail">
                        <div class="item-info">
                            <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                            <p>Quantity: <?php echo $item['quantity']; ?></p>
                            <p>Unit Price: <?php echo formatPrice($item['unit_price']); ?></p>
                            <?php if ($item['selected_options']): ?>
                                <div class="item-options">
                                    <?php 
                                    $options = json_decode($item['selected_options'], true);
                                    if ($options) {
                                        foreach ($options as $key => $value) {
                                            echo '<span>' . ucfirst($key) . ': ' . htmlspecialchars($value) . '</span> ';
                                        }
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($item['customization_notes']): ?>
                                <p><strong>Notes:</strong> <?php echo htmlspecialchars($item['customization_notes']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="item-total">
                            <?php echo formatPrice($item['total_price']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="order-totals">
            <div class="totals-grid">
                <div class="total-line">
                    <span>Subtotal:</span>
                    <span><?php echo formatPrice($order['subtotal']); ?></span>
                </div>
                <div class="total-line">
                    <span>Shipping:</span>
                    <span><?php echo formatPrice($order['shipping_cost']); ?></span>
                </div>
                <div class="total-line">
                    <span>Tax:</span>
                    <span><?php echo formatPrice($order['tax_amount']); ?></span>
                </div>
                <div class="total-line grand-total">
                    <span>Total:</span>
                    <span><?php echo formatPrice($order['total_amount']); ?></span>
                </div>
                <div class="total-line downpayment">
                    <span>Downpayment Paid:</span>
                    <span><?php echo formatPrice($order['downpayment_amount']); ?></span>
                </div>
                <div class="total-line remaining">
                    <span>Remaining Balance:</span>
                    <span><?php echo formatPrice($order['total_amount'] - $order['downpayment_amount']); ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php
    
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database error: " . $e->getMessage();
}
?>