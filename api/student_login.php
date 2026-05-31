<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$username || !$password) {
    echo json_encode(['success' => false, 'message' => 'Completa todos los campos.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM alumnos WHERE username = ? AND activo = 1");
    $stmt->execute([$username]);
    $student = $stmt->fetch();

    if ($student && password_verify($password, $student['password'])) {
        $_SESSION['student_logged_in'] = true;
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['nombre'];
        $_SESSION['student_username'] = $student['username'];
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos, o cuenta desactivada.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de servidor: ' . $e->getMessage()]);
}
exit;
?>
