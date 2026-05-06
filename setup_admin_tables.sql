-- Create site_settings table for admin settings
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL UNIQUE,
  `setting_value` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create categories table for category management
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  KEY `parent_id` (`parent_id`),
  FOREIGN KEY (`parent_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default site settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'ShopSphere'),
('site_email', 'admin@shopsphere.com'),
('site_phone', '+1 (555) 123-4567'),
('site_address', '123 E-commerce St, Digital City'),
('payment_methods', '["credit_card","paypal","bank_transfer","cash_on_delivery"]'),
('smtp_host', 'smtp.gmail.com'),
('smtp_port', '587'),
('smtp_username', ''),
('smtp_password', ''),
('admin_name', 'Admin User'),
('admin_email', 'admin@shopsphere.com'),
('admin_phone', '+1 (555) 123-4567')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Insert default categories
INSERT INTO `categories` (`name`, `description`, `parent_id`, `is_active`) VALUES
('Electronics', 'Electronic devices and gadgets', NULL, 1),
('Fashion', 'Clothing and accessories', NULL, 1),
('Beauty', 'Beauty and personal care products', NULL, 1),
('Grocery', 'Food and grocery items', NULL, 1),
('Home', 'Home and garden products', NULL, 1),
('Mobiles', 'Mobile phones and accessories', 1, 1),
('Laptops', 'Laptops and computers', 1, 1),
('Men', 'Men\'s clothing and accessories', 2, 1),
('Women', 'Women\'s clothing and accessories', 2, 1),
('Skincare', 'Skincare products', 3, 1),
('Makeup', 'Makeup and cosmetics', 3, 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Update existing tables if needed
-- Add any missing columns to existing tables

-- Add tracking_number column to orders table if it doesn't exist
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `tracking_number` varchar(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `payment_method` varchar(50) DEFAULT 'cash_on_delivery';

-- Add more product fields if needed
ALTER TABLE `products` 
ADD COLUMN IF NOT EXISTS `brand` varchar(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `subcategory` varchar(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `is_active` tinyint(1) DEFAULT 1;

-- Add user activity tracking if needed
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `last_login` timestamp NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `is_active` tinyint(1) DEFAULT 1;