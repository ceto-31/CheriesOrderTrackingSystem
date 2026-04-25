-- DineClick Database Schema
-- Run this SQL file to create the database structure

CREATE DATABASE IF NOT EXISTS dineclick_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dineclick_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_admin (is_admin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_category (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT(11),
    image VARCHAR(255),
    stock INT(11) DEFAULT 0,
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_available (is_available)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_fee DECIMAL(10,2) DEFAULT 50.00,
    payment_method VARCHAR(50) NOT NULL,
    delivery_address TEXT NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    order_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT(11) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cart table (for database-based cart)
CREATE TABLE IF NOT EXISTS cart (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id),
    INDEX idx_user_cart (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_notif (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin account (password: admin123)
-- Note: If you can't login, visit: http://localhost/DineClick/generate_password_hash.php for a fix
INSERT INTO users (first_name, last_name, email, password, is_admin) 
VALUES ('Admin', 'User', 'admin@dineclick.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFSy6w5b3qBXhVvLdCxBr4Jh8G3oCBmO', 1);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Burgers', 'Juicy grilled burgers with premium ingredients'),
('Pasta', 'Italian pasta dishes with authentic sauces'),
('Pizza', 'Wood-fired pizzas with fresh toppings'),
('Salads', 'Fresh and healthy salad bowls'),
('Beverages', 'Refreshing drinks and coffee'),
('Desserts', 'Sweet treats and pastries');

-- Insert sample products
INSERT INTO products (name, description, price, category_id, stock, image) VALUES
('Classic Beef Burger', 'Juicy grilled patty with cheese and lettuce', 180.00, 1, 50, 'burger.jpg'),
('Creamy Carbonara', 'Rich cream sauce with bacon and parmesan', 220.00, 2, 30, 'carbonara.jpg'),
('Margherita Pizza', 'Classic tomato, mozzarella, and basil topping', 300.00, 3, 20, 'pizza.jpg'),
('Fresh Garden Salad', 'Crisp veggies with house-made dressing', 150.00, 4, 40, 'salad.jpg'),
('Iced Coffee', 'Cold brew with creamy milk blend', 90.00, 5, 100, 'coffee.jpg'),
('Chocolate Lava Cake', 'Warm chocolate cake with molten center', 120.00, 6, 25, 'cake.jpg');
