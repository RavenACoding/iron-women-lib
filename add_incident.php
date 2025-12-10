<!-- This doesn't need comment other than have someone double check --->
<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') { echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id']);
    $reportedBy = $_SESSION['user_id'];
    $incidentDate = $_POST['incident_date'];
    $incidentTime = $_POST['incident_time'] ?? null;
    $witnessName = trim($_POST['witness_name'] ?? '');
    $description = trim($_POST['description']);
    $affectedParties = trim($_POST['affected_parties'] ?? '');
    $witnessStatements = trim($_POST['witness_statements'] ?? '');
    $incidentResult = trim($_POST['incident_result'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $stmt = $conn->prepare("INSERT INTO incident_reports (user_id, reported_by, incident_date, incident_time, witness_name, description, affected_parties, witness_statements, incident_result, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssssss", $userId, $reportedBy, $incidentDate, $incidentTime, $witnessName, $description, $affectedParties, $witnessStatements, $incidentResult, $notes);
    echo json_encode($stmt->execute() ? ['success' => true, 'message' => 'Submitted'] : ['success' => false, 'message' => 'Error']);
} else { echo json_encode(['success' => false, 'message' => 'Invalid']); }
?>
