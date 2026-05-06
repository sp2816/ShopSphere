<?php
$conn = mysqli_connect("localhost","root","","shopsphere_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                $sql = "INSERT INTO categories (name, description, parent_id, is_active) 
                        VALUES ('$name', '$description', " . ($parent_id ? $parent_id : 'NULL') . ", $is_active)";
                
                if (mysqli_query($conn, $sql)) {
                    $success_message = "Category added successfully!";
                } else {
                    $error_message = "Error adding category: " . mysqli_error($conn);
                }
                break;
                
            case 'edit':
                $category_id = intval($_POST['category_id']);
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                $sql = "UPDATE categories SET 
                        name = '$name', 
                        description = '$description', 
                        parent_id = " . ($parent_id ? $parent_id : 'NULL') . ", 
                        is_active = $is_active 
                        WHERE category_id = $category_id";
                
                if (mysqli_query($conn, $sql)) {
                    $success_message = "Category updated successfully!";
                } else {
                    $error_message = "Error updating category: " . mysqli_error($conn);
                }
                break;
                
            case 'delete':
                $category_id = intval($_POST['category_id']);
                $force_delete = isset($_POST['force_delete']) ? true : false;
                
                // Check if category has products
                $check_sql = "SELECT COUNT(*) as product_count FROM products WHERE category = (SELECT name FROM categories WHERE category_id = $category_id)";
                $check_result = mysqli_query($conn, $check_sql);
                $check_row = mysqli_fetch_assoc($check_result);
                
                if ($check_row['product_count'] > 0 && !$force_delete) {
                    $error_message = "Category has " . $check_row['product_count'] . " products. Are you sure you want to delete it? All products will be moved to 'Uncategorized'.";
                } else {
                    // If category has products, move them to uncategorized first
                    if ($check_row['product_count'] > 0) {
                        $update_products_sql = "UPDATE products SET category = 'Uncategorized' WHERE category = (SELECT name FROM categories WHERE category_id = $category_id)";
                        mysqli_query($conn, $update_products_sql);
                    }
                    
                    $sql = "DELETE FROM categories WHERE category_id = $category_id";
                    
                    if (mysqli_query($conn, $sql)) {
                        $success_message = "Category deleted successfully!" . ($check_row['product_count'] > 0 ? " Products moved to 'Uncategorized'." : "");
                    } else {
                        $error_message = "Error deleting category: " . mysqli_error($conn);
                    }
                }
                break;
                
            case 'toggle_status':
                $category_id = intval($_POST['category_id']);
                $sql = "UPDATE categories SET is_active = !is_active WHERE category_id = $category_id";
                
                if (mysqli_query($conn, $sql)) {
                    $success_message = "Category status updated successfully!";
                } else {
                    $error_message = "Error updating category status: " . mysqli_error($conn);
                }
                break;
                
            case 'bulk_action':
                if (isset($_POST['selected_categories']) && isset($_POST['bulk_operation'])) {
                    $category_ids = $_POST['selected_categories'];
                    $operation = $_POST['bulk_operation'];
                    $updated_count = 0;
                    
                    foreach ($category_ids as $category_id) {
                        $category_id = intval($category_id);
                        $sql = "";
                        
                        switch ($operation) {
                            case 'activate':
                                $sql = "UPDATE categories SET is_active = 1 WHERE category_id = $category_id";
                                break;
                            case 'deactivate':
                                $sql = "UPDATE categories SET is_active = 0 WHERE category_id = $category_id";
                                break;
                            case 'delete':
                                // Check for products before deleting
                                $check_sql = "SELECT COUNT(*) as product_count FROM products WHERE category = (SELECT name FROM categories WHERE category_id = $category_id)";
                                $check_result = mysqli_query($conn, $check_sql);
                                $check_row = mysqli_fetch_assoc($check_result);
                                
                                if ($check_row['product_count'] == 0) {
                                    $sql = "DELETE FROM categories WHERE category_id = $category_id";
                                }
                                break;
                        }
                        
                        if ($sql && mysqli_query($conn, $sql)) {
                            $updated_count++;
                        }
                    }
                    
                    $action_name = ucfirst(str_replace('_', ' ', $operation));
                    $success_message = "Successfully applied '$action_name' to $updated_count categories!";
                }
                break;
        }
    }
}

// Get categories with search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM products p WHERE p.category = c.name) as product_count,
        parent.name as parent_name
        FROM categories c 
        LEFT JOIN categories parent ON c.parent_id = parent.category_id
        WHERE 1=1";

if ($search) {
    $sql .= " AND (c.name LIKE '%$search%' OR c.description LIKE '%$search%')";
}

$sql .= " ORDER BY c.parent_id ASC, c.name ASC";
$result = mysqli_query($conn, $sql);

// Get parent categories for dropdown
$parent_sql = "SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY name";
$parent_result = mysqli_query($conn, $parent_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/adminDashboard.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <style>
        .categories-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .categories-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .controls-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .controls-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }
        
        .search-input {
            width: 90%;
            padding: 12px 20px 12px 50px;
            border: 2px solid #e1e1e1;
            border-radius: 25px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: white;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .categories-table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .bulk-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .table th,
        .table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table tr:hover {
            background: #f8f9ff;
        }
        
        .category-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
        }
        
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .category-hierarchy {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .subcategory {
            padding-left: 20px;
            position: relative;
        }
        
        .subcategory::before {
            content: '└─';
            position: absolute;
            left: 0;
            color: #666;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }
        
        .close:hover {
            color: black;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .controls-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                min-width: auto;
            }
            
            .action-buttons {
                justify-content: center;
            }
            
            .table-header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Navbar -->
    <header class="main-header">
        <div class="logo">ShopSphere<span class="plus">Admin</span></div>
        <nav style="flex:1;">
            <ul class="admin-nav-items">
                <li><a class="link" href="dashboard.php">Dashboard</a></li>
                <li><a class="link" href="products.php">Products</a></li>
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

    <div class="categories-container">
        <div class="categories-header">
            <h1><i class="fas fa-tags"></i> Category Management</h1>
            <p>Organize your products with categories and subcategories</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="controls-section">
            <div class="controls-row">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search categories..." 
                           value="<?php echo htmlspecialchars($search); ?>" onkeyup="searchCategories(this.value)">
                </div>
                
                <div class="action-buttons">
                    <button onclick="showAddModal()" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                    <button onclick="showBulkActions()" class="btn btn-warning">
                        <i class="fas fa-tasks"></i> Bulk Actions
                    </button>
                    <button onclick="exportCategories()" class="btn btn-info">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>
        </div>

        <div class="categories-table-container">
            <div class="table-header">
                <h3><i class="fas fa-list"></i> Categories List</h3>
                <div class="bulk-actions">
                    <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                    <label for="selectAll" style="color: white; margin-left: 5px;">Select All</label>
                </div>
            </div>
            
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Parent Category</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($category = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_categories[]" value="<?php echo $category['category_id']; ?>">
                                </td>
                                <td>
                                    <div class="category-hierarchy">
                                        <?php if ($category['parent_id']): ?>
                                            <span class="subcategory"><?php echo htmlspecialchars($category['name']); ?></span>
                                        <?php else: ?>
                                            <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($category['description'] ?: 'No description'); ?></td>
                                <td><?php echo $category['parent_name'] ? htmlspecialchars($category['parent_name']) : '-'; ?></td>
                                <td>
                                    <span class="badge"><?php echo $category['product_count']; ?> products</span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $category['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="category-actions">
                                        <button onclick="editCategory(<?php echo $category['category_id']; ?>)" 
                                                class="btn btn-primary btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                            <button type="submit" class="btn <?php echo $category['is_active'] ? 'btn-secondary' : 'btn-success'; ?> btn-sm" 
                                                    title="<?php echo $category['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                <i class="fas <?php echo $category['is_active'] ? 'fa-toggle-off' : 'fa-toggle-on'; ?>"></i>
                                            </button>
                                        </form>
                                        <button onclick="deleteCategory(<?php echo $category['category_id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')" 
                                                class="btn btn-danger btn-sm" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                                    <i class="fas fa-tags" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                                    <div>No categories found</div>
                                    <small>Start by adding your first category</small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCategoryModal()">&times;</span>
            <h3 id="modalTitle"><i class="fas fa-plus"></i> Add New Category</h3>
            <form method="POST" id="categoryForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="category_id" id="categoryId">
                
                <div class="form-group">
                    <label for="categoryName">Category Name *</label>
                    <input type="text" id="categoryName" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="categoryDescription">Description</label>
                    <textarea id="categoryDescription" name="description" rows="3" placeholder="Optional description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="parentCategory">Parent Category</label>
                    <select id="parentCategory" name="parent_id">
                        <option value="">No Parent (Main Category)</option>
                        <?php 
                        mysqli_data_seek($parent_result, 0);
                        while ($parent = mysqli_fetch_assoc($parent_result)): 
                        ?>
                            <option value="<?php echo $parent['category_id']; ?>">
                                <?php echo htmlspecialchars($parent['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="isActive" name="is_active" checked>
                        <label for="isActive">Active Category</label>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Category
                    </button>
                    <button type="button" onclick="closeCategoryModal()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Add New Category';
            document.getElementById('formAction').value = 'add';
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryForm').reset();
            document.getElementById('isActive').checked = true;
            document.getElementById('categoryModal').style.display = 'block';
        }

        function editCategory(categoryId) {
            // In a real implementation, you'd fetch category data via AJAX
            // For now, we'll show the modal and let PHP handle the edit
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Category';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('categoryId').value = categoryId;
            document.getElementById('categoryModal').style.display = 'block';
        }

        function closeCategoryModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }

        function deleteCategory(categoryId, categoryName) {
            if (confirm(`Are you sure you want to delete "${categoryName}"?\n\nNote: If this category has products, they will be moved to 'Uncategorized'.\nThis action cannot be undone.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'categories.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                form.appendChild(actionInput);
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'category_id';
                idInput.value = categoryId;
                form.appendChild(idInput);
                
                const forceInput = document.createElement('input');
                forceInput.type = 'hidden';
                forceInput.name = 'force_delete';
                forceInput.value = '1';
                form.appendChild(forceInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const categoryCheckboxes = document.querySelectorAll('input[name="selected_categories[]"]');
            
            categoryCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        function showBulkActions() {
            const checkboxes = document.querySelectorAll('input[name="selected_categories[]"]:checked');
            if (checkboxes.length === 0) {
                alert('Please select categories to apply bulk actions');
                return;
            }

            const action = prompt('Choose action:\n1. activate\n2. deactivate\n3. delete\n\nEnter action name:');
            if (action && ['activate', 'deactivate', 'delete'].includes(action.toLowerCase())) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'categories.php';
                
                checkboxes.forEach(checkbox => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_categories[]';
                    input.value = checkbox.value;
                    form.appendChild(input);
                });
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'bulk_action';
                form.appendChild(actionInput);
                
                const operationInput = document.createElement('input');
                operationInput.type = 'hidden';
                operationInput.name = 'bulk_operation';
                operationInput.value = action.toLowerCase();
                form.appendChild(operationInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        function exportCategories() {
            window.location.href = 'categories.php?export=csv';
        }

        function searchCategories(query) {
            if (query.length > 2 || query.length === 0) {
                window.location.href = `categories.php?search=${encodeURIComponent(query)}`;
            }
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('categoryModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
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
    </script>

</body>
</html>