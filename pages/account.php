<?php
session_start();
$pageTitle = "Account Details";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = '/isabelle-prints/pages/account.php';
    header('Location: /isabelle-prints/pages/login.php');
    exit;
}

require_once '../config/database.php';
require_once '../includes/functions.php';

$error = '';
$success = '';

// Handle form submission
if ($_POST['action'] ?? '' == 'update_profile') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($firstName) || empty($lastName) || empty($email)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            
            if ($stmt->fetch()) {
                $error = 'Email is already taken by another user.';
            } else {
                // Update user information
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
                $stmt->execute([$firstName, $lastName, $email, $phone, $address, $_SESSION['user_id']]);
                
                // Update session variables
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                
                $success = 'Profile updated successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}

// Handle password change
if ($_POST['action'] ?? '' == 'change_password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Please fill in all password fields.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } else {
        try {
            // Get current password hash
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($currentPassword, $user['password'])) {
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                
                $success = 'Password changed successfully!';
            } else {
                $error = 'Current password is incorrect.';
            }
        } catch (PDOException $e) {
            $error = 'Failed to change password. Please try again.';
        }
    }
}

// Get user information
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: /isabelle-prints/pages/logout.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Failed to load user information.';
    $user = [];
}

include '../includes/header.php';
?>

<main class="account-page">
    <div class="container">
        <div class="page-header">
            <h1>Account Details</h1>
            <p>Manage your account information and settings</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="account-content">
            <div class="account-tabs">
                <button class="tab-btn active" onclick="showAccountTab('profile')">Profile Information</button>
                <button class="tab-btn" onclick="showAccountTab('password')">Change Password</button>
                <button class="tab-btn" onclick="showAccountTab('orders')">Order History</button>
            </div>

            <!-- Profile Information Tab -->
            <div id="profile-tab" class="tab-content active">
                <form method="POST" class="account-form">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>

            <!-- Change Password Tab -->
            <div id="password-tab" class="tab-content">
                <?php if (!empty($user['google_id'])): ?>
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <p>You signed up with Google. You can set a password to also login with email and password.</p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="account-form">
                    <input type="hidden" name="action" value="change_password">
                    
                    <?php if (!empty($user['password'])): ?>
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" minlength="6" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?php echo !empty($user['password']) ? 'Change Password' : 'Set Password'; ?>
                    </button>
                </form>
            </div>

            <!-- Order History Tab -->
            <div id="orders-tab" class="tab-content">
                <div class="orders-summary">
                    <p>View your complete order history on the <a href="/isabelle-prints/pages/orders.php">Orders page</a>.</p>
                    <a href="/isabelle-prints/pages/orders.php" class="btn btn-secondary">View All Orders</a>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function showAccountTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}
</script>

<?php include '../includes/footer.php'; ?>