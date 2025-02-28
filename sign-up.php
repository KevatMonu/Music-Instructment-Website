<?php
session_start();
$conn = new mysqli("localhost", "root", "", "musicstore_database", 3306);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone_number = trim($_POST['phone_number'] ?? '');
    $user_role = $_POST['user_role'] ?? 'customer';
    
    // Address Fields
    $flat_house_building = trim($_POST['flat_house_building'] ?? '');
    $area_street_village = trim($_POST['area_street_village'] ?? '');
    $landmark = trim($_POST['landmark'] ?? '');
    $town_city_state_country = trim($_POST['town_city_state_country'] ?? '');

    // Admin-Specific Fields
    $admin_code = trim($_POST['admin_code'] ?? '');

    // Validation
    if (empty($name)) $errors['name'] = "Enter your name";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Enter a valid email address";
    if (empty($password) || strlen($password) < 6) $errors['password'] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match";
    if (empty($phone_number) || !preg_match("/^[0-9]{10}$/", $phone_number)) $errors['phone_number'] = "Enter a valid 10-digit phone_number number";

    if ($user_role == "customer") {
        if (empty($flat_house_building) || empty($area_street_village) || empty($town_city_state_country)) {
            $errors['address'] = "Complete address required";
        }
        $full_address = "$flat_house_building, $area_street_village, $landmark, $town_city_state_country";
    } else {
        if (empty($admin_code)) {
            $errors['admin_code'] = "Admin code is required";
        }
        $full_address = "Admin User";
    }

    // Proceed if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (full_name, email_address, user_password, user_role, user_address, phone_number) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $hashed_password, $user_role, $full_address, $phone_number);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! Please sign in.";
            $stmt->close();
            $conn->close();
            header("Location: sign-in.php");
            exit();
        } else {
            $errors['database'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 350px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        .error {
            color: red;
            font-size: 14px;
        }
        p.success {
            text-align: center;
            color: green;
        }
    </style>
    <script>
        function toggleFields() {
            var userRole = document.getElementById("user_role").value;
            var addressSection = document.getElementById("address_fields");
            var adminFields = document.getElementById("admin_fields");

            if (userRole === "admin") {
                addressSection.style.display = "none";
                adminFields.style.display = "block";
            } else {
                addressSection.style.display = "block";
                adminFields.style.display = "none";
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Create Account</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <p class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="tel" name="phone_number" placeholder="phone_number Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            
            <select name="user_role" id="user_role" required onchange="toggleFields()">
                <option value="customer">Customer</option>
                <option value="admin">Admin</option>
            </select>

            <!-- Address Fields (Visible only for Customers) -->
            <div id="address_fields">
                <h3>Address</h3>
                <input type="text" name="flat_house_building" placeholder="Flat, House no., Building, Apartment">
                <input type="text" name="area_street_village" placeholder="Area, Street, Sector, Village">
                <input type="text" name="landmark" placeholder="Landmark (Optional)">
                <input type="text" name="town_city_state_country" placeholder="Town/City, State, Country">
            </div>

            <!-- Admin Fields (Visible only for Admins) -->
            <div id="admin_fields" style="display: none;">
                <h3>Admin Verification</h3>
                <input type="text" name="admin_code" placeholder="Enter Admin Code">
            </div>

            <button type="submit">Create Account</button>
        </form>
    </div>
</body>
</html>
