<?php
// producto_form.php - Formulario para crear/editar productos
require_once 'config.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;
$producto = null;
$imagenes_producto = [];

// Si es edición, cargar datos del producto
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
    
    if (!$producto) {
        header('Location: productos.php');
        exit;
    }
    
    // Cargar imágenes
    $stmt = $pdo->prepare("SELECT * FROM producto_imagenes WHERE producto_id = ? ORDER BY es_principal DESC, orden ASC");
    $stmt->execute([$id]);
    $imagenes_producto = $stmt->fetchAll();
    
    // Cargar colores asignados
    $stmt = $pdo->prepare("SELECT color_id FROM producto_colores WHERE producto_id = ?");
    $stmt->execute([$id]);
    $colores_asignados = array_column($stmt->fetchAll(), 'color_id');
    
    // Cargar tallas asignadas
    $stmt = $pdo->prepare("SELECT talla_id FROM producto_tallas WHERE producto_id = ?");
    $stmt->execute([$id]);
    $tallas_asignadas = array_column($stmt->fetchAll(), 'talla_id');
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitize($_POST['nombre']);
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = (float)$_POST['precio'];
    $precio_oferta = !empty($_POST['precio_oferta']) ? (float)$_POST['precio_oferta'] : null;
    $stock = (int)$_POST['stock'];
    $categoria_id = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
    $es_nuevo = isset($_POST['es_nuevo']) ? 1 : 0;
    $es_mas_vendido = isset($_POST['es_mas_vendido']) ? 1 : 0;
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $activo = isset($_POST['activo']) ? 1 : 0;
    $colores = $_POST['colores'] ?? [];
    $tallas = $_POST['tallas'] ?? [];
    
    $slug = generateSlug($nombre);
    
    try {
        $pdo->beginTransaction();
        
        if ($is_edit) {
            // Actualizar producto
            $stmt = $pdo->prepare("
                UPDATE productos SET 
                nombre = ?, slug = ?, descripcion = ?, precio = ?, precio_oferta = ?,
                stock = ?, categoria_id = ?, es_nuevo = ?, es_mas_vendido = ?,
                destacado = ?, activo = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $nombre, $slug, $descripcion, $precio, $precio_oferta,
                $stock, $categoria_id, $es_nuevo, $es_mas_vendido,
                $destacado, $activo, $id
            ]);
            $producto_id = $id;
        } else {
            // Crear nuevo producto
            $stmt = $pdo->prepare("
                INSERT INTO productos 
                (nombre, slug, descripcion, precio, precio_oferta, stock, categoria_id, 
                es_nuevo, es_mas_vendido, destacado, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $nombre, $slug, $descripcion, $precio, $precio_oferta, $stock, $categoria_id,
                $es_nuevo, $es_mas_vendido, $destacado, $activo
            ]);
            $producto_id = $pdo->lastInsertId();
        }
        
        // Actualizar colores
        $pdo->prepare("DELETE FROM producto_colores WHERE producto_id = ?")->execute([$producto_id]);
        foreach ($colores as $color_id) {
            $stmt = $pdo->prepare("INSERT INTO producto_colores (producto_id, color_id) VALUES (?, ?)");
            $stmt->execute([$producto_id, $color_id]);
        }
        
        // Actualizar tallas
        $pdo->prepare("DELETE FROM producto_tallas WHERE producto_id = ?")->execute([$producto_id]);
        foreach ($tallas as $talla_id) {
            $stmt = $pdo->prepare("INSERT INTO producto_tallas (producto_id, talla_id) VALUES (?, ?)");
            $stmt->execute([$producto_id, $talla_id]);
        }
        
        // Subir nuevas imágenes
        if (isset($_FILES['imagenes']) && $_FILES['imagenes']['name'][0]) {
            $es_primera = empty($imagenes_producto);
            
            foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['imagenes']['name'][$key],
                        'type' => $_FILES['imagenes']['type'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $_FILES['imagenes']['error'][$key],
                        'size' => $_FILES['imagenes']['size'][$key]
                    ];
                    
                    $upload = uploadImage($file, 'productos');
                    
                    if ($upload['success']) {
                        $es_principal = $es_primera && $key === 0 ? 1 : 0;
                        
                        $stmt = $pdo->prepare("
                            INSERT INTO producto_imagenes 
                            (producto_id, ruta_imagen, es_principal, orden, alt_text)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $producto_id,
                            $upload['path'],
                            $es_principal,
                            $key,
                            $nombre
                        ]);
                    }
                }
            }
        }
        
        $pdo->commit();
        
        $_SESSION['success'] = $is_edit ? 'Producto actualizado correctamente' : 'Producto creado correctamente';
        header('Location: productos.php');
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error al guardar el producto: " . $e->getMessage();
    }
}

// Obtener datos para el formulario
$categorias = $pdo->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre")->fetchAll();
$colores = $pdo->query("SELECT * FROM colores WHERE activo = 1 ORDER BY nombre")->fetchAll();
$tallas = $pdo->query("SELECT * FROM tallas WHERE activo = 1 ORDER BY orden")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit ? 'Editar' : 'Nuevo' ?> Producto - Milan Jeans Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            font-size: 16px;
            color: var(--dark);
            margin-bottom: 15px;
            padding-bottom: 10px;
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
        
        .form-group label .required {
            color: var(--danger);
        }
        
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .checkbox-item label {
            margin: 0;
            font-weight: normal;
            cursor: pointer;
        }
        
        .color-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
        }
        
        .color-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .color-option:hover {
            border-color: var(--primary);
            background: var(--light);
        }
        
        .color-option input[type="checkbox"]:checked + .color-swatch {
            border: 3px solid var(--primary);
        }
        
        .color-swatch {
            width: 30px;
            height: 30px;
            border-radius: 5px;
            border: 2px solid #ddd;
        }
        
        .talla-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 10px;
        }
        
        .talla-option {
            position: relative;
        }
        
        .talla-option input[type="checkbox"] {
            position: absolute;
            opacity: 0;
        }
        
        .talla-option label {
            display: block;
            padding: 12px;
            text-align: center;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .talla-option input[type="checkbox"]:checked + label {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .talla-option label:hover {
            border-color: var(--primary);
        }
        
        .image-upload {
            border: 2px dashed #e0e0e0;
            border-radius: 5px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .image-upload:hover {
            border-color: var(--primary);
            background: var(--light);
        }
        
        .image-upload input[type="file"] {
            display: none;
        }
        
        .image-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .image-preview-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 5px;
            overflow: hidden;
            border: 2px solid #e0e0e0;
        }
        
        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .image-preview-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: var(--success);
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
        }
        
        .btn-delete-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            padding-top: 20px;
            border-top: 2px solid var(--light);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 968px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
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
                <h1><?= $is_edit ? 'Editar Producto' : 'Nuevo Producto' ?></h1>
                <p><?= $is_edit ? 'Actualiza la información del producto' : 'Agrega un nuevo producto al catálogo' ?></p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <!-- Columna principal -->
                    <div>
                        <div class="form-container">
                            <div class="form-section">
                                <h3>Información Básica</h3>
                                
                                <div class="form-group">
                                    <label>Nombre del Producto <span class="required">*</span></label>
                                    <input type="text" name="nombre" value="<?= $producto['nombre'] ?? '' ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Descripción</label>
                                    <textarea name="descripcion" rows="4"><?= $producto['descripcion'] ?? '' ?></textarea>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Precio <span class="required">*</span></label>
                                        <input type="number" name="precio" step="0.01" min="0" value="<?= $producto['precio'] ?? '' ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Precio de Oferta</label>
                                        <input type="number" name="precio_oferta" step="0.01" min="0" value="<?= $producto['precio_oferta'] ?? '' ?>">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Stock <span class="required">*</span></label>
                                        <input type="number" name="stock" min="0" value="<?= $producto['stock'] ?? 0 ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Categoría</label>
                                        <select name="categoria_id">
                                            <option value="">Sin categoría</option>
                                            <?php foreach ($categorias as $cat): ?>
                                                <option value="<?= $cat['id'] ?>" <?= ($producto['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cat['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3>Colores Disponibles</h3>
                                <div class="color-grid">
                                    <?php foreach ($colores as $color): ?>
                                        <label class="color-option">
                                            <input type="checkbox" name="colores[]" value="<?= $color['id'] ?>" 
                                                   <?= in_array($color['id'], $colores_asignados ?? []) ? 'checked' : '' ?>>
                                            <div class="color-swatch" style="background-color: <?= $color['codigo_hex'] ?>"></div>
                                            <span><?= htmlspecialchars($color['nombre']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3>Tallas Disponibles</h3>
                                <div class="talla-grid">
                                    <?php foreach ($tallas as $talla): ?>
                                        <div class="talla-option">
                                            <input type="checkbox" name="tallas[]" value="<?= $talla['id'] ?>" 
                                                   id="talla_<?= $talla['id'] ?>"
                                                   <?= in_array($talla['id'], $tallas_asignadas ?? []) ? 'checked' : '' ?>>
                                            <label for="talla_<?= $talla['id'] ?>"><?= htmlspecialchars($talla['nombre']) ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3>Imágenes del Producto</h3>
                                <div class="image-upload" id="dropZone">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 10px; color: var(--gray);">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                    <p style="color: var(--gray); margin-bottom: 5px;">Haz clic para seleccionar imágenes</p>
                                    <p style="font-size: 12px; color: var(--gray);">o arrastra y suelta aquí</p>
                                    <input type="file" id="imagenes" name="imagenes[]" multiple accept="image/*">
                                </div>
                                
                                <!-- Preview de nuevas imágenes -->
                                <div class="image-preview" id="newImagePreview" style="display: none;"></div>
                                
                                <?php if ($is_edit && !empty($imagenes_producto)): ?>
                                   <div style="margin-top: 20px;">
                                       <h4 style="font-size: 14px; margin-bottom: 10px; color: var(--dark);">Imágenes actuales</h4>
                                       <div class="image-preview" id="imagenesActuales">
                                            <?php foreach ($imagenes_producto as $img): ?>
                                            <div class="image-preview-item" id="imagen-<?= $img['id'] ?>">
                                                <img src="<?= UPLOAD_URL . $img['ruta_imagen'] ?>" alt="">
                                                <?php if ($img['es_principal']): ?>
                                                    <span class="image-preview-badge">Principal</span>
                                                <?php endif; ?>
                                                <button type="button" 
                                                   class="btn-delete-image" 
                                                   onclick="eliminarImagenAjax(<?= $img['id'] ?>)"
                                                   title="Eliminar imagen">
                                                </button>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                               <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Columna lateral -->
                    <div>
                        <div class="form-container">
                            <div class="form-section">
                                <h3>Estado y Visibilidad</h3>
                                
                                <div class="checkbox-group">
                                    <div class="checkbox-item">
                                        <input type="checkbox" name="activo" id="activo" <?= ($producto['activo'] ?? 1) ? 'checked' : '' ?>>
                                        <label for="activo">Producto Activo</label>
                                    </div>
                                    
                                    <div class="checkbox-item">
                                        <input type="checkbox" name="es_nuevo" id="es_nuevo" <?= ($producto['es_nuevo'] ?? 0) ? 'checked' : '' ?>>
                                        <label for="es_nuevo">Marcar como "Nuevo"</label>
                                    </div>
                                    
                                    <div class="checkbox-item">
                                        <input type="checkbox" name="es_mas_vendido" id="es_mas_vendido" <?= ($producto['es_mas_vendido'] ?? 0) ? 'checked' : '' ?>>
                                        <label for="es_mas_vendido">Marcar como "Más Vendido"</label>
                                    </div>
                                    
                                    <div class="checkbox-item">
                                        <input type="checkbox" name="destacado" id="destacado" <?= ($producto['destacado'] ?? 0) ? 'checked' : '' ?>>
                                        <label for="destacado">Producto Destacado</label>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($is_edit): ?>
                                <div class="form-section">
                                    <h3>Estadísticas</h3>
                                    <p style="font-size: 14px; margin-bottom: 10px;">
                                        <strong>Vistas:</strong> <?= $producto['vistas'] ?>
                                    </p>
                                    <p style="font-size: 14px; margin-bottom: 10px;">
                                        <strong>Ventas:</strong> <?= $producto['ventas'] ?>
                                    </p>
                                    <p style="font-size: 14px; color: var(--gray);">
                                        Creado: <?= date('d/m/Y', strtotime($producto['fecha_creacion'])) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-container" style="margin-top: 20px;">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?= $is_edit ? 'Actualizar Producto' : 'Crear Producto' ?>
                        </button>
                        <a href="productos.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </main>
    </div>
    
    <script>
        // Función para eliminar imagen con AJAX
        function eliminarImagenAjax(imagenId) {
          const imagenElement = document.getElementById('imagen-' + imagenId);
    
          // Agregar efecto visual de eliminación
          imagenElement.style.opacity = '0.5';
          imagenElement.style.pointerEvents = 'none';
    
          // Enviar petición AJAX
          fetch('eliminar_imagen.php', {
               method: 'POST',
               headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
               body: 'id=' + imagenId
            })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Eliminar el elemento del DOM con animación
            imagenElement.style.transform = 'scale(0)';
            imagenElement.style.transition = 'all 0.3s ease';
            
            setTimeout(() => {
                imagenElement.remove();
                
                // Verificar si quedan imágenes
                const imagenesRestantes = document.querySelectorAll('#imagenesActuales .image-preview-item');
                if (imagenesRestantes.length === 0) {
                    document.getElementById('imagenesActuales').parentElement.style.display = 'none';
                }
            }, 300);
        } else {
            // Restaurar el elemento si hay error
            imagenElement.style.opacity = '1';
            imagenElement.style.pointerEvents = 'auto';
            alert('Error al eliminar la imagen: ' + data.message);
        }
    })
    .catch(error => {
        // Restaurar el elemento si hay error
        imagenElement.style.opacity = '1';
        imagenElement.style.pointerEvents = 'auto';
        console.error('Error:', error);
        alert('Error al eliminar la imagen');
    });
}
        
        // Manejo de subida de imágenes con preview
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('imagenes');
        const newImagePreview = document.getElementById('newImagePreview');
        
        // Click en la zona de drop abre el selector de archivos
        dropZone.addEventListener('click', (e) => {
            if (e.target !== fileInput) {
                fileInput.click();
            }
        });
        
        // Prevenir comportamiento por defecto en drag & drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        // Efectos visuales al arrastrar
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.style.borderColor = 'var(--primary)';
                dropZone.style.background = 'var(--light)';
            });
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.style.borderColor = '#e0e0e0';
                dropZone.style.background = 'transparent';
            });
        });
        
        // Manejar archivos soltados
        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            handleFiles(files);
        });
        
        // Manejar archivos seleccionados
        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });
        
        function handleFiles(files) {
            if (files.length === 0) return;
            
            newImagePreview.innerHTML = '';
            newImagePreview.style.display = 'grid';
            
            [...files].forEach((file, index) => {
                if (!file.type.startsWith('image/')) return;
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'image-preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        ${index === 0 ? '<span class="image-preview-badge">Nueva Principal</span>' : ''}
                    `;
                    newImagePreview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }
    </script>
</body>
</html>