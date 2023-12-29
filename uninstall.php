<?php

// Include the config file
include('includes/config.inc.php');

// Function to establish a database connection
function connectToDatabase() {
    global $hashedConfig;

    // Use the hashed config to get the original database login info
    $config = unserialize(password_verify('', $hashedConfig));

    $conn = new mysqli($config[0], $config[1], $config[2], $config[3]);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Function to drop tables
function dropTables($conn) {
    // Drop the 'files' table
    $sql = "DROP TABLE IF EXISTS files";
    if ($conn->query($sql) === TRUE) {
        echo "Table 'files' dropped successfully\n";
    } else {
        echo "Error dropping table: " . $conn->error . "\n";
    }

    // Drop the 'users' table
    $sql = "DROP TABLE IF EXISTS users";
    if ($conn->query($sql) === TRUE) {
        echo "Table 'users' dropped successfully\n";
    } else {
        echo "Error dropping table: " . $conn->error . "\n";
    }


}

// Function to delete the uninstall script
function deleteUninstallScript() {
    $scriptPath = __FILE__;
    if (unlink($scriptPath)) {
        echo "Uninstall script deleted successfully\n";
    } else {
        echo "Error deleting uninstall script\n";
    }
}

// Main execution starts here
echo "Uninstalling the database for your file storage app\n";

// Connect to the database
$conn = connectToDatabase();

// Drop tables
dropTables($conn);

// Delete the uninstall script
deleteUninstallScript();

// Close the database connection
$conn->close();

echo "Uninstall completed successfully\n";

?>
