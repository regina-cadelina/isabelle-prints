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

    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $userInfo['email'];
    $_SESSION['user_name'] = $userName ?: 'User';

    // Redirect to previous page or products
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
?><?php
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

    // Get user information from Google - FIXED: Using correct URL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v3/userinfo');
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
    $stmt = $pdo->prepare("SELECT id, email, first_name, last_name, user_type FROM users WHERE email = ? OR google_id = ?");
    $stmt->execute([$userInfo['email'], $userInfo['sub']]);  // FIXED: Google uses 'sub' as the ID
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        // Update Google ID if not set
        if (empty($existingUser['google_id'])) {
            $updateStmt = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
            $updateStmt->execute([$userInfo['sub'], $existingUser['id']]);
        }
        
        $userId = $existingUser['id'];
        $firstName = $existingUser['first_name'];
        $lastName = $existingUser['last_name'];
        $userEmail = $existingUser['email'];
        $userType = $existingUser['user_type'] ?? 'customer';
    } else {
        // Create new user
        $firstName = $userInfo['given_name'] ?? '';
        $lastName = $userInfo['family_name'] ?? '';
        $userEmail = $userInfo['email'];
        $googleId = $userInfo['sub'];  // FIXED: Google uses 'sub' as the ID

        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, google_id, user_type, is_active, created_at) VALUES (?, ?, ?, ?, 'customer', 1, NOW())");
        $stmt->execute([$firstName, $lastName, $userEmail, $googleId]);
        
        $userId = $pdo->lastInsertId();
        $userType = 'customer';
    }

    // FIXED: Set session variables correctly to match what the rest of the app expects
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $userEmail;
    $_SESSION['user_name'] = trim($firstName . ' ' . $lastName);
    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name'] = $lastName;
    $_SESSION['user_type'] = $userType;

    // Redirect to intended page or products
    $redirect = $_SESSION['redirect_after_login'] ?? '/isabelle-prints/index.php';
    unset($_SESSION['redirect_after_login']);
    header('Location: ' . $redirect);
    exit;

} catch (Exception $e) {
    error_log('Google OAuth Error: ' . $e->getMessage());
    header('Location: /isabelle-prints/pages/login.php?error=' . urlencode('Google login failed: ' . $e->getMessage()));
    exit;
}
?>
