<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php'; // Use your actual DB config file
require_once '../includes/functions.php'; // Use your actual functions file

// Fetch cart items
$cartItems = getCartItems($pdo);

// Calculate cart totals (make sure this function exists and works)
$cartTotals = calculateCartTotal($pdo); // Not calculateCartTotals

// Handle form submission
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $shippingAddress = isset($_POST['shipping_address']) ? trim($_POST['shipping_address']) : '';
    $billingAddress = isset($_POST['billing_address']) ? trim($_POST['billing_address']) : '';
    $referenceNumber = isset($_POST['reference_number']) ? trim($_POST['reference_number']) : '';
    $bankOwnerName = isset($_POST['bank_owner_name']) ? trim($_POST['bank_owner_name']) : '';
    $bankName = isset($_POST['bank_name']) ? trim($_POST['bank_name']) : '';

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

    if (!$error) {
        // Handle file upload
        $paymentProofFile = null;
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/payment-proofs/';

            // Create directory if it doesn't exist
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
                $error = 'Invalid file type. Please upload JPG, PNG, or PDF files only.';
            }
        } else {
            $error = 'Please upload your payment proof.';
        }

        if (!$error) {
            try {
                // Insert order into database
                $stmt = $pdo->prepare("
                    INSERT INTO orders (user_id, total_amount, downpayment_amount, payment_status, 
                                       shipping_address, billing_address, payment_proof_file, 
                                       reference_number, bank_owner_name, bank_name, created_at) 
                    VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, NOW())
                ");

                $stmt->execute([
                    $_SESSION['user_id'],
                    $cartTotals['total'],
                    $cartTotals['total'] * 0.5,
                    $shippingAddress,
                    $billingAddress,
                    $paymentProofFile,
                    $referenceNumber,
                    $bankOwnerName,
                    $bankName
                ]);

                $orderId = $pdo->lastInsertId();

                // Insert order items
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");

                foreach ($cartItems as $item) {
                    $stmt->execute([
                        $orderId,
                        $item['product']['id'],
                        $item['quantity'],
                        $item['product']['base_price']
                    ]);
                }

                // Clear cart (if you have a clearCart function, otherwise use unset)
                if (function_exists('clearCart')) {
                    clearCart($pdo, $_SESSION['user_id']);
                } else {
                    unset($_SESSION['cart']);
                }

                // Redirect to order confirmation page
                header('Location: order_confirmation.php?order_id=' . $orderId);
                exit();
            } catch (PDOException $e) {
                $error = 'An error occurred while processing your order. Please try again.';
                error_log("PDO Error: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Gadget Zone</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container mt-5">
        <h1>Checkout</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
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
                            <input type="text" class="form-control" id="shipping_address" name="shipping_address" value="<?= isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : '' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="billing_address">Billing Address</label>
                            <input type="text" class="form-control" id="billing_address" name="billing_address" value="<?= isset($_POST['billing_address']) ? htmlspecialchars($_POST['billing_address']) : '' ?>" required>
                        </div>

                        <hr class="mb-4">

                        <h4 class="mb-3">Payment Information</h4>

                        <div class="mb-3">
                            <label for="payment_proof">Payment Proof (JPG, PNG, or PDF)</label>
                            <input type="file" class="form-control" id="payment_proof" name="payment_proof" accept=".jpg, .jpeg, .png, .pdf" required>
                        </div>

                        <div class="mb-3">
                            <label for="reference_number">Reference Number</label>
                            <input type="text" class="form-control" id="reference_number" name="reference_number" value="<?= isset($_POST['reference_number']) ? htmlspecialchars($_POST['reference_number']) : '' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="bank_owner_name">Bank Owner Name</label>
                            <input type="text" class="form-control" id="bank_owner_name" name="bank_owner_name" value="<?= isset($_POST['bank_owner_name']) ? htmlspecialchars($_POST['bank_owner_name']) : '' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="bank_name">Bank Name</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" value="<?= isset($_POST['bank_name']) ? htmlspecialchars($_POST['bank_name']) : '' ?>" required>
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
    <script>
function changeQuantity(btn, delta) {
    var form = btn.closest('form');
    var input = form.querySelector('input[name="quantity"]');
    var newValue = parseInt(input.value) + delta;
    if (newValue < 1) newValue = 1;
    input.value = newValue;
    form.querySelector('button[type="submit"]').click();
}
</script>
</body>
</html>