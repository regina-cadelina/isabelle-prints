<?php
$pageTitle = "My Orders";
include '../includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = '/isabelle-prints/pages/orders.php';
    header('Location: /isabelle-prints/pages/login.php');
    exit;
}

$success = '';
if (isset($_GET['success']) && $_GET['success'] === 'order_placed') {
    $success = 'Your order has been placed successfully! We will review your payment and update the status shortly.';
}

// Get user's orders
$stmt = $pdo->prepare("
    SELECT o.*, 
           COUNT(oi.id) as item_count,
           GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<main class="orders-page">
    <div class="container">
        <div class="page-header">
            <h1>My Orders</h1>
            <nav class="breadcrumb">
                <a href="/isabelle-prints/">Home</a> / My Orders
            </nav>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <div class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>No Orders Yet</h3>
                    <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
                    <a href="/isabelle-prints/pages/products.php" class="btn btn-primary">Start Shopping</a>
                </div>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <h3>Order #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                                <p class="order-date">Placed on <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                            </div>
                            <div class="order-status">
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="order-details">
                            <div class="order-items">
                                <p><strong>Items:</strong> <?php echo htmlspecialchars($order['product_names']); ?></p>
                                <p><strong>Total Items:</strong> <?php echo $order['item_count']; ?></p>
                            </div>
                            
                            <div class="order-amounts">
                                <div class="amount-row">
                                    <span>Total Amount:</span>
                                    <span><?php echo formatPrice($order['total_amount']); ?></span>
                                </div>
                                <div class="amount-row">
                                    <span>Downpayment Paid:</span>
                                    <span><?php echo formatPrice($order['downpayment_amount']); ?></span>
                                </div>
                                <div class="amount-row remaining">
                                    <span>Remaining Balance:</span>
                                    <span><?php echo formatPrice($order['total_amount'] - $order['downpayment_amount']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-actions">
                            <button class="btn btn-secondary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                View Details
                            </button>
                            <?php if ($order['status'] === 'pending'): ?>
                                <span class="payment-status">Payment under review</span>
                            <?php elseif ($order['status'] === 'processing'): ?>
                                <span class="payment-status">Order being processed</span>
                            <?php elseif ($order['status'] === 'shipped'): ?>
                                <span class="payment-status">Order shipped</span>
                            <?php elseif ($order['status'] === 'delivered'): ?>
                                <span class="payment-status">Order delivered</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Order Details Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="orderModalContent">
            <!-- Content will be loaded via AJAX -->
        </div>
    </div>
</div>

<script>
function viewOrderDetails(orderId) {
    const modal = document.getElementById('orderModal');
    const modalContent = document.getElementById('orderModalContent');
    
    modalContent.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    modal.style.display = 'block';
    
    fetch(`/isabelle-prints/api/order-details.php?id=${orderId}`)
        .then(response => response.text())
        .then(html => {
            modalContent.innerHTML = html;
        })
        .catch(error => {
            modalContent.innerHTML = '<div class="error-message">Error loading order details</div>';
        });
}

// Close modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('orderModal');
    const closeBtn = document.querySelector('.close');
    
    if (closeBtn) {
        closeBtn.onclick = function() {
            modal.style.display = 'none';
        }
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>