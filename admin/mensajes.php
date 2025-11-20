<?php
// mensajes.php - Gestión de mensajes de contacto
require_once 'config.php';
requireLogin();

// Marcar como leído
if (isset($_GET['marcar_leido'])) {
    $id = (int)$_GET['marcar_leido'];
    $stmt = $pdo->prepare("UPDATE mensajes_contacto SET leido = 1 WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Mensaje marcado como leído";
}

// Marcar como respondido
if (isset($_GET['marcar_respondido'])) {
    $id = (int)$_GET['marcar_respondido'];
    $stmt = $pdo->prepare("UPDATE mensajes_contacto SET respondido = 1 WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Mensaje marcado como respondido";
}

// Eliminar mensaje
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    try {
        $stmt = $pdo->prepare("DELETE FROM mensajes_contacto WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Mensaje eliminado correctamente";
    } catch (PDOException $e) {
        $error = "Error al eliminar el mensaje";
    }
}

// Filtros
$filtro = $_GET['filtro'] ?? 'todos';

$sql = "SELECT * FROM mensajes_contacto WHERE 1=1";
$params = [];

if ($filtro === 'no_leidos') {
    $sql .= " AND leido = 0";
} elseif ($filtro === 'leidos') {
    $sql .= " AND leido = 1";
} elseif ($filtro === 'respondidos') {
    $sql .= " AND respondido = 1";
}

$sql .= " ORDER BY fecha_envio DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mensajes = $stmt->fetchAll();

// Estadísticas
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM mensajes_contacto")->fetchColumn(),
    'no_leidos' => $pdo->query("SELECT COUNT(*) FROM mensajes_contacto WHERE leido = 0")->fetchColumn(),
    'pendientes' => $pdo->query("SELECT COUNT(*) FROM mensajes_contacto WHERE respondido = 0")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes de Contacto - Milan Jeans Admin</title>
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
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-filter {
            padding: 10px 20px;
            border: 2px solid var(--border);
            background: white;
            color: var(--dark);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-filter:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .btn-filter.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .mensajes-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .mensaje-card {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.3s;
        }
        
        .mensaje-card:last-child {
            border-bottom: none;
        }
        
        .mensaje-card:hover {
            background: var(--light);
        }
        
        .mensaje-card.no-leido {
            background: #fff9e6;
        }
        
        .mensaje-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .mensaje-info h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .mensaje-meta {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: var(--gray);
        }
        
        .mensaje-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .mensaje-badges {
            display: flex;
            gap: 8px;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-no-leido {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-leido {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-respondido {
            background: #d4edda;
            color: #155724;
        }
        
        .mensaje-asunto {
            font-size: 15px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .mensaje-texto {
            color: var(--dark);
            line-height: 1.6;
            margin-bottom: 15px;
            padding: 15px;
            background: var(--light);
            border-radius: 8px;
            border-left: 3px solid var(--primary);
        }
        
        .mensaje-acciones {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn-accion {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-email {
            background: var(--info);
            color: white;
        }
        
        .btn-marcar {
            background: var(--success);
            color: white;
        }
        
        .btn-delete {
            background: var(--danger);
            color: white;
        }
        
        .btn-accion:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
        
        @media (max-width: 768px) {
            .mensaje-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .mensaje-acciones {
                flex-direction: column;
            }
            
            .btn-accion {
                width: 100%;
                justify-content: center;
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
                <h1>Mensajes de Contacto</h1>
                <p>Gestiona los mensajes enviados desde el formulario de contacto</p>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <!-- Estadísticas -->
            <div class="stats-mini">
                <div class="stat-mini">
                    <h4><?= $stats['total'] ?></h4>
                    <p>Total Mensajes</p>
                </div>
                <div class="stat-mini">
                    <h4><?= $stats['no_leidos'] ?></h4>
                    <p>Sin Leer</p>
                </div>
                <div class="stat-mini">
                    <h4><?= $stats['pendientes'] ?></h4>
                    <p>Pendientes de Respuesta</p>
                </div>
            </div>
            
            <!-- Filtros -->
            <div class="toolbar">
                <div class="filter-buttons">
                    <a href="mensajes.php?filtro=todos" class="btn-filter <?= $filtro === 'todos' ? 'active' : '' ?>">
                        Todos (<?= $stats['total'] ?>)
                    </a>
                    <a href="mensajes.php?filtro=no_leidos" class="btn-filter <?= $filtro === 'no_leidos' ? 'active' : '' ?>">
                        No Leídos (<?= $stats['no_leidos'] ?>)
                    </a>
                    <a href="mensajes.php?filtro=leidos" class="btn-filter <?= $filtro === 'leidos' ? 'active' : '' ?>">
                        Leídos
                    </a>
                    <a href="mensajes.php?filtro=respondidos" class="btn-filter <?= $filtro === 'respondidos' ? 'active' : '' ?>">
                        Respondidos
                    </a>
                </div>
            </div>
            
            <!-- Lista de Mensajes -->
            <?php if (empty($mensajes)): ?>
                <div class="mensajes-container">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <h3>No hay mensajes</h3>
                        <p>Los mensajes de contacto aparecerán aquí</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="mensajes-container">
                    <?php foreach ($mensajes as $mensaje): ?>
                        <div class="mensaje-card <?= !$mensaje['leido'] ? 'no-leido' : '' ?>">
                            <div class="mensaje-header">
                                <div class="mensaje-info">
                                    <h3><?= htmlspecialchars($mensaje['nombre']) ?></h3>
                                    <div class="mensaje-meta">
                                        <span>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                                <polyline points="22,6 12,13 2,6"/>
                                            </svg>
                                            <?= htmlspecialchars($mensaje['email']) ?>
                                        </span>
                                        <?php if ($mensaje['telefono']): ?>
                                        <span>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                                            </svg>
                                            <?= htmlspecialchars($mensaje['telefono']) ?>
                                        </span>
                                        <?php endif; ?>
                                        <span>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"/>
                                                <polyline points="12 6 12 12 16 14"/>
                                            </svg>
                                            <?= date('d/m/Y H:i', strtotime($mensaje['fecha_envio'])) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mensaje-badges">
                                    <?php if (!$mensaje['leido']): ?>
                                        <span class="badge badge-no-leido">Sin Leer</span>
                                    <?php else: ?>
                                        <span class="badge badge-leido">Leído</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($mensaje['respondido']): ?>
                                        <span class="badge badge-respondido">Respondido</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mensaje-asunto">
                                📋 <?= htmlspecialchars($mensaje['asunto']) ?>
                            </div>
                            
                            <div class="mensaje-texto">
                                <?= nl2br(htmlspecialchars($mensaje['mensaje'])) ?>
                            </div>
                            
                            <div class="mensaje-acciones">
                                <a href="mailto:<?= $mensaje['email'] ?>?subject=Re: <?= urlencode($mensaje['asunto']) ?>" 
                                   class="btn-accion btn-email">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                        <polyline points="22,6 12,13 2,6"/>
                                    </svg>
                                    Responder por Email
                                </a>
                                
                                <?php if (!$mensaje['leido']): ?>
                                    <a href="?marcar_leido=<?= $mensaje['id'] ?>" class="btn-accion btn-marcar">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                                            <polyline points="22 4 12 14.01 9 11.01"/>
                                        </svg>
                                        Marcar como Leído
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!$mensaje['respondido']): ?>
                                    <a href="?marcar_respondido=<?= $mensaje['id'] ?>" class="btn-accion btn-marcar">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="9 11 12 14 22 4"/>
                                            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                                        </svg>
                                        Marcar como Respondido
                                    </a>
                                <?php endif; ?>
                                
                                <button onclick="confirmarEliminar(<?= $mensaje['id'] ?>, '<?= addslashes($mensaje['nombre']) ?>')" 
                                        class="btn-accion btn-delete">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
        function confirmarEliminar(id, nombre) {
            if (confirm('¿Estás seguro de eliminar el mensaje de "' + nombre + '"?\n\nEsta acción no se puede deshacer.')) {
                window.location.href = 'mensajes.php?eliminar=' + id;
            }
        }
    </script>
</body>
</html>