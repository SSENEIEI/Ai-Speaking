<?php
require_once '../config/config.php';

// เลือก Database
$conn->select_db($dbname);

// สร้างตาราง practice_scenarios
$sql = "CREATE TABLE IF NOT EXISTS practice_scenarios (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED NOT NULL,
    title VARCHAR(100) NOT NULL,
    scenario_data JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'practice_scenarios' created successfully or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>