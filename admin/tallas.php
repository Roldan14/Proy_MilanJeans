<?php
// tallas.php - Gestión de tallas
require_once 'config.php';
requireLogin();

// Crear o actualizar talla
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nombre = sanitize($_POST['nombre']);
    $orden = (int)$_POST['orden'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    try {
        if ($id > 0) {
            // Actualizar
            $stmt = $pdo->prepare("UPDATE tallas SET nombre = ?, orden = ?, activo = ? WHERE id = ?");
            $stmt->execute([$nombre, $orden, $activo, $id]);
            $success = "Talla actualizada correctamente";
        } else {
            // Crear
            $stmt = $pdo->prepare("INSERT INTO tallas (nombre, orden, activo) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $orden, $activo]);
            $success = "Talla creada correctamente";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Eliminar talla
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM tallas WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Talla eliminada correctamente";
    } catch (PDOException $e) {
        $error = "Error al eliminar: No se puede eliminar una talla asignada a productos";
    }
}

// Obtener todas las tallas
$tallas = $pdo->query("
    SELECT t.*, 
    (SELECT COUNT(*) FROM producto_tallas WHERE talla_id = t.id) as total_productos
    FROM tallas t 
    ORDER BY t.orden ASC, t.nombre ASC
")->fetchAll();

// Si hay ID en GET, cargar para editar
$talla_edit = null;
if (isset($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM tallas WHERE id = ?");
    $stmt->execute([$id]);
    $talla_edit = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tallas - Milan Jeans Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .page-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .card h2 {
            font-size: 18px;
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light);
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
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .tallas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .talla-card {
            border: 2px solid #f0f0f0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .talla-card:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .talla-name {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .talla-meta {
            font-size: 12px;
            color: var(--gray);
            margin-bottom: 10px;
        }
        
        .talla-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .talla-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-sm {
            flex: 1;
            padding: 8px;
            font-size: 12px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-edit {
            background: var(--info);
            color: white;
            text-decoration: none;
        }
        
        .btn-delete {
            background: var(--danger);
            color: white;
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
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray);
        }
        
        .quick-add {
            background: var(--light);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .quick-add h4 {
            font-size: 14px;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .quick-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .btn-quick {
            padding: 8px 15px;
            background: white;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-quick:hover {
            background: var(--primary);
            color: white;
        }
        
        @media (max-width: 968px) {
            .page-layout {
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
                <h1>Tallas</h1>
                <p>Gestiona las tallas disponibles para tus productos</p>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="page-layout">
                <!-- Lista de tallas -->
                <div class="card">
                    <h2>Todas las Tallas (<?= count($tallas) ?>)</h2>
                    
                    <?php if (empty($tallas)): ?>
                        <div class="empty-state">
                            <p>No hay tallas registradas</p>
                        </div>
                    <?php else: ?>
                        <div class="tallas-grid">
                            <?php foreach ($tallas as $talla): ?>
                                <div class="talla-card">
                                    <div class="talla-name"><?= htmlspecialchars($talla['nombre']) ?></div>
                                    <div class="talla-meta">
                                        Orden: <?= $talla['orden'] ?> | <?= $talla['total_productos'] ?> productos
                                    </div>
                                    <div>
                                        <span class="talla-badge <?= $talla['activo'] ? 'badge-active' : 'badge-inactive' ?>">
                                            <?= $talla['activo'] ? 'Activa' : 'Inactiva' ?>
                                        </span>
                                    </div>
                                    
                                    <div class="talla-actions">
                                        <a href="?editar=<?= $talla['id'] ?>" class="btn-sm btn-edit">Editar</a>
                                        <button onclick="confirmarEliminar(<?= $talla['id'] ?>, '<?= addslashes($talla['nombre']) ?>')" class="btn-sm btn-delete">Eliminar</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Formulario -->
                <div class="card">
                    <h2><?= $talla_edit ? 'Editar' : 'Nueva' ?> Talla</h2>
                    
                    <div class="quick-add">
                        <h4>Agregar Rápido</h4>
                        <div class="quick-buttons">
                            <button type="button" class="btn-quick" onclick="agregarTalla('XS', 1)">XS</button>
                            <button type="button" class="btn-quick" onclick="agregarTalla('S', 2)">S</button>
                            <button type="button" class="btn-quick" onclick="agregarTalla('M', 3)">M</button>
                            <button type="button" class="btn-quick" onclick="agregarTalla('L', 4)">L</button>
                            <button type="button" class="btn-quick" onclick="agregarTalla('XL', 5)">XL</button>
                            <button type="button" class="btn-quick" onclick="agregarTalla('XXL', 6)">XXL</button>
                        </div>
                        <div class="quick-buttons" style="margin-top: 8px;">
                            <button type="button" class="btn-quick" onclick="agregarTalla('28', 7)">28</button>
                            <button type="button" class="btn-quick" onclick="agregarTalla('30', 8)">30</button>
                            <button type="button" class="btn-quick" onclick="agregarTalla('32', 9)">32</button>
                            <button type="button" class="btn-quick" onclick="agregarTalla('34', 10)">34</button>
                            <button type="button" class="btn-quick" onclick="agregarTalla('36', 11)">36</button>
                            <button type="button" class="btn-quick" onclick="agregarTalla('38', 12)">38</button>
                        </div>
                    </div>
                    
                    <form method="POST" id="tallaForm">
                        <?php if ($talla_edit): ?>
                            <input type="hidden" name="id" value="<?= $talla_edit['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>Nombre de la Talla *</label>
                            <input type="text" name="nombre" id="nombreTalla" value="<?= $talla_edit['nombre'] ?? '' ?>" required placeholder="Ej: M, L, 32">
                        </div>
                        
                        <div class="form-group">
                            <label>Orden de Visualización</label>
                            <input type="number" name="orden" id="ordenTalla" value="<?= $talla_edit['orden'] ?? 0 ?>" min="0">
                            <small style="display: block; margin-top: 5px; color: var(--gray); font-size: 12px;">
                                Número menor aparece primero
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" name="activo" id="activo" <?= ($talla_edit['activo'] ?? 1) ? 'checked' : '' ?>>
                                <label for="activo" style="margin: 0; font-weight: normal; cursor: pointer;">
                                    Talla Activa
                                </label>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <?= $talla_edit ? 'Actualizar' : 'Crear' ?> Talla
                            </button>
                            <?php if ($talla_edit): ?>
                                <a href="tallas.php" class="btn btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function agregarTalla(nombre, orden) {
            document.getElementById('nombreTalla').value = nombre;
            document.getElementById('ordenTalla').value = orden;
        }
        
        function confirmarEliminar(id, nombre) {
            if (confirm('¿Estás seguro de eliminar la talla "' + nombre + '"?')) {
                window.location.href = 'tallas.php?eliminar=' + id;
            }
        }
    </script>
</body>
</html>