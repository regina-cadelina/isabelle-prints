<?php
// Fix Permissions Script - Run this once to diagnose and fix upload directory issues
// DELETE THIS FILE AFTER USE FOR SECURITY

echo "<h1>Directory Permissions Fixer</h1>";
echo "<p>This script will help diagnose and fix upload directory permission issues.</p>";

$directories = [
    'uploads',
    'uploads/payment-proofs',
    'uploads/products',
    'uploads/categories',
    'uploads/custom-images'
];

echo "<h2>Current Status:</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Directory</th><th>Exists</th><th>Writable</th><th>Permissions</th><th>Action</th></tr>";

foreach ($directories as $dir) {
    $exists = is_dir($dir);
    $writable = $exists ? is_writable($dir) : false;
    $perms = $exists ? substr(sprintf('%o', fileperms($dir)), -4) : 'N/A';
    
    echo "<tr>";
    echo "<td>$dir</td>";
    echo "<td>" . ($exists ? '✅ Yes' : '❌ No') . "</td>";
    echo "<td>" . ($writable ? '✅ Yes' : '❌ No') . "</td>";
    echo "<td>$perms</td>";
    
    if (!$exists) {
        echo "<td>Need to create directory</td>";
    } elseif (!$writable) {
        echo "<td>Need to fix permissions</td>";
    } else {
        echo "<td>✅ OK</td>";
    }
    echo "</tr>";
}
echo "</table>";

// Auto-fix section
if (isset($_GET['fix']) && $_GET['fix'] === 'auto') {
    echo "<h2>Auto-Fix Results:</h2>";
    
    foreach ($directories as $dir) {
        echo "<p><strong>Processing: $dir</strong><br>";
        
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                echo "✅ Created directory successfully<br>";
            } else {
                echo "❌ Failed to create directory<br>";
            }
        }
        
        if (is_dir($dir)) {
            if (chmod($dir, 0755)) {
                echo "✅ Set permissions to 755<br>";
            } else {
                echo "❌ Failed to set permissions<br>";
            }
        }
        echo "</p>";
    }
    
    echo "<p><strong>Auto-fix completed!</strong> <a href='fix-permissions.php'>Refresh to check status</a></p>";
} else {
    echo "<h2>Actions:</h2>";
    echo "<p><a href='fix-permissions.php?fix=auto' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Auto-Fix All Issues</a></p>";
}

// Manual instructions
echo "<h2>Manual Fix Instructions:</h2>";
echo "<h3>For Windows (XAMPP/WAMP):</h3>";
echo "<pre>";
echo "1. Open File Explorer and navigate to your project folder\n";
echo "2. Right-click on the 'uploads' folder\n";
echo "3. Select 'Properties' → 'Security' tab\n";
echo "4. Click 'Edit' → 'Add'\n";
echo "5. Type 'Everyone' and click 'Check Names'\n";
echo "6. Give 'Everyone' 'Full Control' permissions\n";
echo "7. Click 'OK' to apply\n";
echo "</pre>";

echo "<h3>For Mac/Linux:</h3>";
echo "<pre>";
echo "cd " . getcwd() . "\n";
echo "mkdir -p uploads/payment-proofs uploads/products uploads/categories uploads/custom-images\n";
echo "chmod -R 755 uploads/\n";
echo "# If 755 doesn't work, try:\n";
echo "chmod -R 777 uploads/\n";
echo "</pre>";

// Test upload functionality
echo "<h2>Test File Upload:</h2>";
if (isset($_POST['test_upload']) && isset($_FILES['test_file'])) {
    $uploadDir = 'uploads/payment-proofs/';
    
    if ($_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
        $fileName = 'test_' . time() . '.txt';
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['test_file']['tmp_name'], $uploadPath)) {
            echo "<p style='color: green;'>✅ Test upload successful! File saved as: $fileName</p>";
            // Clean up test file
            unlink($uploadPath);
            echo "<p>Test file cleaned up.</p>";
        } else {
            echo "<p style='color: red;'>❌ Test upload failed - could not move file</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Test upload failed - upload error: " . $_FILES['test_file']['error'] . "</p>";
    }
}

echo "<form method='post' enctype='multipart/form-data'>";
echo "<p>Upload a small test file to verify permissions:</p>";
echo "<input type='file' name='test_file' required>";
echo "<button type='submit' name='test_upload'>Test Upload</button>";
echo "</form>";

// System information
echo "<h2>System Information:</h2>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>Operating System:</strong> " . PHP_OS . "</li>";
echo "<li><strong>Current Working Directory:</strong> " . getcwd() . "</li>";
echo "<li><strong>Web Server User:</strong> " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'Unknown') . "</li>";
echo "<li><strong>Upload Max Filesize:</strong> " . ini_get('upload_max_filesize') . "</li>";
echo "<li><strong>Post Max Size:</strong> " . ini_get('post_max_size') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p style='color: red; font-weight: bold;'>⚠️ IMPORTANT: Delete this file (fix-permissions.php) after use for security reasons!</p>";
?>
