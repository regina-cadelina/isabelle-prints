<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Isabelle Prints Website Structure Check</h1>";

// Define the base directory
$baseDir = __DIR__;
echo "<p>Base directory: " . $baseDir . "</p>";

// Check important directories
$directories = [
    'assets',
    'assets/css',
    'assets/js',
    'assets/images',
    'auth',
    'config',
    'includes',
    'pages',
    'uploads',
    'uploads/payment-proofs'
];

echo "<h2>Directory Check</h2>";
echo "<ul>";
foreach ($directories as $dir) {
    $fullPath = $baseDir . '/' . $dir;
    if (is_dir($fullPath)) {
        echo "<li style='color:green'>✓ Directory exists: {$dir}</li>";
        
        // Check if directory is writable
        if (is_writable($fullPath)) {
            echo "<li style='color:green; margin-left:20px'>✓ Directory is writable: {$dir}</li>";
        } else {
            echo "<li style='color:red; margin-left:20px'>✗ Directory is NOT writable: {$dir}</li>";
            echo "<li style='margin-left:40px'>Try running: <code>chmod 755 {$fullPath}</code></li>";
        }
    } else {
        echo "<li style='color:red'>✗ Directory missing: {$dir}</li>";
        echo "<li style='margin-left:20px'>Create it with: <code>mkdir -p {$fullPath}</code></li>";
    }
}
echo "</ul>";

// Check important files
$files = [
    'includes/header.php',
    'includes/footer.php',
    'includes/functions.php',
    'config/database.php',
    'config/google-config.php',
    'assets/css/style.css',
    'assets/css/login.css',
    'assets/css/account.css',
    'assets/css/checkout-orders.css',
    'pages/login.php',
    'pages/logout.php',
    'pages/account.php',
    'pages/cart.php',
    'pages/checkout.php',
    'pages/orders.php',
    'auth/google-callback.php'
];

echo "<h2>File Check</h2>";
echo "<ul>";
foreach ($files as $file) {
    $fullPath = $baseDir . '/' . $file;
    if (file_exists($fullPath)) {
        echo "<li style='color:green'>✓ File exists: {$file}</li>";
        
        // Check if file is readable
        if (is_readable($fullPath)) {
            echo "<li style='color:green; margin-left:20px'>✓ File is readable</li>";
        } else {
            echo "<li style='color:red; margin-left:20px'>✗ File is NOT readable</li>";
        }
        
        // Get file size
        $fileSize = filesize($fullPath);
        echo "<li style='margin-left:20px'>File size: {$fileSize} bytes</li>";
        
        // Check if file is empty
        if ($fileSize == 0) {
            echo "<li style='color:red; margin-left:20px'>✗ Warning: File is empty!</li>";
        }
    } else {
        echo "<li style='color:red'>✗ File missing: {$file}</li>";
    }
}
echo "</ul>";

// Check PHP configuration
echo "<h2>PHP Configuration</h2>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>upload_max_filesize: " . ini_get('upload_max_filesize') . "</li>";
echo "<li>post_max_size: " . ini_get('post_max_size') . "</li>";
echo "<li>max_file_uploads: " . ini_get('max_file_uploads') . "</li>";
echo "<li>session.save_path: " . ini_get('session.save_path') . "</li>";
echo "</ul>";

// Check session functionality
echo "<h2>Session Test</h2>";
session_start();
$_SESSION['test'] = 'Session is working';
echo "<p>Set test session variable. Refresh page to see if it persists.</p>";

if (isset($_SESSION['test'])) {
    echo "<p style='color:green'>Session variable found: " . $_SESSION['test'] . "</p>";
} else {
    echo "<p style='color:red'>Session variable not found. Sessions may not be working correctly.</p>";
}

// Database connection test
echo "<h2>Database Connection Test</h2>";
if (file_exists($baseDir . '/config/database.php')) {
    include $baseDir . '/config/database.php';
    
    try {
        // Assuming $pdo is your database connection variable
        if (isset($pdo)) {
            $stmt = $pdo->query("SELECT 1");
            echo "<p style='color:green'>Database connection successful!</p>";
            
            // Check if users table exists
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                $count = $stmt->fetchColumn();
                echo "<p style='color:green'>Users table exists with {$count} records.</p>";
            } catch (PDOException $e) {
                echo "<p style='color:red'>Error checking users table: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color:red'>Database connection variable (\$pdo) not found in database.php</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>Database connection failed: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red'>Database configuration file not found.</p>";
}
?>