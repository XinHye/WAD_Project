<?php
session_start();
$errors = [];
$success_message = '';

include 'config.php';
include 'navbar.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize input
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $contact_number = filter_input(INPUT_POST, 'contact_number', FILTER_SANITIZE_STRING);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

    // Password validation
    if (strlen($password) < 6 || strlen($password) > 8) {
        $errors[] = "Password must be between 6-8 characters long";
    }
    if (!preg_match("/[a-z]/", $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match("/[!@#$%^&*()\-_=+{};:,<.>]/", $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    if (preg_match("/\s/", $password)) {
        $errors[] = "Password must not contain spaces";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Check if user already exists
        $select = "SELECT * FROM members WHERE member_email = ?";
        $stmt = mysqli_prepare($conn, $select);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $errors[] = "User already exists!";
        } else {
            if ($user_type === "Member") {
                // Insert user data into the `members` table
                $insert = "INSERT INTO members (member_fname, member_lname, member_email, member_address, member_contactno, member_city, member_state, member_password) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $insert);
                mysqli_stmt_bind_param($stmt, "ssssssss", 
                    $first_name, $last_name, $email, $address, $contact_number, 
                    $city, $state, $hashed_password);

                if (mysqli_stmt_execute($stmt)) {
                    echo "<script>
                        alert('Your account has been created successfully as a Member!');
                        window.location.href = 'login.php';
                    </script>";
                    exit;
                } else {
                    $errors[] = "Error: " . mysqli_error($conn);
                }
            } elseif ($user_type === "Admin") {
                // Insert user data into the `user_form` table (or handle admin-specific logic here)
                $insert = "INSERT INTO user_form (user_id, first_name, last_name, email, address, contact_number, city, state, password, user_type) 
                           VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $insert);
                mysqli_stmt_bind_param($stmt, "sssssssss", 
                    $first_name, $last_name, $email, $address, $contact_number, 
                    $city, $state, $hashed_password, $user_type);

                if (mysqli_stmt_execute($stmt)) {
                    echo "<script>
                        alert('Your account has been created successfully as an Admin!');
                        window.location.href = 'login.php';
                    </script>";
                    exit;
                } else {
                    $errors[] = "Error: " . mysqli_error($conn);
                }
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
    <title>Hawra Trading - Registration</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Style all input fields */
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            margin-top: 6px;
            margin-bottom: 16px;
        }

        /* Style the submit button */
        input[type=submit] {
            background-color: #04AA6D;
            color: white;
        }

        /* Style the container for inputs */
        .container {
            background-color: #f1f1f1;
            padding: 20px;
        }

        /* The message box is shown when the user clicks on the password field */
        #message {
            display: none;
            background: #f1f1f1;
            color: #000;
            position: relative;
            padding: 20px;
            margin-top: 10px;
        }

        #message p {
            padding: 10px 35px;
            font-size: 18px;
        }

        /* Add a green text color and a checkmark when the requirements are right */
        .valid {
            color: green;
            position: relative;
            padding-left: 35px;
            margin-bottom: 10px;
            border: 2px solid green;
            border-radius: 4px;
            display: inline-block;
            width: auto;
        }

        .valid:before {
            content: "✔";
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        /* Add a red text color and an "x" when the requirements are wrong */
        .invalid {
            color: red;
            position: relative;
            padding-left: 35px;
            margin-bottom: 10px;
            border: 2px solid red;
            border-radius: 4px;
            display: inline-block;
            width: auto;
        }

        .invalid:before {
            content: "✖";
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        /* Style for the password container to align the input and checkbox */
        .password-container {
            display: flex;
            align-items: center;
        }

        /* Style for the show password label */
        .show-password-label {
            margin-left: 10px;
            font-size: 15px;
        }

        .error-messages {
            color: red;
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid red;
            border-radius: 4px;
            background-color: #fff3f3;
        }
    </style>
</head>
<body>
    <main>
        <div class="registration-form">
            <h2>Registration</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p class="error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="success-message">
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>

            <form id="registerForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required 
                       value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                       placeholder="Enter your first name">
                
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required 
                       value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                       placeholder="Enter your last name">

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       placeholder="Enter your email">

                <label for="address">Address</label>
                <input type="text" id="address" name="address" required 
                       value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>"
                       placeholder="Enter your address">

                <label for="contact_number">Contact Number</label>
                <input type="tel" id="contact_number" name="contact_number" required 
                       value="<?php echo isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : ''; ?>"
                       pattern="^\+?[0-9]{10,15}$" placeholder="+60123456789">

                <label for="city">City</label>
                <input type="text" id="city" name="city" required 
                       value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>"
                       placeholder="Enter your city">

                <label for="state">State</label>
                <select id="state" name="state" required>
                    <option value="" disabled <?php echo !isset($_POST['state']) ? 'selected' : ''; ?>>Select your state</option>
                    <?php
                    $states = ['Sarawak','Sabah','Selangor', 'Perak', 'Negeri Sembilan', 'Kedah', 'Kelantan',
                              'Pahang', 'Terengganu', 'Perlis', 'Melaka', 'Johor','Penang'];
                    foreach ($states as $state_option) {
                        $selected = (isset($_POST['state']) && $_POST['state'] === $state_option) ? 'selected' : '';
                        echo "<option value=\"" . htmlspecialchars($state_option) . "\" $selected>" . 
                             htmlspecialchars($state_option) . "</option>";
                    }
                    ?>
                </select>

                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required 
                           placeholder="Create a password" oninput="validatePassword()">
                    <label class="show-password-label">
                        <input type="checkbox" onclick="myFunction()"> Show Password
                    </label>
                </div>
                <span id="password-error" class="error-message" style="display:none;">Password must meet all requirements</span>

                <p id="letter" class="invalid">ONE <b>lowercase</b> letter</p>
                <p id="capital" class="invalid">ONE <b>capital (uppercase)</b> letter</p>
                <p id="number" class="invalid">ONE <b>number</b></p>
                <p id="special" class="invalid">ONE <b>special character</b></p>
                <p id="length" class="invalid">Between <b>6-8 characters long</b></p>
                <p id="no-space" class="invalid">No <b>spaces</b> allowed</p>

                <label for="user_type">User Type</label>
                <select name="user_type" id="user_type">
                    <option value="Member">Member</option>
                    <option value="Admin">Admin</option>
                </select>

                <button type="submit">Create Now</button>
            </form>
            <p class="login-link">If you have an account with us, <a href="login.php">Log in</a>.</p>
        </div>
    </main>
    <footer>
        <a href="contact.php">Contact Us</a>
    </footer>

    <script src="scripts.js"></script>
</body>
</html>
