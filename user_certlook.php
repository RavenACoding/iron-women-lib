<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') { 
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); 
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);
    
    // Fetch certificates for the specified user
    $stmt = $conn->prepare("
        SELECT c.*, ct.name as cert_name 
        FROM certificates c 
        JOIN certificate_types ct ON c.certificate_type_id = ct.id 
        WHERE c.user_id = ? 
        ORDER BY c.expiry_date ASC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $certificates = [];
    while ($row = $result->fetch_assoc()) {
        $certificates[] = [
            'id' => $row['id'],
            'cert_name' => $row['cert_name'],
            'certificate_number' => $row['certificate_number'],
            'issue_date' => $row['issue_date'],
            'expiry_date' => $row['expiry_date']
        ];
    }
    
    echo json_encode(['success' => true, 'certificates' => $certificates]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
