<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') { echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id']);
    $firstName = trim($_POST['first_name']);
    $middleName = trim($_POST['middle_name'] ?? '');
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $dateOfHire = $_POST['date_of_hire'] ?? null;
    $assignedAdmin = isset($_POST['assigned_admin']) && $_POST['assigned_admin'] !== '' ? intval($_POST['assigned_admin']) : null;
    $newPassword = isset($_POST['new_password']) && trim($_POST['new_password']) !== '' ? trim($_POST['new_password']) : null;
    $fullName = preg_replace('/\s+/', ' ', trim($firstName . ' ' . $middleName . ' ' . $lastName));
    
    if ($newPassword !== null) {
        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
            exit();
        }
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name = ?, first_name = ?, middle_name = ?, last_name = ?, email = ?, phone = ?, company = ?, department = ?, date_of_hire = ?, assigned_admin = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssssssssssi", $fullName, $firstName, $middleName, $lastName, $email, $phone, $company, $department, $dateOfHire, $assignedAdmin, $hashedPassword, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, first_name = ?, middle_name = ?, last_name = ?, email = ?, phone = ?, company = ?, department = ?, date_of_hire = ?, assigned_admin = ? WHERE id = ?");
        $stmt->bind_param("sssssssssii", $fullName, $firstName, $middleName, $lastName, $email, $phone, $company, $department, $dateOfHire, $assignedAdmin, $userId);
    }
    echo json_encode($stmt->execute() ? ['success' => true, 'message' => 'Updated'] : ['success' => false, 'message' => 'Error']);
} else { echo json_encode(['success' => false, 'message' => 'Invalid']); }
?>
