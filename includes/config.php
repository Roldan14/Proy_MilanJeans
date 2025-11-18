<?php
// includes/config.php - Configuración del frontend
session_start();

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'milanjeans_db');

// Configuración del sitio
define('SITE_URL', 'http://localhost/milanjeans');
define('ADMIN_URL', SITE_URL . '/admin');

// Rutas de archivos
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Conexión a la base de datos
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Función para sanitizar entrada
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Función para formatear precio
function formatPrice($price) {
    return 'S/ ' . number_format($price, 2);
}

// Función para obtener configuración
function getConfig($key, $default = '') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT valor FROM configuracion WHERE clave = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['valor'] : $default;
}

// Función para contar items del carrito
function contarCarrito() {
    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['cantidad'];
    }
    return $total;
}

// Función para calcular total del carrito
function totalCarrito() {
    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }
    return $total;
}

// Cargar configuración global
$config_sitio = [
    'nombre' => getConfig('nombre_tienda', 'Milan Jeans'),
    'email' => getConfig('email_contacto'),
    'telefono' => getConfig('telefono_contacto'),
    'whatsapp' => getConfig('whatsapp_numero'),
    'facebook' => getConfig('facebook_url'),
    'instagram' => getConfig('instagram_url'),
    'logo' => getConfig('logo')
];

// Zona horaria
date_default_timezone_set('America/Lima');
?>