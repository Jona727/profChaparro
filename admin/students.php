<?php
require_once 'auth.php';
require_once '../includes/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_student') {
    // Validar token CSRF para peticiones POST de creación de cuenta
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $error = 'Acceso denegado: Token CSRF inválido.';
    } else {
        $nombre   = trim($_POST['nombre'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        if ($nombre && $username && $password) {
            try {
                // Verificar si el usuario ya existe
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM alumnos WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'El nombre de usuario ya está registrado.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO alumnos (nombre, username, password, activo) VALUES (?, ?, ?, 1)");
                    $stmt->execute([$nombre, $username, $hash]);
                    $message = 'Alumno registrado con éxito. Ya puede iniciar sesión.';
                }
            } catch (PDOException $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        } else {
            $error = 'Todos los campos son obligatorios.';
        }
    }
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id'] ?? 0);
    
    // Validar token CSRF para peticiones GET críticas
    $token = $_GET['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        die("Acceso denegado: Token CSRF inválido.");
    }
    
    if ($action === 'toggle_active' && $id) {
        $stmt = $pdo->prepare("UPDATE alumnos SET activo = NOT activo WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: students.php');
        exit;
    }
    
    if ($action === 'delete_student' && $id) {
        $stmt = $pdo->prepare("DELETE FROM alumnos WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: students.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Alumnos | Admin</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        :root {
            --bg-color: #ffffff;
            --bg-alt: #f9f9f9;
            --text-primary: #111111;
            --text-secondary: #666666;
            --border-color: #e0e0e0;
        }
        body { background: var(--bg-color); color: var(--text-primary); }
        
        .admin-nav { 
            padding: 2rem 4rem; 
            border-bottom: 1px solid var(--border-color); 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            background: #fff;
        }
        .admin-nav h3 { font-family: 'Syne', sans-serif; text-transform: uppercase; letter-spacing: 0.1em; font-size: 1rem; }
        .nav-links a { margin-left: 2rem; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700; color: #666; text-decoration: none; }
        .nav-links a:hover, .nav-links a.active { color: #111; }
        
        .admin-container { padding: 4rem; max-width: 1400px; margin: 0 auto; }
        
        .admin-grid { 
            display: grid; 
            grid-template-columns: 400px 1fr; 
            gap: 4rem; 
        }
        
        .card { 
            background: #fff; 
            padding: 3rem; 
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            height: fit-content;
        }
        
        .card h2 { font-family: 'Playfair Display', serif; margin-bottom: 2rem; font-size: 2rem; }
        
        .form-input-admin {
            width: 100%;
            border: none;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            margin-bottom: 2rem;
            outline: none;
            font-size: 1rem;
            font-family: var(--font-body);
        }
        
        table { width: 100%; border-collapse: collapse; }
        th { 
            text-align: left; 
            text-transform: uppercase; 
            letter-spacing: 0.1em; 
            font-size: 0.7rem; 
            padding: 1rem; 
            border-bottom: 2px solid #111; 
            font-family: 'Syne', sans-serif;
        }
        td { padding: 1.5rem 1rem; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        
        .btn-action { 
            text-decoration: none; 
            font-size: 0.7rem; 
            text-transform: uppercase; 
            font-weight: 800; 
            letter-spacing: 0.1em; 
            margin-right: 1rem;
            cursor: pointer;
            background: none;
            border: none;
        }
        .btn-delete { color: #ff4d4d; }
        .btn-delete:hover { text-decoration: underline; }
        .btn-toggle { color: #2b6cb0; }
        .btn-toggle:hover { text-decoration: underline; }

        .btn-publish {
            background: #111;
            color: #fff;
            border: none;
            padding: 1.2rem;
            width: 100%;
            font-family: 'Syne', sans-serif;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.1em;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-publish:hover { background: #333; transform: translateY(-2px); }

        .alert-info {
            background: #ebf8ff;
            color: #2b6cb0;
            padding: 1rem;
            margin-bottom: 2rem;
            font-size: 0.85rem;
            border-left: 4px solid #2b6cb0;
            font-family: var(--font-body);
        }

        .alert-error {
            background: #fff5f5;
            color: #c53030;
            padding: 1rem;
            margin-bottom: 2rem;
            font-size: 0.85rem;
            border-left: 4px solid #c53030;
            font-family: var(--font-body);
        }

        /* Estilos Responsivos para Dispositivos Móviles */
        @media (max-width: 1023px) {
            .admin-nav {
                padding: 1.5rem !important;
                flex-direction: column !important;
                gap: 1.2rem !important;
                text-align: center !important;
            }
            .admin-nav .nav-links {
                display: flex !important;
                flex-wrap: wrap !important;
                justify-content: center !important;
                gap: 0.8rem 1rem !important;
            }
            .admin-nav .nav-links a {
                margin: 0 !important;
                font-size: 0.7rem !important;
            }
            .admin-container {
                padding: 2rem 1.2rem !important;
            }
            .admin-grid {
                grid-template-columns: 1fr !important;
                gap: 2rem !important;
            }
            .card {
                padding: 2rem 1.2rem !important;
            }
            /* Adaptar tabla para evitar roturas de diseño */
            table {
                display: block !important;
                width: 100% !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
                white-space: nowrap !important;
            }
        }
    </style>
</head>
<body>
    <nav class="admin-nav">
        <h3>Chaparro · Admin</h3>
        <div class="nav-links">
            <a href="index.php">Subir Material</a>
            <a href="manage.php">Gestionar Estructura</a>
            <a href="debates.php">Debates</a>
            <a href="students.php" class="active">Alumnos</a>
            <a href="logout.php">Salir</a>
        </div>
    </nav>

    <div class="admin-container">
        <?php if ($message): ?>
            <div class="alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="admin-grid">
            <!-- Sidebar: Registrar Alumno -->
            <div class="card">
                <h2>Registrar Alumno</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="create_student">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <label style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em; color: #888;">Nombre Completo</label>
                    <input type="text" name="nombre" class="form-input-admin" required placeholder="Ej: Juan Pérez">
                    
                    <label style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em; color: #888;">Usuario (para login)</label>
                    <input type="text" name="username" class="form-input-admin" required placeholder="Ej: jperez">
                    
                    <label style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em; color: #888;">Contraseña Temporal</label>
                    <input type="password" name="password" class="form-input-admin" required placeholder="••••••••">
                    
                    <button type="submit" class="btn-publish">Crear Cuenta</button>
                </form>
            </div>

            <!-- Content: Lista de Alumnos -->
            <div class="card">
                <h2>Alumnos Registrados</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Nombre de Usuario</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM alumnos ORDER BY nombre");
                        while($row = $stmt->fetch()):
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                            <td style="font-family: monospace; font-size: 0.85rem; color: #555;"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td>
                                <span style="font-weight: 800; font-size: 0.7rem; text-transform: uppercase; color: <?php echo $row['activo'] ? '#2f855a' : '#c53030'; ?>">
                                    <?php echo $row['activo'] ? 'Activo' : 'Desactivado'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="students.php?action=toggle_active&id=<?php echo $row['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-action btn-toggle">
                                    <?php echo $row['activo'] ? 'Desactivar' : 'Activar'; ?>
                                </a>
                                <a href="students.php?action=delete_student&id=<?php echo $row['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-action btn-delete" onclick="return confirm('¿Eliminar la cuenta del alumno y todas sus opiniones definitivamente?')">Eliminar</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
