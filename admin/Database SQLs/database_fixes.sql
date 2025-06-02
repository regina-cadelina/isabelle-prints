-- Update users table to match your login.php expectations
ALTER TABLE users ADD COLUMN user_type ENUM('customer', 'admin', 'staff') DEFAULT 'customer';
ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE;

-- Create admin user (password: admin123)
INSERT INTO users (first_name, last_name, email, password, user_type, is_active, created_at) 
VALUES (
    'Admin', 
    'User', 
    'admin@isabelleprints.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin', 
    1, 
    NOW()
) ON DUPLICATE KEY UPDATE email = email;

-- Create test customer (password: admin123)
INSERT INTO users (first_name, last_name, email, password, user_type, is_active, created_at) 
VALUES (
    'Test', 
    'Customer', 
    'customer@test.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'customer', 
    1, 
    NOW()
) ON DUPLICATE KEY UPDATE email = email;