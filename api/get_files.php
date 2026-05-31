<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

$school = $_GET['school'] ?? '';
$year = $_GET['year'] ?? '';
$subject = $_GET['subject'] ?? '';
$topic = $_GET['topic'] ?? '';

// Construir consulta dinámica
$query = "SELECT a.* FROM archivos a 
          JOIN temas t ON a.tema_id = t.id
          JOIN materias m ON t.materia_id = m.id
          JOIN anios an ON m.anio_id = an.id
          JOIN escuelas e ON an.escuela_id = e.id
          WHERE 1=1";

$params = [];

if ($school) {
    $query .= " AND e.id = ?";
    $params[] = $school;
}
if ($year) {
    $query .= " AND an.id = ?";
    $params[] = $year;
}
if ($subject) {
    $query .= " AND m.id = ?";
    $params[] = $subject;
}
if ($topic) {
    $query .= " AND t.id = ?";
    $params[] = $topic;
}

$query .= " ORDER BY a.fecha_subida DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$files = $stmt->fetchAll();

echo json_encode($files);
?>
