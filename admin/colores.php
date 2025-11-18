<?php
// colores.php - Gestión de colores
require_once 'config.php';
requireLogin();

// Crear o actualizar color
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nombre = sanitize($_POST['nombre']);
    $codigo_hex = $_POST['codigo_hex'] ?? '#000000';
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    try {
        if ($id > 0) {
            // Actualizar
            $stmt = $pdo->prepare("UPDATE colores SET nombre = ?, codigo_hex = ?, activo = ? WHERE id = ?");
            $stmt->execute([$nombre, $codigo_hex, $activo, $id]);
            $success = "Color actualizado correctamente";
        } else {
            // Crear
            $stmt = $pdo->prepare("INSERT INTO colores (nombre, codigo_hex, activo) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $codigo_hex, $activo]);
            $success = "Color creado correctamente";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Eliminar color
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM colores WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Color eliminado correctamente";
    } catch (PDOException $e) {
        $error = "Error al eliminar: No se puede eliminar un color asignado a productos";
    }
}

// Obtener todos los colores
$colores = $pdo->query("
    SELECT c.*, 
    (SELECT COUNT(*) FROM producto_colores WHERE color_id = c.id) as total_productos
    FROM colores c 
    ORDER BY c.nombre ASC
")->fetchAll();

// Si hay ID en GET, cargar para editar
$color_edit = null;
if (isset($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM colores WHERE id = ?");
    $stmt->execute([$id]);
    $color_edit = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colores - Milan Jeans Admin</title>
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
        
        .color-picker-wrapper {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .color-picker-wrapper input[type="color"] {
            width: 80px;
            height: 50px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .color-picker-wrapper input[type="text"] {
            flex: 1;
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
        
        .colors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .color-card {
            border: 2px solid #f0f0f0;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .color-card:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .color-preview {
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        
        .color-body {
            padding: 15px;
        }
        
        .color-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .color-meta {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 10px;
        }
        
        .color-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .color-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-sm {
            flex: 1;
            padding: 8px;
            font-size: 13px;
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
                <h1>Colores</h1>
                <p>Gestiona los colores disponibles para tus productos</p>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="page-layout">
                <!-- Lista de colores -->
                <div class="card">
                    <h2>Todos los Colores (<?= count($colores) ?>)</h2>
                    
                    <?php if (empty($colores)): ?>
                        <div class="empty-state">
                            <p>No hay colores registrados</p>
                        </div>
                    <?php else: ?>
                        <div class="colors-grid">
                            <?php foreach ($colores as $color): ?>
                                <div class="color-card">
                                    <div class="color-preview" style="background-color: <?= $color['codigo_hex'] ?>">
                                        <?= htmlspecialchars($color['nombre']) ?>
                                    </div>
                                    
                                    <div class="color-body">
                                        <div class="color-name">
                                            <?= htmlspecialchars($color['nombre']) ?>
                                        </div>
                                        <div class="color-meta">
                                            <?= $color['codigo_hex'] ?> | <?= $color['total_productos'] ?> producto(s)
                                        </div>
                                        <div style="margin-bottom: 10px;">
                                            <span class="color-badge <?= $color['activo'] ? 'badge-active' : 'badge-inactive' ?>">
                                                <?= $color['activo'] ? 'Activo' : 'Inactivo' ?>
                                            </span>
                                        </div>
                                        
                                        <div class="color-actions">
                                            <a href="?editar=<?= $color['id'] ?>" class="btn-sm btn-edit">Editar</a>
                                            <button onclick="confirmarEliminar(<?= $color['id'] ?>, '<?= addslashes($color['nombre']) ?>')" class="btn-sm btn-delete">Eliminar</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Formulario -->
                <div class="card">
                    <h2><?= $color_edit ? 'Editar' : 'Nuevo' ?> Color</h2>
                    
                    <form method="POST">
                        <?php if ($color_edit): ?>
                            <input type="hidden" name="id" value="<?= $color_edit['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>Nombre del Color *</label>
                            <input type="text" name="nombre" value="<?= $color_edit['nombre'] ?? '' ?>" required placeholder="Ej: Azul Marino">
                        </div>
                        
                        <div class="form-group">
                            <label>Código de Color *</label>
                            <div class="color-picker-wrapper">
                                <input type="color" id="colorPicker" value="<?= $color_edit['codigo_hex'] ?? '#000000' ?>">
                                <input type="text" name="codigo_hex" id="colorHex" value="<?= $color_edit['codigo_hex'] ?? '#000000' ?>" required placeholder="#000000">
                            </div>
                            <small style="display: block; margin-top: 5px; color: var(--gray); font-size: 12px;">
                                Usa el selector o ingresa el código hexadecimal
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" name="activo" id="activo" <?= ($color_edit['activo'] ?? 1) ? 'checked' : '' ?>>
                                <label for="activo" style="margin: 0; font-weight: normal; cursor: pointer;">
                                    Color Activo
                                </label>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <?= $color_edit ? 'Actualizar' : 'Crear' ?> Color
                            </button>
                            <?php if ($color_edit): ?>
                                <a href="colores.php" class="btn btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Sincronizar color picker con input de texto
        const colorPicker = document.getElementById('colorPicker');
        const colorHex = document.getElementById('colorHex');
        
        colorPicker.addEventListener('input', function() {
            colorHex.value = this.value;
        });
        
        colorHex.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                colorPicker.value = this.value;
            }
        });
        
        function confirmarEliminar(id, nombre) {
            if (confirm('¿Estás seguro de eliminar el color "' + nombre + '"?')) {
                window.location.href = 'colores.php?eliminar=' + id;
            }
        }
    </script>
</body>
</html>