<?php
ob_start(); // Start buffering
session_start();

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle form actions BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update_cart') {
        $cartKey = $_POST['cart_key'] ?? null;
        $quantity = $_POST['quantity'] ?? null;

        if ($cartKey && is_numeric($quantity)) {
            $quantity = (int)$quantity;
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$cartKey]);
            } else {
                $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
            }
        }

        header('Location: cart.php');
        exit;
    }

    if ($action === 'remove_item') {
        $cartKey = $_POST['cart_key'] ?? null;
        if ($cartKey && isset($_SESSION['cart'][$cartKey])) {
            unset($_SESSION['cart'][$cartKey]);
        }

        header('Location: cart.php');
        exit;
    }

    if ($action === 'clear_cart') {
        unset($_SESSION['cart']);
        header('Location: cart.php');
        exit;
    }
}

$pageTitle = "Shopping Cart";
include '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$cartItems = getCartItems($pdo);
$cartTotals = calculateCartTotal($pdo);

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update_cart') {
        $cartKey = $_POST['cart_key'] ?? null;
        $quantity = $_POST['quantity'] ?? null;

        if ($cartKey && is_numeric($quantity)) {
            $quantity = (int)$quantity;
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$cartKey]);
            } else {
                $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
            }
        }

        header('Location: cart.php');
        exit;
    }

    if ($action === 'remove_item') {
        $cartKey = $_POST['cart_key'] ?? null;
        if ($cartKey && isset($_SESSION['cart'][$cartKey])) {
            unset($_SESSION['cart'][$cartKey]);
        }

        header('Location: cart.php');
        exit;
    }

    if ($action === 'clear_cart') {
        unset($_SESSION['cart']);
        header('Location: cart.php');
        exit;
    }
}

$cartItems = getCartItems($pdo);
$cartTotals = calculateCartTotal($pdo);
?>

<main class="cart-page">
    <div class="container">
        <div class="page-header">
            <h1>Shopping Cart</h1>
            <?php if (!empty($cartItems)): ?>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="clear_cart">
                    <button type="submit" class="btn btn-link">Clear Cart</button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <a href="/isabelle-prints/pages/products.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <i class="fas fa-image"></i>
                            </div>
                            
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['product']['name']); ?></h3>
                                <?php if (!empty($item['options'])): ?>
                                    <div class="item-options">
                                        <?php foreach ($item['options'] as $key => $value): ?>
                                            <span><?php echo ucfirst($key); ?>: <?php echo htmlspecialchars($value); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="item-price"><?php echo formatPrice($item['product']['base_price']); ?></div>
                            </div>
                            
                            <div class="item-quantity">
                                <form method="POST" class="quantity-form">
                                    <input type="hidden" name="action" value="update_cart">
                                    <input type="hidden" name="cart_key" value="<?php echo $item['cart_key']; ?>">
                                    <button type="button" class="qty-btn" onclick="changeQuantity(this, -1)">-</button>
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="qty-input">
                                    <button type="button" class="qty-btn" onclick="changeQuantity(this, 1)">+</button>
                                </form>
                            </div>
                            
                            <div class="item-total">
                                <?php echo formatPrice($item['product']['base_price'] * $item['quantity']); ?>
                            </div>
                            
                            <div class="item-remove">
                                <form method="POST">
                                    <input type="hidden" name="action" value="remove_item">
                                    <input type="hidden" name="cart_key" value="<?php echo $item['cart_key']; ?>">
                                    <button type="submit" class="remove-btn">&times;</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-line">
                        <span>Subtotal:</span>
                        <span><?php echo formatPrice($cartTotals['subtotal']); ?></span>
                    </div>
                    <div class="summary-line">
                        <span>Shipping:</span>
                        <span><?php echo formatPrice($cartTotals['shipping']); ?></span>
                    </div>
                    <div class="summary-line">
                        <span>Tax:</span>
                        <span><?php echo formatPrice($cartTotals['tax']); ?></span>
                    </div>
                    <div class="summary-line total">
                        <span>Total:</span>
                        <span><?php echo formatPrice($cartTotals['total']); ?></span>
                    </div>
                    
                    <div class="downpayment-info">
                        <div class="summary-line downpayment">
                            <span>Required Downpayment (50%):</span>
                            <span><?php echo formatPrice($cartTotals['total'] * 0.5); ?></span>
                        </div>
                    </div>
                    
                    <a href="/isabelle-prints/pages/checkout.php" class="btn btn-primary btn-full">Proceed to Checkout</a>
                    <a href="/isabelle-prints/pages/products.php" class="btn btn-secondary btn-full">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>