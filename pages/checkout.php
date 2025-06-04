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
$debug_info = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $billingAddress = trim($_POST['billing_address'] ?? '');
    $referenceNumber = trim($_POST['reference_number'] ?? '');
    $bankOwnerName = trim($_POST['bank_owner_name'] ?? '');
    $bankName = trim($_POST['bank_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Debug: Check if file was uploaded
    $debug_info[] = "Files array: " . print_r($_FILES, true);
    $debug_info[] = "POST array: " . print_r($_POST, true);

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
        // Debug file upload
        if (!isset($_FILES['payment_proof'])) {
            $error = 'No file was uploaded. Please select a payment proof file.';
            $debug_info[] = "No payment_proof in FILES array";
        } elseif ($_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
            ];
            $error_code = $_FILES['payment_proof']['error'];
            $error = 'File upload error: ' . ($upload_errors[$error_code] ?? 'Unknown error');
            $debug_info[] = "Upload error code: " . $error_code;
        } else {
            // File upload is OK, proceed with processing
            $uploadDir = '../uploads/payment-proofs/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $error = 'Failed to create upload directory.';
                    $debug_info[] = "Failed to create directory: " . $uploadDir;
                } else {
                    $debug_info[] = "Created directory: " . $uploadDir;
                }
            } else {
                $debug_info[] = "Directory exists: " . $uploadDir;
            }

            if (!$error) {
                // Check if directory is writable
                if (!is_writable($uploadDir)) {
                    $error = 'Upload directory is not writable.';
                    $debug_info[] = "Directory not writable: " . $uploadDir;
                } else {
                    $debug_info[] = "Directory is writable: " . $uploadDir;
                    
                    $fileExtension = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

                    if (!in_array($fileExtension, $allowedExtensions)) {
                        $error = 'Invalid file type. Only JPG, PNG, and PDF files are allowed.';
                        $debug_info[] = "Invalid file extension: " . $fileExtension;
                    } else {
                        $fileName = 'payment_' . time() . '_' . uniqid() . '.' . $fileExtension;
                        $uploadPath = $uploadDir . $fileName;
                        
                        $debug_info[] = "Attempting to move file to: " . $uploadPath;
                        $debug_info[] = "Temp file: " . $_FILES['payment_proof']['tmp_name'];
                        $debug_info[] = "File size: " . $_FILES['payment_proof']['size'];

                        if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $uploadPath)) {
                            $paymentProofFile = $fileName;
                            $debug_info[] = "File uploaded successfully: " . $fileName;
                        } else {
                            $error = 'Failed to upload payment proof file. Please try again.';
                            $debug_info[] = "move_uploaded_file failed";
                            $debug_info[] = "Source exists: " . (file_exists($_FILES['payment_proof']['tmp_name']) ? 'yes' : 'no');
                            $debug_info[] = "Destination writable: " . (is_writable(dirname($uploadPath)) ? 'yes' : 'no');
                        }
                    }
                }
            }
        }
    }

    if (!$error && $paymentProofFile) {
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
                if (!empty($item['custom_image_file']) && !$orderCustomImage) {
                    $orderCustomImage = $item['custom_image_file'];
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
                    customization_notes, custom_image_file,
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
                    $item['custom_image_file'] ?? null,
                    $options['size'] ?? null,
                    $options['color'] ?? null,
                    $options['finish'] ?? null,
                    $options['material'] ?? null
                ]);

                // Subtract stock
                $updateStockStmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $updateStockStmt->execute([$quantity, $item['product']['id']]);
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
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Isabelle Concept & Prints</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include('../includes/header.php'); ?>

<div class="container mt-5">
    <h1>Checkout</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
            <?php if (!empty($debug_info) && ($_SESSION['user_type'] ?? '') === 'admin'): ?>
                <details style="margin-top: 10px;">
                    <summary>Debug Information (Admin Only)</summary>
                    <pre style="font-size: 12px; background: #f8f9fa; padding: 10px; margin-top: 10px;">
                        <?= htmlspecialchars(implode("\n", $debug_info)) ?>
                    </pre>
                </details>
            <?php endif; ?>
        </div>
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
                                <?php if (!empty($item['custom_image_file'])): ?>
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
                        <input type="text" class="form-control" id="phone" name="phone" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>

                    <h4 class="mb-3">Payment Information</h4>

                    <div class="mb-3">
                        <label for="payment_proof">Payment Proof (JPG, PNG, PDF) - Max 10MB</label>
                        <input type="file" class="form-control" id="payment_proof" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required>
                        <small class="form-text text-muted">Please upload a clear image or PDF of your payment receipt.</small>
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
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
// Add file validation
document.getElementById('payment_proof').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        
        if (file.size > maxSize) {
            alert('File size must be less than 10MB');
            e.target.value = '';
            return;
        }
        
        if (!allowedTypes.includes(file.type)) {
            alert('Only JPG, PNG, and PDF files are allowed');
            e.target.value = '';
            return;
        }
        
        // Show file name
        const fileName = file.name;
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        console.log(`Selected file: ${fileName} (${fileSize} MB)`);
    }
});
</script>
</body>
</html>
