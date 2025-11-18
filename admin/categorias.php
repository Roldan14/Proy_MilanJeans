<?php
// categorias.php - Gestión de categorías
require_once 'config.php';
requireLogin();

// Crear o actualizar categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nombre = sanitize($_POST['nombre']);
    $descripcion = sanitize($_POST['descripcion'] ?? '');
    $orden = (int)$_POST['orden'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    $slug = generateSlug($nombre);
    
    try {
        if ($id > 0) {
            // Actualizar
            $stmt = $pdo->prepare("UPDATE categorias SET nombre = ?, slug = ?, descripcion = ?, orden = ?, activo = ? WHERE id = ?");
            $stmt->execute([$nombre, $slug, $descripcion, $orden, $activo, $id]);
            $success = "Categoría actualizada correctamente";
        } else {
            // Crear
            $stmt = $pdo->prepare("INSERT INTO categorias (nombre, slug, descripcion, orden, activo) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $slug, $descripcion, $orden, $activo]);
            $success = "Categoría creada correctamente";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Eliminar categoría
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Categoría eliminada correctamente";
    } catch (PDOException $e) {
        $error = "Error al eliminar: " . $e->getMessage();
    }
}

// Obtener todas las categorías
$categorias = $pdo->query("
    SELECT c.*, 
    (SELECT COUNT(*) FROM productos WHERE categoria_id = c.id) as total_productos
    FROM categorias c 
    ORDER BY c.orden ASC, c.nombre ASC
")->fetchAll();

// Si hay ID en GET, cargar para editar
$categoria_edit = null;
if (isset($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmt->execute([$id]);
    $categoria_edit = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías - Milan Jeans Admin</title>
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
        
        .form-group input,
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
            min-height: 80px;
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
        
        .category-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 2px solid #f0f0f0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .category-item:hover {
            border-color: var(--primary);
            background: var(--light);
        }
        
        .category-info {
            flex: 1;
        }
        
        .category-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .category-meta {
            font-size: 13px;
            color: var(--gray);
        }
        
        .category-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .category-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-icon {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-edit {
            background: var(--info);
            color: white;
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
                <h1>Categorías</h1>
                <p>Organiza tus productos en categorías</p>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="page-layout">
                <!-- Lista de categorías -->
                <div class="card">
                    <h2>Todas las Categorías (<?= count($categorias) ?>)</h2>
                    
                    <?php if (empty($categorias)): ?>
                        <div class="empty-state">
                            <p>No hay categorías registradas</p>
                        </div>
                    <?php else: ?>
                        <div class="category-list">
                            <?php foreach ($categorias as $cat): ?>
                                <div class="category-item">
                                    <div class="category-info">
                                        <div class="category-name">
                                            <?= htmlspecialchars($cat['nombre']) ?>
                                            <span class="category-badge <?= $cat['activo'] ? 'badge-active' : 'badge-inactive' ?>">
                                                <?= $cat['activo'] ? 'Activa' : 'Inactiva' ?>
                                            </span>
                                        </div>
                                        <div class="category-meta">
                                            <?= $cat['total_productos'] ?> producto(s) | Orden: <?= $cat['orden'] ?>
                                            <?php if ($cat['descripcion']): ?>
                                                | <?= htmlspecialchars($cat['descripcion']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="category-actions">
                                        <a href="?editar=<?= $cat['id'] ?>" class="btn-icon btn-edit" title="Editar">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                        </a>
                                        <button onclick="confirmarEliminar(<?= $cat['id'] ?>, '<?= addslashes($cat['nombre']) ?>')" class="btn-icon btn-delete" title="Eliminar">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Formulario -->
                <div class="card">
                    <h2><?= $categoria_edit ? 'Editar' : 'Nueva' ?> Categoría</h2>
                    
                    <form method="POST">
                        <?php if ($categoria_edit): ?>
                            <input type="hidden" name="id" value="<?= $categoria_edit['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>Nombre *</label>
                            <input type="text" name="nombre" value="<?= $categoria_edit['nombre'] ?? '' ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="descripcion"><?= $categoria_edit['descripcion'] ?? '' ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Orden de Visualización</label>
                            <input type="number" name="orden" value="<?= $categoria_edit['orden'] ?? 0 ?>" min="0">
                            <small style="display: block; margin-top: 5px; color: var(--gray); font-size: 12px;">
                                Número menor aparece primero
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" name="activo" id="activo" <?= ($categoria_edit['activo'] ?? 1) ? 'checked' : '' ?>>
                                <label for="activo" style="margin: 0; font-weight: normal; cursor: pointer;">
                                    Categoría Activa
                                </label>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <?= $categoria_edit ? 'Actualizar' : 'Crear' ?> Categoría
                            </button>
                            <?php if ($categoria_edit): ?>
                                <a href="categorias.php" class="btn btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function confirmarEliminar(id, nombre) {
            if (confirm('¿Estás seguro de eliminar la categoría "' + nombre + '"?\n\nLos productos asociados no se eliminarán.')) {
                window.location.href = 'categorias.php?eliminar=' + id;
            }
        }
    </script>
</body>
</html>