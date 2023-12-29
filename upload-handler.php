<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
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
$s3Client = new S3Client($awsS3Credentials);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["uploaded_file"])) {
    // Assuming the user is logged in and you have their username
    $username = $_SESSION['username']; 

    $bucketName = strtolower($username);
 $maxFileSize = 1024 * 1024 * 1024; // 1 GB in bytes
    if ($_FILES["uploaded_file"]["size"] > $maxFileSize) {
        echo "Error: File size exceeds the limit of 1 GB.";
        exit();
    }

    // Generate a unique key (e.g., timestamp + original filename)
    $timestamp = time();
    $originalFilename = $_FILES["uploaded_file"]["name"];
    $fileKey = $timestamp . '_' . $originalFilename;

    // Get the temporary file path
    $tempFilePath = $_FILES["uploaded_file"]["tmp_name"];

    // Debugging output
echo "Temp File Path: " . $tempFilePath;

// Debugging output for $_FILES
echo "<pre>";
print_r($_FILES);
echo "</pre>";

    // Example: Upload a file to the user's bucket in MinIO
    try {
        $s3Client->putObject([
            'Bucket' => $bucketName,
            'Key'    => $fileKey,
            'Body'   => fopen($tempFilePath, 'r'),
        ]);

        echo "File uploaded to user's bucket successfully!\n";
        echo "<br>Redirecting...";
        header("refresh:3;url=mydrive.php");
    } catch (S3Exception $e) {
        echo "Error uploading file: " . $e->getMessage() . "\n";
    }
}

?>


