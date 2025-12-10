<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') { echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $certId = intval($_POST['certificate_id']);
    $stmt = $conn->prepare("DELETE FROM certificates WHERE id = ?");
    $stmt->bind_param("i", $certId);
    echo json_encode($stmt->execute() ? ['success' => true] : ['success' => false, 'message' => 'Error']);
} else { echo json_encode(['success' => false, 'message' => 'Invalid']); }
?>
