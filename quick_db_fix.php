<?php
// Quick database fix for missing tables
$conn = mysqli_connect("localhost", "root", "", "shopsphere_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Creating missing database tables...<br><br>";

// Create site_settings table
$sql_site_settings = "CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL UNIQUE,
  `setting_value` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $sql_site_settings)) {
    echo "✓ site_settings table created successfully<br>";
} else {
    echo "✗ Error creating site_settings table: " . mysqli_error($conn) . "<br>";
}

// Create categories table
$sql_categories = "CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $sql_categories)) {
    echo "✓ categories table created successfully<br>";
} else {
    echo "✗ Error creating categories table: " . mysqli_error($conn) . "<br>";
}

// Insert default settings
$default_settings = [
    ['site_name', 'ShopSphere'],
    ['site_email', 'admin@shopsphere.com'],
    ['site_phone', '+1 (555) 123-4567'],
    ['site_address', '123 E-commerce St, Digital City'],
    ['payment_methods', '["credit_card","paypal","bank_transfer","cash_on_delivery"]'],
    ['admin_name', 'Admin User'],
    ['admin_email', 'admin@shopsphere.com'],
    ['admin_phone', '+1 (555) 123-4567']
];

foreach ($default_settings as $setting) {
    $key = mysqli_real_escape_string($conn, $setting[0]);
    $value = mysqli_real_escape_string($conn, $setting[1]);
    
    $sql = "INSERT INTO site_settings (setting_key, setting_value) VALUES ('$key', '$value') 
            ON DUPLICATE KEY UPDATE setting_value = '$value'";
    
    if (mysqli_query($conn, $sql)) {
        echo "✓ Setting '$key' inserted/updated<br>";
    } else {
        echo "✗ Error with setting '$key': " . mysqli_error($conn) . "<br>";
    }
}

// Insert default categories
$default_categories = [
    ['Electronics', 'Electronic devices and gadgets', null],
    ['Fashion', 'Clothing and accessories', null],
    ['Beauty', 'Beauty and personal care products', null],
    ['Grocery', 'Food and grocery items', null],
    ['Home', 'Home and garden products', null]
];

foreach ($default_categories as $category) {
    $name = mysqli_real_escape_string($conn, $category[0]);
    $desc = mysqli_real_escape_string($conn, $category[1]);
    
    $sql = "INSERT INTO categories (name, description, parent_id, is_active) VALUES ('$name', '$desc', NULL, 1) 
            ON DUPLICATE KEY UPDATE description = '$desc'";
    
    if (mysqli_query($conn, $sql)) {
        echo "✓ Category '$name' inserted/updated<br>";
    } else {
        echo "✗ Error with category '$name': " . mysqli_error($conn) . "<br>";
    }
}

// Add missing columns to existing tables
$alter_queries = [
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS tracking_number varchar(255) DEFAULT NULL",
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method varchar(50) DEFAULT 'cash_on_delivery'",
    "ALTER TABLE products ADD COLUMN IF NOT EXISTS brand varchar(255) DEFAULT NULL",
    "ALTER TABLE products ADD COLUMN IF NOT EXISTS subcategory varchar(255) DEFAULT NULL",
    "ALTER TABLE products ADD COLUMN IF NOT EXISTS is_active tinyint(1) DEFAULT 1",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login timestamp NULL DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS is_active tinyint(1) DEFAULT 1"
];

foreach ($alter_queries as $query) {
    if (mysqli_query($conn, $query)) {
        echo "✓ Table column updated successfully<br>";
    } else {
        // Ignore column already exists errors
        if (strpos(mysqli_error($conn), 'Duplicate column name') === false) {
            echo "✗ Error updating table: " . mysqli_error($conn) . "<br>";
        }
    }
}

echo "<br><strong>Database setup completed!</strong><br>";
echo "<a href='admin/php/dashboard.php'>Go to Admin Dashboard</a>";

mysqli_close($conn);
?>