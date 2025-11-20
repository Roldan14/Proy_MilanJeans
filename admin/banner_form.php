<?php
// banner_form.php - Formulario para crear/editar banners
require_once 'config.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;
$banner = null;

// Si es edición, cargar datos del banner
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
    $stmt->execute([$id]);
    $banner = $stmt->fetch();
    
    if (!$banner) {
        header('Location: banners.php');
        exit;
    }
}

// Procesar formulario
// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = sanitize($_POST['titulo']);
    $subtitulo = sanitize($_POST['subtitulo'] ?? '');
    $descripcion = $_POST['descripcion'] ?? '';
    $enlace = $_POST['enlace'] ?? '';
    $texto_boton = sanitize($_POST['texto_boton'] ?? '');
    $posicion = $_POST['posicion'];
    $orden = (int)$_POST['orden'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    $fecha_inicio = $_POST['fecha_inicio'] ?: null;
    $fecha_fin = $_POST['fecha_fin'] ?: null;
    $posicion_texto = $_POST['posicion_texto'] ?? 'centro-centro';
    $mostrar_contenido = isset($_POST['mostrar_contenido']) ? 1 : 0;
    
    try {
        // Subir imagen si existe
        $imagen_path = $banner['imagen'] ?? '';
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['imagen'], 'banners');
            
            if ($upload['success']) {
                // Eliminar imagen anterior si existe
                if ($banner && $banner['imagen']) {
                    deleteImage($banner['imagen']);
                }
                $imagen_path = $upload['path'];
            } else {
                throw new Exception($upload['message']);
            }
        }
        
        if ($is_edit) {
            // Actualizar banner
            $stmt = $pdo->prepare("
                UPDATE banners SET 
                titulo = ?, subtitulo = ?, descripcion = ?, imagen = ?, 
                enlace = ?, texto_boton = ?, posicion = ?, orden = ?, activo = ?,
                fecha_inicio = ?, fecha_fin = ?, posicion_texto = ?, mostrar_contenido = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $titulo, $subtitulo, $descripcion, $imagen_path,
                $enlace, $texto_boton, $posicion, $orden, $activo,
                $fecha_inicio, $fecha_fin, $posicion_texto, $mostrar_contenido, $id
            ]);
            
            $_SESSION['success'] = "Banner actualizado correctamente";
        } else {
            // Crear nuevo banner
            if (empty($imagen_path)) {
                throw new Exception("La imagen es obligatoria");
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO banners 
                (titulo, subtitulo, descripcion, imagen, enlace, texto_boton, posicion, orden, activo, fecha_inicio, fecha_fin, posicion_texto, mostrar_contenido)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $titulo, $subtitulo, $descripcion, $imagen_path,
                $enlace, $texto_boton, $posicion, $orden, $activo,
                $fecha_inicio, $fecha_fin, $posicion_texto, $mostrar_contenido
            ]);
            
            $_SESSION['success'] = "Banner creado correctamente";
        }
        
        header('Location: banners.php');
        exit;
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit ? 'Editar' : 'Nuevo' ?> Banner - Milan Jeans Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .form-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
        
        .form-group .required {
            color: var(--danger);
        }
        
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="date"],
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
            min-height: 80px;
        }
        
        .form-group small {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: var(--gray);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .image-upload-area {
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: var(--light);
        }
        
        .image-upload-area:hover {
            border-color: var(--primary);
            background: white;
        }
        
        .image-upload-area input[type="file"] {
            display: none;
        }
        
        .image-preview {
            margin-top: 20px;
        }
        
        .image-preview img {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        
        .btn-group {
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
        
        .info-box {
            background: #fff3cd;
            border-left: 4px solid var(--warning);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-box p {
            font-size: 13px;
            color: #856404;
            margin: 0;
        }

        .position-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 10px;
        }

        .position-option {
           cursor: pointer;
        }

        .position-option input[type="radio"] {
           display: none;
        }

        .position-box {
          border: 2px solid #e0e0e0;
          border-radius: 8px;
          padding: 15px 10px;
          text-align: center;
          transition: all 0.3s;
          background: white;
          display: flex;
          flex-direction: column;
          gap: 5px;
          min-height: 80px;
          justify-content: center;
        }

        .position-option:hover .position-box {
          border-color: var(--primary);
          background: var(--light);
        }

        .position-option input[type="radio"]:checked + .position-box {
          border-color: var(--primary);
          background: var(--primary);
          color: white;
        }

        .pos-h {
          font-weight: 700;
          font-size: 13px;
        }

        .pos-v {
          font-size: 11px;
          opacity: 0.8;
        }

        @media (max-width: 768px) {
          .form-row {
              grid-template-columns: 1fr;
            }
          .position-grid {
              grid-template-columns: repeat(3, 1fr);
              gap: 8px;
            }
    
            .position-box {
              padding: 10px 5px;
              min-height: 60px;
            }
    
           .pos-h {
               font-size: 11px;
            }
    
           .pos-v {
              font-size: 9px;
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
                    <a href="banners.php" style="color: var(--primary); text-decoration: none; font-size: 14px; display: inline-block; margin-bottom: 10px;">
                        ← Volver a banners
                    </a>
                    <h1><?= $is_edit ? 'Editar Banner' : 'Nuevo Banner' ?></h1>
                    <p><?= $is_edit ? 'Actualiza la información del banner' : 'Crea un nuevo banner para la página principal' ?></p>
                </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="info-box">
                <p><strong>Dimensiones recomendadas:</strong> 1920x600px para banners principales, 800x400px para secundarios. Formato JPG o PNG, máximo 5MB.</p>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="form-container">
                
                <div class="form-section">
                    <h3>Contenido del Banner</h3>
                    
                    <div class="form-group">
                        <label>Título <span class="required">*</span></label>
                        <input type="text" name="titulo" value="<?= htmlspecialchars($banner['titulo'] ?? '') ?>" required>
                        <small>Texto principal del banner (ej: "Nueva Colección 2025")</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Subtítulo</label>
                        <input type="text" name="subtitulo" value="<?= htmlspecialchars($banner['subtitulo'] ?? '') ?>">
                        <small>Texto secundario (ej: "Los mejores jeans para ti")</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" rows="3"><?= htmlspecialchars($banner['descripcion'] ?? '') ?></textarea>
                        <small>Descripción adicional del banner</small>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Imagen del Banner</h3>
                    
                    <div class="form-group">
                        <div class="image-upload-area" onclick="document.getElementById('imagenInput').click()">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 10px; color: var(--gray);">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                            <p style="color: var(--gray); margin-bottom: 5px;">Haz clic para seleccionar imagen</p>
                            <p style="font-size: 12px; color: var(--gray);">o arrastra y suelta aquí</p>
                            <input type="file" id="imagenInput" name="imagen" accept="image/*" onchange="previewImage(event)" <?= $is_edit ? '' : 'required' ?>>
                        </div>
                        
                        <div class="image-preview" id="imagePreview">
                            <?php if ($banner && $banner['imagen']): ?>
                                <img src="<?= UPLOAD_URL . $banner['imagen'] ?>" alt="Preview">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                     <h3>Configuración de Contenido</h3>
    
                    <div class="form-group">
                       <div class="checkbox-item">
                           <input type="checkbox" name="mostrar_contenido" id="mostrar_contenido" 
                           <?= ($banner['mostrar_contenido'] ?? 1) ? 'checked' : '' ?>
                           onchange="toggleContenido()">
                          <label for="mostrar_contenido" style="margin: 0; font-weight: normal;">
                              Mostrar texto sobre la imagen (desmarcar para banner solo con imagen)
                          </label>
                        </div>
                    </div>
    
                   <div id="contenidoSection" style="<?= ($banner['mostrar_contenido'] ?? 1) ? '' : 'display: none;' ?>">
                       <div class="form-group">
                           <label>Posición del Contenido <span class="required">*</span></label>
                           <div class="position-grid">
                              <?php
                                  $posiciones = [
                                 'izquierda-arriba' => ['Izquierda', 'Arriba'],
                                 'centro-arriba' => ['Centro', 'Arriba'],
                                 'derecha-arriba' => ['Derecha', 'Arriba'],
                                 'izquierda-centro' => ['Izquierda', 'Centro'],
                                 'centro-centro' => ['Centro', 'Centro'],
                                 'derecha-centro' => ['Derecha', 'Centro'],
                                 'izquierda-abajo' => ['Izquierda', 'Abajo'],
                                 'centro-abajo' => ['Centro', 'Abajo'],
                                 'derecha-abajo' => ['Derecha', 'Abajo'],
                                ];
                
                                 $posicion_actual = $banner['posicion_texto'] ?? 'centro-centro';
                
                                foreach ($posiciones as $valor => $labels):
                                ?>
                                <label class="position-option">
                                   <input type="radio" name="posicion_texto" value="<?= $valor ?>" 
                                   <?= $posicion_actual == $valor ? 'checked' : '' ?>>
                                   <div class="position-box">
                                     <span class="pos-h"><?= $labels[0] ?></span>
                                     <span class="pos-v"><?= $labels[1] ?></span>
                                   </div>
                                </label>
                                <?php endforeach; ?>
                           </div>
                           <small>Selecciona dónde quieres que aparezca el texto sobre la imagen</small>
                       </div>
        
                      <div class="form-group">
                           <label>Enlace (URL)</label>
                           <input type="text" name="enlace" value="<?= htmlspecialchars($banner['enlace'] ?? '') ?>" placeholder="productos.php?categoria=1">
                           <small>Página a la que llevará el banner al hacer clic</small>
                       </div>
                  </div>
                </div>

                
                <div class="form-section">
                    <h3>Configuración</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Posición <span class="required">*</span></label>
                            <select name="posicion" required>
                                <option value="principal" <?= ($banner['posicion'] ?? 'principal') == 'principal' ? 'selected' : '' ?>>Principal (Slider)</option>
                                <option value="secundario" <?= ($banner['posicion'] ?? '') == 'secundario' ? 'selected' : '' ?>>Secundario (Promocional)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Orden <span class="required">*</span></label>
                            <input type="number" name="orden" min="0" value="<?= $banner['orden'] ?? 0 ?>" required>
                            <small>Orden de aparición (menor = primero)</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Fecha de Inicio</label>
                            <input type="date" name="fecha_inicio" value="<?= $banner['fecha_inicio'] ?? '' ?>">
                            <small>Banner visible desde esta fecha</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Fecha de Fin</label>
                            <input type="date" name="fecha_fin" value="<?= $banner['fecha_fin'] ?? '' ?>">
                            <small>Banner visible hasta esta fecha</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-item">
                            <input type="checkbox" name="activo" id="activo" <?= ($banner['activo'] ?? 1) ? 'checked' : '' ?>>
                            <label for="activo" style="margin: 0; font-weight: normal;">Banner activo (visible en el sitio)</label>
                        </div>
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <?= $is_edit ? 'Actualizar Banner' : 'Crear Banner' ?>
                    </button>
                    <a href="banners.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </main>
    </div>
    
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                }
                reader.readAsDataURL(file);
            }
        }
        
        // Drag and drop
        const dropArea = document.querySelector('.image-upload-area');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.style.borderColor = 'var(--primary)';
                dropArea.style.background = 'white';
            });
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.style.borderColor = '#e0e0e0';
                dropArea.style.background = 'var(--light)';
            });
        });
        
        dropArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            document.getElementById('imagenInput').files = files;
            
            if (files.length > 0) {
                previewImage({ target: { files: files } });
            }
        });
        
        function toggleContenido() {
    const checkbox = document.getElementById('mostrar_contenido');
    const section = document.getElementById('contenidoSection');
    
    if (checkbox.checked) {
        section.style.display = 'block';
    } else {
        section.style.display = 'none';
    }
}

    </script>
</body>
</html>