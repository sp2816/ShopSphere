-- ShopSphere E-commerce Database Tables
-- Created: September 20, 2025
-- This file contains the SQL schema for the products, orders, and order_items tables

-- =============================================
-- PRODUCTS TABLE
-- =============================================
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    subcategory VARCHAR(100),
    brand VARCHAR(100),
    stock_quantity INT DEFAULT 0,
    image_url VARCHAR(500),
    image_alt VARCHAR(255),
    sku VARCHAR(100) UNIQUE,
    weight DECIMAL(8, 2),
    dimensions VARCHAR(100),
    color VARCHAR(50),
    size VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    featured BOOLEAN DEFAULT FALSE,
    discount_percentage DECIMAL(5, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_category (category),
    INDEX idx_name (name),
    INDEX idx_price (price),
    INDEX idx_featured (featured),
    INDEX idx_active (is_active)
);

-- =============================================
-- ORDERS TABLE
-- =============================================
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    tax_amount DECIMAL(10, 2) DEFAULT 0.00,
    shipping_cost DECIMAL(10, 2) DEFAULT 0.00,
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    final_amount DECIMAL(10, 2) NOT NULL,
    order_status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT NOT NULL,
    billing_address TEXT,
    customer_notes TEXT,
    admin_notes TEXT,
    estimated_delivery DATE,
    actual_delivery_date DATE,
    tracking_number VARCHAR(100),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_order_status (order_status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_order_date (order_date)
);

-- =============================================
-- ORDER ITEMS TABLE (Junction table for orders and products)
-- =============================================
CREATE TABLE order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    product_name VARCHAR(255) NOT NULL, -- Store product name at time of order
    product_sku VARCHAR(100), -- Store SKU at time of order
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id),
    
    -- Ensure no duplicate product in same order
    UNIQUE KEY unique_order_product (order_id, product_id)
);

-- =============================================
-- SAMPLE DATA INSERTS
-- =============================================

-- Sample Products Data
INSERT INTO products (name, description, price, category, subcategory, brand, stock_quantity, image_url, sku, featured) VALUES
-- Electronics - Mobiles
('iPhone 15 Pro Max', 'Latest iPhone with A17 Pro chip, titanium design, and advanced camera system', 129900.00, 'Electronics', 'Mobiles', 'Apple', 25, 'images/mobile1.jpg', 'IPH15PM256', TRUE),
('Samsung Galaxy S24 Ultra', 'Premium Android phone with S Pen, 200MP camera, and AI features', 119900.00, 'Electronics', 'Mobiles', 'Samsung', 30, 'images/mobile2.jpg', 'SGS24U512', TRUE),
('OnePlus 12', 'Flagship killer with Snapdragon 8 Gen 3 and 100W fast charging', 64999.00, 'Electronics', 'Mobiles', 'OnePlus', 40, 'images/mobile3.jpg', 'OP12256GB', FALSE),
('Google Pixel 8 Pro', 'AI-powered photography and pure Android experience', 84999.00, 'Electronics', 'Mobiles', 'Google', 20, 'images/mobile4.jpg', 'GP8P128GB', TRUE),

-- Fashion - Men
('Levi\'s 501 Original Jeans', 'Classic straight-fit jeans in premium denim', 4999.00, 'Fashion', 'Men', 'Levi\'s', 50, 'images/men1.jpg', 'LV501BL32', FALSE),
('Nike Air Force 1', 'Iconic basketball shoes with premium leather upper', 8495.00, 'Fashion', 'Men', 'Nike', 35, 'images/men2.jpg', 'NAF1WHT42', TRUE),
('Tommy Hilfiger Polo Shirt', 'Classic fit polo shirt in 100% cotton', 2999.00, 'Fashion', 'Men', 'Tommy Hilfiger', 60, 'images/men3.jpg', 'THPOLOM', FALSE),

-- Fashion - Women
('Zara Floral Dress', 'Elegant floral print midi dress perfect for any occasion', 3999.00, 'Fashion', 'Women', 'Zara', 25, 'images/women1.jpg', 'ZRFLDRS', TRUE),
('H&M Denim Jacket', 'Classic blue denim jacket with vintage wash', 2499.00, 'Fashion', 'Women', 'H&M', 40, 'images/women2.jpg', 'HMDJKT', FALSE),
('Adidas Ultraboost 22', 'Women\'s running shoes with responsive cushioning', 16999.00, 'Fashion', 'Women', 'Adidas', 30, 'images/women3.jpg', 'ADUB22W', TRUE),

-- Beauty
('Lakme 9to5 Lipstick', 'Long-lasting matte lipstick in vibrant shades', 625.00, 'Beauty', 'Makeup', 'Lakme', 100, 'images/lipstick.jpg', 'LK9T5LP', FALSE),
('Mamaearth Face Wash', 'Natural face wash with neem and tea tree oil', 399.00, 'Beauty', 'Skincare', 'Mamaearth', 80, 'images/beauty1.jpg', 'MEFW100ML', TRUE),
('Plum Body Lotion', 'Moisturizing body lotion with shea butter', 649.00, 'Beauty', 'Body Care', 'Plum', 60, 'images/beauty2.jpg', 'PLBL250ML', FALSE),

-- Grocery
('Basmati Rice 5kg', 'Premium quality aged basmati rice', 899.00, 'Grocery', 'Staples', 'India Gate', 200, 'images/grocery1.jpg', 'IGBR5KG', FALSE),
('Amul Butter 500g', 'Fresh white butter made from pure cream', 285.00, 'Grocery', 'Dairy', 'Amul', 150, 'images/grocery2.jpg', 'AMBT500G', TRUE),
('Britannia Good Day Cookies', 'Delicious butter cookies perfect for tea time', 45.00, 'Grocery', 'Snacks', 'Britannia', 300, 'images/grocery3.jpg', 'BRGD100G', FALSE);

-- Sample Orders Data
INSERT INTO orders (user_id, order_number, total_amount, tax_amount, shipping_cost, final_amount, order_status, payment_status, payment_method, shipping_address) VALUES
(1, 'ORD-2025-001', 129900.00, 23382.00, 0.00, 153282.00, 'delivered', 'paid', 'Credit Card', '123 Main Street, Mumbai, Maharashtra 400001'),
(2, 'ORD-2025-002', 4999.00, 899.82, 99.00, 5997.82, 'shipped', 'paid', 'UPI', '456 Park Avenue, Delhi, Delhi 110001'),
(1, 'ORD-2025-003', 8495.00, 1529.10, 149.00, 10173.10, 'processing', 'paid', 'Debit Card', '123 Main Street, Mumbai, Maharashtra 400001'),
(3, 'ORD-2025-004', 3999.00, 719.82, 99.00, 4817.82, 'confirmed', 'paid', 'Cash on Delivery', '789 Lake View, Bangalore, Karnataka 560001'),
(2, 'ORD-2025-005', 1570.00, 282.60, 99.00, 1951.60, 'pending', 'pending', 'UPI', '456 Park Avenue, Delhi, Delhi 110001');

-- Sample Order Items Data
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price, product_name, product_sku) VALUES
-- Order 1: iPhone 15 Pro Max
(1, 1, 1, 129900.00, 129900.00, 'iPhone 15 Pro Max', 'IPH15PM256'),

-- Order 2: Levi's Jeans
(2, 5, 1, 4999.00, 4999.00, 'Levi\'s 501 Original Jeans', 'LV501BL32'),

-- Order 3: Nike Shoes
(3, 6, 1, 8495.00, 8495.00, 'Nike Air Force 1', 'NAF1WHT42'),

-- Order 4: Zara Dress
(4, 8, 1, 3999.00, 3999.00, 'Zara Floral Dress', 'ZRFLDRS'),

-- Order 5: Multiple items (Beauty products)
(5, 11, 2, 625.00, 1250.00, 'Lakme 9to5 Lipstick', 'LK9T5LP'),
(5, 13, 1, 649.00, 649.00, 'Plum Body Lotion', 'PLBL250ML');

-- =============================================
-- USEFUL QUERIES FOR DASHBOARD
-- =============================================

-- Query to get total products count
-- SELECT COUNT(*) as total_products FROM products WHERE is_active = TRUE;

-- Query to get total orders count
-- SELECT COUNT(*) as total_orders FROM orders;

-- Query to get monthly revenue
-- SELECT SUM(final_amount) as monthly_revenue FROM orders WHERE MONTH(order_date) = MONTH(CURRENT_DATE()) AND YEAR(order_date) = YEAR(CURRENT_DATE());

-- Query to get recent orders with user details
-- SELECT o.order_number, u.full_name, o.final_amount, o.order_status, o.order_date 
-- FROM orders o 
-- JOIN users u ON o.user_id = u.id 
-- ORDER BY o.order_date DESC LIMIT 10;

-- Query to get top selling products
-- SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.total_price) as total_revenue
-- FROM products p 
-- JOIN order_items oi ON p.product_id = oi.product_id
-- JOIN orders o ON oi.order_id = o.order_id
-- WHERE o.order_status IN ('delivered', 'shipped')
-- GROUP BY p.product_id 
-- ORDER BY total_sold DESC LIMIT 10;

-- Query to get low stock products
-- SELECT name, stock_quantity FROM products WHERE stock_quantity < 10 AND is_active = TRUE ORDER BY stock_quantity ASC;