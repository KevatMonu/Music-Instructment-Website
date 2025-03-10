<?php
session_start();
$conn = new mysqli("localhost", "root", "", "musicstore_database");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$userId = $_SESSION['user_id']; // Get logged-in user's ID

// Fetch user details
$stmt = $conn->prepare("SELECT user_id, full_name, email_address, user_role, user_image, created_on FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['full_name'];
    $email = $_POST['email_address'];

    $updateStmt = $conn->prepare("UPDATE users SET full_name = ?, email_address = ? WHERE user_id = ?");
    $updateStmt->bind_param("ssi", $fullName, $email, $userId);
    $updateStmt->execute();

    // Refresh user data after update
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
    <div class="container">
        <div class="header">User Profile</div>

        <div class="profile-content">
            <div class="profile-image-container">
                <?php if ($user['user_image']): ?>
                    <img src="<?php echo htmlspecialchars($user['user_image']); ?>" alt="Profile Picture">
                <?php else: ?>
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                <?php endif; ?>
            </div>

            <div class="info-section">
                <form method="POST" id="profileForm">
                    <div class="field">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" disabled required>
                    </div>

                    <div class="field">
                        <label>Email Address</label>
                        <input type="email" name="email_address" value="<?php echo htmlspecialchars($user['email_address']); ?>" disabled required>
                    </div>

                    <div class="field">
                        <label>Role</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['user_role']); ?>" disabled>
                    </div>

                    <div class="field">
                        <label>Member Since</label>
                        <input type="text" value="<?php echo date('F j, Y', strtotime($user['created_on'])); ?>" disabled>
                    </div>

                    <div class="buttons">
                        <button type="button" class="edit-button" onclick="toggleEdit()">Edit</button>
                        <button type="submit" class="save-button">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleEdit() {
            const form = document.getElementById('profileForm');
            const inputs = form.querySelectorAll('input[name="full_name"], input[name="email_address"]');
            const saveButton = document.querySelector('.save-button');
            const editButton = document.querySelector('.edit-button');

            const isEditing = inputs[0].disabled;

            inputs.forEach(input => input.disabled = !isEditing);
            saveButton.style.display = isEditing ? 'block' : 'none';
            editButton.textContent = isEditing ? 'Cancel' : 'Edit';
        }
    </script>
</body>
</html>
