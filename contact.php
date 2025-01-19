<?php
session_start();
include("config.php");
include("navbar.php");
$errors = [];
$success_message = '';

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['member_id']; // Fetch member_id from session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $member_fname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $member_lname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $member_email = mysqli_real_escape_string($conn, $_POST['email']);
    $member_contactno = mysqli_real_escape_string($conn, $_POST['phone']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);

    // Insert the data into the contact_us table
    $sql = "INSERT INTO contact_us (member_id, member_fname, member_lname, member_email, member_contactno, subject) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $member_id, $member_fname, $member_lname, $member_email, $member_contactno, $subject);

    if ($stmt->execute()) {
        $success_message = "Your message has been sent successfully!";
    } else {
        $errors[] = "Failed to send your message. Please try again later.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
<style>
    
/* Contact Page Main Content */
main {
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    text-align: left;
}

/* Contact Form Styling */
.contact-container form {
    margin-top: 20px;
}

.contact-container label {
    display: block;
    font-size: 1rem;
    color: #333;
    margin-bottom: 5px;
}

.contact-container input,
.contact-container textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.contact-container textarea {
    resize: vertical;
}

.contact-container button {
    display: inline-block;
    background-color: green;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: bold;
}

.contact-container button:hover {
    background-color: darkgreen;
}

/* Responsive Design */
@media (max-width: 600px) {
    .nav-center input[type="search"] {
        width: 100%;
    }

    main {
        margin: 20px;
        padding: 15px;
    }

    .contact-container button {
        width: 100%;
    }
}


    </style>
</head>
<body>
<main>
    <h1 style="text-align:center">Feel Free to Contact Us</h1>
    <div class="contact-container">
        <form method="POST" action="">
            <?php if ($success_message): ?>
                <p style="color: green;"><?= $success_message ?></p>
            <?php elseif (!empty($errors)): ?>
                <p style="color: red;"><?= implode('<br>', $errors) ?></p>
            <?php endif; ?>

            <label for="fname">First Name</label>
            <input type="text" id="fname" name="firstname" placeholder="Your first name.." required>

            <label for="lname">Last Name</label>
            <input type="text" id="lname" name="lastname" placeholder="Your last name.." required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Your email.." required>

            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" placeholder="Your phone number.." required>

            <label for="subject">Subject</label>
            <textarea id="subject" name="subject" placeholder="Write something.." style="height:200px" required></textarea>

            <button type="submit">Submit</button>
        </form>
    </div>
</main>
<?php if ($success_message): ?>
    <script type="text/javascript">
        alert("<?php echo addslashes($success_message); ?>");
    </script>
<?php elseif (!empty($errors)): ?>
    <script type="text/javascript">
        alert("<?php echo addslashes(implode(", ", $errors)); ?>");
    </script>
<?php endif; ?>
</body>
</html>
