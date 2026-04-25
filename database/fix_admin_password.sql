-- Fix Admin Password
-- This SQL file fixes the admin account password issue
-- Password will be: admin123

USE dineclick_db;

-- Method 1: Update existing admin account (RECOMMENDED)
-- This will reset the admin password to: admin123
UPDATE users 
SET password = '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFSy6w5b3qBXhVvLdCxBr4Jh8G3oCBmO'
WHERE email = 'admin@dineclick.com';

-- Verify the update worked:
SELECT id, email, first_name, is_admin FROM users WHERE email = 'admin@dineclick.com';

-- Method 2: Delete and recreate admin account (if Method 1 doesn't work)
-- Uncomment the lines below ONLY if Method 1 fails:
-- DELETE FROM users WHERE email = 'admin@dineclick.com';
-- INSERT INTO users (first_name, last_name, email, password, is_admin) 
-- VALUES ('Admin', 'User', 'admin@dineclick.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFSy6w5b3qBXhVvLdCxBr4Jh8G3oCBmO', 1);

-- After running this SQL:
-- 1. Go to: http://localhost/DineClick/login.php
-- 2. Email: admin@dineclick.com
-- 3. Password: admin123
-- 4. You should now be able to login!
