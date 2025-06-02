<?php
session_start();
require_once '../config/database.php';
require_once '../config/google-config.php';
require_once '../includes/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if 'code' is present
if (!isset($_GET['code'])) {
    $error = $_GET['error'] ?? 'access_denied';
    header('Location: /isabelle-prints/pages/login.php?error=' . urlencode($error));
    exit;
}

$authCode = $_GET['code'];

try {
    // Exchange auth code for access token
    $tokenData = [
        'client_id' => '23042345108-l7dg6vqrr1jnb83efue17ojemldlvnar.apps.googleusercontent.com',
        'client_secret' => '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',
        'code' => $authCode,
        'grant_type' => 'authorization_code',
        'redirect_uri' => 'http://localhost/isabelle-prints/auth/google-callback.php'
    ];

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For localhost only

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('CURL Error: ' . curl_error($ch));
    }
    curl_close($ch);

    $tokenInfo = json_decode($response, true);

    if (!isset($tokenInfo['access_token'])) {
        throw new Exception('No access token received: ' . $response);
    }

    // Get user info from Google
    $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For localhost only
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $tokenInfo['access_token']
    ]);

    $userResponse = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('CURL Error (userinfo): ' . curl_error($ch));
    }
    curl_close($ch);

    $userInfo = json_decode($userResponse, true);

    if (!isset($userInfo['email'])) {
        throw new Exception('No email found in user info: ' . $userResponse);
    }

    // Extract user info
    $email = $userInfo['email'];
    $googleId = $userInfo['id'];
    $firstName = $userInfo['given_name'] ?? '';
    $lastName = $userInfo['family_name'] ?? '';

    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Update google_id if not present
        if (empty($user['google_id'])) {
            $update = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
            $update->execute([$googleId, $user['id']]);
        }
        $userId = $user['id'];
        $userName = trim($user['first_name'] . ' ' . $user['last_name']);
    } else {
        // Insert new user
        $defaultPassword = password_hash('password', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (email, google_id, password, first_name, last_name, created_at, is_active, user_type) VALUES (?, ?, ?, ?, ?, NOW(), 1, 'customer')");
        $insert->execute([$email, $googleId, $defaultPassword, $firstName, $lastName]);
        $userId = $pdo->lastInsertId();
        $userName = trim($firstName . ' ' . $lastName);
    }

    // Set session
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $userName ?: 'User';

    // Redirect to previous page or products
    $redirect = $_SESSION['redirect_after_login'] ?? '/isabelle-prints/pages/products.php';
    unset($_SESSION['redirect_after_login']);
    header('Location: ' . $redirect);
    exit;

} catch (Exception $e) {
    error_log('Google OAuth Error: ' . $e->getMessage());
    header('Location: /isabelle-prints/pages/login.php?error=' . urlencode('Google login failed: ' . $e->getMessage()));
    exit;
}
?>
