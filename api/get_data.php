<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

$type = $_GET['type'] ?? '';
$parent_id = $_GET['parent_id'] ?? 0;

if (!$type || !$parent_id) {
    echo json_encode([]);
    exit;
}

$data = [];

switch ($type) {
    case 'anios':
        $stmt = $pdo->prepare("SELECT id, numero_anio FROM anios WHERE escuela_id = ? ORDER BY numero_anio");
        $stmt->execute([$parent_id]);
        $data = $stmt->fetchAll();
        break;
    case 'materias':
        $stmt = $pdo->prepare("SELECT id, nombre FROM materias WHERE anio_id = ? ORDER BY nombre");
        $stmt->execute([$parent_id]);
        $data = $stmt->fetchAll();
        break;
    case 'temas':
        $stmt = $pdo->prepare("SELECT id, nombre FROM temas WHERE materia_id = ? ORDER BY nombre");
        $stmt->execute([$parent_id]);
        $data = $stmt->fetchAll();
        break;
}

echo json_encode($data);
?>
