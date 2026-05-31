<?php
// 1. Configuración de seguridad para las sesiones (Mitigación de Robo de Sesión y CSRF/XSS)
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1 || $_SERVER['SERVER_PORT'] == 443)) {
    ini_set('session.cookie_secure', 1);
}
ini_set('session.use_only_cookies', 1);

// 2. Parser ligero del archivo .env para entornos de hosting
$env_path = dirname(__DIR__) . '/.env';
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) continue;
        
        // Separar clave=valor
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            
            // Quitar comillas si existen
            if (preg_match('/^"?(.*?)"?$/', $val, $matches)) {
                $val = $matches[1];
            }
            
            // Definir en variables del sistema si no están definidas
            if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                putenv("{$key}={$val}");
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }
}

// 3. Obtener credenciales desde el entorno o fallback a local (XAMPP)
$host    = getenv('DB_HOST')    ?: 'localhost';
$db      = getenv('DB_NAME')    ?: 'portfolio_chaparro';
$user    = getenv('DB_USER')    ?: 'root';
$pass    = getenv('DB_PASS')    !== false ? getenv('DB_PASS') : '';
$charset = getenv('DB_CHARSET')  ?: 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // En producción, no mostrar el error detallado para evitar fuga de información
     die("Error de conexión a la base de datos.");
}
?>
