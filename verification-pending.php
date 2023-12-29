<?php
include 'includes/config.inc.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Pending</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container">
    <h2 class="mt-5">Verification Pending</h2>

    <p>Your account is pending verification. Please check your email for the verification link. If you haven't received the email, you can request a new verification link by clicking <a href="email.php">here</a></p>

    <p><a href="login.php">Back to login</a></p>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
