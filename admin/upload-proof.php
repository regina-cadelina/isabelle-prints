<?php
require_once '../config/database.php';

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$upload_dir = '../payment-proofs/';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['proof'])) {
    $file = $_FILES['proof'];

    // Validate file upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die('Upload failed with error code ' . $file['error']);
    }

    // Only allow image types
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        die('Only image files (jpg, png, gif) are allowed.');
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'payment_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $destination = $upload_dir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        die('Failed to move uploaded file.');
    }

    // Update order record with filename
    $stmt = $pdo->prepare("UPDATE orders SET payment_proof_file = ? WHERE id = ?");
    $stmt->execute([$filename, $order_id]);

    echo "<p style='color: green;'>Proof of payment uploaded successfully.</p>";
    echo "<p><a href='order-details.php?id=$order_id'>Go back to Order Details</a></p>";
} else {
    echo "Invalid request.";
}
?>