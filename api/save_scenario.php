<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'] ?? 'Untitled Scenario';
    
    // Collect form data matching setup_practice.php
    $data = [
        'user_role' => $_POST['user_role'] ?? '',
        'speaker_count' => $_POST['speakerCount'] ?? 2,
        'speakers' => $_POST['speakers'] ?? [],
        'script_speaker' => $_POST['script_speaker'] ?? [],
        'script_text' => $_POST['script_text'] ?? []
    ];

    $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);

    // Check if table exists, if not create it (Safety check)
    $conn->query("CREATE TABLE IF NOT EXISTS practice_scenarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        scenario_data JSON NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $conn->prepare("INSERT INTO practice_scenarios (user_id, title, scenario_data) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $title, $json_data);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'บันทึกข้อมูลสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
