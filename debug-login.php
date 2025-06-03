<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

echo "<h1>Login Debug Information</h1>";

// Check session status
echo "<h2>Session Status</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . (session_status() == PHP_SESSION_ACTIVE ? "Active" : "Not Active") . "<br>";

// Display session variables
echo "<h2>Session Variables</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check if user is logged in
echo "<h2>Login Status</h2>";
if (isset($_SESSION['user_id'])) {
    echo "User is logged in with ID: " . $_SESSION['user_id'] . "<br>";
    echo "User Email: " . ($_SESSION['user_email'] ?? 'Not set') . "<br>";
    echo "User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "<br>";
    echo "User Type: " . ($_SESSION['user_type'] ?? 'Not set') . "<br>";
} else {
    echo "User is NOT logged in<br>";
}

// Check database connection
echo "<h2>Database Connection Test</h2>";
try {
    require_once 'config/database.php';
    echo "Database connection successful<br>";
    
    // If user is logged in, fetch their details from database
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "User found in database:<br>";
            echo "ID: " . $user['id'] . "<br>";
            echo "Email: " . $user['email'] . "<br>";
            echo "Name: " . $user['first_name'] . " " . $user['last_name'] . "<br>";
            echo "User Type: " . $user['user_type'] . "<br>";
            echo "Google ID: " . ($user['google_id'] ?? 'Not set') . "<br>";
        } else {
            echo "User with ID " . $_SESSION['user_id'] . " not found in database!<br>";
        }
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

// Display useful links
echo "<h2>Useful Links</h2>";
echo "<ul>";
echo "<li><a href='pages/login.php'>Login Page</a></li>";
echo "<li><a href='pages/logout.php'>Logout</a></li>";
echo "<li><a href='index.php'>Home Page</a></li>";
echo "<li><a href='admin/dashboard.php'>Admin Dashboard</a></li>";
echo "</ul>";

// Display PHP info
echo "<h2>PHP Information</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Session Save Path: " . session_save_path() . "<br>";
echo "Session Cookie Parameters:<br>";
echo "<pre>";
print_r(session_get_cookie_params());
echo "</pre>";
?>