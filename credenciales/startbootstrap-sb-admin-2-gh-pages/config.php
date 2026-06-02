<?php
$host = '127.0.0.1';
$db   = 'credenciales';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$charset = 'utf8mb4';

// Session security configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Note: In production, do not output the raw error message
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Load .env file for encryption keys
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) continue;
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

$envKey = getenv('ENCRYPTION_KEY');
if ($envKey !== false && $envKey !== '') {
    define('ENCRYPTION_KEY', $envKey);
} else {
    throw new \RuntimeException('ENCRYPTION_KEY not set. Copy .env.example to .env and configure your keys.');
}

$paymentKey = getenv('PAYMENT_ENCRYPTION_KEY');
if ($paymentKey !== false && $paymentKey !== '') {
    define('PAYMENT_ENCRYPTION_KEY', $paymentKey);
} else {
    throw new \RuntimeException('PAYMENT_ENCRYPTION_KEY not set. Copy .env.example to .env and configure your keys.');
}
?>
