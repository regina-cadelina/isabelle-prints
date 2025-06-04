<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';
require_once '../includes/functions.php';

$cartItems = getCartItems($pdo);
$cartTotals = calculateCartTotal($pdo);

function generateOrderNumber($pdo) {
    $prefix = 'ORD-' . date('Ymd') . '-';
    do {
        $randomPart = strtoupper(bin2hex(random_bytes(4)));
        $orderNumber = $prefix . $randomPart;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_number = ?");
        $stmt->execute([$orderNumber]);
        $exists = $stmt->fetchColumn() > 0;
    } while ($exists);

    return $orderNumber;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $billingAddress = trim($_POST['billing_address'] ?? '');
    $referenceNumber = trim($_POST['reference_number'] ?? '');
    $bankOwnerName = trim($_POST['bank_owner_name'] ?? '');
    $bankName = trim($_POST['bank_name'] ?? '');

    if (empty($shippingAddress)) {
        $error = 'Shipping address is required.';
    } elseif (empty($billingAddress)) {
        $error = 'Billing address is required.';
    } elseif (empty($referenceNumber)) {
        $error = 'Reference number is required.';
    } elseif (empty($bankOwnerName)) {
        $error = 'Bank owner name is required.';
    } elseif (empty($bankName)) {
        $error = 'Bank name is required.';
    }

    $paymentProofFile = null;

    if (!$error) {
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/payment-proofs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $fileName = 'payment_' . time() . '_' . uniqid() . '.' . $fileExtension;
                $uploadPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $uploadPath)) {
                    $paymentProofFile = $fileName;
                } else {
                    $error = 'Failed to upload payment proof file.';
                }
            } else {
                $error = 'Invalid file type. Only JPG, PNG, and PDF allowed.';
            }
        } else {
            $error = 'Please upload your payment proof.';
        }
    }

    if (!$error) {
        try {
            $pdo->beginTransaction();

            $orderNumber = generateOrderNumber($pdo);
            $subtotal = $cartTotals['subtotal'] ?? $cartTotals['total']; // Default to total if subtotal not calculated
            $shippingCost = $cartTotals['shipping'] ?? 0;
            $totalAmount = $cartTotals['total'];
            $downpaymentAmount = $totalAmount * 0.5;

            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    user_id, order_number, status,
                    subtotal, shipping_cost, total_amount,
                    downpayment_amount, payment_proof_file,
                    reference_number, bank_owner_name, bank_name,
                    shipping_address, billing_address, created_at
                ) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $_SESSION['user_id'],
                $orderNumber,
                $subtotal,
                $shippingCost,
                $totalAmount,
                $downpaymentAmount,
                $paymentProofFile,
                $referenceNumber,
                $bankOwnerName,
                $bankName,
                $shippingAddress,
                $billingAddress
            ]);

            $orderId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("
                INSERT INTO order_items (
                    order_id, product_id, quantity,
                    unit_price, total_price, selected_options,
                    customization_notes, file_upload
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($cartItems as $item) {
                $unitPrice = $item['product']['base_price'];
                $quantity = $item['quantity'];
                $totalPrice = $unitPrice * $quantity;
                $stmt->execute([
                    $orderId,
                    $item['product']['id'],
                    $quantity,
                    $unitPrice,
                    $totalPrice,
                    $item['selected_options'] ?? null,
                    $item['customization_notes'] ?? null,
                    $item['file_upload'] ?? null
                ]);
            }

            if (function_exists('clearCart')) {
                clearCart($pdo, $_SESSION['user_id']);
            } else {
                unset($_SESSION['cart']);
            }

            $pdo->commit();
            header('Location: order_confirmation.php?order_id=' . $orderId);
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'An error occurred while processing your order.';
            error_log("Checkout Error: " . $e->getMessage());
            echo "PDO Error: " . $e->getMessage();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Gadget Zone</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('../includes/header.php'); ?>

<div class="container mt-5">
    <h1>Checkout</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <p>Your cart is empty. <a href="products.php">Continue shopping</a>.</p>
    <?php else: ?>
        <div class="row">
            <div class="col-md-4 order-md-2 mb-4">
                <h4 class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Your cart</span>
                    <span class="badge badge-secondary badge-pill"><?= count($cartItems) ?></span>
                </h4>
                <ul class="list-group mb-3">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Total (PHP)</span>
                        <strong>₱<?= number_format($cartTotals['total'], 2) ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Downpayment (50%)</span>
                        <strong>₱<?= number_format($cartTotals['total'] * 0.5, 2) ?></strong>
                    </li>
                </ul>
            </div>
            <div class="col-md-8 order-md-1">
                <h4 class="mb-3">Billing Information</h4>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="shipping_address">Shipping Address</label>
                        <input type="text" class="form-control" id="shipping_address" name="shipping_address" required value="<?= htmlspecialchars($_POST['shipping_address'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="billing_address">Billing Address</label>
                        <input type="text" class="form-control" id="billing_address" name="billing_address" required value="<?= htmlspecialchars($_POST['billing_address'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="phone">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone"
                               required pattern="^\d{11}$" maxlength="11"
                               title="Phone number must be exactly 11 digits"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>

                    <h4 class="mb-3">Payment Information</h4>

                    <div class="mb-3">
                        <label for="payment_proof">Payment Proof (JPG, PNG, PDF)</label>
                        <input type="file" class="form-control" id="payment_proof" name="payment_proof" accept=".jpg, .jpeg, .png, .pdf" required>
                    </div>
                    <div class="mb-3">
                        <label for="reference_number">Reference Number</label>
                        <input type="text" class="form-control" id="reference_number" name="reference_number"
                               required pattern=".{10,32}" minlength="10" maxlength="32"
                               title="Reference number must be between 10 and 32 characters"
                               value="<?= htmlspecialchars($_POST['reference_number'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="bank_owner_name">Bank Owner Name</label>
                        <input type="text" class="form-control" id="bank_owner_name" name="bank_owner_name" required value="<?= htmlspecialchars($_POST['bank_owner_name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="bank_name">Bank Name</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name" required value="<?= htmlspecialchars($_POST['bank_name'] ?? '') ?>">
                    </div>

                    <hr class="mb-4">
                    <button class="btn btn-primary btn-lg btn-block" type="submit">Place Order</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
