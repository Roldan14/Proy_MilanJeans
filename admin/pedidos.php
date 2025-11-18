<?php
// pedidos.php - Gestión de pedidos
require_once 'config.php';
requireLogin();

// Cambiar estado de pedido rápido
if (isset($_POST['cambiar_estado'])) {
    $pedido_id = (int)$_POST['pedido_id'];
    $nuevo_estado = $_POST['estado'];
    
    $estados_validos = ['pendiente', 'confirmado', 'procesando', 'enviado', 'entregado', 'cancelado'];
    
    if (in_array($nuevo_estado, $estados_validos)) {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $pedido_id]);
        $success = "Estado del pedido actualizado correctamente";
    }
}

// Eliminar pedido
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Pedido eliminado correctamente";
    } catch (PDOException $e) {
        $error = "Error al eliminar el pedido: " . $e->getMessage();
    }
}

// Filtros
$estado = $_GET['estado'] ?? '';
$search = $_GET['search'] ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';

// Construir query
$sql = "SELECT p.*, 
        (SELECT COUNT(*) FROM pedido_detalles WHERE pedido_id = p.id) as total_items
        FROM pedidos p
        WHERE 1=1";

$params = [];

if ($estado && $estado !== 'todos') {
    $sql .= " AND p.estado = ?";
    $params[] = $estado;
}

if ($search) {
    $sql .= " AND (p.codigo_pedido LIKE ? OR p.nombre_cliente LIKE ? OR p.email_cliente LIKE ? OR p.telefono_cliente LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($fecha_desde) {
    $sql .= " AND DATE(p.fecha_pedido) >= ?";
    $params[] = $fecha_desde;
}

if ($fecha_hasta) {
    $sql .= " AND DATE(p.fecha_pedido) <= ?";
    $params[] = $fecha_hasta;
}

$sql .= " ORDER BY p.fecha_pedido DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

// Estadísticas
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn(),
    'pendientes' => $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'")->fetchColumn(),
    'confirmados' => $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'confirmado'")->fetchColumn(),
    'enviados' => $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'enviado'")->fetchColumn(),
    'total_ventas' => $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE estado != 'cancelado'")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Milan Jeans Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .stats-mini {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-mini {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .stat-mini h4 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .stat-mini p {
            font-size: 12px;
            color: var(--gray);
            text-transform: uppercase;
        }
        
        .toolbar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .toolbar-filters {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 10px;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .search-box button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        select, input[type="date"] {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }
        
        .orders-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .order-card {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.3s;
        }
        
        .order-card:hover {
            background: var(--light);
        }
        
        .order-card:last-child {
            border-bottom: none;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .order-code {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .order-date {
            font-size: 13px;
            color: var(--gray);
        }
        
        .order-status {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .order-body {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .order-info h4 {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .order-info p {
            font-size: 14px;
            color: var(--dark);
        }
        
        .order-total {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .order-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 8px 15px;
            font-size: 13px;
            border-radius: 5px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-view {
            background: var(--info);
            color: white;
        }
        
        .btn-delete {
            background: var(--danger);
            color: white;
        }
        
        .status-select {
            padding: 6px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            color: var(--gray);
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
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 968px) {
            .toolbar-filters {
                grid-template-columns: 1fr;
            }
            
            .order-body {
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
                <h1>Pedidos</h1>
                <p>Gestiona los pedidos de tu tienda</p>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <!-- Mini estadísticas -->
            <div class="stats-mini">
                <div class="stat-mini">
                    <h4><?= $stats['total'] ?></h4>
                    <p>Total Pedidos</p>
                </div>
                <div class="stat-mini">
                    <h4><?= $stats['pendientes'] ?></h4>
                    <p>Pendientes</p>
                </div>
                <div class="stat-mini">
                    <h4><?= $stats['confirmados'] ?></h4>
                    <p>Confirmados</p>
                </div>
                <div class="stat-mini">
                    <h4><?= $stats['enviados'] ?></h4>
                    <p>Enviados</p>
                </div>
                <div class="stat-mini">
                    <h4><?= formatPrice($stats['total_ventas']) ?></h4>
                    <p>Total Ventas</p>
                </div>
            </div>
            
            <!-- Filtros -->
            <div class="toolbar">
                <form method="GET" class="toolbar-filters">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Buscar por código, cliente, email o teléfono..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                        </button>
                    </div>
                    
                    <select name="estado" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
                        <option value="confirmado" <?= $estado === 'confirmado' ? 'selected' : '' ?>>Confirmados</option>
                        <option value="procesando" <?= $estado === 'procesando' ? 'selected' : '' ?>>Procesando</option>
                        <option value="enviado" <?= $estado === 'enviado' ? 'selected' : '' ?>>Enviados</option>
                        <option value="entregado" <?= $estado === 'entregado' ? 'selected' : '' ?>>Entregados</option>
                        <option value="cancelado" <?= $estado === 'cancelado' ? 'selected' : '' ?>>Cancelados</option>
                    </select>
                    
                    <input type="date" name="fecha_desde" value="<?= $fecha_desde ?>" onchange="this.form.submit()" placeholder="Desde">
                    
                    <input type="date" name="fecha_hasta" value="<?= $fecha_hasta ?>" onchange="this.form.submit()" placeholder="Hasta">
                </form>
            </div>
            
            <?php if (empty($pedidos)): ?>
                <div class="orders-container">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 01-8 0"/>
                        </svg>
                        <h3>No hay pedidos</h3>
                        <p>Los pedidos aparecerán aquí cuando los clientes realicen compras</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="orders-container">
                    <?php foreach ($pedidos as $pedido): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <div class="order-code"><?= $pedido['codigo_pedido'] ?></div>
                                    <div class="order-date">
                                        <?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?>
                                    </div>
                                </div>
                                
                                <div class="order-status">
                                    <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
                                        <input type="hidden" name="cambiar_estado" value="1">
                                        <select name="estado" class="status-select badge-<?= $pedido['estado'] ?>" onchange="if(confirm('¿Cambiar el estado del pedido?')) this.form.submit();">
                                            <option value="pendiente" <?= $pedido['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                            <option value="confirmado" <?= $pedido['estado'] == 'confirmado' ? 'selected' : '' ?>>Confirmado</option>
                                            <option value="procesando" <?= $pedido['estado'] == 'procesando' ? 'selected' : '' ?>>Procesando</option>
                                            <option value="enviado" <?= $pedido['estado'] == 'enviado' ? 'selected' : '' ?>>Enviado</option>
                                            <option value="entregado" <?= $pedido['estado'] == 'entregado' ? 'selected' : '' ?>>Entregado</option>
                                            <option value="cancelado" <?= $pedido['estado'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="order-body">
                                <div class="order-info">
                                    <h4>Cliente</h4>
                                    <p><strong><?= htmlspecialchars($pedido['nombre_cliente']) ?></strong></p>
                                    <p><?= htmlspecialchars($pedido['email_cliente']) ?></p>
                                    <p><?= htmlspecialchars($pedido['telefono_cliente']) ?></p>
                                </div>
                                
                                <div class="order-info">
                                    <h4>Dirección</h4>
                                    <p><?= htmlspecialchars($pedido['direccion_entrega']) ?></p>
                                    <p><?= htmlspecialchars($pedido['distrito']) ?></p>
                                </div>
                                
                                <div class="order-info">
                                    <h4>Items</h4>
                                    <p><?= $pedido['total_items'] ?> producto(s)</p>
                                    <p style="font-size: 12px; color: var(--gray);">
                                        <?= $pedido['metodo_pago'] ?>
                                    </p>
                                </div>
                                
                                <div class="order-info">
                                    <h4>Total</h4>
                                    <p class="order-total"><?= formatPrice($pedido['total']) ?></p>
                                </div>
                            </div>
                            
                            <div class="order-actions">
                                <a href="pedido_detalle.php?id=<?= $pedido['id'] ?>" class="btn-sm btn-view">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle;">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    Ver Detalle
                                </a>
                                <button onclick="confirmarEliminar(<?= $pedido['id'] ?>, '<?= $pedido['codigo_pedido'] ?>')" class="btn-sm btn-delete">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle;">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                    </svg>
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script>
        function confirmarEliminar(id, codigo) {
            if (confirm('¿Estás seguro de eliminar el pedido "' + codigo + '"?\n\nEsta acción no se puede deshacer.')) {
                window.location.href = 'pedidos.php?eliminar=' + id;
            }
        }
    </script>
</body>
</html>