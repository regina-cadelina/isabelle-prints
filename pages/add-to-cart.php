<?php
session_start();
require 'db_connect.php'; // update with your actual DB connection file

// Gather session/user
$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();

// Get form data
$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];
$selected_color = $_POST['selected_color'];
$selected_size = $_POST['selected_size'];
$customization_notes = $_POST['customization_notes'];
$created_at = date('Y-m-d H:i:s');
$updated_at = $created_at;

// File upload
$custom_image = null;
if (isset($_FILES['custom_image']) && $_FILES['custom_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $fileName = time() . '_' . basename($_FILES['custom_image']['name']);
    $uploadPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['custom_image']['tmp_name'], $uploadPath)) {
        $custom_image = $uploadPath;
    }
}

// Combine selected options as JSON
$selected_options = json_encode([
    'color' => $selected_color,
    'size' => $selected_size
]);

// Insert to cart
$stmt = $conn->prepare("INSERT INTO cart (user_id, session_id, product_id, quantity, selected_options, customization_notes, custom_image, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ississsss", $user_id, $session_id, $product_id, $quantity, $selected_options, $customization_notes, $custom_image, $created_at, $updated_at);

if ($stmt->execute()) {
    header("Location: cart.php");
    exit();
} else {
    echo "Error adding to cart: " . $stmt->error;
}
?>