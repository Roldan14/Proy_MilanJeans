<?php
// cliente_detalle.php - Detalle del cliente
require_once 'config.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener datos del cliente
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    header('Location: clientes.php');
    exit;
}

// Obtener pedidos del cliente
$stmt = $pdo->prepare("
    SELECT p.*, 
    (SELECT COUNT(*) FROM pedido_detalles WHERE pedido_id = p.id) as total_items
    FROM pedidos p
    WHERE p.cliente_id = ?
    ORDER BY p.fecha_pedido DESC
");
$stmt->execute([$id]);
$pedidos = $stmt->fetchAll();

// Estadísticas del cliente
$stats = [
    'total_pedidos' => count($pedidos),
    'total_gastado' => $pdo->prepare("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE cliente_id = ?")->execute([$id]) ? $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE cliente_id = $id")->fetchColumn() : 0,
    'pedidos_completados' => $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE cliente_id = ? AND estado = 'entregado'")->execute([$id]) ? $pdo->query("SELECT COUNT(*) FROM pedidos WHERE cliente_id = $id AND estado = 'entregado'")->fetchColumn() : 0,
    'pedidos_pendientes' => $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE cliente_id = ? AND estado IN ('pendiente', 'confirmado', 'procesando')")->execute([$id]) ? $pdo->query("SELECT COUNT(*) FROM pedidos WHERE cliente_id = $id AND estado IN ('pendiente', 'confirmado', 'procesando')")->fetchColumn() : 0
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente: <?= htmlspecialchars($cliente['nombre']) ?> - Milan Jeans Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .detail-container {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 20px;
        }
        
        .detail-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .cliente-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--light);
            margin-bottom: 20px;
        }
        
        .cliente-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: 700;
            color: white;
            margin: 0 auto 15px;
        }
        
        .cliente-header h2 {
            font-size: 22px;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .cliente-header p {
            color: var(--gray);
            font-size: 14px;
        }
        
        .badge-vip {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            margin-top: 10px;
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
            padding: 10px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: var(--gray);
            font-size: 13px;
        }
        
        .info-value {
            color: var(--dark);
            font-weight: 600;
            font-size: 13px;
        }
        
        .stats-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            background: var(--light);
            border-radius: 8px;
        }
        
        .stat-item h4 {
            font-size: 28px;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .stat-item p {
            font-size: 12px;
            color: var(--gray);
        }
        
        .pedidos-list h3 {
            font-size: 18px;
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light);
        }
        
        .pedido-item {
            padding: 15px;
            border: 2px solid #f0f0f0;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .pedido-item:hover {
            border-color: var(--primary);
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.1);
        }
        
        .pedido-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .pedido-code {
            font-size: 16px;
            font-weight: 700;
            color: var(--dark);
        }
        
        .pedido-body {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            font-size: 13px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray);
        }
        
        @media (max-width: 968px) {
            .detail-container {
                grid-template-columns: 1fr;
            }
            
            .pedido-body {
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
                    <a href="clientes.php" style="color: var(--primary); text-decoration: none; font-size: 14px; display: inline-block; margin-bottom: 10px;">
                        ← Volver a clientes
                    </a>
                    <h1>Detalle del Cliente</h1>
                    <p>Información completa e historial de compras</p>
                </div>
            </div>
            
            <div class="detail-container">
                <!-- Sidebar del Cliente -->
                <div>
                    <div class="detail-card">
                        <div class="cliente-header">
                            <div class="cliente-avatar-large">
                                <?= strtoupper(substr($cliente['nombre'], 0, 1)) ?>
                            </div>
                            <h2><?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?></h2>
                            <p>Cliente #<?= $cliente['id'] ?></p>
                            <?php if ($stats['total_gastado'] > 500): ?>
                                <span class="badge-vip">Cliente VIP</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="stats-box">
                            <div class="stat-item">
                                <h4><?= $stats['total_pedidos'] ?></h4>
                                <p>Pedidos</p>
                            </div>
                            <div class="stat-item">
                                <h4><?= formatPrice($stats['total_gastado']) ?></h4>
                                <p>Total Gastado</p>
                            </div>
                        </div>
                        
                        <div class="info-section">
                            <h3>Información de Contacto</h3>
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value"><?= htmlspecialchars($cliente['email']) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Teléfono:</span>
                                <span class="info-value"><?= htmlspecialchars($cliente['telefono']) ?></span>
                            </div>
                        </div>
                        
                        <div class="info-section">
                            <h3>Dirección</h3>
                            <div class="info-row">
                                <span class="info-label">Dirección:</span>
                                <span class="info-value"><?= htmlspecialchars($cliente['direccion'] ?: 'No registrada') ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Distrito:</span>
                                <span class="info-value"><?= htmlspecialchars($cliente['distrito'] ?: '-') ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Ciudad:</span>
                                <span class="info-value"><?= htmlspecialchars($cliente['ciudad'] ?: '-') ?></span>
                            </div>
                        </div>
                        
                        <div class="info-section">
                            <h3>Estadísticas</h3>
                            <div class="info-row">
                                <span class="info-label">Pedidos Completados:</span>
                                <span class="info-value"><?= $stats['pedidos_completados'] ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Pedidos Pendientes:</span>
                                <span class="info-value"><?= $stats['pedidos_pendientes'] ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Cliente desde:</span>
                                <span class="info-value"><?= date('d/m/Y', strtotime($cliente['fecha_registro'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Historial de Pedidos -->
                <div class="detail-card pedidos-list">
                    <h3>Historial de Pedidos (<?= count($pedidos) ?>)</h3>
                    
                    <?php if (empty($pedidos)): ?>
                        <div class="empty-state">
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-bottom: 15px;">
                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                                <line x1="3" y1="6" x2="21" y2="6"/>
                                <path d="M16 10a4 4 0 01-8 0"/>
                            </svg>
                            <p>Este cliente aún no tiene pedidos</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($pedidos as $pedido): ?>
                            <div class="pedido-item">
                                <div class="pedido-header">
                                    <div class="pedido-code"><?= $pedido['codigo_pedido'] ?></div>
                                    <span class="badge badge-<?= $pedido['estado'] ?>"><?= ucfirst($pedido['estado']) ?></span>
                                </div>
                                
                                <div class="pedido-body">
                                    <div>
                                        <strong>Fecha:</strong><br>
                                        <?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?>
                                    </div>
                                    <div>
                                        <strong>Items:</strong><br>
                                        <?= $pedido['total_items'] ?> producto(s)
                                    </div>
                                    <div>
                                        <strong>Total:</strong><br>
                                        <span style="color: var(--primary); font-size: 16px; font-weight: 700;">
                                            <?= formatPrice($pedido['total']) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div style="margin-top: 15px;">
                                    <a href="pedido_detalle.php?id=<?= $pedido['id'] ?>" class="btn btn-sm btn-info">
                                        Ver Detalle del Pedido
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>