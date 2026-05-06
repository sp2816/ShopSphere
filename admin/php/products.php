<?php
$conn = mysqli_connect("localhost","root","","shopsphere_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                $price = floatval($_POST['price']);
                $category = mysqli_real_escape_string($conn, $_POST['category']);
                $subcategory = mysqli_real_escape_string($conn, $_POST['subcategory']);
                $brand = mysqli_real_escape_string($conn, $_POST['brand']);
                $stock = intval($_POST['stock_quantity']);
                $sku = mysqli_real_escape_string($conn, $_POST['sku']);
                
                $sql = "INSERT INTO products (name, description, price, category, subcategory, brand, stock_quantity, sku, is_active) 
                        VALUES ('$name', '$description', $price, '$category', '$subcategory', '$brand', $stock, '$sku', 1)";
                
                if (mysqli_query($conn, $sql)) {
                    $success_message = "Product added successfully!";
                } else {
                    $error_message = "Error adding product: " . mysqli_error($conn);
                }
                break;
                
            case 'delete':
                $product_id = intval($_POST['product_id']);
                $sql = "DELETE FROM products WHERE product_id = $product_id";
                
                if (mysqli_query($conn, $sql)) {
                    $success_message = "Product deleted successfully!";
                } else {
                    $error_message = "Error deleting product: " . mysqli_error($conn);
                }
                break;
                
            case 'toggle_status':
                $product_id = intval($_POST['product_id']);
                $sql = "UPDATE products SET is_active = !is_active WHERE product_id = $product_id";
                
                if (mysqli_query($conn, $sql)) {
                    $success_message = "Product status updated successfully!";
                } else {
                    $error_message = "Error updating product status: " . mysqli_error($conn);
                }
                break;
                
            case 'bulk_action':
                if (isset($_POST['selected_products']) && isset($_POST['bulk_operation'])) {
                    $product_ids = $_POST['selected_products'];
                    $operation = $_POST['bulk_operation'];
                    $updated_count = 0;
                    
                    foreach ($product_ids as $product_id) {
                        $product_id = intval($product_id);
                        $sql = "";
                        
                        switch ($operation) {
                            case 'activate':
                                $sql = "UPDATE products SET is_active = 1 WHERE product_id = $product_id";
                                break;
                            case 'deactivate':
                                $sql = "UPDATE products SET is_active = 0 WHERE product_id = $product_id";
                                break;
                            case 'delete':
                                $sql = "DELETE FROM products WHERE product_id = $product_id";
                                break;
                            case 'update_category':
                                if (isset($_POST['new_category'])) {
                                    $new_category = mysqli_real_escape_string($conn, $_POST['new_category']);
                                    $sql = "UPDATE products SET category = '$new_category' WHERE product_id = $product_id";
                                }
                                break;
                        }
                        
                        if ($sql && mysqli_query($conn, $sql)) {
                            $updated_count++;
                        }
                    }
                    
                    $action_name = ucfirst(str_replace('_', ' ', $operation));
                    $success_message = "Successfully applied '$action_name' to $updated_count products!";
                }
                break;
        }
    }
}

// Get products with search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

$sql = "SELECT * FROM products WHERE 1=1";
if ($search) {
    $sql .= " AND (name LIKE '%$search%' OR brand LIKE '%$search%' OR sku LIKE '%$search%')";
}
if ($category_filter) {
    $sql .= " AND category = '$category_filter'";
}
$sql .= " ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);

// Get categories for filter
$categories_sql = "SELECT DISTINCT category FROM products ORDER BY category";
$categories_result = mysqli_query($conn, $categories_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Products Management</title>
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/adminProducts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            background: #f5f7fa !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .products-container {
            max-width: 100% !important;
            margin: 40px 0 0 0 !important;
            padding: 30px !important;
            background: transparent !important;
        }
        .products-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 30px !important;
            background: white !important;
            padding: 20px !important;
            border-radius: 10px !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
        }
        .search-filters {
            display: flex !important;
            gap: 15px !important;
            align-items: center !important;
        }
        .search-input, .filter-select {
            padding: 10px !important;
            border: 1px solid #ddd !important;
            border-radius: 5px !important;
            font-size: 14px !important;
        }
        .btn {
            padding: 10px 20px !important;
            border: none !important;
            border-radius: 5px !important;
            cursor: pointer !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            display: inline-block !important;
            transition: all 0.3s ease !important;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
        }
        .btn-success {
            background: #28a745 !important;
            color: white !important;
        }
        .btn-danger {
            background: #dc3545 !important;
            color: white !important;
        }
        .btn-warning {
            background: #ffc107 !important;
            color: #333 !important;
        }
        .products-table {
            background: white !important;
            border-radius: 10px !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
            overflow: hidden !important;
        }
        .table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        .table th, .table td {
            padding: 15px !important;
            text-align: left !important;
            border-bottom: 1px solid #eee !important;
        }
        .table th {
            background: #f8f9fa !important;
            font-weight: 600 !important;
            color: #333 !important;
        }
        .product-actions {
            display: flex !important;
            gap: 5px !important;
        }
        .status-badge {
            padding: 5px 10px !important;
            border-radius: 15px !important;
            font-size: 12px !important;
            font-weight: 500 !important;
        }
        .status-active {
            background: #d4edda !important;
            color: #155724 !important;
        }
        .status-inactive {
            background: #f8d7da !important;
            color: #721c24 !important;
        }
        .add-product-form {
            background: white !important;
            padding: 25px !important;
            border-radius: 10px !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
            margin-bottom: 30px !important;
            display: none !important;
        }
        .form-row {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
            gap: 15px !important;
            margin-bottom: 15px !important;
        }
        .form-group {
            display: flex !important;
            flex-direction: column !important;
        }
        .form-group label {
            margin-bottom: 5px !important;
            font-weight: 500 !important;
            color: #333 !important;
        }
        .form-group input, .form-group textarea, .form-group select {
            padding: 10px !important;
            border: 1px solid #ddd !important;
            border-radius: 5px !important;
            font-size: 14px !important;
        }
        .alert {
            padding: 15px !important;
            border-radius: 5px !important;
            margin-bottom: 20px !important;
        }
        .alert-success {
            background: #d4edda !important;
            color: #155724 !important;
            border: 1px solid #c3e6cb !important;
        }
        .alert-danger {
            background: #f8d7da !important;
            color: #721c24 !important;
            border: 1px solid #f5c6cb !important;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: none;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            position: relative;
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px !important;
            font-weight: bold !important;
            cursor: pointer !important;
            position: absolute !important;
            top: 15px !important;
            right: 20px !important;
        }
        
        .close:hover,
        .close:focus {
            color: #000 !important;
            text-decoration: none !important;
        }
        
        /* Form Styles for Modals */
        .modal .form-row {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
            gap: 15px !important;
            margin-bottom: 15px !important;
        }
        
        .modal .form-group {
            display: flex !important;
            flex-direction: column !important;
        }
        
        .modal .form-group label {
            margin-bottom: 5px !important;
            font-weight: 500 !important;
            color: #333 !important;
        }
        
        .modal .form-group input, 
        .modal .form-group textarea, 
        .modal .form-group select {
            padding: 10px !important;
            border: 1px solid #ddd !important;
            border-radius: 5px !important;
            font-size: 14px !important;
        }
    </style>
</head>
<body>
    <!-- Include Admin Navbar -->
    <header class="main-header">
        <div class="logo">ShopSphere<span class="plus">Admin</span></div>
        <nav style="flex:1;">
            <ul class="admin-nav-items">
                <li><a class="link" href="dashboard.php">Dashboard</a></li>
                <li><a class="link" href="products.php" style="background: #f1f3f6; color: #2874f0;">Products</a></li>
                <li><a class="link" href="orders.php">Orders</a></li>
                <li><a class="link" href="users.php">Users</a></li>
            </ul>
        </nav>
        <div style="display: flex; align-items: center; gap: 20px; position: relative;">
            <div class="dropdown" style="position: relative;">
                <img src="../../images/men1.jpg" alt="Admin" id="userImg" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #2874f0; cursor:pointer;">
                <div class="dropdown-content right" id="userDropdown" style="right:0; min-width:140px;">
                    <a href="profile.php">Profile</a>
                    <a href="settings.php">Settings</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="products-container">
        <!-- Page Header -->
        <div class="products-header">
            <div>
                <h1><i class="fas fa-box"></i> Products Management</h1>
                <p>Manage your product catalog</p>
            </div>
            <div class="search-filters">
                <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="search" class="search-input" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="category" class="filter-select">
                        <option value="">All Categories</option>
                        <?php 
                        if ($categories_result) {
                            while ($cat = mysqli_fetch_assoc($categories_result)) {
                                $selected = ($category_filter == $cat['category']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($cat['category']) . "' $selected>" . htmlspecialchars($cat['category']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                </form>
                <button onclick="toggleAddForm()" class="btn btn-success"><i class="fas fa-plus"></i> Add Product</button>
                <button onclick="exportProducts()" class="btn btn-info"><i class="fas fa-download"></i> Export CSV</button>
                <button onclick="showBulkActions()" class="btn btn-warning"><i class="fas fa-tasks"></i> Bulk Actions</button>
                <button onclick="showStockAlert()" class="btn btn-danger"><i class="fas fa-exclamation-triangle"></i> Low Stock</button>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Products Table -->
        <div class="products-table">
            <table class="table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllProducts" onclick="toggleSelectAllProducts()"> Select</th>
                        <th>Product ID</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($product = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_products[]" value="<?php echo $product['product_id']; ?>">
                                </td>
                                <td>#<?php echo $product['product_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    <?php if ($product['description']): ?>
                                        <br><small style="color: #666;"><?php echo substr(htmlspecialchars($product['description']), 0, 50) . '...'; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($product['category']); ?>
                                    <?php if ($product['subcategory']): ?>
                                        <br><small><?php echo htmlspecialchars($product['subcategory']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['brand']); ?></td>
                                <td>₹<?php echo number_format($product['price'], 2); ?></td>
                                <td>
                                    <span style="color: <?php echo $product['stock_quantity'] < 10 ? '#dc3545' : '#28a745'; ?>">
                                        <?php echo $product['stock_quantity']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $product['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="product-actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                            <button type="submit" class="btn <?php echo $product['is_active'] ? 'btn-warning' : 'btn-success'; ?>" 
                                                    onclick="return confirm('Are you sure you want to <?php echo $product['is_active'] ? 'deactivate' : 'activate'; ?> this product?')">
                                                <i class="fas fa-<?php echo $product['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                            <button type="submit" class="btn btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: #666;">
                                <i class="fas fa-box" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                                <br>No products found. Add your first product to get started!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleAddForm() {
            console.log('toggleAddForm called');
            // Create modal for add product form
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.id = 'addProductModal';
            modal.style.display = 'block';
            console.log('Add product modal created and set to display');
            
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 800px;">
                    <span class="close" onclick="closeAddProductModal()">&times;</span>
                    <h2><i class="fas fa-plus"></i> Add New Product</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Product Name *</label>
                                <input type="text" name="name" required>
                            </div>
                            <div class="form-group">
                                <label>SKU *</label>
                                <input type="text" name="sku" required>
                            </div>
                            <div class="form-group">
                                <label>Price *</label>
                                <input type="number" name="price" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label>Stock Quantity *</label>
                                <input type="number" name="stock_quantity" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Category *</label>
                                <select name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Electronics">Electronics</option>
                                    <option value="Fashion">Fashion</option>
                                    <option value="Beauty">Beauty</option>
                                    <option value="Grocery">Grocery</option>
                                    <option value="Home">Home</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Subcategory</label>
                                <input type="text" name="subcategory">
                            </div>
                            <div class="form-group">
                                <label>Brand</label>
                                <input type="text" name="brand">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" rows="3"></textarea>
                        </div>
                        <div style="display: flex; gap: 10px; margin-top: 20px;">
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Product</button>
                            <button type="button" onclick="closeAddProductModal()" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</button>
                        </div>
                    </form>
                </div>
            `;
            
            document.body.appendChild(modal);
            console.log('Add product modal added to document');
        }

        function closeAddProductModal() {
            const modal = document.getElementById('addProductModal');
            if (modal) {
                modal.remove();
            }
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        // Navbar dropdown functionality
        document.getElementById('userImg').addEventListener('click', function() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const userImg = document.getElementById('userImg');
            if (!userImg.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Logout confirmation
        document.addEventListener('DOMContentLoaded', function() {
            const logoutLinks = document.querySelectorAll('a[href*="logout.php"]');
            logoutLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to logout?')) {
                        window.location.href = this.href;
                    }
                });
            });
        });

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                const modals = ['addProductModal', 'bulkActionsModal'];
                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (modal && event.target === modal) {
                        modal.remove();
                    }
                });
            }
        }

        // Enhanced functionality functions
        function exportProducts() {
            // Create CSV export functionality
            const table = document.querySelector('.table');
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = [], cols = rows[i].querySelectorAll('td, th');
                for (let j = 0; j < cols.length - 1; j++) { // Exclude actions column
                    row.push(cols[j].innerText.replace(/,/g, ';'));
                }
                csv.push(row.join(','));
            }
            
            const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
            const downloadLink = document.createElement('a');
            downloadLink.download = 'products_' + new Date().toISOString().split('T')[0] + '.csv';
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }

        function showBulkActions() {
            console.log('showBulkActions called');
            const checkboxes = document.querySelectorAll('input[name="selected_products[]"]:checked');
            console.log('Found checked products:', checkboxes.length);
            
            if (checkboxes.length === 0) {
                alert('Please select products to apply bulk actions');
                return;
            }

            // Create bulk actions modal
            const bulkModal = document.createElement('div');
            bulkModal.className = 'modal';
            bulkModal.id = 'bulkActionsModal';
            bulkModal.style.display = 'block';
            console.log('Bulk actions modal created and set to display');
            
            bulkModal.innerHTML = `
                <div class="modal-content">
                    <span class="close" onclick="closeBulkModal()">&times;</span>
                    <h2>Bulk Actions (${checkboxes.length} products selected)</h2>
                    <form method="POST" onsubmit="return confirmBulkAction()">
                        <input type="hidden" name="action" value="bulk_action">
                        ${Array.from(checkboxes).map(cb => `<input type="hidden" name="selected_products[]" value="${cb.value}">`).join('')}
                        
                        <div class="form-group">
                            <label>Select Action:</label>
                            <select name="bulk_operation" id="bulkOperation" onchange="toggleCategoryField()" required>
                                <option value="">Choose action...</option>
                                <option value="activate">Activate Products</option>
                                <option value="deactivate">Deactivate Products</option>
                                <option value="update_category">Update Category</option>
                                <option value="delete" style="color: red;">Delete Products</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="categoryField" style="display: none;">
                            <label>New Category:</label>
                            <select name="new_category">
                                <option value="Electronics">Electronics</option>
                                <option value="Fashion">Fashion</option>
                                <option value="Beauty">Beauty</option>
                                <option value="Grocery">Grocery</option>
                                <option value="Home">Home</option>
                            </select>
                        </div>
                        
                        <div style="display: flex; gap: 10px; margin-top: 20px;">
                            <button type="submit" class="btn btn-primary">Apply Action</button>
                            <button type="button" onclick="closeBulkModal()" class="btn btn-secondary">Cancel</button>
                        </div>
                    </form>
                </div>
            `;
            
            document.body.appendChild(bulkModal);
            console.log('Bulk actions modal added to document');
        }

        function toggleSelectAllProducts() {
            const selectAllCheckbox = document.getElementById('selectAllProducts');
            const productCheckboxes = document.querySelectorAll('input[name="selected_products[]"]');
            
            productCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        function closeBulkModal() {
            const modal = document.getElementById('bulkActionsModal');
            if (modal) {
                modal.remove();
            }
        }

        function toggleCategoryField() {
            const operation = document.getElementById('bulkOperation').value;
            const categoryField = document.getElementById('categoryField');
            categoryField.style.display = operation === 'update_category' ? 'block' : 'none';
        }

        function confirmBulkAction() {
            const operation = document.getElementById('bulkOperation').value;
            if (operation === 'delete') {
                return confirm('Are you sure you want to delete the selected products? This action cannot be undone.');
            }
            return confirm('Are you sure you want to apply this action to the selected products?');
        }

        function showStockAlert() {
            // Filter and highlight low stock products
            const rows = document.querySelectorAll('.table tbody tr');
            let lowStockCount = 0;
            
            rows.forEach(row => {
                const stockCell = row.cells[6]; // Stock column
                if (stockCell && stockCell.textContent) {
                    const stock = parseInt(stockCell.textContent.trim());
                    if (stock < 10) {
                        row.style.backgroundColor = '#ffebee';
                        row.style.border = '2px solid #f44336';
                        lowStockCount++;
                    } else {
                        row.style.backgroundColor = '';
                        row.style.border = '';
                    }
                }
            });
            
            if (lowStockCount > 0) {
                alert(`Found ${lowStockCount} products with low stock (less than 10 units). They are highlighted in red.`);
            } else {
                alert('All products have sufficient stock!');
            }
        }
    </script>
</body>
</html>