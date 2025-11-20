<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

// Buscar productos
$stmt = $pdo->prepare("
    SELECT p.id, p.nombre, p.precio, p.precio_oferta,
    (SELECT ruta_imagen FROM producto_imagenes WHERE producto_id = p.id AND es_principal = 1 LIMIT 1) as imagen
    FROM productos p
    WHERE p.activo = 1 
    AND (p.nombre LIKE ? OR p.descripcion LIKE ?)
    ORDER BY p.ventas DESC, p.nombre ASC
    LIMIT 8
");

$searchTerm = "%$query%";
$stmt->execute([$searchTerm, $searchTerm]);
$productos = $stmt->fetchAll();

// Formatear resultados
$resultados = array_map(function($producto) {
    return [
        'id' => $producto['id'],
        'nombre' => $producto['nombre'],
        'precio' => formatPrice($producto['precio_oferta'] ?: $producto['precio']),
        'precio_numerico' => $producto['precio_oferta'] ?: $producto['precio'],
        'imagen' => $producto['imagen'] ? UPLOAD_URL . $producto['imagen'] : 'https://via.placeholder.com/80x100?text=Sin+Imagen',
        'url' => 'producto.php?id=' . $producto['id']
    ];
}, $productos);

echo json_encode($resultados);