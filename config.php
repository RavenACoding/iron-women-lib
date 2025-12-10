<?php
$conn = new mysqli('localhost', 'root', '', 'user_db');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

function getCertificateTypes($conn) {
    $result = $conn->query("SELECT * FROM certificate_types ORDER BY name");
    $types = [];
    while ($row = $result->fetch_assoc()) $types[] = $row;
    return $types;
}

function getDepartments() {
    return ['Safety', 'Operations', 'Training', 'Maintenance', 'Field Work', 'Administration'];
}
?>
