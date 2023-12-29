<?php

include('includes/config.inc.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle form submission
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Validate input 
    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    } else {
        $conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT * FROM users WHERE username=? OR email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Verify password using password_verify() and check user status
        if ($user && password_verify($password, $user["password"])) {
            if ($user["verified"] == 1) {
                // User is verified, set session variables
                session_start();
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                header("refresh:3;url=mydrive.php");
                $success = "Login successful! Redirecting...";
            } else {
                // User is not verified, display error message
                session_start();
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["email"] = $user["email"];
                header("location: verification-pending.php");
            }
        } else {
            $error = "Invalid username or password.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container">
    <h2 class="mt-5">Login</h2>

    <?php
    if (isset($error)) {
        echo '<div class="alert alert-danger">' . $error . '</div>';
    } elseif (isset($success)) {
        echo '<div class="alert alert-success">' . $success . '</div>';
    }
    ?>

    <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>" class="mt-3">
        <div class="mb-3">
            <label for="username" class="form-label">Username or Email:</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Login</button>
    </form>

    <p class="mt-3">Don't have an account? <a href="signup.php">Sign up here</a>.</p>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
