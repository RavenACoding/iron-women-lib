<!-- ECHO ECHO ECHO --->
<?php

session_start();
require_once 'config.php';
header('Content-Type: application/json');
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') { echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $certTypeId = intval($_POST['certificate_type_id']);
    $issueDate = $_POST['issue_date'];
    $expiryDate = $_POST['expiry_date'];
    $uploadedBy = $_SESSION['user_id'];
    $userIds = $_POST['user_ids'] ?? [];
    if (empty($userIds)) { echo json_encode(['success' => false, 'message' => 'No users']); exit(); }
    $count = 0;
    foreach ($userIds as $userId) {
        $userId = intval($userId);
        $certNumber = 'CERT-' . time() . '-' . $userId;
        $stmt = $conn->prepare("INSERT INTO certificates (user_id, uploaded_by, certificate_type_id, certificate_number, issue_date, expiry_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisss", $userId, $uploadedBy, $certTypeId, $certNumber, $issueDate, $expiryDate);
        if ($stmt->execute()) $count++;
    }
    echo json_encode($count > 0 ? ['success' => true, 'message' => "Added to $count user(s)"] : ['success' => false, 'message' => 'Failed']);
} else { echo json_encode(['success' => false, 'message' => 'Invalid']); }
?>
