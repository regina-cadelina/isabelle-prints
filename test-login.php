<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing Login Page Dependencies</h1>";

// Test 1: Check if config/database.php exists and works
echo "<h2>1. Testing Database Connection</h2>";
if (file_exists('config/database.php')) {
    echo "✓ config/database.php exists<br>";
    try {
        require_once 'config/database.php';
        echo "✓ Database connection successful<br>";
        
        // Test database query
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "✓ Users table accessible. Found " . $result['count'] . " users<br>";
    } catch (Exception $e) {
        echo "✗ Database error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ config/database.php does not exist<br>";
}

// Test 2: Check if includes/functions.php exists
echo "<h2>2. Testing Functions File</h2>";
if (file_exists('includes/functions.php')) {
    echo "✓ includes/functions.php exists<br>";
    try {
        require_once 'includes/functions.php';
        echo "✓ Functions loaded successfully<br>";
    } catch (Exception $e) {
        echo "✗ Functions error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ includes/functions.php does not exist<br>";
}

// Test 3: Check if config/google-config.php exists
echo "<h2>3. Testing Google Config</h2>";
if (file_exists('config/google-config.php')) {
    echo "✓ config/google-config.php exists<br>";
    try {
        require_once 'config/google-config.php';
        echo "✓ Google config loaded successfully<br>";
    } catch (Exception $e) {
        echo "✗ Google config error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ config/google-config.php does not exist<br>";
}

// Test 4: Check if header and footer exist
echo "<h2>4. Testing Include Files</h2>";
if (file_exists('includes/header.php')) {
    echo "✓ includes/header.php exists<br>";
} else {
    echo "✗ includes/header.php does not exist<br>";
}

if (file_exists('includes/footer.php')) {
    echo "✓ includes/footer.php exists<br>";
} else {
    echo "✗ includes/footer.php does not exist<br>";
}

echo "<h2>5. Testing Login Page</h2>";
echo "<a href='pages/login.php'>Click here to test login page</a><br>";

echo "<h2>6. File Structure Check</h2>";
echo "Current directory: " . getcwd() . "<br>";
echo "Files in current directory:<br>";
$files = scandir('.');
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "- " . $file . "<br>";
    }
}
?>