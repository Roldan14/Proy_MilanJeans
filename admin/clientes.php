<?php
// clientes.php - Gestión de clientes
require_once 'config.php';
requireLogin();

// Filtros y búsqueda
$search = $_GET['search'] ?? '';
$order = $_GET['order'] ?? 'reciente';

// Construir query
$sql = "SELECT c.*, 
        COUNT(DISTINCT p.id) as total_pedidos,
        COALESCE(SUM(p.total), 0) as total_gastado,
        MAX(p.fecha_pedido) as ultimo_pedido
        FROM clientes c
        LEFT JOIN pedidos p ON c.id = p.cliente_id
        WHERE 1=1";

$params = [];

if ($search) {
    $sql .= " AND (c.nombre LIKE ? OR c.apellido LIKE ? OR c.email LIKE ? OR c.telefono LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " GROUP BY c.id";

// Ordenamiento
switch ($order) {
    case 'nombre':
        $sql .= " ORDER BY c.nombre ASC";
        break;
    case 'gastado':
        $sql .= " ORDER BY total_gastado DESC";
        break;
    case 'pedidos':
        $sql .= " ORDER BY total_pedidos DESC";
        break;
    default:
        $sql .= " ORDER BY c.fecha_registro DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();

// Estadísticas
$stats = [
    'total_clientes' => $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn(),
    'clientes_mes' => $pdo->query("SELECT COUNT(*) FROM clientes WHERE MONTH(fecha_registro) = MONTH(NOW()) AND YEAR(fecha_registro) = YEAR(NOW())")->fetchColumn(),
    'total_ventas' => $pdo->query("SELECT COALESCE(SUM(p.total), 0) FROM pedidos p")->fetchColumn(),
    'pedidos_total' => $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Milan Jeans Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .stats-mini {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-mini {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .stat-mini h4 {
            font-size: 28px;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .stat-mini p {
            font-size: 13px;
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
        
        .toolbar-content {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .search-box {
            flex: 1;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 50px 12px 15px;
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
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        select {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }
        
        .clientes-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: var(--light);
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            font-size: 13px;
            text-transform: uppercase;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        tbody tr:hover {
            background: var(--light);
        }
        
        .cliente-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .cliente-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .cliente-detalles h4 {
            font-size: 15px;
            color: var(--dark);
            margin-bottom: 3px;
        }
        
        .cliente-detalles p {
            font-size: 13px;
            color: var(--gray);
        }
        
        .badge-vip {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            margin-left: 5px;
            text-transform: uppercase;
        }
        
        .btn-icon {
            padding: 8px 12px;
            background: var(--info);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-icon:hover {
            background: #3b9aee;
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
        
        @media (max-width: 768px) {
            .toolbar-content {
                flex-direction: column;
            }
            
            .search-box {
                width: 100%;
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
                <h1>Clientes</h1>
                <p>Gestiona la base de datos de tus clientes</p>
            </div>
            
            <!-- Estadísticas -->
            <div class="stats-mini">
                <div class="stat-mini">
                    <h4><?= $stats['total_clientes'] ?></h4>
                    <p>Total Clientes</p>
                </div>
                <div class="stat-mini">
                    <h4><?= $stats['clientes_mes'] ?></h4>
                    <p>Nuevos este mes</p>
                </div>
                <div class="stat-mini">
                    <h4><?= formatPrice($stats['total_ventas']) ?></h4>
                    <p>Total en Ventas</p>
                </div>
                <div class="stat-mini">
                    <h4><?= $stats['pedidos_total'] ?></h4>
                    <p>Pedidos Totales</p>
                </div>
            </div>
            
            <!-- Barra de herramientas -->
            <div class="toolbar">
                <form method="GET" class="toolbar-content">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Buscar por nombre, email o teléfono..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                        </button>
                    </div>
                    
                    <select name="order" onchange="this.form.submit()">
                        <option value="reciente" <?= $order === 'reciente' ? 'selected' : '' ?>>Más Recientes</option>
                        <option value="nombre" <?= $order === 'nombre' ? 'selected' : '' ?>>Por Nombre</option>
                        <option value="gastado" <?= $order === 'gastado' ? 'selected' : '' ?>>Mayor Gasto</option>
                        <option value="pedidos" <?= $order === 'pedidos' ? 'selected' : '' ?>>Más Pedidos</option>
                    </select>
                </form>
            </div>
            
            <!-- Tabla de clientes -->
            <?php if (empty($clientes)): ?>
                <div class="clientes-table">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                            <path d="M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                        <h3>No hay clientes registrados</h3>
                        <p>Los clientes aparecerán aquí cuando realicen su primer pedido</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="clientes-table">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Contacto</th>
                                    <th>Ubicación</th>
                                    <th>Pedidos</th>
                                    <th>Total Gastado</th>
                                    <th>Último Pedido</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientes as $cliente): ?>
                                    <tr>
                                        <td>
                                            <div class="cliente-info">
                                                <div class="cliente-avatar">
                                                    <?= strtoupper(substr($cliente['nombre'], 0, 1)) ?>
                                                </div>
                                                <div class="cliente-detalles">
                                                    <h4>
                                                        <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?>
                                                        <?php if ($cliente['total_gastado'] > 500): ?>
                                                            <span class="badge-vip">VIP</span>
                                                        <?php endif; ?>
                                                    </h4>
                                                    <p>Cliente desde <?= date('M Y', strtotime($cliente['fecha_registro'])) ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-size: 14px;">
                                                <strong><?= htmlspecialchars($cliente['email']) ?></strong><br>
                                                <span style="color: var(--gray); font-size: 13px;"><?= htmlspecialchars($cliente['telefono']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-size: 13px; color: var(--gray);">
                                                <?= htmlspecialchars($cliente['distrito'] ?: '-') ?><br>
                                                <?= htmlspecialchars($cliente['ciudad'] ?: '-') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong style="font-size: 18px; color: var(--primary);">
                                                <?= $cliente['total_pedidos'] ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <strong style="font-size: 16px; color: var(--success);">
                                                <?= formatPrice($cliente['total_gastado']) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php if ($cliente['ultimo_pedido']): ?>
                                                <span style="font-size: 13px; color: var(--gray);">
                                                    <?= date('d/m/Y', strtotime($cliente['ultimo_pedido'])) ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="font-size: 13px; color: var(--gray);">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="cliente_detalle.php?id=<?= $cliente['id'] ?>" class="btn-icon">
                                                Ver Detalle
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>