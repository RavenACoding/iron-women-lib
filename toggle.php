<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin' || $_SESSION['admin_type'] !== 'master') { echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id']);
    $status = intval($_POST['status']);
    $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $userId);
    echo json_encode($stmt->execute() ? ['success' => true] : ['success' => false, 'message' => 'Database error']);
} else { echo json_encode(['success' => false, 'message' => 'Invalid request']); }
?>
