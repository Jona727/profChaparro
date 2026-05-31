<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['student_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$student_id = $_SESSION['student_id'];
$debate_id  = intval($_POST['debate_id'] ?? 0);
$opinion    = trim($_POST['opinion'] ?? '');
$puntuacion = isset($_POST['puntuacion']) && $_POST['puntuacion'] !== '' ? intval($_POST['puntuacion']) : null;

if (!$debate_id || !$opinion) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
    exit;
}

if ($puntuacion !== null && ($puntuacion < 1 || $puntuacion > 5)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Puntuación inválida.']);
    exit;
}

try {
    // Verificar si ya participó en este debate (seguridad extra en PHP)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM opiniones WHERE debate_id = ? AND alumno_id = ?");
    $stmt->execute([$debate_id, $student_id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya has opinado en este debate.']);
        exit;
    }

    // Insertar la opinión
    $stmt = $pdo->prepare("INSERT INTO opiniones (debate_id, alumno_id, opinion, puntuacion) VALUES (?, ?, ?, ?)");
    $stmt->execute([$debate_id, $student_id, $opinion, $puntuacion]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}
exit;
?>
