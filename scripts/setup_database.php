<?php
require_once '../config/config.php';

// 1. สร้าง Database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// 2. เลือก Database ที่เพิ่งสร้าง
$conn->select_db($dbname);

// 3. สร้างตาราง Users
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'users' created successfully or already exists.<br>";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
echo "<br>Setup Complete! You can now use the system.";
?>