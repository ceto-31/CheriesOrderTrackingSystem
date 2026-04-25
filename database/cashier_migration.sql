-- ============================================================
-- cashier_migration.sql
-- Run once to add cashier-module columns to the orders table
-- and seed the cashier + walk-in users.
-- ============================================================

USE food_ordering_db;

-- ── 1. Make delivery columns nullable so cashier in-store orders work ──────
ALTER TABLE orders
  MODIFY COLUMN delivery_address TEXT          DEFAULT NULL,
  MODIFY COLUMN contact_number   VARCHAR(20)   DEFAULT NULL;

-- ── 2. Add cashier-tracking columns ────────────────────────────────────────
-- Run each ALTER separately; skip if column already exists.
ALTER TABLE orders ADD COLUMN created_by    INT          NULL           AFTER user_id;
ALTER TABLE orders ADD COLUMN order_source  ENUM('online','cashier') NOT NULL DEFAULT 'online' AFTER created_by;
ALTER TABLE orders ADD COLUMN customer_name VARCHAR(100) NULL           AFTER order_source;

-- ── 3. Foreign key: created_by → users.id ──────────────────────────────────
-- Drop first if it already exists (idempotent)
SET @fk_exists = (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = 'food_ordering_db'
    AND TABLE_NAME         = 'orders'
    AND CONSTRAINT_NAME    = 'fk_ord_creator'
);
SET @sql = IF(@fk_exists = 0,
  'ALTER TABLE orders ADD CONSTRAINT fk_ord_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ── 4. Index on order_source for fast cashier history queries ───────────────
CREATE INDEX idx_order_source ON orders (order_source, created_by, created_at);

-- ── 5. Seed: Walk-in placeholder customer (role=0) ─────────────────────────
-- cashier orders reference this user_id
INSERT IGNORE INTO users (first_name, last_name, email, password, role, is_admin)
VALUES ('Walk-in', 'Customer', 'walkin@cheries.com',
        '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFSy6w5b3qBXhVvLdCxBr4Jh8G3oCBmO',
        0, 0);

-- ── 6. Seed: Default cashier account (role=2, password: cashier123) ─────────
-- Hash for 'cashier123':
-- $2y$10$... run: php -r "echo password_hash('cashier123', PASSWORD_DEFAULT);"
-- Placeholder uses same hash as admin123 — reset via db_check if needed.
INSERT IGNORE INTO users (first_name, last_name, email, password, role, is_admin)
VALUES ('Cashier', 'Staff', 'cashier@cheries.com',
        '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFSy6w5b3qBXhVvLdCxBr4Jh8G3oCBmO',
        2, 0);
