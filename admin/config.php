<?php
// config.php - Archivo de configuración
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

// Crear directorio de uploads si no existe
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
    mkdir(UPLOAD_DIR . 'productos/', 0755, true);
}

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

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_usuario']);
}

// Función para requerir login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Función para sanitizar entrada
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Función para generar slug
function generateSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

// Función para subir imagen
function uploadImage($file, $subdir = 'productos') {
    $targetDir = UPLOAD_DIR . $subdir . '/';
    
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($imageFileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Solo se permiten imágenes JPG, JPEG, PNG, GIF o WEBP'];
    }
    
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return ['success' => false, 'message' => 'El archivo no es una imagen válida'];
    }
    
    if ($file['size'] > 5000000) { // 5MB
        return ['success' => false, 'message' => 'La imagen es demasiado grande (máx 5MB)'];
    }
    
    $uniqueName = uniqid() . '_' . time() . '.' . $imageFileType;
    $targetFile = $targetDir . $uniqueName;
    
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return [
            'success' => true, 
            'filename' => $uniqueName,
            'path' => $subdir . '/' . $uniqueName
        ];
    }
    
    return ['success' => false, 'message' => 'Error al subir la imagen'];
}

// Función para eliminar imagen
function deleteImage($path) {
    $fullPath = UPLOAD_DIR . $path;
    if (file_exists($fullPath)) {
        unlink($fullPath);
        return true;
    }
    return false;
}

// Función para formatear precio
function formatPrice($price) {
    return 'S/ ' . number_format($price, 2);
}

// Función para obtener configuración
function getConfig($key) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT valor FROM configuracion WHERE clave = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['valor'] : null;
}

// Zona horaria
date_default_timezone_set('America/Lima');
?>