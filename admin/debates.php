<?php
require_once 'auth.php';
require_once '../includes/db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_debate') {
    // Validar token CSRF para creación de debate
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $message = 'Acceso denegado: Token CSRF inválido.';
    } else {
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        
        if ($titulo && $descripcion) {
            $stmt = $pdo->prepare("INSERT INTO debates (titulo, descripcion, activo) VALUES (?, ?, 1)");
            $stmt->execute([$titulo, $descripcion]);
            $message = 'Debate publicado con éxito.';
        } else {
            $message = 'Todos los campos son obligatorios.';
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
        $stmt = $pdo->prepare("UPDATE debates SET activo = NOT activo WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: debates.php');
        exit;
    }
    
    if ($action === 'delete_debate' && $id) {
        $stmt = $pdo->prepare("DELETE FROM debates WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: debates.php');
        exit;
    }
    
    if ($action === 'delete_opinion' && $id) {
        $stmt = $pdo->prepare("DELETE FROM opiniones WHERE id = ?");
        $stmt->execute([$id]);
        $debate_id = intval($_GET['debate_id'] ?? 0);
        header("Location: debates.php?view_opinions=$debate_id");
        exit;
    }
}

$view_opinions_id = intval($_GET['view_opinions'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Debates | Admin</title>
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
        
        .form-textarea-admin {
            width: 100%;
            border: 1px solid var(--border-color);
            padding: 1rem;
            margin-bottom: 2rem;
            outline: none;
            font-size: 1rem;
            font-family: var(--font-body);
            resize: vertical;
            min-height: 120px;
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
        .btn-edit { color: #111; }
        .btn-edit:hover { text-decoration: underline; }
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

        .opinions-panel {
            margin-top: 3rem;
            padding-top: 3rem;
            border-top: 1px solid var(--border-color);
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
            <a href="debates.php" class="active">Debates</a>
            <a href="students.php">Alumnos</a>
            <a href="logout.php">Salir</a>
        </div>
    </nav>

    <div class="admin-container">
        <?php if ($message): ?>
            <div class="alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="admin-grid">
            <!-- Sidebar: Cargar Debate -->
            <div class="card">
                <h2>Nuevo Debate</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="create_debate">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <label style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em; color: #888;">Título del Debate / Pregunta</label>
                    <input type="text" name="titulo" class="form-input-admin" required placeholder="Ej: ¿Es la moral individual o social?">
                    
                    <label style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em; color: #888;">Planteamiento del Tema / Contexto</label>
                    <textarea name="descripcion" class="form-textarea-admin" required placeholder="Describe el contexto del debate y las pautas para los alumnos..."></textarea>
                    
                    <button type="submit" class="btn-publish">Abrir Debate</button>
                </form>
            </div>

            <!-- Content: Lista de Debates -->
            <div class="card">
                <h2>Debates Activos e Historial</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Título / Pregunta</th>
                            <th>Estado</th>
                            <th>Opiniones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT d.*, COUNT(o.id) as total_opiniones FROM debates d LEFT JOIN opiniones o ON d.id = o.debate_id GROUP BY d.id ORDER BY d.id DESC");
                        while($row = $stmt->fetch()):
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['titulo']); ?></strong>
                                <p style="font-size: 0.75rem; color: #888; margin-top: 0.5rem; max-width: 500px;"><?php echo htmlspecialchars(substr($row['descripcion'], 0, 100)) . (strlen($row['descripcion']) > 100 ? '...' : ''); ?></p>
                            </td>
                            <td>
                                <span style="font-weight: 800; font-size: 0.7rem; text-transform: uppercase; color: <?php echo $row['activo'] ? '#2f855a' : '#c53030'; ?>">
                                    <?php echo $row['activo'] ? 'Abierto' : 'Cerrado'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="debates.php?view_opinions=<?php echo $row['id']; ?>" style="font-family: 'Syne', sans-serif; font-size: 0.75rem; font-weight: 700; color: #111; text-decoration: underline;">
                                    <?php echo $row['total_opiniones']; ?> respuestas
                                </a>
                            </td>
                            <td>
                                <a href="debates.php?action=toggle_active&id=<?php echo $row['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-action btn-toggle">
                                    <?php echo $row['activo'] ? 'Cerrar' : 'Abrir'; ?>
                                </a>
                                <a href="debates.php?action=delete_debate&id=<?php echo $row['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-action btn-delete" onclick="return confirm('¿Eliminar el debate y todas las opiniones de los alumnos definitivamente?')">Eliminar</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Detalles de Opiniones de un Debate -->
                <?php if ($view_opinions_id): 
                    $stmt = $pdo->prepare("SELECT titulo FROM debates WHERE id = ?");
                    $stmt->execute([$view_opinions_id]);
                    $debate_title = $stmt->fetchColumn();
                    
                    if ($debate_title):
                ?>
                <div class="opinions-panel">
                    <h2 style="font-size: 1.5rem; margin-bottom: 2rem;">Opiniones del Debate: <span style="font-family: var(--font-serif); font-style: italic;"><?php echo htmlspecialchars($debate_title); ?></span></h2>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 200px;">Alumno (Identificado)</th>
                                <th>Opinión</th>
                                <th style="width: 120px;">Voto</th>
                                <th style="width: 100px;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("SELECT o.*, al.nombre as alumno_nombre FROM opiniones o JOIN alumnos al ON o.alumno_id = al.id WHERE o.debate_id = ? ORDER BY o.id DESC");
                            $stmt->execute([$view_opinions_id]);
                            $opinions = $stmt->fetchAll();
                            
                            if (empty($opinions)):
                            ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #999;">Ningún alumno ha opinado en este debate todavía.</td>
                            </tr>
                            <?php else:
                                foreach ($opinions as $op):
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($op['alumno_nombre']); ?></strong>
                                    <p style="font-size: 0.7rem; color: #888; margin-top: 0.2rem;"><?php echo date('d/m/Y H:i', strtotime($op['created_at'])); ?></p>
                                </td>
                                <td style="line-height: 1.6; font-size: 0.9rem;"><?php echo htmlspecialchars($op['opinion']); ?></td>
                                <td>
                                    <?php if ($op['puntuacion']): ?>
                                        <span style="color: #ecc94b; letter-spacing: 0.1em; font-size: 1rem;">
                                            <?php echo str_repeat('★', $op['puntuacion']) . str_repeat('☆', 5 - $op['puntuacion']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #ccc; font-size: 0.75rem; text-transform: uppercase;">Sin voto</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="debates.php?action=delete_opinion&id=<?php echo $op['id']; ?>&debate_id=<?php echo $view_opinions_id; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-action btn-delete" onclick="return confirm('¿Eliminar la opinión de este alumno definitivamente?')">Moderar</a>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
