<?php

include('includes/config.inc.php');
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

// Display user's drive content
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Drive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container">
    <h2 class="mt-5">Welcome, <?php echo $user['username']; ?>!</h2>

    <div class="mt-3">
        <h3>Upload Files</h3>
        <form action="upload-handler.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <input type="file" name="uploaded_file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>

    <div class="mt-5">
        <h3>Your Files</h3>
        <?php
        // Initialize AWS S3 client
        require 'vendor/autoload.php';
        use Aws\S3\S3Client;
        use Aws\S3\Exception\S3Exception;

        $awsS3Credentials = [
            'version' => 'latest',
            'region'  => 'fsn-ger-1',
            'endpoint' => 'http://localhost:9090',
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => $dbConfig['apiKey'],
                'secret' => $dbConfig['apiSecret'],
            ],
        ];

        // Initialize AWS S3 client
        $s3Client = new S3Client($awsS3Credentials);

        // Fetch user's files from MinIO bucket
        $bucketName = strtolower($user['username']);
        try {
            $objects = $s3Client->listObjects([
                'Bucket' => $bucketName,
            ]);

            // Display the list of files with download links and file sizes
            echo '<table class="table">';
            echo '<thead><tr><th>File Name</th><th>File Size</th><th>Action</th></tr></thead>';
            echo '<tbody>';
            foreach ($objects['Contents'] as $object) {
                $fileKey = $object['Key'];
                $downloadUrl = "download-handler.php?file=$fileKey";
                $fileSize = formatBytes($object['Size']);
                echo '<tr>';
                echo "<td>$fileKey</td>";
                echo "<td>$fileSize</td>";
                echo "<td><a href=\"$downloadUrl\">Download</a></td>";
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } catch (S3Exception $e) {
            echo "Error fetching user's files: " . $e->getMessage();
        }
// Function to format bytes into a human-readable format
function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}

        ?>
    </div>

    <p class="mt-3"><a href="logout.php">Logout</a></p>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>