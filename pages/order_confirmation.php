<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
$pageTitle = "Order Confirmation";
include '../includes/header.php';

$order = null;
$order_items = [];

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    // Fetch order by id
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    // Fetch order items
    if ($order) {
        $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?");
        $stmt->execute([$order['id']]);
        $order_items = $stmt->fetchAll();
    }
}
?>

<main class="order-confirmation-page">
    <div class="container">
        <?php if ($order): ?>
            <!-- Success Header -->
            <div class="confirmation-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Order Confirmed!</h1>
                <p class="confirmation-message">Thank you for your order. We've received your request and will begin processing it shortly.</p>
            </div>

            <!-- Order Details Card -->
            <div class="order-confirmation-card">
                <div class="order-header">
                    <div class="order-info">
                        <h2>Order Details</h2>
                        <div class="order-meta">
                            <div class="meta-item">
                                <span class="label">Order Number:</span>
                                <span class="value">#<?php echo htmlspecialchars($order['order_number']); ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="label">Order Date:</span>
                                <span class="value"><?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="label">Status:</span>
                                <span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                </span>
                            </div>
                            <div class="meta-item">
                                <span class="label">Payment Status:</span>
                                <span class="payment-badge payment-<?php echo htmlspecialchars($order['payment_status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($order['payment_status'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="order-items-section">
                    <h3>Items Ordered</h3>
                    <div class="items-list">
                        <?php foreach ($order_items as $item): ?>
                            <div class="order-item">
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                    <div class="item-options">
                                        <?php if (!empty($item['selected_size'])): ?>
                                            <span class="option">Size: <?php echo htmlspecialchars($item['selected_size']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['selected_color'])): ?>
                                            <span class="option">Color: <?php echo htmlspecialchars($item['selected_color']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['selected_finish'])): ?>
                                            <span class="option">Finish: <?php echo htmlspecialchars($item['selected_finish']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($item['notes'])): ?>
                                        <div class="item-notes">
                                            <strong>Notes:</strong> <?php echo htmlspecialchars($item['notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="item-quantity">
                                    <span class="qty">Qty: <?php echo $item['quantity']; ?></span>
                                </div>
                                <div class="item-pricing">
                                    <div class="unit-price">₱<?php echo number_format($item['unit_price'], 2); ?> each</div>
                                    <div class="total-price">₱<?php echo number_format($item['total_price'], 2); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-details">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>₱<?php echo number_format($order['subtotal'], 2); ?></span>
                        </div>
                        <?php if ($order['shipping_cost'] > 0): ?>
                            <div class="summary-row">
                                <span>Shipping:</span>
                                <span>₱<?php echo number_format($order['shipping_cost'], 2); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($order['tax_amount'] > 0): ?>
                            <div class="summary-row">
                                <span>Tax:</span>
                                <span>₱<?php echo number_format($order['tax_amount'], 2); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="summary-row total-row">
                            <span>Total Amount:</span>
                            <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <div class="summary-row downpayment-row">
                            <span>Downpayment (50%):</span>
                            <span>₱<?php echo number_format($order['downpayment_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Shipping & Billing Info -->
                <div class="address-section">
                    <div class="address-column">
                        <h3>Shipping Address</h3>
                        <div class="address-details">
                            <p><?php echo htmlspecialchars($order['shipping_first_name'] . ' ' . $order['shipping_last_name']); ?></p>
                            <p><?php echo htmlspecialchars($order['shipping_address']); ?></p>
                            <p><?php echo htmlspecialchars($order['shipping_city'] . ', ' . $order['shipping_state'] . ' ' . $order['shipping_zip']); ?></p>
                        </div>
                    </div>
                    <div class="address-column">
                        <h3>Billing Address</h3>
                        <div class="address-details">
                            <p><?php echo htmlspecialchars($order['billing_first_name'] . ' ' . $order['billing_last_name']); ?></p>
                            <p><?php echo htmlspecialchars($order['billing_address']); ?></p>
                            <p><?php echo htmlspecialchars($order['billing_city'] . ', ' . $order['billing_state'] . ' ' . $order['billing_zip']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="payment-section">
                    <h3>Payment Information</h3>
                    <div class="payment-details">
                        <div class="payment-method">
                            <span class="label">Payment Method:</span>
                            <span class="value"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                        </div>
                        <?php if (!empty($order['bank_name'])): ?>
                            <div class="bank-details">
                                <p><strong>Bank:</strong> <?php echo htmlspecialchars($order['bank_name']); ?></p>
                                <p><strong>Reference Number:</strong> <?php echo htmlspecialchars($order['reference_number']); ?></p>
                                <p><strong>Account Holder:</strong> <?php echo htmlspecialchars($order['account_holder']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="next-steps">
                <h3>What's Next?</h3>
                <div class="steps-grid">
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h4>Payment Verification</h4>
                        <p>We'll verify your payment and update your order status within 24 hours.</p>
                    </div>
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h4>Production</h4>
                        <p>Once payment is confirmed, we'll begin production of your custom items.</p>
                    </div>
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h4>Delivery</h4>
                        <p>Your order will be carefully packaged and shipped to your address.</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="confirmation-actions">
                <a href="/isabelle-prints/pages/orders.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> View All Orders
                </a>
                <a href="/isabelle-prints/pages/products.php" class="btn btn-secondary">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
            </div>

        <?php else: ?>
            <!-- Order Not Found -->
            <div class="error-state">
                <div class="error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h1>Order Not Found</h1>
                <p>Sorry, we couldn't find the order you're looking for. Please check your order number or contact our support team.</p>
                <div class="error-actions">
                    <a href="/isabelle-prints/" class="btn btn-primary">Go to Homepage</a>
                    <a href="/isabelle-prints/pages/contact.php" class="btn btn-secondary">Contact Support</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
/* Order Confirmation Styles */
.order-confirmation-page {
    padding: 2rem 0;
    background-color: #f8f9fa;
    min-height: 80vh;
}

.confirmation-header {
    text-align: center;
    margin-bottom: 3rem;
    padding: 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.success-icon {
    font-size: 4rem;
    color: #28a745;
    margin-bottom: 1rem;
}

.confirmation-header h1 {
    color: #28a745;
    margin-bottom: 0.5rem;
    font-size: 2.5rem;
}

.confirmation-message {
    color: #666;
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
}

.order-confirmation-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.order-header {
    padding: 2rem;
    border-bottom: 1px solid #eee;
}

.order-header h2 {
    margin-bottom: 1rem;
    color: #333;
}

.order-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.meta-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.meta-item .label {
    font-weight: 600;
    color: #666;
    font-size: 0.9rem;
}

.meta-item .value {
    font-size: 1.1rem;
    color: #333;
}

.status-badge, .payment-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-processing { background: #d1ecf1; color: #0c5460; }
.status-completed { background: #d4edda; color: #155724; }
.status-cancelled { background: #f8d7da; color: #721c24; }

.payment-pending { background: #fff3cd; color: #856404; }
.payment-paid { background: #d4edda; color: #155724; }
.payment-unpaid { background: #f8d7da; color: #721c24; }

.order-items-section, .order-summary, .address-section, .payment-section {
    padding: 2rem;
    border-bottom: 1px solid #eee;
}

.order-items-section:last-child, .payment-section:last-child {
    border-bottom: none;
}

.order-items-section h3, .order-summary h3, .address-section h3, .payment-section h3 {
    margin-bottom: 1.5rem;
    color: #333;
    font-size: 1.3rem;
}

.order-item {
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 1.5rem;
    padding: 1.5rem;
    border: 1px solid #eee;
    border-radius: 8px;
    margin-bottom: 1rem;
    align-items: start;
}

.item-details h4 {
    margin-bottom: 0.5rem;
    color: #333;
}

.item-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.option {
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.85rem;
    color: #666;
}

.item-notes {
    margin-top: 0.5rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 4px;
    font-size: 0.9rem;
    color: #666;
}

.item-quantity {
    text-align: center;
    font-weight: 600;
    color: #666;
}

.item-pricing {
    text-align: right;
}

.unit-price {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.25rem;
}

.total-price {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
}

.summary-details {
    max-width: 400px;
    margin-left: auto;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #eee;
}

.total-row {
    font-weight: 600;
    font-size: 1.1rem;
    border-bottom: 2px solid #333;
    color: #333;
}

.downpayment-row {
    font-weight: 600;
    color: #f4c430;
    border-bottom: none;
}

.address-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.address-column h3 {
    margin-bottom: 1rem;
    color: #333;
}

.address-details p {
    margin-bottom: 0.5rem;
    color: #666;
    line-height: 1.5;
}

.payment-details .payment-method {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.payment-method .label {
    font-weight: 600;
    color: #666;
}

.bank-details p {
    margin-bottom: 0.5rem;
    color: #666;
}

.next-steps {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.next-steps h3 {
    text-align: center;
    margin-bottom: 2rem;
    color: #333;
    font-size: 1.5rem;
}

.steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.step {
    text-align: center;
    padding: 1.5rem;
    border: 1px solid #eee;
    border-radius: 8px;
}

.step-icon {
    font-size: 2.5rem;
    color: #f4c430;
    margin-bottom: 1rem;
}

.step h4 {
    margin-bottom: 0.5rem;
    color: #333;
}

.step p {
    color: #666;
    line-height: 1.5;
}

.confirmation-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.error-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.error-icon {
    font-size: 4rem;
    color: #dc3545;
    margin-bottom: 1rem;
}

.error-state h1 {
    color: #dc3545;
    margin-bottom: 1rem;
}

.error-state p {
    color: #666;
    margin-bottom: 2rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.error-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Print Styles */
@media print {
    .confirmation-actions, .next-steps {
        display: none;
    }
    
    .order-confirmation-page {
        background: white;
    }
    
    .order-confirmation-card, .confirmation-header {
        box-shadow: none;
        border: 1px solid #ddd;
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .order-item {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .item-quantity, .item-pricing {
        text-align: left;
    }
    
    .address-section {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .order-meta {
        grid-template-columns: 1fr;
    }
    
    .confirmation-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .confirmation-actions .btn {
        width: 100%;
        max-width: 300px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
