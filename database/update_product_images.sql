-- Update sample product images to use actual uploaded files
-- Run this SQL in phpMyAdmin to fix 404 image errors

USE dineclick_db;

-- Update products to use existing images or NULL for placeholder
UPDATE products SET image = 'burgerrr.webp' WHERE name LIKE '%Burger%';
UPDATE products SET image = 'carmac.webp' WHERE name LIKE '%Coffee%' OR name LIKE '%Macchiato%';
UPDATE products SET image = 'pastaa.webp' WHERE name LIKE '%Pasta%' OR name LIKE '%Carbonara%';

-- Set other products to NULL so they use placeholder
UPDATE products SET image = NULL WHERE image IN ('cake.jpg', 'pizza.jpg', 'salad.jpg');

-- Or set all to use the existing images
-- UPDATE products SET image = 'burgerrr.webp' WHERE id = 1;
-- UPDATE products SET image = 'pastaa.webp' WHERE id = 2;
-- UPDATE products SET image = 'carmac.webp' WHERE id = 5;

-- Check results
SELECT id, name, image FROM products;
