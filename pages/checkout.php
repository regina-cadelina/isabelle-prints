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
    $phone = trim($_POST['phone'] ?? '');

    // Validate required fields
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
    } elseif (empty($phone)) {
        $error = 'Phone number is required.';
    }

    $paymentProofFile = null;

    if (!$error) {
        if (isset($_FILES['payment_proof_file']) && $_FILES['payment_proof_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/payment-proofs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['payment_proof_file']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $fileName = 'payment_' . time() . '_' . uniqid() . '.' . $fileExtension;
                $uploadPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['payment_proof_file']['tmp_name'], $uploadPath)) {
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
            $subtotal = $cartTotals['subtotal'] ?? $cartTotals['total'];
            $shippingCost = $cartTotals['shipping'] ?? 0;
            $totalAmount = $cartTotals['total'];
            $downpaymentAmount = $totalAmount * 0.5;

            // Collect all customization notes and custom images from cart items
            $orderCustomizationNotes = [];
            $orderCustomImage = null;
            
            foreach ($cartItems as $item) {
                if (!empty($item['customization_notes'])) {
                    $orderCustomizationNotes[] = $item['product']['name'] . ': ' . $item['customization_notes'];
                }
                if (!empty($item['custom_image']) && !$orderCustomImage) {
                    $orderCustomImage = $item['custom_image'];
                }
            }
            
            $combinedNotes = implode("\n\n", $orderCustomizationNotes);

            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    user_id, order_number, status,
                    subtotal, shipping_cost, total_amount,
                    downpayment_amount, payment_proof_file,
                    reference_number, bank_owner_name, bank_name,
                    shipping_address, billing_address, phone,
                    customization_notes, custom_image, created_at
                ) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
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
                $billingAddress,
                $phone,
                $combinedNotes,
                $orderCustomImage
            ]);

            $orderId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("
                INSERT INTO order_items (
                    order_id, product_id, quantity,
                    unit_price, total_price, selected_options,
                    customization_notes, custom_image,
                    selected_size, selected_color, selected_finish, selected_material
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($cartItems as $item) {
                $unitPrice = $item['product']['base_price'];
                $quantity = $item['quantity'];
                $totalPrice = $unitPrice * $quantity;
                
                $options = $item['options'] ?? [];
                
                $stmt->execute([
                    $orderId,
                    $item['product']['id'],
                    $quantity,
                    $unitPrice,
                    $totalPrice,
                    json_encode($options),
                    $item['customization_notes'] ?? null,
                    $item['custom_image'] ?? null,
                    $options['size'] ?? null,
                    $options['color'] ?? null,
                    $options['finish'] ?? null,
                    $options['material'] ?? null
                ]);

                // Subtract stock
                $updateStockStmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $updateStockStmt->execute([$quantity, $item['product']['id']]);
            }

            // Clear cart function or fallback
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
            $error = 'An error occurred while processing your order. Please try again.';
            // Log the error for debugging:
            error_log('Checkout Error: ' . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Checkout - Isabelle Concept & Prints</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../assets/css/style.css" rel="stylesheet" />
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
                    <?php foreach ($cartItems as $item): ?>
                        <li class="list-group-item d-flex justify-content-between lh-condensed">
                            <div>
                                <h6 class="my-0"><?= htmlspecialchars($item['product']['name']) ?></h6>
                                <small class="text-muted">Qty: <?= $item['quantity'] ?></small>
                                <?php if (!empty($item['customization_notes'])): ?>
                                    <br><small class="text-info">Custom: <?= htmlspecialchars(substr($item['customization_notes'], 0, 50)) ?>...</small>
                                <?php endif; ?>
                                <?php if (!empty($item['custom_image'])): ?>
                                    <br><small class="text-success">Custom image uploaded</small>
                                <?php endif; ?>
                            </div>
                            <span class="text-muted">₱<?= number_format($item['product']['base_price'] * $item['quantity'], 2) ?></span>
                        </li>
                    <?php endforeach; ?>
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
                <form method="POST" enctype="multipart/form-data" novalidate>
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
                        <input type="text" class="form-control" id="phone" name="phone" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>

                    <h4 class="mb-3">Payment Information</h4>

                    <div class="mb-3">
<<<<<<< HEAD
                        <label for="payment_proof_file">Payment Proof (JPG, PNG, PDF)</label>
                        <input type="file" class="form-control" id="payment_proof_file" name="payment_proof_file" accept=".jpg, .jpeg, .png, .pdf" required>
=======
                        <label for="payment_proof">Payment Proof (JPG, PNG, PDF)</label>
                        <input type="file" class="form-control" id="payment_proof" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required>
>>>>>>> 903e1ee3e69c4305fa48629f1f32faf6e63c973e
                    </div>
                    <div class="mb-3">
                        <label for="reference_number">Reference Number</label>
                        <input type="text" class="form-control" id="reference_number" name="reference_number" required value="<?= htmlspecialchars($_POST['reference_number'] ?? '') ?>">
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>