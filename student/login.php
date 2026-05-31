<?php
session_start();
require_once '../includes/db.php';

if (isset($_SESSION['student_logged_in'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM alumnos WHERE username = ? AND activo = 1");
    $stmt->execute([$username]);
    $student = $stmt->fetch();

    if ($student && password_verify($password, $student['password'])) {
        $_SESSION['student_logged_in'] = true;
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['nombre'];
        $_SESSION['student_username'] = $student['username'];
        
        header('Location: ../index.php');
        exit;
    } else {
        $error = 'Credenciales inválidas o cuenta desactivada.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Estudiantes | Debates</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        :root {
            --bg-color: #ffffff;
            --text-primary: #111111;
        }
        body {
            background: var(--bg-color);
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }
        .login-container {
            display: flex;
            width: 900px;
            height: 600px;
            background: #fff;
            box-shadow: 0 50px 100px rgba(0,0,0,0.1);
            border: 1px solid #eee;
        }
        .login-visual {
            flex: 1;
            background: url('https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?q=80&w=1000&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            filter: grayscale(1) contrast(1.1);
        }
        .login-form-side {
            width: 400px;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .login-header {
            margin-bottom: 3rem;
            width: 100%;
        }
        .login-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            font-family: 'Syne', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            font-size: 0.7rem;
            color: #888;
        }
        .form-group {
            margin-bottom: 2rem;
            width: 100%;
            text-align: left;
        }
        .form-group label {
            display: block;
            font-family: 'Syne', sans-serif;
            text-transform: uppercase;
            font-size: 0.6rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            letter-spacing: 0.1em;
        }
        .form-input {
            width: 100%;
            border: none;
            border-bottom: 1px solid #ddd;
            padding: 0.8rem 0;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.3s;
        }
        .form-input:focus {
            border-color: #111;
        }
        .btn-login {
            background: #111;
            color: #fff;
            border: none;
            padding: 1.2rem;
            width: 100%;
            font-family: 'Syne', sans-serif;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.2em;
            cursor: pointer;
            transition: transform 0.3s, background 0.3s;
            margin-top: 1rem;
        }
        .btn-login:hover {
            background: #333;
            transform: translateY(-2px);
        }
        .error-msg {
            color: #ff4d4d;
            font-size: 0.8rem;
            margin-bottom: 1.5rem;
            font-family: 'Inter', sans-serif;
            width: 100%;
        }
        @media (max-width: 900px) {
            .login-container { width: 90%; height: auto; }
            .login-visual { display: none; }
            .login-form-side { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-visual"></div>
        <div class="login-form-side">
            <div class="login-header">
                <p>Portal Alumnos</p>
                <h2>Debate<br>Académico</h2>
            </div>
            
            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" style="width: 100%;">
                <div class="form-group">
                    <label>Usuario</label>
                    <input type="text" name="username" class="form-input" required autofocus>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                <button type="submit" class="btn-login">Ingresar</button>
            </form>
            
            <p style="margin-top: auto; padding-top: 2rem; font-size: 0.6rem; color: #ccc; text-transform: uppercase; letter-spacing: 0.1em;">
                &copy; <?php echo date('Y'); ?> Prof. Chaparro Bruno Rafael
            </p>
        </div>
    </div>
</body>
</html>
