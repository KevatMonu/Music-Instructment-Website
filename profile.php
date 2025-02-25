<?php
session_start();
$conn = new mysqli("localhost", "root", "", "musicstore_database");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user data
$userId = 1; // You would typically get this from the session
$stmt = $conn->prepare("SELECT user_id, full_name, email_address, user_role, user_image, created_on FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['full_name'];
    $email = $_POST['email_address'];
    
    $updateStmt = $conn->prepare("UPDATE users SET full_name = ?, email_address = ? WHERE user_id = ?");
    $updateStmt->bind_param("ssi", $fullName, $email, $userId);
    $updateStmt->execute();
    
    // Refresh user data
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f3f4f6;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: #2563eb;
            color: white;
            padding: 1.5rem;
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .profile-content {
            padding: 1.5rem;
        }

        .profile-image {
            text-align: center;
            margin-bottom: 1.5rem;
            position: relative;
            display: inline-block;
        }

        .profile-image-container {
            width: 128px;
            height: 128px;
            margin: 0 auto;
            background: #e5e7eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-image .camera-icon {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #2563eb;
            color: white;
            padding: 0.5rem;
            border-radius: 50%;
            cursor: pointer;
            border: none;
        }

        .info-section {
            margin-top: 1.5rem;
        }

        .info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .info-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .edit-button {
            color: #2563eb;
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .field {
            margin-bottom: 1rem;
        }

        .field label {
            display: block;
            font-size: 0.875rem;
            color: #4b5563;
            margin-bottom: 0.5rem;
        }

        .field input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
        }

        .field input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .save-button {
            background: #2563eb;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
            margin-top: 1rem;
        }

        .save-button:hover {
            background: #1d4ed8;
        }

        @media (max-width: 640px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>User Profile</h1>
        </div>
        
        <div class="profile-content">
            <div style="text-align: center;">
                <div class="profile-image">
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
                    <button class="camera-icon" title="Upload new picture">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                            <circle cx="12" cy="13" r="4"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="info-section">
                <div class="info-header">
                    <h2>Personal Information</h2>
                    <button class="edit-button" onclick="toggleEdit()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <span id="editButtonText">Edit</span>
                    </button>
                </div>

                <form method="POST" id="profileForm">
                    <div class="grid">
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
                    </div>

                    <div id="saveButton" style="display: none; text-align: right;">
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
            const saveButton = document.getElementById('saveButton');
            const editButtonText = document.getElementById('editButtonText');
            
            const isEditing = inputs[0].disabled;
            
            inputs.forEach(input => {
                input.disabled = !isEditing;
            });
            
            saveButton.style.display = isEditing ? 'block' : 'none';
            editButtonText.textContent = isEditing ? 'Cancel' : 'Edit';
        }
    </script>
</body>
</html>