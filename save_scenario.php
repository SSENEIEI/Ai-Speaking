<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'] ?? 'Untitled Scenario';
    
    // Collect form data
    $data = [
        'user_name' => $_POST['user_name'],
        'num_speakers' => $_POST['num_speakers'],
        'speaker_names' => $_POST['speaker_names'] ?? [],
        'script_speaker' => $_POST['script_speaker'] ?? [],
        'script_text' => $_POST['script_text'] ?? []
    ];

    $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);

    $stmt = $conn->prepare("INSERT INTO practice_scenarios (user_id, title, scenario_data) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $title, $json_data);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Scenario saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>