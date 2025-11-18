<?php
// index.php - Dashboard principal
require_once 'config.php';
requireLogin();

// Obtener estadísticas
$stats = [
    'total_productos' => $pdo->query("SELECT COUNT(*) FROM productos WHERE activo = 1")->fetchColumn(),
    'total_pedidos' => $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn(),
    'pedidos_pendientes' => $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'")->fetchColumn(),
    'ventas_mes' => $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE MONTH(fecha_pedido) = MONTH(NOW()) AND YEAR(fecha_pedido) = YEAR(NOW())")->fetchColumn()
];

// Últimos pedidos
$stmt = $pdo->query("SELECT * FROM pedidos ORDER BY fecha_pedido DESC LIMIT 5");
$ultimos_pedidos = $stmt->fetchAll();

// Productos más vendidos
$stmt = $pdo->query("SELECT * FROM productos WHERE activo = 1 ORDER BY ventas DESC LIMIT 5");
$mas_vendidos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Milan Jeans Admin</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Bienvenido, <?= $_SESSION['admin_nombre'] ?></p>
            </div>
            
            <!-- Tarjetas de estadísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #667eea;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 7h-9M14 17H5M20 17h-3M10 7H5M7 7a2 2 0 100-4 2 2 0 000 4zM17 17a2 2 0 100-4 2 2 0 000 4z"/>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['total_productos'] ?></h3>
                        <p>Productos Activos</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f093fb;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 01-8 0"/>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['total_pedidos'] ?></h3>
                        <p>Total Pedidos</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4facfe;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['pedidos_pendientes'] ?></h3>
                        <p>Pedidos Pendientes</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #43e97b;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3><?= formatPrice($stats['ventas_mes']) ?></h3>
                        <p>Ventas del Mes</p>
                    </div>
                </div>
            </div>
            
            <!-- Sección de contenido -->
            <div class="dashboard-grid">
                <!-- Últimos pedidos -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Últimos Pedidos</h2>
                        <a href="pedidos.php" class="btn-link">Ver todos</a>
                    </div>
                    
                    <?php if (empty($ultimos_pedidos)): ?>
                        <p class="empty-state">No hay pedidos registrados aún</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimos_pedidos as $pedido): ?>
                                    <tr>
                                        <td><strong><?= $pedido['codigo_pedido'] ?></strong></td>
                                        <td><?= $pedido['nombre_cliente'] ?></td>
                                        <td><?= formatPrice($pedido['total']) ?></td>
                                        <td><span class="badge badge-<?= $pedido['estado'] ?>"><?= ucfirst($pedido['estado']) ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($pedido['fecha_pedido'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <!-- Productos más vendidos -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Productos Más Vendidos</h2>
                        <a href="productos.php" class="btn-link">Ver todos</a>
                    </div>
                    
                    <?php if (empty($mas_vendidos)): ?>
                        <p class="empty-state">No hay productos registrados aún</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Ventas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mas_vendidos as $producto): ?>
                                    <tr>
                                        <td><strong><?= $producto['nombre'] ?></strong></td>
                                        <td><?= formatPrice($producto['precio']) ?></td>
                                        <td><?= $producto['stock'] ?></td>
                                        <td><?= $producto['ventas'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>