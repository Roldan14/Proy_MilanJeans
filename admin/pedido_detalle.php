<?php
// pedido_detalle.php - Ver detalles completos del pedido
require_once 'config.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener datos del pedido
$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
$stmt->execute([$id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header('Location: pedidos.php');
    exit;
}

// Obtener detalles del pedido
$stmt = $pdo->prepare("
    SELECT pd.*, p.nombre as nombre_producto_real, 
    (SELECT ruta_imagen FROM producto_imagenes WHERE producto_id = p.id AND es_principal = 1 LIMIT 1) as imagen
    FROM pedido_detalles pd
    LEFT JOIN productos p ON pd.producto_id = p.id
    WHERE pd.pedido_id = ?
");
$stmt->execute([$id]);
$detalles = $stmt->fetchAll();

// Actualizar estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $nuevo_estado = $_POST['estado'];
    $notas = $_POST['notas'] ?? '';
    
    $stmt = $pdo->prepare("UPDATE pedidos SET estado = ?, notas = ? WHERE id = ?");
    $stmt->execute([$nuevo_estado, $notas, $id]);
    
    $success = "Pedido actualizado correctamente";
    
    // Recargar datos
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
    $stmt->execute([$id]);
    $pedido = $stmt->fetch();
}

// Función para obtener clase de estado
function getEstadoClass($estado) {
    $clases = [
        'pendiente' => 'warning',
        'confirmado' => 'info',
        'procesando' => 'secondary',
        'enviado' => 'success',
        'entregado' => 'success',
        'cancelado' => 'danger'
    ];
    return $clases[$estado] ?? 'secondary';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido <?= $pedido['codigo_pedido'] ?> - Milan Jeans Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .detail-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        .detail-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--light);
            margin-bottom: 20px;
        }
        
        .pedido-code {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
        }
        
        .status-badge-large {
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .info-section h3 {
            font-size: 14px;
            color: var(--gray);
            text-transform: uppercase;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: var(--gray);
            font-size: 14px;
        }
        
        .info-value {
            color: var(--dark);
            font-weight: 600;
            font-size: 14px;
        }
        
        .product-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            border: 1px solid #f0f0f0;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        
        .product-item:hover {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            background: var(--light);
        }
        
        .product-info {
            flex: 1;
        }
        
        .product-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .product-meta {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 5px;
        }
        
        .product-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
        }
        
        .price-unit {
            font-size: 14px;
            color: var(--gray);
        }
        
        .price-total {
            font-size: 16px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .total-section {
            background: var(--light);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 15px;
        }
        
        .total-row.grand {
            border-top: 2px solid #ddd;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }
        
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e0e0e0;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -26px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary);
            border: 3px solid white;
            box-shadow: 0 0 0 2px var(--primary);
        }
        
        .timeline-date {
            font-size: 12px;
            color: var(--gray);
            margin-bottom: 3px;
        }
        
        .timeline-text {
            font-size: 14px;
            color: var(--dark);
        }
        
        @media (max-width: 968px) {
            .detail-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <div>
                    <a href="pedidos.php" style="color: var(--primary); text-decoration: none; font-size: 14px; display: inline-block; margin-bottom: 10px;">
                        ← Volver a pedidos
                    </a>
                    <h1>Detalle del Pedido</h1>
                    <p>Información completa del pedido</p>
                </div>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <div class="detail-container">
                <!-- Columna Principal -->
                <div>
                    <!-- Información del Pedido -->
                    <div class="detail-card">
                        <div class="detail-header">
                            <div class="pedido-code"><?= $pedido['codigo_pedido'] ?></div>
                            <span class="status-badge-large badge-<?= $pedido['estado'] ?>">
                                <?= ucfirst($pedido['estado']) ?>
                            </span>
                        </div>
                        
                        <div class="info-section">
                            <h3>Información del Cliente</h3>
                            <div class="info-row">
                                <span class="info-label">Nombre:</span>
                                <span class="info-value"><?= htmlspecialchars($pedido['nombre_cliente']) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value"><?= htmlspecialchars($pedido['email_cliente']) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Teléfono:</span>
                                <span class="info-value"><?= htmlspecialchars($pedido['telefono_cliente']) ?></span>
                            </div>
                        </div>
                        
                        <div class="info-section">
                            <h3>Dirección de Entrega</h3>
                            <div class="info-row">
                                <span class="info-label">Dirección:</span>
                                <span class="info-value"><?= htmlspecialchars($pedido['direccion_entrega']) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Distrito:</span>
                                <span class="info-value"><?= htmlspecialchars($pedido['distrito'] ?: 'No especificado') ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Ciudad:</span>
                                <span class="info-value"><?= htmlspecialchars($pedido['ciudad'] ?: 'No especificado') ?></span>
                            </div>
                            <?php if ($pedido['referencia']): ?>
                                <div class="info-row">
                                    <span class="info-label">Referencia:</span>
                                    <span class="info-value"><?= htmlspecialchars($pedido['referencia']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="info-section">
                            <h3>Información de Pago</h3>
                            <div class="info-row">
                                <span class="info-label">Método de Pago:</span>
                                <span class="info-value"><?= htmlspecialchars($pedido['metodo_pago']) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Fecha del Pedido:</span>
                                <span class="info-value"><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></span>
                            </div>
                            <?php if ($pedido['comprobante_pago']): ?>
                                <div class="info-row">
                                    <span class="info-label">Comprobante:</span>
                                    <span class="info-value">
                                        <a href="<?= UPLOAD_URL . $pedido['comprobante_pago'] ?>" target="_blank" style="color: var(--primary);">
                                            Ver comprobante
                                        </a>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Productos del Pedido -->
                    <div class="detail-card" style="margin-top: 20px;">
                        <h3 style="font-size: 18px; margin-bottom: 20px; color: var(--dark);">Productos</h3>
                        
                        <?php foreach ($detalles as $detalle): ?>
                            <div class="product-item">
                                <img src="<?= $detalle['imagen'] ? UPLOAD_URL . $detalle['imagen'] : 'https://via.placeholder.com/80?text=Sin+Imagen' ?>" 
                                     alt="" class="product-image">
                                
                                <div class="product-info">
                                    <div class="product-name"><?= htmlspecialchars($detalle['nombre_producto']) ?></div>
                                    <div class="product-meta">
                                        <?php if ($detalle['color']): ?>
                                            Color: <?= htmlspecialchars($detalle['color']) ?>
                                        <?php endif; ?>
                                        <?php if ($detalle['talla']): ?>
                                            | Talla: <?= htmlspecialchars($detalle['talla']) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-price">
                                        <span class="price-unit">
                                            <?= formatPrice($detalle['precio_unitario']) ?> x <?= $detalle['cantidad'] ?>
                                        </span>
                                        <span class="price-total">
                                            <?= formatPrice($detalle['subtotal']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Total -->
                        <div class="total-section">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span><?= formatPrice($pedido['subtotal']) ?></span>
                            </div>
                            <div class="total-row">
                                <span>Costo de Envío:</span>
                                <span><?= formatPrice($pedido['costo_envio']) ?></span>
                            </div>
                            <div class="total-row grand">
                                <span>Total:</span>
                                <span><?= formatPrice($pedido['total']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Columna Lateral -->
                <div>
                    <!-- Actualizar Estado -->
                    <div class="detail-card">
                        <h3 style="font-size: 18px; margin-bottom: 20px; color: var(--dark);">Actualizar Pedido</h3>
                        
                        <form method="POST">
                            <div class="form-group">
                                <label>Estado del Pedido</label>
                                <select name="estado">
                                    <option value="pendiente" <?= $pedido['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="confirmado" <?= $pedido['estado'] == 'confirmado' ? 'selected' : '' ?>>Confirmado</option>
                                    <option value="procesando" <?= $pedido['estado'] == 'procesando' ? 'selected' : '' ?>>Procesando</option>
                                    <option value="enviado" <?= $pedido['estado'] == 'enviado' ? 'selected' : '' ?>>Enviado</option>
                                    <option value="entregado" <?= $pedido['estado'] == 'entregado' ? 'selected' : '' ?>>Entregado</option>
                                    <option value="cancelado" <?= $pedido['estado'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Notas Internas</label>
                                <textarea name="notas" placeholder="Agregar notas sobre el pedido..."><?= htmlspecialchars($pedido['notas'] ?? '') ?></textarea>
                            </div>
                            
                            <button type="submit" name="actualizar" class="btn btn-primary" style="width: 100%;">
                                Actualizar Pedido
                            </button>
                        </form>
                    </div>
                    
                    <!-- Timeline -->
                    <div class="detail-card" style="margin-top: 20px;">
                        <h3 style="font-size: 18px; margin-bottom: 20px; color: var(--dark);">Historial</h3>
                        
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-date"><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></div>
                                <div class="timeline-text">Pedido creado</div>
                            </div>
                            
                            <?php if ($pedido['fecha_actualizacion'] != $pedido['fecha_pedido']): ?>
                                <div class="timeline-item">
                                    <div class="timeline-date"><?= date('d/m/Y H:i', strtotime($pedido['fecha_actualizacion'])) ?></div>
                                    <div class="timeline-text">Última actualización</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>