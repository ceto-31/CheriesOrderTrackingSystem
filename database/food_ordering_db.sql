-- ============================================================
-- food_ordering_db  –  Single-database schema
-- Food Ordering & Order Tracking System
-- Engine : InnoDB  |  Charset : utf8mb4_unicode_ci
-- ============================================================

CREATE DATABASE IF NOT EXISTS food_ordering_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE food_ordering_db;

-- ------------------------------------------------------------
-- users  (role column: 0 = customer, 1 = admin)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT          NOT NULL AUTO_INCREMENT,
    first_name  VARCHAR(50)  NOT NULL,
    last_name   VARCHAR(50)  NOT NULL,
    email       VARCHAR(100) NOT NULL,
    password    VARCHAR(255) NOT NULL,
    phone       VARCHAR(20)  DEFAULT NULL,
    address     TEXT         DEFAULT NULL,
    role        TINYINT(1)   NOT NULL DEFAULT 0  COMMENT '0=customer 1=admin',
    is_admin    TINYINT(1)   NOT NULL DEFAULT 0  COMMENT 'alias kept for compatibility',
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY  uq_email  (email),
    INDEX       idx_role  (role),
    INDEX       idx_admin (is_admin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- categories
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id          INT          NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    description TEXT         DEFAULT NULL,
    icon        VARCHAR(10)  DEFAULT '🍽️' COMMENT 'emoji for UI',
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cat_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- products
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id           INT            NOT NULL AUTO_INCREMENT,
    name         VARCHAR(200)   NOT NULL,
    description  TEXT           DEFAULT NULL,
    price        DECIMAL(10,2)  NOT NULL,
    category_id  INT            DEFAULT NULL,
    image        VARCHAR(255)   DEFAULT NULL,
    stock        INT            NOT NULL DEFAULT 0,
    low_stock_threshold INT     NOT NULL DEFAULT 5 COMMENT 'alert when stock <= this',
    is_available TINYINT(1)     NOT NULL DEFAULT 1,
    created_at   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_prod_cat FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_cat       (category_id),
    INDEX idx_available (is_available),
    INDEX idx_stock     (stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- orders
-- status flow: Pending → Confirmed → Preparing → Out for Delivery → Delivered
--              any → Cancelled
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id               INT            NOT NULL AUTO_INCREMENT,
    user_id          INT            NOT NULL,
    total_amount     DECIMAL(10,2)  NOT NULL,
    delivery_fee     DECIMAL(10,2)  NOT NULL DEFAULT 50.00,
    payment_method   VARCHAR(50)    NOT NULL,
    delivery_address TEXT           NOT NULL,
    contact_number   VARCHAR(20)    NOT NULL,
    status           ENUM(
        'Pending',
        'Confirmed',
        'Preparing',
        'Out for Delivery',
        'Delivered',
        'Cancelled'
    ) NOT NULL DEFAULT 'Pending',
    notes       TEXT      DEFAULT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_ord_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_orders (user_id),
    INDEX idx_status      (status),
    INDEX idx_created_at  (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- order_items
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id           INT            NOT NULL AUTO_INCREMENT,
    order_id     INT            NOT NULL,
    product_id   INT            NOT NULL,
    product_name VARCHAR(200)   NOT NULL,
    price        DECIMAL(10,2)  NOT NULL,
    quantity     INT            NOT NULL,
    subtotal     DECIMAL(10,2)  NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_item_ord  FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    CONSTRAINT fk_item_prod FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- cart  (session-style per-user cart stored in DB)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cart (
    id         INT       NOT NULL AUTO_INCREMENT,
    user_id    INT       NOT NULL,
    product_id INT       NOT NULL,
    quantity   INT       NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cart_item (user_id, product_id),
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_cart_prod FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user_cart (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- notifications
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id         INT          NOT NULL AUTO_INCREMENT,
    user_id    INT          NOT NULL,
    title      VARCHAR(200) NOT NULL,
    message    TEXT         NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_notif (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Seed data
-- ============================================================

-- Default admin  (password: admin123)
INSERT IGNORE INTO users (first_name, last_name, email, password, role, is_admin)
VALUES ('Admin', 'Cheries',
        'admin@cheries.com',
        '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFSy6w5b3qBXhVvLdCxBr4Jh8G3oCBmO',
        1, 1);

-- Categories
INSERT IGNORE INTO categories (name, description, icon) VALUES
('Burgers',   'Juicy grilled burgers with premium ingredients', '🍔'),
('Pasta',     'Italian pasta dishes with authentic sauces',     '🍝'),
('Pizza',     'Wood-fired pizzas with fresh toppings',          '🍕'),
('Salads',    'Fresh and healthy salad bowls',                  '🥗'),
('Beverages', 'Refreshing drinks and coffee',                   '☕'),
('Desserts',  'Sweet treats and pastries',                      '🍰');

-- Products
INSERT IGNORE INTO products (name, description, price, category_id, stock, image) VALUES
('Classic Beef Burger',  'Juicy grilled patty with cheese and lettuce', 180.00, 1, 50, 'burger.jpg'),
('BBQ Bacon Burger',     'Smoky BBQ sauce with crispy bacon strips',    210.00, 1, 40, 'bbq_burger.jpg'),
('Creamy Carbonara',     'Rich cream sauce with bacon and parmesan',    220.00, 2, 30, 'carbonara.jpg'),
('Pesto Pasta',          'Basil pesto with cherry tomatoes',            200.00, 2, 25, 'pesto.jpg'),
('Margherita Pizza',     'Classic tomato, mozzarella, and basil',       300.00, 3, 20, 'pizza.jpg'),
('Pepperoni Pizza',      'Loaded with pepperoni and mozzarella',        330.00, 3, 18, 'pepperoni.jpg'),
('Fresh Garden Salad',   'Crisp veggies with house-made dressing',      150.00, 4, 40, 'salad.jpg'),
('Caesar Salad',         'Romaine lettuce, croutons, parmesan dressing',170.00, 4, 35, 'caesar.jpg'),
('Iced Coffee',          'Cold brew with creamy milk blend',             90.00, 5, 100,'coffee.jpg'),
('Fresh Lemonade',       'Freshly squeezed lemon with honey',            75.00, 5,  80, 'lemonade.jpg'),
('Chocolate Lava Cake',  'Warm chocolate cake with molten center',      120.00, 6, 25, 'cake.jpg'),
('Mango Cheesecake',     'Creamy cheesecake with fresh mango topping',  130.00, 6, 20, 'cheesecake.jpg');
