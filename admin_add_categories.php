<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo "<script>alert('Unauthorized access!'); window.location.href='sign-in.php';</script>";
    exit();
}

$conn = new mysqli("localhost", "root", "", "musicstore_database");

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$messageType = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = trim($_POST['category_name'] ?? '');
    $category_description = trim($_POST['category_description'] ?? '');
    $image_data = null; // Initialize image data as NULL

    // Validate fields
    if (empty($category_name) || empty($category_description)) {
        $message = "Please fill in all required fields.";
        $messageType = "error";
    } else {
        // Check if image is uploaded
        if (!empty($_FILES['category_image']['name']) && $_FILES['category_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['category_image']['name'];
            $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($filetype, $allowed)) {
                $image_data = file_get_contents($_FILES['category_image']['tmp_name']);
            } else {
                $message = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
                $messageType = "error";
            }
        }

        if (empty($message)) { // Proceed only if no validation errors
            $stmt = $conn->prepare("INSERT INTO categories (category_name, category_description, category_image, created_on) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $category_name, $category_description, $image_data);

            if ($stmt->execute()) {
                $message = "Category added successfully!";
                $messageType = "success";
            } else {
                $message = "Error: " . $stmt->error;
                $messageType = "error";
            }

            $stmt->close();
        }
    }
}

$full_name = $_SESSION['full_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #007bff;
            color: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            padding: 10px;
            border-bottom: 1px solid #0056b3;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
        }
        .sidebar ul li a:hover {
            background: #0056b3;
            border-radius: 5px;
            padding: 10px;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .logout-btn {
            margin-top: 20px;
            background: red;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
        }
        .logout-btn a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        .logout-btn a:hover {
            opacity: 0.8;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
        .back-btn {
            background-color: #6c757d;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="manage_categories.php">Manage Categories</a></li>
            <li><a href="admin_add_product.php">Add Products</a></li>
            <li><a href="manage_orders.php">View Orders</a></li>
            <li><a href="reports.php">Reports</a></li>
        </ul>
        <div class="logout-btn">
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="main-content">
        <h1>Add Category</h1>

        <?php if(!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="category_name">Category Name:</label>
                    <input type="text" id="category_name" name="category_name" required>
                </div>

                <div class="form-group">
                    <label for="category_description">Category Description:</label>
                    <textarea id="category_description" name="category_description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="category_image">Category Image:</label>
                    <input type="file" id="category_image" name="category_image" accept="image/*">
                </div>

                <button type="submit" class="submit-btn">Add Category</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
