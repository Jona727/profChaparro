<?php
require_once 'auth.php';
require_once '../includes/db.php';

$action = $_REQUEST['action'] ?? '';

if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF para mitigar ataques de sitios cruzados
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die("Acceso denegado: Token CSRF inválido.");
    }

    $tema_id = intval($_POST['tema_id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    
    if (!$tema_id || !$titulo) {
        die("Error: Faltan campos obligatorios.");
    }
    
    // Manejo de archivo
    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === 0) {
        // 1. Validar extensión del archivo en el servidor (case-insensitive)
        $file_name_original = $_FILES['pdf']['name'];
        $extension = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            die("Error: Solo se permiten archivos con extensión .pdf.");
        }

        // 2. Validar tipo MIME real del archivo en el servidor
        $tmp_name = $_FILES['pdf']['tmp_name'];
        $mime_type = '';
        if (function_exists('mime_content_type')) {
            $mime_type = mime_content_type($tmp_name);
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);
        }

        if ($mime_type && $mime_type !== 'application/pdf') {
            die("Error: El contenido del archivo no corresponde a un documento PDF válido.");
        }
        
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        // 3. Sanitizar nombre de archivo para evitar Path Traversal e inyecciones de caracteres
        $safe_name = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($file_name_original));
        $file_name = time() . '_' . $safe_name;
        $target_path = $upload_dir . $file_name;
        $db_path = 'uploads/' . $file_name;

        if (move_uploaded_file($tmp_name, $target_path)) {
            $stmt = $pdo->prepare("INSERT INTO archivos (titulo, ruta_pdf, tema_id) VALUES (?, ?, ?)");
            $stmt->execute([$titulo, $db_path, $tema_id]);
            header('Location: index.php?status=success');
            exit;
        } else {
            die("Error al mover el archivo.");
        }
    } else {
        die("Error en el archivo cargado.");
    }
}

if ($action === 'delete') {
    // Validar token CSRF para mitigar ataques GET maliciosos
    $token = $_GET['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die("Acceso denegado: Token CSRF inválido.");
    }

    $id = intval($_GET['id'] ?? 0);
    
    // Buscar ruta para borrar el archivo físico
    $stmt = $pdo->prepare("SELECT ruta_pdf FROM archivos WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch();
    
    if ($file) {
        $full_path = '../' . $file['ruta_pdf'];
        // Evitar manipulación de rutas externas y borrar solo archivos dentro de uploads/
        if (file_exists($full_path) && strpos(realpath($full_path), realpath('../uploads/')) === 0) {
            unlink($full_path);
        }
        
        $stmt = $pdo->prepare("DELETE FROM archivos WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: index.php?status=deleted');
    exit;
}

if ($action === 'edit_title' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Validar token CSRF para llamadas AJAX POST
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado: Token CSRF inválido.']);
        exit;
    }

    $id     = intval($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    
    if (!$id || !$titulo) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
        exit;
    }
    
    $stmt = $pdo->prepare("UPDATE archivos SET titulo = ? WHERE id = ?");
    $stmt->execute([$titulo, $id]);
    
    echo json_encode(['success' => true]);
    exit;
}
?>
