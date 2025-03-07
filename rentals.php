<?php
session_start();

// Enable detailed error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database Connection
$conn = new mysqli("localhost", "root", "", "musicstore_database");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'available';

// Process Rental Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rent_product'])) {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;
    $rental_days = isset($_POST['rental_days']) ? intval($_POST['rental_days']) : 0;
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d');
    
    // Validation
    $errors = [];
    
    if ($product_id <= 0) $errors[] = "Invalid product selected.";
    if ($customer_id <= 0) $errors[] = "Please select a customer.";
    if ($rental_days <= 0) $errors[] = "Rental period must be at least 1 day.";
    if (empty($start_date)) $errors[] = "Start date is required.";
    
    // Get product details to calculate total cost
    if (empty($errors)) {
        $product_query = "SELECT rental_cost, stock_quantity FROM products WHERE product_id = ? AND rental_cost IS NOT NULL";
        $stmt = $conn->prepare($product_query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $errors[] = "Selected product is not available for rent.";
        } else {
            $product = $result->fetch_assoc();
            if ($product['stock_quantity'] <= 0) {
                $errors[] = "This product is out of stock.";
            }
        }
        $stmt->close();
    }
    
    if (!empty($errors)) {
        $message = "<div class='error-message'><ul>";
        foreach ($errors as $error) {
            $message .= "<li>" . htmlspecialchars($error) . "</li>";
        }
        $message .= "</ul></div>";
    } else {
        // Calculate end date and total cost
        $end_date = date('Y-m-d', strtotime($start_date . ' + ' . $rental_days . ' days'));
        $total_cost = $product['rental_cost'] * $rental_days;
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Insert rental record
            $rental_stmt = $conn->prepare("INSERT INTO rentals (product_id, customer_id, start_date, end_date, rental_days, total_cost, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
            $rental_stmt->bind_param("iissid", $product_id, $customer_id, $start_date, $end_date, $rental_days, $total_cost);
            
            if (!$rental_stmt->execute()) {
                throw new Exception("Error creating rental: " . $rental_stmt->error);
            }
            
            // Update product stock
            $update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - 1 WHERE product_id = ?");
            $update_stock->bind_param("i", $product_id);
            
            if (!$update_stock->execute()) {
                throw new Exception("Error updating inventory: " . $update_stock->error);
            }
            
            $conn->commit();
            $message = "<p class='success-message'>Rental created successfully!</p>";
            $active_tab = 'active'; // Switch to active rentals tab
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = "<p class='error-message'>" . $e->getMessage() . "</p>";
        }
    }
}

// Process Rental Return
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['return_rental'])) {
    $rental_id = isset($_POST['rental_id']) ? intval($_POST['rental_id']) : 0;
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $condition_notes = isset($_POST['condition_notes']) ? trim($_POST['condition_notes']) : '';
    $late_fees = isset($_POST['late_fees']) ? floatval($_POST['late_fees']) : 0;
    
    if ($rental_id <= 0 || $product_id <= 0) {
        $message = "<p class='error-message'>Invalid rental or product information.</p>";
    } else {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Update rental status
            $update_rental = $conn->prepare("UPDATE rentals SET status = 'Returned', return_date = CURRENT_DATE, condition_notes = ?, late_fees = ? WHERE rental_id = ?");
            $update_rental->bind_param("sdi", $condition_notes, $late_fees, $rental_id);
            
            if (!$update_rental->execute()) {
                throw new Exception("Error updating rental: " . $update_rental->error);
            }
            
            // Update product stock
            $update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + 1 WHERE product_id = ?");
            $update_stock->bind_param("i", $product_id);
            
            if (!$update_stock->execute()) {
                throw new Exception("Error updating inventory: " . $update_stock->error);
            }
            
            $conn->commit();
            $message = "<p class='success-message'>Item returned successfully!</p>";
            $active_tab = 'history'; // Switch to rental history tab
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = "<p class='error-message'>" . $e->getMessage() . "</p>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Management</title>
    <link rel="stylesheet" href="css/rentals.css">
    <style>
        /* Basic Styles */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .tab {
            padding: 10px 15px;
            cursor: pointer;
            background: #f9f9f9;
            margin-right: 5px;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
        }
        
        .tab.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        /* Tab Content */
        .tab-content {
            display: none;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            background: #fff;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Forms */
        form {
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover {
            background: #2980b9;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table th,
        table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        /* Messages */
        .error-message {
            color: #d9534f;
            background-color: #f2dede;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .error-message ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .success-message {
            color: #5cb85c;
            background-color: #dff0d8;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        /* Product Cards */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .product-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-image {
            height: 150px;
            background-position: center;
            background-size: contain;
            background-repeat: no-repeat;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        
        .product-details h3 {
            margin-top: 0;
            color: #333;
        }
        
        .product-price {
            font-weight: bold;
            color: #3498db;
            margin: 10px 0;
        }
        
        .product-stock {
            color: #777;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        
        .filter-form {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        
        .filter-form .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        .filter-form button {
            height: 38px;
        }
        
        .rental-details {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        
        .return-form {
            background: #f1f1f1;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Rental Management</h2>
    
    <?php echo $message; ?>
    
    <div class="tabs">
        <div class="tab <?php echo $active_tab === 'available' ? 'active' : ''; ?>" onclick="openTab('available')">Available for Rent</div>
        <div class="tab <?php echo $active_tab === 'new' ? 'active' : ''; ?>" onclick="openTab('new')">New Rental</div>
        <div class="tab <?php echo $active_tab === 'active' ? 'active' : ''; ?>" onclick="openTab('active')">Active Rentals</div>
        <div class="tab <?php echo $active_tab === 'history' ? 'active' : ''; ?>" onclick="openTab('history')">Rental History</div>
    </div>
    
    <!-- Available Products Tab -->
    <div id="available" class="tab-content <?php echo $active_tab === 'available' ? 'active' : ''; ?>">
        <div class="filter-section">
            <form class="filter-form" method="GET" action="">
                <input type="hidden" name="tab" value="available">
                <div class="form-group">
                    <label for="search">Search Products</label>
                    <input type="text" id="search" name="search" placeholder="Search by name..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <?php
                        $category_query = "SELECT category_id, category_name FROM categories";
                        $category_result = $conn->query($category_query);
                        if ($category_result && $category_result->num_rows > 0) {
                            while ($cat = $category_result->fetch_assoc()) {
                                $selected = (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : '';
                                echo "<option value='{$cat['category_id']}' {$selected}>{$cat['category_name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <button type="submit">Filter</button>
                <button type="submit" name="reset" value="1">Reset</button>
            </form>
        </div>
        
        <div class="product-grid">
            <?php
            // Get rentable products with stock
            $search = isset($_GET['search']) ? '%' . $conn->real_escape_string($_GET['search']) . '%' : '%';
            $category = isset($_GET['category']) && !empty($_GET['category']) ? intval($_GET['category']) : null;
            
            $query = "SELECT p.*, c.category_name 
                      FROM products p 
                      JOIN categories c ON p.category_ref = c.category_id 
                      WHERE p.rental_cost IS NOT NULL 
                      AND p.stock_quantity > 0 
                      AND p.product_name LIKE ?";
                      
            if ($category) {
                $query .= " AND p.category_ref = ?";
            }
            
            $stmt = $conn->prepare($query);
            
            if ($category) {
                $stmt->bind_param("si", $search, $category);
            } else {
                $stmt->bind_param("s", $search);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                while ($product = $result->fetch_assoc()) {
                    echo "<div class='product-card'>";
                    // Fetch image from database if available or use placeholder
                    if (isset($product['product_image'])) {
                        echo "<div class='product-image' style='background-image: url(\"get_image.php?id={$product['product_id']}\");'></div>";
                    } else {
                        echo "<div class='product-image' style='background-image: url(\"images/placeholder.jpg\");'></div>";
                    }
                    
                    echo "<div class='product-details'>
                            <h3>" . htmlspecialchars($product['product_name']) . "</h3>
                            <p class='product-category'>" . htmlspecialchars($product['category_name']) . "</p>
                            <p class='product-price'>Rental: $" . number_format($product['rental_cost'], 2) . " per day</p>
                            <p class='product-stock'>Available: " . $product['stock_quantity'] . " in stock</p>
                            <form action='rentals.php?tab=new' method='POST'>
                                <input type='hidden' name='product_id' value='" . $product['product_id'] . "'>
                                <button type='submit' name='select_product'>Rent This Item</button>
                            </form>
                        </div>
                    </div>";
                }
            } else {
                echo "<p>No rentable products available.</p>";
            }
            ?>
        </div>
    </div>
    
    <!-- New Rental Tab -->
    <div id="new" class="tab-content <?php echo $active_tab === 'new' ? 'active' : ''; ?>">
        <h3>Create New Rental</h3>
        <form action="rentals.php" method="POST">
            <input type="hidden" name="tab" value="new">
            
            <div class="form-group">
                <label for="product_id">Product</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Select Product</option>
                    <?php
                    // Get rentable products
                    $product_query = "SELECT product_id, product_name, rental_cost, stock_quantity 
                                    FROM products 
                                    WHERE rental_cost IS NOT NULL 
                                    AND stock_quantity > 0 
                                    ORDER BY product_name";
                    $product_result = $conn->query($product_query);
                    
                    if ($product_result && $product_result->num_rows > 0) {
                        while ($prod = $product_result->fetch_assoc()) {
                            $selected = (isset($_POST['product_id']) && $_POST['product_id'] == $prod['product_id']) ? 'selected' : '';
                            if (isset($_POST['select_product']) && $_POST['product_id'] == $prod['product_id']) {
                                $selected = 'selected';
                            }
                            echo "<option value='{$prod['product_id']}' data-price='{$prod['rental_cost']}' {$selected}>
                                    {$prod['product_name']} - ${$prod['rental_cost']}/day ({$prod['stock_quantity']} available)
                                  </option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="customer_id">Customer</label>
                <select id="customer_id" name="customer_id" required>
                    <option value="">Select Customer</option>
                    <?php
                    // Get customers
                    $customer_query = "SELECT customer_id, CONCAT(first_name, ' ', last_name) AS customer_name 
                                      FROM customers 
                                      ORDER BY last_name, first_name";
                    $customer_result = $conn->query($customer_query);
                    
                    if ($customer_result && $customer_result->num_rows > 0) {
                        while ($cust = $customer_result->fetch_assoc()) {
                            $selected = (isset($_POST['customer_id']) && $_POST['customer_id'] == $cust['customer_id']) ? 'selected' : '';
                            echo "<option value='{$cust['customer_id']}' {$selected}>{$cust['customer_name']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="rental_days">Rental Period (Days)</label>
                <input type="number" id="rental_days" name="rental_days" min="1" value="1" required>
            </div>
            
            <div class="form-group">
                <label>Estimated Total</label>
                <div id="total_cost" class="rental-details">
                    Select a product and rental period to see the estimated cost.
                </div>
            </div>
            
            <button type="submit" name="rent_product">Create Rental</button>
        </form>
    </div>
    
    <!-- Active Rentals Tab -->
    <div id="active" class="tab-content <?php echo $active_tab === 'active' ? 'active' : ''; ?>">
        <h3>Active Rentals</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Days</th>
                    <th>Total Cost</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get active rentals
                $active_query = "SELECT r.*, p.product_name, 
                                CONCAT(c.first_name, ' ', c.last_name) AS customer_name 
                                FROM rentals r 
                                JOIN products p ON r.product_id = p.product_id 
                                JOIN customers c ON r.customer_id = c.customer_id 
                                WHERE r.status = 'Active' 
                                ORDER BY r.end_date ASC";
                $active_result = $conn->query($active_query);
                
                if ($active_result && $active_result->num_rows > 0) {
                    while ($rental = $active_result->fetch_assoc()) {
                        $currentDate = new DateTime();
                        $endDate = new DateTime($rental['end_date']);
                        $isOverdue = $currentDate > $endDate;
                        $rowClass = $isOverdue ? "style='background-color: #ffebee;'" : "";
                        
                        echo "<tr {$rowClass}>
                                <td>{$rental['rental_id']}</td>
                                <td>" . htmlspecialchars($rental['product_name']) . "</td>
                                <td>" . htmlspecialchars($rental['customer_name']) . "</td>
                                <td>" . date('M d, Y', strtotime($rental['start_date'])) . "</td>
                                <td>" . date('M d, Y', strtotime($rental['end_date'])) . "</td>
                                <td>{$rental['rental_days']}</td>
                                <td>$" . number_format($rental['total_cost'], 2) . "</td>
                                <td class='actions'>
                                    <button type='button' onclick='showReturnForm({$rental['rental_id']}, {$rental['product_id']})' class='return-btn'>Return</button>
                                </td>
                              </tr>";
                              
                        // Return form (hidden by default)
                        echo "<tr id='return-form-{$rental['rental_id']}' style='display: none;'>
                                <td colspan='8'>
                                    <div class='return-form'>
                                        <h4>Return Item</h4>
                                        <form action='rentals.php?tab=active' method='POST'>
                                            <input type='hidden' name='rental_id' value='{$rental['rental_id']}'>
                                            <input type='hidden' name='product_id' value='{$rental['product_id']}'>
                                            
                                            <div class='form-group'>
                                                <label for='condition-{$rental['rental_id']}'>Condition Notes</label>
                                                <textarea id='condition-{$rental['rental_id']}' name='condition_notes' rows='3'></textarea>
                                            </div>
                                            
                                            <div class='form-group'>
                                                <label for='late-fees-{$rental['rental_id']}'>Late Fees (if applicable)</label>
                                                <input type='number' id='late-fees-{$rental['rental_id']}' name='late_fees' step='0.01' min='0' value='" . ($isOverdue ? "10.00" : "0.00") . "'>
                                            </div>
                                            
                                            <button type='submit' name='return_rental'>Confirm Return</button>
                                            <button type='button' onclick='hideReturnForm({$rental['rental_id']})'>Cancel</button>
                                        </form>
                                    </div>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No active rentals found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <!-- Rental History Tab -->
    <div id="history" class="tab-content <?php echo $active_tab === 'history' ? 'active' : ''; ?>">
        <h3>Rental History</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Return Date</th>
                    <th>Cost</th>
                    <th>Late Fees</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get rental history
                $history_query = "SELECT r.*, p.product_name, 
                                 CONCAT(c.first_name, ' ', c.last_name) AS customer_name 
                                 FROM rentals r 
                                 JOIN products p ON r.product_id = p.product_id 
                                 JOIN customers c ON r.customer_id = c.customer_id 
                                 WHERE r.status = 'Returned' 
                                 ORDER BY r.return_date DESC 
                                 LIMIT 50";
                $history_result = $conn->query($history_query);
                
                if ($history_result && $history_result->num_rows > 0) {
                    while ($rental = $history_result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$rental['rental_id']}</td>
                                <td>" . htmlspecialchars($rental['product_name']) . "</td>
                                <td>" . htmlspecialchars($rental['customer_name']) . "</td>
                                <td>" . date('M d, Y', strtotime($rental['start_date'])) . "</td>
                                <td>" . date('M d, Y', strtotime($rental['end_date'])) . "</td>
                                <td>" . ($rental['return_date'] ? date('M d, Y', strtotime($rental['return_date'])) : 'N/A') . "</td>
                                <td>$" . number_format($rental['total_cost'], 2) . "</td>
                                <td>$" . number_format($rental['late_fees'], 2) . "</td>
                                <td>{$rental['status']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No rental history found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Tab switching function
function openTab(tabName) {
    // Update URL with tab parameter
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);
    
    // Hide all tabs
    const tabContents = document.getElementsByClassName('tab-content');
    for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].classList.remove('active');
    }
    
    // Remove active class from all tabs
    const tabs = document.getElementsByClassName('tab');
    for (let i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }
    
    // Show the selected tab
    document.getElementById(tabName).classList.add('active');
    
    // Add active class to clicked tab
    const activeTabButtons = document.querySelectorAll(`.tab[onclick="openTab('${tabName}')"]`);
    activeTabButtons.forEach(btn => btn.classList.add('active'));
}

// Return form toggle functions
function showReturnForm(rentalId, productId) {
    document.getElementById(`return-form-${rentalId}`).style.display = 'table-row';
}

function hideReturnForm(rentalId) {
    document.getElementById(`return-form-${rentalId}`).style.display = 'none';
}

// Calculate estimated rental cost
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const rentalDaysInput = document.getElementById('rental_days');
    const totalCostDiv = document.getElementById('total_cost');
    
    function calculateTotal() {
        if (productSelect.value) {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const rentalCost = parseFloat(selectedOption.dataset.price);
            const days = parseInt(rentalDaysInput.value) || 0;
            
            if (!isNaN(rentalCost) && !isNaN(days) && days > 0) {
                const totalCost = rentalCost * days;
                totalCostDiv.innerHTML = `
                    <p><strong>Product:</strong> ${selectedOption.text}</p>
                    <p><strong>Daily Rate:</strong> $${rentalCost.toFixed(2)}</p>
                    <p><strong>Days:</strong> ${days}</p>
                    <p><strong>Total Cost:</strong> $${totalCost.toFixed(2)}</p>
                    <p><strong>Total Cost:</strong> $${totalCost.toFixed(2)}</p>
                `;
            } else {
                totalCostDiv.innerHTML = "Please enter a valid rental period.";
            }
        } else {
            totalCostDiv.innerHTML = "Select a product and rental period to see the estimated cost.";
        }
    }
    
    // Calculate total when product or days change
    if (productSelect && rentalDaysInput && totalCostDiv) {
        productSelect.addEventListener('change', calculateTotal);
        rentalDaysInput.addEventListener('input', calculateTotal);
        
        // Initialize calculation
        calculateTotal();
    }
});
</script>
</body>
</html>