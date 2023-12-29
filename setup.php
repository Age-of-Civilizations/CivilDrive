<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to create a config file
function createConfigFile($host, $username, $password, $database) {
    $dbConfig = [
        'host' => $host,
        'username' => $username,
        'password' => $password,
        'database' => $database,
        'key' => $apiKey,
        'secret' => $apiSecret,
        'emailHost' => $emailHost,
        'emailUser' => $emailUser,
        '$emailPassword' => $emailPassword,
        '$emailPort' => $emailPort
    ];

    $configContent = "<?php\n";
    $configContent .= "// Database configuration\n";
    $configContent .= "\$dbConfig = " . var_export($dbConfig, true) . ";\n";
    $configContent .= "?>";

    // Write the config to config.inc.php in the includes folder
    file_put_contents('includes/config.inc.php', $configContent);

    // Return the database configuration for use in setting up tables
    return $dbConfig;
}

// Function to establish a database connection
function connectToDatabase($host, $username, $password, $database) {
    $conn = new mysqli($host, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Function to create necessary tables
function createTables($conn) {
    // Table for user accounts
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        verified tinyint default 0,
        verification_code VARCHAR(255) NOT NULL
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table 'users' created successfully\n";
    } else {
        echo "Error creating table 'users': " . $conn->error . "\n";
    }

    // Table for files
    $sql = "CREATE TABLE IF NOT EXISTS files (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table 'files' created successfully\n";
    } else {
        echo "Error creating table 'files': " . $conn->error . "\n";
    }


    // Close the database connection
    $conn->close();
}

// Main execution starts here
echo "Setting up the database";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect database connection information from the form
    $host = $_POST["host"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $database = $_POST["database"];
    $apiKey = $_POST["apiKey"];
    $apiSecret = $_POST["apiSecret"];
    $emailHost = $_POST["emailHost"];
    $emailUser = $_POST["emailUser"];
    $emailPassword = $_POST["emailPassword"];
    $emailPort = $_POST["emailPort"];

    // Create config file and get database configuration
    $dbConfig = createConfigFile($host, $username, $password, $database);

    // Connect to the database using the configuration
    $conn = connectToDatabase($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);

    // Create necessary tables
    createTables($conn);

    echo "Setup completed successfully\n";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container">
    <h2 class="mt-5">Database Setup</h2>

    <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>" class="mt-3">
        <div class="mb-3">
            <label for="host" class="form-label">MySQL Host:</label>
            <input type="text" name="host" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="username" class="form-label">MySQL Username:</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">MySQL Password:</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label for="database" class="form-label">MySQL Database Name:</label>
            <input type="text" name="database" class="form-control" required>
        </div>
               <div class="mb-3">
            <label for="apiKey" class="form-label">Api Key:</label>
            <input type="text" name="apiKey" class="form-control" required>
        </div>
               <div class="mb-3">
            <label for="apiSecret" class="form-label">Api Secret:</label>
            <input type="text" name="apiSecret" class="form-control" required>
        </div>
                       <div class="mb-3">
            <label for="emailHost" class="form-label">Email Host:</label>
            <input type="text" name="emailHost" class="form-control" required>
        </div>
               <div class="mb-3">
            <label for="emailUser" class="form-label">Email User:</label>
            <input type="text" name="emailUser" class="form-control" required>
        </div>
                       <div class="mb-3">
            <label for="emailPassword" class="form-label">Email Password:</label>
            <input type="text" name="emailPassword" class="form-control" required>
        </div>
               <div class="mb-3">
            <label for="emailPort" class="form-label">Email Port:</label>
            <input type="number" name="emailPort" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Setup</button>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
