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
    $scenario_id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM practice_scenarios WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $scenario_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Scenario deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>