<?php
// agregar_carrito.php - Procesar agregar al carrito
require_once 'includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = (int)$_POST['producto_id'];
    $cantidad = (int)$_POST['cantidad'];
    $color = $_POST['color'] ?? '';
    $talla = $_POST['talla'] ?? '';
    
    // Validar datos
    if ($producto_id <= 0 || $cantidad <= 0) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    // Verificar que el producto existe y está activo
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ? AND activo = 1");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch();
    
    if (!$producto) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        exit;
    }
    
    // Verificar stock
    if ($cantidad > $producto['stock']) {
        echo json_encode(['success' => false, 'message' => 'Stock insuficiente']);
        exit;
    }
    
    // Crear identificador único del item (producto + color + talla)
    $item_key = $producto_id . '_' . $color . '_' . $talla;
    
    // Agregar al carrito (sesión)
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }
    
    if (isset($_SESSION['carrito'][$item_key])) {
        // Si ya existe, aumentar cantidad
        $_SESSION['carrito'][$item_key]['cantidad'] += $cantidad;
        
        // Verificar que no exceda el stock
        if ($_SESSION['carrito'][$item_key]['cantidad'] > $producto['stock']) {
            $_SESSION['carrito'][$item_key]['cantidad'] = $producto['stock'];
        }
    } else {
        // Agregar nuevo item
        $_SESSION['carrito'][$item_key] = [
            'producto_id' => $producto_id,
            'nombre' => $_POST['producto_nombre'],
            'precio' => (float)$_POST['producto_precio'],
            'cantidad' => $cantidad,
            'color' => $color,
            'talla' => $talla,
            'imagen' => $_POST['producto_imagen']
        ];
    }
    
    // Contar total de items
    $total_items = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $total_items += $item['cantidad'];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Producto agregado al carrito',
        'total_items' => $total_items
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>