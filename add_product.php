<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form values
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $price = $_POST['price'];
    $image = $_FILES['image'];

    // Check if fields are empty
    if (empty($name) || empty($price) || empty($category) || empty($image['name'])) {
        $error_message = "All fields are required!";
    } else {
        // Check if image format is valid
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $imageExtension = pathinfo($image['name'], PATHINFO_EXTENSION);

        if (!in_array(strtolower($imageExtension), $allowedExtensions)) {
            $error_message = "Invalid image format! Only JPG, PNG, GIF allowed.";
        } else {
            // Process image upload
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($image['name']);
            if (move_uploaded_file($image['tmp_name'], $targetFile)) {
                // Database connection
                $conn = new mysqli("localhost", "root", "", "music_store");

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Prepare and insert product into the database
                $stmt = $conn->prepare("INSERT INTO products (name, description, category, price, image) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $name, $description, $category, $price, $targetFile);

                if ($stmt->execute()) {
                    $success_message = "Product added successfully!";
                } else {
                    $error_message = "Error adding product: " . $stmt->error;
                }

                $stmt->close();
                $conn->close();
            } else {
                $error_message = "Error uploading image.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="styles.css">
</head>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
    body { background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; }
    .container { background: #fff; padding: 20px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); border-radius: 10px; width: 350px; text-align: center; }
    h2 { margin-bottom: 15px; }
    input, textarea, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
    button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
    button:hover { background-color: #218838; }
    .error-message { color: red; font-size: 14px; margin-top: 10px; }
    .success-message { color: green; font-size: 14px; margin-top: 10px; }
</style>

<body>
<div class="container">
    <h2>Add New Product</h2>

    <?php if (isset($error_message)): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
        <p class="success-message"><?php echo $success_message; ?></p>
    <?php endif; ?>

    <form action="add_product.php" method="POST" enctype="multipart/form-data">
        <input type="text" id="name" name="name" placeholder="Product Name" required>
        <textarea id="description" name="description" placeholder="Product Description"></textarea>

        <!-- Category Selection -->
        <select id="category" name="category" required>
            <option value="">Select Category</option>
            <option value="guitars">Guitars</option>
            <option value="pianos">Pianos</option>
            <option value="drums">Drums</option>
            <option value="wind">Wind Instruments</option>
            <option value="accessories">Accessories</option>
        </select>

        <input type="number" step="0.01" id="price" name="price" placeholder="Price" required>
        <input type="file" id="image" name="image" accept="image/*" required>
        <button type="submit">Add Product</button>
    </form>
</div>
</body>
</html>
