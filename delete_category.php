<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo "<script>alert('Unauthorized access!'); window.location.href='sign-in.php';</script>";
    exit();
}

// Check if the category ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Category ID is missing!'); window.location.href='manage_categories.php';</script>";
    exit();
}

$category_id = $_GET['id'];

$conn = new mysqli("localhost", "root", "", "musicstore_database");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete the category
$sql = "DELETE FROM categories WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);

if ($stmt->execute()) {
    echo "<script>alert('Category deleted successfully!'); window.location.href='manage_categories.php';</script>";
} else {
    echo "<script>alert('Error deleting category: " . $stmt->error . "'); window.location.href='manage_categories.php';</script>";
}

$stmt->close();
$conn->close();
?>