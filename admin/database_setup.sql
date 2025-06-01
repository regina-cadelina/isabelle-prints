-- Create the main database
CREATE DATABASE isabelle_prints;
USE isabelle_prints;

-- Users table for account management
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    base_price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(500),
    is_bestseller BOOLEAN DEFAULT FALSE,
    is_new BOOLEAN DEFAULT FALSE,
    is_sale BOOLEAN DEFAULT FALSE,
    sale_price DECIMAL(10,2),
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Product options (sizes, colors, finishes)
CREATE TABLE product_options (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    option_type ENUM('size', 'color', 'finish') NOT NULL,
    option_name VARCHAR(100) NOT NULL,
    option_value VARCHAR(100) NOT NULL,
    price_modifier DECIMAL(10,2) DEFAULT 0,
    is_default BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Shopping cart
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    session_id VARCHAR(255),
    product_id INT,
    quantity INT NOT NULL DEFAULT 1,
    selected_options JSON, -- Store selected size, color, finish as JSON
    customization_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT,
    billing_address TEXT,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    selected_options JSON,
    customization_notes TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert sample categories
INSERT INTO categories (name, slug, description) VALUES
('Business Cards', 'business-cards', 'Professional business cards for your brand'),
('Banners', 'banners', 'High-quality banners for events and promotions'),
('Flyers', 'flyers', 'Eye-catching flyers for marketing campaigns'),
('Custom Designs', 'custom-designs', 'Personalized designs for any occasion');

-- Insert sample products
INSERT INTO products (category_id, name, slug, description, base_price, is_bestseller, is_new, is_sale, sale_price) VALUES
(1, 'Premium Business Card', 'premium-business-card', 'Our premium business cards are printed on high-quality 350gsm silk card stock with a smooth matte finish. These cards are perfect for making a professional impression.', 24.99, TRUE, FALSE, FALSE, NULL),
(1, 'Standard Business Card', 'standard-business-card', 'High-quality standard business cards perfect for everyday use.', 19.99, FALSE, FALSE, FALSE, NULL),
(3, 'Event Flyer', 'event-flyer', 'Professional event flyers to promote your events.', 19.99, FALSE, TRUE, FALSE, NULL),
(2, 'Event Banner', 'event-banner', 'Large format banners perfect for events and promotions.', 59.99, FALSE, FALSE, FALSE, NULL),
(2, 'Promotional Banner', 'promotional-banner', 'Eye-catching promotional banners for your business.', 49.99, FALSE, FALSE, FALSE, NULL),
(3, 'Promotional Flyer', 'promotional-flyer', 'Marketing flyers to boost your promotional campaigns.', 29.99, FALSE, FALSE, FALSE, NULL),
(4, 'Custom Mug Design', 'custom-mug-design', 'Personalized mug designs for gifts or branding.', 14.99, FALSE, FALSE, FALSE, NULL),
(4, 'Custom T-Shirt Design', 'custom-t-shirt-design', 'Custom t-shirt designs for events or personal use.', 29.99, FALSE, FALSE, TRUE, 29.99);

-- Insert product options for Premium Business Card
INSERT INTO product_options (product_id, option_type, option_name, option_value, price_modifier, is_default) VALUES
-- Sizes
(1, 'size', 'Standard', '3.5" x 2"', 0, TRUE),
(1, 'size', 'Square', '2.5" x 2.5"', 5.00, FALSE),
(1, 'size', 'Folded', '3.5" x 4"', 10.00, FALSE),
-- Colors
(1, 'color', 'White', '#FFFFFF', 0, TRUE),
(1, 'color', 'Cream', '#F5F5DC', 2.00, FALSE),
(1, 'color', 'Gray', '#808080', 2.00, FALSE),
-- Finishes
(1, 'finish', 'Matte', 'matte', 0, TRUE),
(1, 'finish', 'Gloss', 'gloss', 3.00, FALSE);