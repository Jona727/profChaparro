<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

require_once '../includes/db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// Validar token CSRF para todas las acciones de modificación
if ($action && $action !== 'get_all') {
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado: Token CSRF inválido.']);
        exit;
    }
}

switch ($action) {

    case 'add_escuela':
        $nombre = trim($_POST['nombre'] ?? '');
        if (!$nombre) { echo json_encode(['success' => false, 'message' => 'Nombre vacío']); exit; }
        $stmt = $pdo->prepare("INSERT INTO escuelas (nombre) VALUES (?)");
        $stmt->execute([$nombre]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'nombre' => $nombre]);
        break;

    case 'add_anio':
        $nombre = trim($_POST['nombre'] ?? '');
        $escuela_id = $_POST['escuela_id'] ?? '';
        if (!$nombre || !$escuela_id) { echo json_encode(['success' => false, 'message' => 'Datos incompletos']); exit; }
        $stmt = $pdo->prepare("INSERT INTO anios (numero_anio, escuela_id) VALUES (?, ?)");
        $stmt->execute([$nombre, $escuela_id]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'nombre' => $nombre]);
        break;

    case 'add_materia':
        $nombre = trim($_POST['nombre'] ?? '');
        $anio_id = $_POST['anio_id'] ?? '';
        if (!$nombre || !$anio_id) { echo json_encode(['success' => false, 'message' => 'Datos incompletos']); exit; }
        $stmt = $pdo->prepare("INSERT INTO materias (nombre, anio_id) VALUES (?, ?)");
        $stmt->execute([$nombre, $anio_id]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'nombre' => $nombre]);
        break;

    case 'add_tema':
        $nombre = trim($_POST['nombre'] ?? '');
        $materia_id = $_POST['materia_id'] ?? '';
        if (!$nombre || !$materia_id) { echo json_encode(['success' => false, 'message' => 'Datos incompletos']); exit; }
        $stmt = $pdo->prepare("INSERT INTO temas (nombre, materia_id) VALUES (?, ?)");
        $stmt->execute([$nombre, $materia_id]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'nombre' => $nombre]);
        break;

    case 'delete_escuela':
        $id = $_POST['id'] ?? '';
        $stmt = $pdo->prepare("DELETE FROM escuelas WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    case 'delete_anio':
        $id = $_POST['id'] ?? '';
        $stmt = $pdo->prepare("DELETE FROM anios WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    case 'delete_materia':
        $id = $_POST['id'] ?? '';
        $stmt = $pdo->prepare("DELETE FROM materias WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    case 'delete_tema':
        $id = $_POST['id'] ?? '';
        $stmt = $pdo->prepare("DELETE FROM temas WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    case 'get_all':
        $escuelas = $pdo->query("SELECT * FROM escuelas ORDER BY nombre")->fetchAll();
        $anios = $pdo->query("SELECT a.*, e.nombre as escuela_nombre FROM anios a JOIN escuelas e ON a.escuela_id = e.id ORDER BY e.nombre, a.numero_anio")->fetchAll();
        $materias = $pdo->query("SELECT m.*, a.numero_anio, e.nombre as escuela_nombre FROM materias m JOIN anios a ON m.anio_id = a.id JOIN escuelas e ON a.escuela_id = e.id ORDER BY e.nombre, a.numero_anio, m.nombre")->fetchAll();
        $temas = $pdo->query("SELECT t.*, m.nombre as materia_nombre FROM temas t JOIN materias m ON t.materia_id = m.id ORDER BY m.nombre, t.nombre")->fetchAll();
        echo json_encode(['escuelas' => $escuelas, 'anios' => $anios, 'materias' => $materias, 'temas' => $temas]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}
?>
