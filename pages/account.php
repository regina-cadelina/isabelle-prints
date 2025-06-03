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

$error_profile = '';
$success_profile = '';
$error_password = '';
$success_password = '';

// Handle form submission
if ($_POST['action'] ?? '' == 'update_profile') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($firstName) || empty($lastName) || empty($email)) {
        $error_profile = 'Please fill in all required fields.';
    } else {
        try {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            
            if ($stmt->fetch()) {
                $error_profile = 'Email is already taken by another user.';
            } else {
                // Update user information
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
                $stmt->execute([$firstName, $lastName, $email, $phone, $address, $_SESSION['user_id']]);
                
                // Update session variables
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                
                $success_profile = 'Profile updated successfully!';
            }
        } catch (PDOException $e) {
            $error_profile = 'Failed to update profile. Please try again.';
        }
    }
}

if ($_POST['action'] ?? '' == 'change_password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error_password = 'Please fill in all password fields.';
    } elseif ($newPassword !== $confirmPassword) {
        $error_password = 'New passwords do not match.';
    } elseif (strlen($newPassword) < 6) {
        $error_password = 'New password must be at least 6 characters long.';
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
                
                $success_password = 'Password changed successfully!';
            } else {
                $error_password = 'Current password is incorrect.';
            }
        } catch (PDOException $e) {
            $error_password = 'Failed to change password. Please try again.';
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

        <div class="account-content">
            <div class="account-tabs">
                <button class="tab-btn active" onclick="showAccountTab('profile')">Profile Information</button>
                <button class="tab-btn" onclick="showAccountTab('password')">Change Password</button>
                <button class="tab-btn" onclick="showAccountTab('orders')">Order History</button>
            </div>

            <!-- Profile Information Tab -->
            <div id="profile-tab" class="tab-content active">
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile'): ?>
                    <?php if ($error_profile): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error_profile); ?></div>
                    <?php endif; ?>
                    <?php if ($success_profile): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success_profile); ?></div>
                    <?php endif; ?>
                <?php endif; ?>
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
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password'): ?>
                    <?php if ($error_password): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error_password); ?></div>
                    <?php endif; ?>
                    <?php if ($success_password): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success_password); ?></div>
                    <?php endif; ?>
                <?php endif; ?>
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
                <?php
                // Fetch up to 3 most recent orders for this user
                $stmt = $pdo->prepare("
                    SELECT o.*, 
                           COUNT(oi.id) as item_count,
                           GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
                    FROM orders o
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE o.user_id = ?
                    GROUP BY o.id
                    ORDER BY o.created_at DESC
                    LIMIT 3
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $recent_orders = $stmt->fetchAll();
                ?>

                <?php if (empty($recent_orders)): ?>
                    <div class="empty-orders">
                        <p>No recent orders found.</p>
                    </div>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($recent_orders as $order): ?>
                            <div class="order-card" style="margin-bottom: 1rem;">
                                <div class="order-header">
                                    <strong>Order #<?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    <span class="order-date" style="margin-left:10px;"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                                    <span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>" style="margin-left:10px;">
                                        <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                    </span>
                                </div>
                                <div class="order-details">
                                    <div><strong>Items:</strong> <?php echo htmlspecialchars($order['product_names']); ?></div>
                                    <div><strong>Total:</strong> <?php echo formatPrice($order['total_amount']); ?></div>
                                </div>
                                <button class="btn btn-sm btn-secondary" onclick="window.location.href='/isabelle-prints/pages/orders.php'">
                                    View Details
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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