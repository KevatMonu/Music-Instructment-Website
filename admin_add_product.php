

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Product Name" required><br>
    <textarea name="description" placeholder="Product Description"></textarea><br>
    <input type="number" step="0.01" name="price" placeholder="Price" required><br>
    <input type="file" name="image" required><br>
    <button type="submit">Add Product</button>
</form>

</body>
</html>

<?php
$conn = new mysqli("localhost", "root", "", "music_store");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    // Image Upload
    $target_dir = "uploads/"; // Ensure this folder exists
$image_name = basename($_FILES["image"]["name"]);
$target_file = $target_dir . $image_name;
move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

// Store relative path in the database
$image_path = $target_dir . $image_name;

$sql = "INSERT INTO products (name, description, price, image) VALUES ('$name', '$description', '$price', '$image_path')";

}
?>






