<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

try {
    $student_id = $_SESSION['student_id'] ?? 0;

    $stmt = $pdo->query("SELECT * FROM debates WHERE activo = 1 ORDER BY id DESC");
    $debates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($student_id) {
        // Consultar debates en los que ya participó
        $stmt = $pdo->prepare("SELECT debate_id FROM opiniones WHERE alumno_id = ?");
        $stmt->execute([$student_id]);
        $participated = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($debates as &$d) {
            $d['participado'] = in_array($d['id'], $participated);
        }
    } else {
        foreach ($debates as &$d) {
            $d['participado'] = false;
        }
    }

    echo json_encode($debates);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
exit;
?>
