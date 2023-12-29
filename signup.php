<?php

include('includes/config.inc.php');
require 'vendor/autoload.php'; 
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;

function generateUniqueCode() {
    // Generate a random string of length 10
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $code = '';
    for ($i = 0; $i < 10; $i++) {
        $code .= $characters[rand(0, $charactersLength - 1)];
    }
    return $code;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle form submission
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Validate input (you may need more validation based on your requirements)
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Database connection
        $conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if the username or email already exists
        if ($stmt = $conn->prepare("SELECT * FROM users WHERE username=? OR email=?")) {
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                $error = "Username or email already exists.";
            } else {
                // Hash the password before storing in the database
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // After successful user registration
                $verificationCode = generateUniqueCode();

                // Save $verificationCode in the database along with other user details
                $sql = "INSERT INTO users (username, email, password, verification_code) VALUES (?, ?, ?, ?)";

                // Prepare the insert statement
                if ($stmt = $conn->prepare($sql)) {
                    // Bind parameters to the statement
                    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $verificationCode);

                    // Execute the statement
                    if ($stmt->execute()) {
                        // AWS S3 credentials
                        $awsS3Credentials = [
                            'version' => 'latest',
                            'region'  => 'fsn-ger-1', 
                            'endpoint' => 'http://localhost:9000', 
                            'use_path_style_endpoint' => true,
                            'credentials' => [
                                'key'    => $dbConfig['apiKey']",
                                'secret' => $dbConfig['apiSecret']",
                            ],
                        ];

                        // Initialize AWS S3 client
                        $s3Client = new Aws\S3\S3Client($awsS3Credentials);

                        // After user registration is successful
                        $bucketName = strtolower($username);

                        try {
                            $s3Client->createBucket([
                                'Bucket' => $bucketName,
                                'BucketQuota' => 1073741824, // 1 GB
                            ]);

                            echo "Bucket created successfully!\n";
                        } catch (S3Exception $e) {
                            echo "Error creating bucket: " . $e->getMessage() . "\n";
                        }

                        $success = "Account created successfully! Check your Email For verification.";
                        // Send verification email using Nette\Mail
                        $mail = new Message;
                        $mail->setFrom('Civil-Drive Account Verification <support@civilhost.net>')
                            ->addTo($email)
                            ->setSubject('Account Verification')
                            ->setBody("Hi $username, click this link to activate your account <a href='https://drive.pterodactyl.host/verify.php?code=$verificationCode Thanks, <br> Civilhost Team", 'text/html');

                        $mailer = new Nette\Mail\SmtpMailer([
                            'host' => $dbConfig['emailHost'],
                            'username' => $dbConfig['emailUser'],
                            'password' => $dbConfig['emailPassword'],
                            'port' => $dbConfig['emailPort'],
                        ]);


                        try {
                            $mailer->send($mail);
                        } catch (Exception $e) {
                            // Handle any exceptions that occur during email sending
                            $error = "Error sending verification email: " . $e->getMessage();
                            exit();
                        }

                        // Redirect the user to a page indicating that they need to check their email for verification
                        session_start();
                        $_SESSION["user_id"] = $user["id"];
                        $_SESSION["username"] = $user["username"];
                        $_SESSION["email"] = $user["email"];
                        header("Location: verification-pending.php");
                        exit();
                    } else {
                        $error = "Error creating account: " . $stmt->error;
                    }

                    $stmt->close();  // Close the statement
                } else {
                    $error = "Error preparing statement: " . $conn->error;
                }
            }
        } else {
            $error = "Error preparing statement: " . $conn->error;
        }

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container">
    <h2 class="mt-5">Sign Up</h2>

    <?php
    if (isset($error)) {
        echo '<div class="alert alert-danger">' . $error . '</div>';
    } elseif (isset($success)) {
        echo '<div class="alert alert-success">' . $success . '</div>';
    }
    ?>

    <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>" class="mt-3">
        <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email address:</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Sign Up</button>
    </form>

    <p class="mt-3">Already have an account? <a href="login.php">Login here</a>.</p>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
