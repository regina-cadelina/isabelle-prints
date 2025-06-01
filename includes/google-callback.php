<?php
require_once '../config/database.php';
require_once '../includes/google-auth.php';
require_once '../includes/functions.php';

if (!isset($_GET['code'])) {
    header('Location: /isabelle-prints/pages/login.php?error=oauth_failed');
    exit;
}

try {
    // Get access token
    $tokenData = GoogleAuth::getAccessToken($_GET['code']);
    
    if (!isset($tokenData['access_token'])) {
        throw new Exception('Failed to get access token');
    }
    
    // Get user info
    $userInfo = GoogleAuth::getUserInfo($tokenData['access_token']);
    
    if (!isset($userInfo['email'])) {
        throw new Exception('Failed to get user information');
    }
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$userInfo['email']]);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        // User exists, log them in
        $_SESSION['user_id'] = $existingUser['id'];
        $_SESSION['user_email'] = $existingUser['email'];
        $_SESSION['user_name'] = $existingUser['first_name'] . ' ' . $existingUser['last_name'];
    } else {
        // Create new user
        $firstName = $userInfo['given_name'] ?? '';
        $lastName = $userInfo['family_name'] ?? '';
        $email = $userInfo['email'];
        $googleId = $userInfo['id'];
        
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, google_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$firstName, $lastName, $email, $googleId]);
        
        $userId = $pdo->lastInsertId();
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
    }
    
    // Redirect to intended page or home
    $redirectUrl = $_SESSION['redirect_after_login'] ?? '/isabelle-prints/';
    unset($_SESSION['redirect_after_login']);
    
    header('Location: ' . $redirectUrl);
    exit;
    
} catch (Exception $e) {
    error_log('Google OAuth Error: ' . $e->getMessage());
    header('Location: /isabelle-prints/pages/login.php?error=oauth_failed');
    exit;
}
?>