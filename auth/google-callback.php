<?php
session_start();
require_once '../config/database.php';
require_once '../config/google-config.php';
require_once '../includes/functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if we have an authorization code
if (!isset($_GET['code'])) {
    $error = $_GET['error'] ?? 'access_denied';
    header('Location: /isabelle-prints/pages/login.php?error=' . urlencode($error));
    exit;
}

$authCode = $_GET['code'];

try {
    // Exchange authorization code for access token
    $tokenData = [
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'code' => $authCode,
        'grant_type' => 'authorization_code',
        'redirect_uri' => GOOGLE_REDIRECT_URI
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, GOOGLE_TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For localhost testing
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_error($ch)) {
        throw new Exception('CURL Error: ' . curl_error($ch));
    }
    
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Failed to get access token. HTTP Code: ' . $httpCode . '. Response: ' . $response);
    }

    $tokenInfo = json_decode($response, true);
    
    if (!isset($tokenInfo['access_token'])) {
        throw new Exception('No access token received. Response: ' . $response);
    }

    // Get user information from Google
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, GOOGLE_USER_INFO_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For localhost testing
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $tokenInfo['access_token']
    ]);

    $userResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_error($ch)) {
        throw new Exception('CURL Error getting user info: ' . curl_error($ch));
    }
    
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Failed to get user information. HTTP Code: ' . $httpCode);
    }

    $userInfo = json_decode($userResponse, true);

    if (!isset($userInfo['email'])) {
        throw new Exception('No email received from Google. Response: ' . $userResponse);
    }

    // Check if user exists in database
    $stmt = $pdo->prepare("SELECT id, email, first_name, last_name, google_id FROM users WHERE email = ? OR google_id = ?");
    $stmt->execute([$userInfo['email'], $userInfo['id']]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        // Update Google ID if not set
        if (empty($existingUser['google_id'])) {
            $updateStmt = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
            $updateStmt->execute([$userInfo['id'], $existingUser['id']]);
        }
        
        $userId = $existingUser['id'];
        $userName = trim($existingUser['first_name'] . ' ' . $existingUser['last_name']);
    } else {
        // Create new user
        $firstName = $userInfo['given_name'] ?? '';
        $lastName = $userInfo['family_name'] ?? '';
        $email = $userInfo['email'];
        $googleId = $userInfo['id'];

        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, google_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$firstName, $lastName, $email, $googleId]);
        
        $userId = $pdo->lastInsertId();
        $userName = trim($firstName . ' ' . $lastName);
    }

    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $userInfo['email'];
    $_SESSION['user_name'] = $userName ?: 'User';

    // Redirect to intended page or products
    $redirect = $_SESSION['redirect_after_login'] ?? '/isabelle-prints/pages/products.php';
    unset($_SESSION['redirect_after_login']);
    header('Location: ' . $redirect);
    exit;

} catch (Exception $e) {
    // Log error and redirect to login with error message
    error_log('Google OAuth Error: ' . $e->getMessage());
    header('Location: /isabelle-prints/pages/login.php?error=' . urlencode('Google login failed: ' . $e->getMessage()));
    exit;
}
?>