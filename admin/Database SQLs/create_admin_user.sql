-- First, make sure your users table has the user_type column
-- If it doesn't exist, add it:
ALTER TABLE users ADD COLUMN user_type ENUM('customer', 'admin', 'staff') DEFAULT 'customer';
ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE;

-- Create an admin user
-- Password will be "admin123" (change this after first login!)
INSERT INTO users (first_name, last_name, email, password, user_type, is_active, created_at) 
VALUES (
    'Admin', 
    'User', 
    'admin@isabelleprints.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
    'admin', 
    1, 
    NOW()
);

-- You can also create a test customer
INSERT INTO users (first_name, last_name, email, password, user_type, is_active, created_at) 
VALUES (
    'Test', 
    'Customer', 
    'customer@test.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
    'customer', 
    1, 
    NOW()
);