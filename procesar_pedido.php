<?php
require_once 'includes/config.php';

// Verificar que hay productos en el carrito
if (empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit;
}

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

// Sanitizar y validar datos
$nombre = sanitize($_POST['nombre']);
$apellido = sanitize($_POST['apellido']);
$email = sanitize($_POST['email']);
$telefono = sanitize($_POST['telefono']);
$direccion = sanitize($_POST['direccion']);
$ciudad = sanitize($_POST['ciudad']);
$distrito = sanitize($_POST['distrito']);
$referencia = sanitize($_POST['referencia'] ?? '');
$metodo_pago = sanitize($_POST['metodo_pago']);
$notas = sanitize($_POST['notas'] ?? '');

// Validar campos requeridos
if (empty($nombre) || empty($apellido) || empty($email) || empty($telefono) || 
    empty($direccion) || empty($ciudad) || empty($distrito) || empty($metodo_pago)) {
    $_SESSION['error'] = 'Por favor completa todos los campos requeridos';
    header('Location: checkout.php');
    exit;
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Email inválido';
    header('Location: checkout.php');
    exit;
}

// Calcular totales
$subtotal = 0;
foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}

// Calcular costo de envío según ciudad
$costo_envio_lima = (float)getConfig('costo_envio_lima', 10.00);
$costo_envio_provincia = (float)getConfig('costo_envio_provincia', 15.00);

$costo_envio = ($ciudad === 'Lima') ? $costo_envio_lima : $costo_envio_provincia;
$total = $subtotal + $costo_envio;

// Procesar comprobante de pago si existe


try {
    $pdo->beginTransaction();
    
    // 1. Verificar o crear cliente
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    $cliente = $stmt->fetch();
    
    if ($cliente) {
        $cliente_id = $cliente['id'];
        
        // Actualizar información del cliente
        $stmt = $pdo->prepare("
            UPDATE clientes SET 
            nombre = ?, apellido = ?, telefono = ?, 
            direccion = ?, distrito = ?, ciudad = ?, referencia = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $nombre, $apellido, $telefono, 
            $direccion, $distrito, $ciudad, $referencia,
            $cliente_id
        ]);
    } else {
        // Crear nuevo cliente
        $stmt = $pdo->prepare("
            INSERT INTO clientes 
            (nombre, apellido, email, telefono, direccion, distrito, ciudad, referencia)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $nombre, $apellido, $email, $telefono,
            $direccion, $distrito, $ciudad, $referencia
        ]);
        $cliente_id = $pdo->lastInsertId();
    }
    
    // 2. Generar código de pedido único
    $codigo_pedido = 'PED-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Verificar que no exista
    $stmt = $pdo->prepare("SELECT id FROM pedidos WHERE codigo_pedido = ?");
    $stmt->execute([$codigo_pedido]);
    while ($stmt->fetch()) {
        $codigo_pedido = 'PED-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("SELECT id FROM pedidos WHERE codigo_pedido = ?");
        $stmt->execute([$codigo_pedido]);
    }
    
    // 3. Crear pedido
    $stmt = $pdo->prepare("
        INSERT INTO pedidos 
        (codigo_pedido, cliente_id, nombre_cliente, email_cliente, telefono_cliente,
        direccion_entrega, distrito, ciudad, referencia, 
        subtotal, costo_envio, total, metodo_pago, comprobante_pago, estado, notas)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', ?)
    ");
    $stmt->execute([
        $codigo_pedido,
        $cliente_id,
        $nombre . ' ' . $apellido,
        $email,
        $telefono,
        $direccion,
        $distrito,
        $ciudad,
        $referencia,
        $subtotal,
        $costo_envio,
        $total,
        $metodo_pago,
        $comprobante_path,
        $notas
    ]);
    
    $pedido_id = $pdo->lastInsertId();
    
    // 4. Agregar detalles del pedido
    foreach ($_SESSION['carrito'] as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO pedido_detalles 
            (pedido_id, producto_id, nombre_producto, color, talla, precio_unitario, cantidad, subtotal)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $pedido_id,
            $item['producto_id'],
            $item['nombre'],
            $item['color'] ?? null,
            $item['talla'] ?? null,
            $item['precio'],
            $item['cantidad'],
            $item['precio'] * $item['cantidad']
        ]);
    }
    
    // 5. Actualizar estadísticas del cliente
    $stmt = $pdo->prepare("
        UPDATE clientes 
        SET total_compras = total_compras + ?, 
            numero_pedidos = numero_pedidos + 1
        WHERE id = ?
    ");
    $stmt->execute([$total, $cliente_id]);
    
    $pdo->commit();
    
    // Guardar código de pedido en sesión
    $_SESSION['ultimo_pedido'] = $codigo_pedido;
    
    // Limpiar carrito
    $_SESSION['carrito'] = [];
    
    // Redirigir a página de agradecimiento
    header('Location: gracias.php?pedido=' . $codigo_pedido);
    exit;
    
} catch (Exception $e) {
    $pdo->rollBack();
    
    // Si hay error, eliminar comprobante si se subió
    if ($comprobante_path) {
        deleteImage($comprobante_path);
    }
    
    $_SESSION['error'] = 'Error al procesar el pedido. Por favor intenta nuevamente.';
    header('Location: checkout.php');
    exit;
}
?>