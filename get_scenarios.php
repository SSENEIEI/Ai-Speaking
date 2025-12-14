<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT id, title, scenario_data, created_at FROM practice_scenarios WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$scenarios = [];
while ($row = $result->fetch_assoc()) {
    $scenarios[] = $row;
}

echo json_encode(['success' => true, 'scenarios' => $scenarios]);

$stmt->close();
$conn->close();
?>