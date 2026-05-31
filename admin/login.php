<?php
session_start();
require_once '../includes/db.php';

if (isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Credenciales no válidas.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Editorial | Admin</title>
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
            background: url('../assets/img/login-bg.png') no-repeat center center;
            background-size: cover;
            filter: grayscale(1);
        }
        .login-form-side {
            width: 400px;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-header {
            margin-bottom: 3rem;
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
                <p>System Access</p>
                <h2>Portfolio<br>Admin</h2>
            </div>
            
            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-input" required autofocus>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                <button type="submit" class="btn-login">Ingresar</button>
            </form>
            
            <p style="margin-top: 3rem; font-size: 0.6rem; color: #ccc; text-transform: uppercase; letter-spacing: 0.1em;">
                &copy; <?php echo date('Y'); ?> Prof. Chaparro Bruno Rafael
            </p>
        </div>
    </div>
</body>
</html>
