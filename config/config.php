<?php
$servername = getenv('DB_HOST') ?: "gateway01.ap-southeast-1.prod.aws.tidbcloud.com";
$port = getenv('DB_PORT') ?: "4000";
$username = getenv('DB_USER') ?: "3CxVvU9yW5XoNFr.root";
$password = getenv('DB_PASS') ?: "B88J4GhVFxQXC5Cu";
$dbname = getenv('DB_NAME') ?: "ai_speaking_db";

// Initialize MySQLi
$conn = mysqli_init();
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

// Connect without selecting database first (to create it if needed)
// Note: On TiDB Cloud, you might need to connect to the specific DB directly or have permissions.
if (!$conn->real_connect($servername, $username, $password, null, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Connection failed: " . mysqli_connect_error());
}
?>