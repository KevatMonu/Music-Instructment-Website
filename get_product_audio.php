<?php
session_start();
include 'db_connection.php';

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

$productId = $_GET['id'];

// Prepare the query to get product audio
$query = "SELECT sound, sound_type FROM products WHERE product_id = ? AND sound IS NOT NULL";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
    
    if ($product['sound']) {
        // Create a temporary file with the audio data
        $tempDir = 'temp_audio';
        
        // Create directory if it doesn't exist
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Generate a unique filename
        $filename = $tempDir . '/product_' . $productId . '_' . time() . '.' . pathinfo($product['sound_type'], PATHINFO_EXTENSION);
        
        // Write sound data to file
        file_put_contents($filename, $product['sound']);
        
        // Return the URL to the temporary file
        echo json_encode(['audio_url' => $filename]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No audio available for this product']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found or has no audio']);
}

$stmt->close();
$conn->close();
?>