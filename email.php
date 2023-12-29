<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'includes/config.inc.php';
require 'vendor/autoload.php';

use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;

$conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$email = $_SESSION["email"];
echo "Email from session: $email"; // Debug statement
$username = $_SESSION["username"];
echo "<br>Username from session: $username"; // Debug statement
$sql = 'SELECT verification_code FROM users WHERE username = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$verificationCode = $user["verification_code"];

// Send verification email using Nette\Mail
$mail = new Message;
$mail->setFrom('Civil-Drive Account Verification <support@civilhost.net>')
    ->addTo($email)
    ->setSubject('Account Verification')
    ->setBody("Hi $username, click this link to activate your account <a href='https://drive.pterodactyl.host/verify.php?code=$verificationCode'>Thanks,<br>Civilhost Team</a>", 'text/html');

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
finally {
    // Redirect the user to a page indicating that they need to check their email for verification
    header("Location: verification-pending.php");
    exit();
}
?>
