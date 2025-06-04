<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$count = getCartItemCount();

echo json_encode(['count' => $count]);