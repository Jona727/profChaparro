<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['student_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

$student_id = $_SESSION['student_id'];
$debate_id  = intval($_GET['debate_id'] ?? 0);

if (!$debate_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de debate inválido.']);
    exit;
}

try {
    // Obtener opiniones ordenadas de forma descendente
    $stmt = $pdo->prepare("SELECT id, alumno_id, opinion, puntuacion, created_at FROM opiniones WHERE debate_id = ? ORDER BY id DESC");
    $stmt->execute([$debate_id]);
    $opiniones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapear alumnos a seudónimos consistentes (Compañero 1, Compañero 2, etc.) en este debate, excepto el propio alumno que será "Tú"
    $alumno_ids = array_unique(array_column($opiniones, 'alumno_id'));
    $alumno_map = [];
    $idx = 1;
    foreach ($alumno_ids as $aid) {
        if ($aid == $student_id) {
            $alumno_map[$aid] = 'Tú';
        } else {
            $alumno_map[$aid] = 'Compañero ' . $idx++;
        }
    }

    $result = [];
    foreach ($opiniones as $op) {
        $result[] = [
            'id' => $op['id'],
            'autor' => $alumno_map[$op['alumno_id']],
            'opinion' => htmlspecialchars($op['opinion']),
            'puntuacion' => $op['puntuacion'],
            'fecha' => date('d/m/Y H:i', strtotime($op['created_at'])),
            'is_mine' => ($op['alumno_id'] == $student_id)
        ];
    }

    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}
exit;
?>
