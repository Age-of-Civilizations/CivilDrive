<?php

include('includes/config.inc.php');
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user's information from the database 
$user_id = $_SESSION['user_id'];
$conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();

// Initialize AWS S3 client with credentials from configuration file
$awsS3Credentials = [
    'version' => 'latest',
    'region'  => 'fsn-ger-1',
    'endpoint' => 'http://localhost:9000',
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => $dbConfig['apiKey'],
        'secret' => $dbConfig['apiSecret'],
    ],
];

$s3Client = new S3Client($awsS3Credentials);

// Check if the file key is provided in the query string
if (isset($_GET['file'])) {
    $fileKey = $_GET['file'];
    $bucketName = strtolower($user['username']);

    try {
        $fileStream = $s3Client->getObject([
            'Bucket' => $bucketName,
            'Key'    => $fileKey,
        ]);

        // Set headers for file download
        header('Content-Type: ' . $fileStream['ContentType']);
        header('Content-Disposition: attachment; filename="' . $fileKey . '"');

        // Output the file content
        echo $fileStream['Body']->getContents();
    } catch (S3Exception $e) {
        echo "Error downloading file: " . $e->getMessage();
    }
} else {
    echo "File key not provided.";
}
?>
