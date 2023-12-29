<?php
include('includes/config.inc.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Get the verification code from the URL
    $verificationCode = $_GET["code"];

    if (!empty($verificationCode)) {
        // Database connection
        $conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if the verification code exists in the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE verification_code = ?");
        $stmt->bind_param("s", $verificationCode);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // Update user status to "verified" (you might have a different column name)
            $updateStmt = $conn->prepare("UPDATE users SET verified = 1 WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();

            // Display success message
            $success = "Email verification successful. You can now <a href='login.php'>login</a>.";
        } else {
            // Display error message
            $error = "Invalid verification code.";
        }

        $stmt->close();
        $conn->close();
    } else {
        // Display error message if verification code is empty
        $error = "Invalid verification code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container">
    <h2 class="mt-5">Email Verification</h2>

    <?php
    if (isset($error)) {
        echo '<div class="alert alert-danger">' . $error . '</div>';
    } elseif (isset($success)) {
        echo '<div class="alert alert-success">' . $success . '</div>';
    }
    ?>

    <p class="mt-3"><a href="login.php">Back to login</a>.</p>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
