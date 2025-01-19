<?php
session_start();
$errors = [];
$success_message = '';

include("config.php");
include("functions.php");

// Fetch current user data
function fetch_user_data($conn, $member_id) {
    $stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

if (isset($_SESSION['member_id'])) {
    try {
        $user = fetch_user_data($conn, $_SESSION['member_id']);
    } catch (Exception $e) {
        $errors[] = "Error fetching user data: " . $e->getMessage();
    }
} else {
    header("Location: login.php"); // Redirect if user is not logged in
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize input
        $updateData = [
            'member_fname' => htmlspecialchars(trim($_POST['member_fname'])),
            'member_lname' => htmlspecialchars(trim($_POST['member_lname'])),
            'member_email' => filter_var(trim($_POST['member_email']), FILTER_SANITIZE_EMAIL),
            'member_address' => htmlspecialchars(trim($_POST['member_address'])),
            'member_contactno' => htmlspecialchars(trim($_POST['member_contactno'])),
            'member_city' => htmlspecialchars(trim($_POST['member_city'])),
            'member_state' => htmlspecialchars(trim($_POST['member_state'])),
            'member_id' => $_SESSION['member_id']
        ];

        // Validate email format
        if (!filter_var($updateData['member_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        // Ensure contact number is numeric
        if (!preg_match('/^\d{10,15}$/', $updateData['member_contactno'])) {
            $errors[] = "Invalid contact number format. Please use 10-15 digits.";
        }

        // If password is provided, update it
        if (!empty($_POST['member_password'])) {
            $updateData['member_password'] = password_hash($_POST['member_password'], PASSWORD_DEFAULT);
        }

        // Prevent updating if there are validation errors
        if (empty($errors)) {
            // Create SQL query dynamically based on whether password is included
            $sql = "UPDATE members SET 
                    member_fname = ?, 
                    member_lname = ?, 
                    member_email = ?, 
                    member_address = ?, 
                    member_contactno = ?, 
                    member_city = ?, 
                    member_state = ?";

            if (isset($updateData['member_password'])) {
                $sql .= ", member_password = ?";
            }

            $sql .= " WHERE member_id = ?";

            // Prepare and bind
            $stmt = $conn->prepare($sql);
            if (isset($updateData['member_password'])) {
                $stmt->bind_param("ssssssssi", $updateData['member_fname'], $updateData['member_lname'], $updateData['member_email'], $updateData['member_address'], $updateData['member_contactno'], $updateData['member_city'], $updateData['member_state'], $updateData['member_password'], $updateData['member_id']);
            } else {
                $stmt->bind_param("sssssssi", $updateData['member_fname'], $updateData['member_lname'], $updateData['member_email'], $updateData['member_address'], $updateData['member_contactno'], $updateData['member_city'], $updateData['member_state'], $updateData['member_id']);
            }

            // Execute update
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $success_message = "Profile successfully updated!";
                // Fetch updated user data
                $user = fetch_user_data($conn, $_SESSION['member_id']);
            } else {
                $errors[] = "No changes were made to your profile.";
            }
        }

    } catch (Exception $e) {
        $errors[] = "Error updating profile: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Hawra Trading</title>
    <style>
        /* General Reset */
        body, h1, h2, p, a, input, select, button {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8e9f1;
        }

        /* Header Styling */
        header {
            background-color: #000;
            color: white;
            padding: 10px 20px;
        }

        /* Main Navigation Styling */
        .main-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
        }

        .nav-left h1 {
            font-size: 1.5rem;
        }

        .nav-center input[type="search"] {
            padding: 5px 10px;
            width: 300px;
        }

        .nav-right {
            display: flex;
            gap: 20px;
        }

        .nav-right a {
            text-decoration: none;
            color: white;
        }

        .nav-right a:hover {
            text-decoration: underline;
        }

        /* Sub Navigation Styling */
        .sub-nav {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 10px 0;
            background-color: #000;
        }

        .sub-nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .sub-nav a:hover {
            text-decoration: underline;
        }

        /* Container Styles */
        .container {
            display: flex;
            max-width: 1200px;
            margin: 20px auto;
            gap: 20px;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #FFE6F3;
            padding: 20px;
            border-radius: 10px;
        }

        .sidebar h2 {
            margin-top: 0;
            margin-bottom: 20px;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .sidebar-menu a {
            padding: 10px;
            text-decoration: none;
            color: black;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar-menu a:hover {
            background-color: #FFD6E8;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            background-color: #FFE6F3;
            padding: 20px;
            border-radius: 10px;
        }

        .main-content h2 {
            margin-top: 0;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #E8E8E8;
        }

        .button-group {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-cancel {
            background-color: #f5f5f5;
        }

        .btn-save {
            background-color: #FFE6F3;
        }

        .success-message {
            color: green;
            padding: 10px;
            background-color: #e8f5e9;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .error-message {
            color: red;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        /* Footer Styling */
        footer {
            background-color: #000; /* Black background */
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        footer a {
            color: white;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Your Account</h2>
            <div class="sidebar-menu">
                <a href="edit_profile.php">Edit Profile</a>
                <a href="purchase_history.php">Purchase History</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h2>Edit Profile</h2>
            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="member_fname">First Name</label>
                    <input type="text" id="member_fname" name="member_fname" value="<?php echo htmlspecialchars($user['member_fname']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="member_lname">Last Name</label>
                    <input type="text" id="member_lname" name="member_lname" value="<?php echo htmlspecialchars($user['member_lname']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="member_email">Email</label>
                    <input type="email" id="member_email" name="member_email" value="<?php echo htmlspecialchars($user['member_email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="member_address">Address</label>
                    <input type="text" id="member_address" name="member_address" value="<?php echo htmlspecialchars($user['member_address']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="member_contactno">Contact Number</label>
                    <input type="text" id="member_contactno" name="member_contactno" value="<?php echo htmlspecialchars($user['member_contactno']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="member_city">City</label>
                    <input type="text" id="member_city" name="member_city" value="<?php echo htmlspecialchars($user['member_city']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="member_state">State</label>
                    <select id="member_state" name="member_state" required>
                        <option value="">Select State</option>
                        <?php
                        $states = ['Sarawak','Sabah','Selangor', 'Perak', 'Negeri Sembilan', 'Kedah', 'Kelantan',
                        'Pahang', 'Terengganu', 'Perlis', 'Melaka', 'Johor','Penang'];
                        foreach ($states as $state): ?>
                            <option value="<?php echo $state; ?>" <?php echo $user['member_state'] == $state ? 'selected' : ''; ?>>
                                <?php echo $state; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="member_password">Password</label>
                    <input type="password" id="member_password" name="member_password">
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-save">Update Profile</button>
                </div>
            </form>
        </div>
    </div>
    <footer>
        <div class="contact-us">
        <img src="/img/phoneicon.png" alt="Phone" id="phone-icon">
        <a href="contact.php" id="contact-text">Contact Us</a>
    </div>
    </footer>
    <?php if (!empty($success_message)): ?>
        <script>
            alert("<?php echo $success_message; ?>");
        </script>
    <?php endif; ?>
</body>
</html>