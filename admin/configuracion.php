<?php
// configuracion.php - Configuración del sitio
require_once 'config.php';
requireLogin();

// Guardar configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $configs = [
            'nombre_tienda' => sanitize($_POST['nombre_tienda']),
            'email_contacto' => sanitize($_POST['email_contacto']),
            'telefono_contacto' => sanitize($_POST['telefono_contacto']),
            'direccion_tienda' => $_POST['direccion_tienda'] ?? '',
            'costo_envio_lima' => (float)$_POST['costo_envio_lima'],
            'costo_envio_provincia' => (float)$_POST['costo_envio_provincia'],
            'yape_numero' => sanitize($_POST['yape_numero']),
            'yape_nombre' => sanitize($_POST['yape_nombre']),
            'facebook_url' => $_POST['facebook_url'] ?? '',
            'instagram_url' => $_POST['instagram_url'] ?? '',
            'whatsapp_numero' => sanitize($_POST['whatsapp_numero'] ?? ''),
            'mensaje_bienvenida' => $_POST['mensaje_bienvenida'] ?? '',
            'mensaje_pie_pagina' => $_POST['mensaje_pie_pagina'] ?? ''
        ];
        
        foreach ($configs as $clave => $valor) {
            $stmt = $pdo->prepare("
                INSERT INTO configuracion (clave, valor) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE valor = ?
            ");
            $stmt->execute([$clave, $valor, $valor]);
        }
        
        // Subir logo si existe
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['logo'], 'config');
            if ($upload['success']) {
                // Eliminar logo anterior si existe
                $logoActual = getConfig('logo');
                if ($logoActual) {
                    deleteImage($logoActual);
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO configuracion (clave, valor) 
                    VALUES ('logo', ?) 
                    ON DUPLICATE KEY UPDATE valor = ?
                ");
                $stmt->execute([$upload['path'], $upload['path']]);
            }
        }
        
        $success = "Configuración guardada correctamente";
        
    } catch (PDOException $e) {
        $error = "Error al guardar: " . $e->getMessage();
    }
}

// Cargar configuración actual
function getConfigValue($key, $default = '') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT valor FROM configuracion WHERE clave = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['valor'] : $default;
}

$config = [
    'nombre_tienda' => getConfigValue('nombre_tienda', 'Milan Jeans'),
    'email_contacto' => getConfigValue('email_contacto'),
    'telefono_contacto' => getConfigValue('telefono_contacto'),
    'direccion_tienda' => getConfigValue('direccion_tienda'),
    'costo_envio_lima' => getConfigValue('costo_envio_lima', '10.00'),
    'costo_envio_provincia' => getConfigValue('costo_envio_provincia', '15.00'),
    'yape_numero' => getConfigValue('yape_numero'),
    'yape_nombre' => getConfigValue('yape_nombre'),
    'facebook_url' => getConfigValue('facebook_url'),
    'instagram_url' => getConfigValue('instagram_url'),
    'whatsapp_numero' => getConfigValue('whatsapp_numero'),
    'mensaje_bienvenida' => getConfigValue('mensaje_bienvenida'),
    'mensaje_pie_pagina' => getConfigValue('mensaje_pie_pagina'),
    'logo' => getConfigValue('logo')
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Milan Jeans Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .config-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .config-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .config-card h2 {
            font-size: 18px;
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .config-card h2 svg {
            color: var(--primary);
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
        .form-group input[type="email"],
        .form-group input[type="number"],
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
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
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
        
        .logo-upload {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .logo-preview {
            width: 150px;
            height: 150px;
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: var(--light);
        }
        
        .logo-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .logo-preview svg {
            color: var(--gray);
        }
        
        .logo-upload-btn {
            flex: 1;
        }
        
        .logo-upload-btn input[type="file"] {
            display: none;
        }
        
        .btn-upload {
            display: inline-block;
            padding: 10px 20px;
            background: var(--info);
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-upload:hover {
            background: #3b9aee;
            transform: translateY(-2px);
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
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid var(--info);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-box p {
            font-size: 13px;
            color: #0c5460;
            margin: 0;
        }
        
        .sticky-save {
            position: sticky;
            bottom: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 -2px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .logo-upload {
                flex-direction: column;
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
                <h1>Configuración del Sitio</h1>
                <p>Personaliza la información de tu tienda</p>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="config-container">
                <form method="POST" enctype="multipart/form-data">
                    
                    <!-- Información General -->
                    <div class="config-card">
                        <h2>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="16" x2="12" y2="12"/>
                                <line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                            Información General
                        </h2>
                        
                        <div class="form-group">
                            <label>Nombre de la Tienda <span class="required">*</span></label>
                            <input type="text" name="nombre_tienda" value="<?= htmlspecialchars($config['nombre_tienda']) ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email de Contacto <span class="required">*</span></label>
                                <input type="email" name="email_contacto" value="<?= htmlspecialchars($config['email_contacto']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Teléfono de Contacto <span class="required">*</span></label>
                                <input type="text" name="telefono_contacto" value="<?= htmlspecialchars($config['telefono_contacto']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Dirección de la Tienda</label>
                            <textarea name="direccion_tienda" rows="2"><?= htmlspecialchars($config['direccion_tienda']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Logo de la Tienda</label>
                            <div class="logo-upload">
                                <div class="logo-preview">
                                    <?php if ($config['logo']): ?>
                                        <img src="<?= UPLOAD_URL . $config['logo'] ?>" alt="Logo" id="logoPreview">
                                    <?php else: ?>
                                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                            <circle cx="8.5" cy="8.5" r="1.5"/>
                                            <polyline points="21 15 16 10 5 21"/>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div class="logo-upload-btn">
                                    <input type="file" id="logoInput" name="logo" accept="image/*" onchange="previewLogo(event)">
                                    <label for="logoInput" class="btn-upload">
                                        Seleccionar Logo
                                    </label>
                                    <small>Recomendado: 500x500px, formato PNG con fondo transparente</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Costos de Envío -->
                    <div class="config-card">
                        <h2>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="3" width="15" height="13"/>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                <circle cx="5.5" cy="18.5" r="2.5"/>
                                <circle cx="18.5" cy="18.5" r="2.5"/>
                            </svg>
                            Costos de Envío
                        </h2>
                        
                        <div class="info-box">
                            <p>Define los costos de envío para diferentes zonas. Estos valores se aplicarán automáticamente en el checkout.</p>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Costo de Envío en Lima <span class="required">*</span></label>
                                <input type="number" name="costo_envio_lima" step="0.01" min="0" value="<?= $config['costo_envio_lima'] ?>" required>
                                <small>En soles (S/)</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Costo de Envío en Provincia <span class="required">*</span></label>
                                <input type="number" name="costo_envio_provincia" step="0.01" min="0" value="<?= $config['costo_envio_provincia'] ?>" required>
                                <small>En soles (S/)</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información de Yape -->
                    <div class="config-card">
                        <h2>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                                <line x1="12" y1="18" x2="12.01" y2="18"/>
                            </svg>
                            Información de Yape
                        </h2>
                        
                        <div class="info-box">
                            <p>Esta información se mostrará a los clientes para que puedan realizar pagos por Yape.</p>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Número de Yape <span class="required">*</span></label>
                                <input type="text" name="yape_numero" value="<?= htmlspecialchars($config['yape_numero']) ?>" required placeholder="999999999">
                            </div>
                            
                            <div class="form-group">
                                <label>Nombre del Titular <span class="required">*</span></label>
                                <input type="text" name="yape_nombre" value="<?= htmlspecialchars($config['yape_nombre']) ?>" required placeholder="Milan Jeans">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Redes Sociales -->
                    <div class="config-card">
                        <h2>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
                            </svg>
                            Redes Sociales
                        </h2>
                        
                        <div class="form-group">
                            <label>Facebook</label>
                            <input type="text" name="facebook_url" value="<?= htmlspecialchars($config['facebook_url']) ?>" placeholder="https://facebook.com/tupagina">
                        </div>
                        
                        <div class="form-group">
                            <label>Instagram</label>
                            <input type="text" name="instagram_url" value="<?= htmlspecialchars($config['instagram_url']) ?>" placeholder="https://instagram.com/tuperfil">
                        </div>
                        
                        <div class="form-group">
                            <label>WhatsApp</label>
                            <input type="text" name="whatsapp_numero" value="<?= htmlspecialchars($config['whatsapp_numero']) ?>" placeholder="51999999999">
                            <small>Incluye código de país (ej: 51 para Perú)</small>
                        </div>
                    </div>
                    
                    <!-- Mensajes Personalizados -->
                    <div class="config-card">
                        <h2>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                            </svg>
                            Mensajes Personalizados
                        </h2>
                        
                        <div class="form-group">
                            <label>Mensaje de Bienvenida</label>
                            <textarea name="mensaje_bienvenida" rows="3"><?= htmlspecialchars($config['mensaje_bienvenida']) ?></textarea>
                            <small>Se mostrará en la página principal</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Mensaje del Pie de Página</label>
                            <textarea name="mensaje_pie_pagina" rows="2"><?= htmlspecialchars($config['mensaje_pie_pagina']) ?></textarea>
                            <small>Aparecerá al final de todas las páginas</small>
                        </div>
                    </div>
                    
                    <!-- Botón Guardar Sticky -->
                    <div class="sticky-save">
                        <div>
                            <strong style="color: var(--dark);">¿Listo para guardar los cambios?</strong>
                            <p style="font-size: 13px; color: var(--gray); margin: 5px 0 0 0;">Todos los cambios se aplicarán inmediatamente</p>
                        </div>
                        <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
                                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                                <polyline points="7 3 7 8 15 8"/>
                            </svg>
                            Guardar Configuración
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script>
        function previewLogo(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.logo-preview');
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Logo Preview" id="logoPreview">';
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>