<?php
    session_start();
    if (!isset($_SESSION['adminID'])) {
        // Redirect to login page if the admin is not logged in
        header("Location: loginadmin.php");
        exit();
    }

    include 'config.php';

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch admin's name from the database using the session `adminID`
    $adminName = "Admin"; // Default name
    $adminId = $_SESSION['adminID'];
    $sql = "SELECT Name FROM login_admin WHERE AdminID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $adminId); // Assuming AdminID is a string
    $stmt->execute();
    $stmt->bind_result($name);
    if ($stmt->fetch()) {
        $adminName = $name;
    }
    $stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Report</title>
    <!-- Include Flatpickr -->
    <script src=
        "https://cdn.jsdelivr.net/npm/flatpickr">
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- html2pdf CDN-->
    <script src=
        "https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js">
    </script>
    <link rel="stylesheet" href="TR.css">
</head>
<body>
    <header>
        <nav class="main-nav">
            <div class="nav-left">
                <h1>Hawra Trading</h1>
            </div>
            <div class="nav-center">
                <input type="search" placeholder="Search">
            </div>
            <div class="nav-right">
                <a href="loginadmin.php">Log Out</a>
                <a href="profileadmin.php">Profile</a>
            </div>
        </nav>
        <div class="sub-nav">
            <a href="index.html">Home</a>
            <a href="categories.php">Categories</a>
            <a href="messages.php">Messages</a>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h3>Welcome, <?php echo htmlspecialchars($adminName); ?>!</h3>
            <button onclick="location.href='memberdetails.php'">Member Details</button>
            <button onclick="location.href='product.php'">Product Details</button>
            <button onclick="location.href='ServiceDetails.php'">Service Details</button>
            <button onclick="location.href='TransactionReport.php'">Transaction Report</button>
            </br></br></br></br></br></br></br></br></br></br></br></br>
            <button onclick="location.href='Admin.php'">Back to Dashboard</button>
        </div>

        <!-- Main Dashboard -->
        <div class="dashboard" id="dashboard">
            <?php 
                include 'config.php';     
                date_default_timezone_set('Asia/Kuala_Lumpur');
            ?>
            
            <h1>Transaction Report</h1>

            <!-- Sections for filters -->
            <label for="filter">Filter by:</label>
            <select id="filter" onchange="filterTransaction()">
                <option value="all" <?php echo (($_POST['filter'] ?? '') === 'all' ? 'selected' : ''); ?>>All</option>
                <option value="daily" <?php echo (($_POST['filter'] ?? '') === 'daily' ? 'selected' : ''); ?>>Daily</option>
                <option value="weekly" <?php echo (($_POST['filter'] ?? '') === 'weekly' ? 'selected' : ''); ?>>Weekly</option>
                <option value="monthly" <?php echo (($_POST['filter'] ?? '') === 'monthly' ? 'selected' : ''); ?>>Monthly</option>
                <option value="custom" <?php echo (($_POST['filter'] ?? '') === 'custom' ? 'selected' : ''); ?>>Custom</option>
            </select>


            <!--No Transactions Filter-->
            <div id="allFilter" class="filter-section">
                <table class="table table-bordered">
                    <tbody>
                        <?php $_POST['filter'] = 'all'; include 'filter.php'; ?>
                    </tbody>
                </table>
            </div>

            <!--Daily Transactions Filter-->
            <div id="dailyFilter" class="filter-section hidden">
                <table class="table table-bordered">
                    <tbody>
                        <?php $_POST['filter'] = 'daily'; include 'filter.php'; ?>
                    </tbody>
                </table>
            </div>

            <!--Weekly Transactions Filter-->
            <div id="weeklyFilter" class="filter-section hidden">
                <table class="table table-bordered">
                    <tbody>
                        <?php $_POST['filter'] = 'weekly'; include 'filter.php'; ?>
                    </tbody>
                </table>
            </div>

            <!--Monthly Transactions Filter-->
            <div id="monthlyFilter" class="filter-section hidden">
                <form method="POST" action="TransactionReport.php">
                    <select name="month" required>
                        <option value="">---Select a Month---</option>
                        <?php
                        foreach (range(1, 12) as $month) {
                            echo "<option value='$month'>" . date('F', mktime(0, 0, 0, $month, 1)) . "</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" name="filter" value="monthly">Filter</button>
                </form>
                <table class="table table-bordered">
                    <tbody>
                        <?php $_POST['filter'] = 'monthly'; include 'filter.php'; ?>
                    </tbody>
                </table>
            </div>

            <!-- Custom Date Transactions Filter -->
            <div id="customFilter" class="filter-section hidden">
                <?php require 'customfilter.php'; ?>
                <form name="frmSearch" method="POST" action="TransactionReport.php">
                    <div class="search_input">
                        <label for="post_at">From Date:</label>
                        <input 
                            type="text"
                            id="post_at" 
                            name="search[post_at]"  
                            value="<?php echo htmlspecialchars($post_at); ?>" 
                            class="input-control datepicker" 
                        />

                        <label for="post_at_to_date" style="margin-left:10px">To Date:</label>
                        <input 
                            type="text" 
                            id="post_at_to_date" 
                            name="search[post_at_to_date]" 
                            value="<?php echo htmlspecialchars($post_at_to_date); ?>" 
                            class="input-control datepicker" 
                        />		
                        
                        <input type="submit" name="go" value="Search">
                    </div>
                </form>
                <table class="table table-bordered">
                    <tbody>
                    <?php 
                        $_POST['filter'] = 'custom'; 
                        include 'filter.php'; 
                    ?>
                    </tbody>
                </table>
            </div>
            
            </br>
            <!-- Generate PDF Button -->
            <button id="button">Generate PDF</button>

            <!-- Footer -->
            <div class="footer">
                <p>&copy; 2024 Hawra Trading</p>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function filterTransaction() {
            const filter = document.getElementById('filter').value;
            const sections = document.querySelectorAll('.filter-section');
            sections.forEach(section => section.classList.add('hidden'));
            document.getElementById(`${filter}Filter`).classList.remove('hidden');
        }
        document.addEventListener('DOMContentLoaded', function() {
            const selectedFilter = "<?php echo $_POST['filter'] ?? 'all'; ?>";
            document.getElementById('filter').value = selectedFilter;
            const sections = document.querySelectorAll('.filter-section');
            sections.forEach(section => section.classList.add('hidden'));
            document.getElementById(`${selectedFilter}Filter`).classList.remove('hidden');
        });
        document.addEventListener("DOMContentLoaded", function() {
            let button = document.getElementById("button");
            let dashboard = document.getElementById("dashboard");

            button.addEventListener("click", function () {
                html2pdf().from(dashboard).save();
            });
        });
        // Initialize date picker for input fields
        document.addEventListener('DOMContentLoaded', function() {
            const datepickers = document.querySelectorAll('.datepicker');
            datepickers.forEach(function(datepicker) {
                // Use any date picker library, e.g., flatpickr, Pikaday, etc.
                flatpickr(datepicker, {
                    dateFormat: "d-m-Y",
                });
            });
        });
    </script>
</body>
</html>