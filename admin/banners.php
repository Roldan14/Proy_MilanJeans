<?php
// banners.php - Gestión de banners
require_once 'config.php';
requireLogin();

// Eliminar banner
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    
    try {
        // Obtener imagen para eliminarla
        $stmt = $pdo->prepare("SELECT imagen FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $banner = $stmt->fetch();
        
        if ($banner && $banner['imagen']) {
            deleteImage($banner['imagen']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        
        $success = "Banner eliminado correctamente";
    } catch (PDOException $e) {
        $error = "Error al eliminar el banner: " . $e->getMessage();
    }
}

// Cambiar estado rápido
if (isset($_POST['cambiar_estado'])) {
    $id = (int)$_POST['banner_id'];
    $activo = (int)$_POST['activo'];
    
    $stmt = $pdo->prepare("UPDATE banners SET activo = ? WHERE id = ?");
    $stmt->execute([$activo, $id]);
    
    $success = "Estado actualizado correctamente";
}

// Obtener banners
$banners = $pdo->query("SELECT * FROM banners ORDER BY posicion, orden ASC, fecha_creacion DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banners - Milan Jeans Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .banners-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .banners-grid {
            display: grid;
            gap: 20px;
        }
        
        .banner-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: grid;
            grid-template-columns: 300px 1fr auto;
            gap: 20px;
            transition: all 0.3s;
        }
        
        .banner-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .banner-image {
            width: 300px;
            height: 180px;
            object-fit: cover;
            background: var(--light);
        }
        
        .banner-info {
            padding: 20px 0;
        }
        
        .banner-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .banner-subtitle {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 10px;
        }
        
        .banner-description {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 10px;
            line-height: 1.5;
        }
        
        .banner-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        .meta-badge {
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-principal {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-secundario {
            background: #fff3e0;
            color: #e65100;
        }
        
        .badge-activo {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-inactivo {
            background: #f8d7da;
            color: #721c24;
        }
        
        .banner-actions {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            justify-content: center;
        }
        
        .btn-icon {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            white-space: nowrap;
        }
        
        .btn-edit {
            background: var(--info);
            color: white;
        }
        
        .btn-toggle {
            background: var(--warning);
            color: white;
        }
        
        .btn-delete {
            background: var(--danger);
            color: white;
        }
        
        .btn-edit:hover {
            background: #3b9aee;
        }
        
        .btn-toggle:hover {
            background: #e08e0b;
        }
        
        .btn-delete:hover {
            background: #c0392b;
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
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
        }
        
        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            color: var(--gray);
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
        
        @media (max-width: 968px) {
            .banner-card {
                grid-template-columns: 1fr;
            }
            
            .banner-image {
                width: 100%;
                height: 200px;
            }
            
            .banner-info {
                padding: 20px;
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
                <h1>Banners del Sitio</h1>
                <p>Gestiona los banners que aparecen en la página principal</p>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="info-box">
                <p><strong>Tip:</strong> Los banners principales aparecen en el slider grande de la página de inicio. Los secundarios se muestran en secciones promocionales. El orden determina la secuencia de aparición.</p>
            </div>
            
            <div class="banners-header">
                <h2 style="font-size: 18px; color: var(--dark);">
                    <?= count($banners) ?> banner(es) registrado(s)
                </h2>
                <a href="banner_form.php" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 5px;">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nuevo Banner
                </a>
            </div>
            
            <?php if (empty($banners)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                    <h3>No hay banners registrados</h3>
                    <p>Crea tu primer banner para mostrarlo en la página principal</p>
                    <a href="banner_form.php" class="btn btn-primary" style="margin-top: 20px;">
                        Crear Primer Banner
                    </a>
                </div>
            <?php else: ?>
                <div class="banners-grid">
                    <?php foreach ($banners as $banner): ?>
                        <div class="banner-card">
                            <img src="<?= $banner['imagen'] ? UPLOAD_URL . $banner['imagen'] : 'https://via.placeholder.com/300x180?text=Sin+Imagen' ?>" 
                                 alt="<?= htmlspecialchars($banner['titulo']) ?>" 
                                 class="banner-image">
                            
                            <div class="banner-info">
                                <div class="banner-title"><?= htmlspecialchars($banner['titulo']) ?></div>
                                <?php if ($banner['subtitulo']): ?>
                                    <div class="banner-subtitle"><?= htmlspecialchars($banner['subtitulo']) ?></div>
                                <?php endif; ?>
                                <?php if ($banner['descripcion']): ?>
                                    <div class="banner-description"><?= htmlspecialchars($banner['descripcion']) ?></div>
                                <?php endif; ?>
                                
                                <div class="banner-meta">
                                    <span class="meta-badge badge-<?= $banner['posicion'] ?>">
                                        <?= ucfirst($banner['posicion']) ?>
                                    </span>
                                    <span class="meta-badge <?= $banner['activo'] ? 'badge-activo' : 'badge-inactivo' ?>">
                                        <?= $banner['activo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                    <span class="meta-badge" style="background: var(--light); color: var(--dark);">
                                        Orden: <?= $banner['orden'] ?>
                                    </span>
                                    <?php if ($banner['enlace']): ?>
                                        <span class="meta-badge" style="background: #f3e5f5; color: #7b1fa2;">
                                            Con enlace
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($banner['texto_boton']): ?>
                                        <span class="meta-badge" style="background: #e8f5e9; color: #2e7d32;">
                                            Botón: "<?= htmlspecialchars($banner['texto_boton']) ?>"
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($banner['fecha_inicio'] || $banner['fecha_fin']): ?>
                                    <p style="font-size: 12px; color: var(--gray); margin-top: 10px;">
                                        <?php if ($banner['fecha_inicio']): ?>
                                            Desde: <?= date('d/m/Y', strtotime($banner['fecha_inicio'])) ?>
                                        <?php endif; ?>
                                        <?php if ($banner['fecha_fin']): ?>
                                            - Hasta: <?= date('d/m/Y', strtotime($banner['fecha_fin'])) ?>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="banner-actions">
                                <a href="banner_form.php?id=<?= $banner['id'] ?>" class="btn-icon btn-edit">
                                    Editar
                                </a>
                                
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="banner_id" value="<?= $banner['id'] ?>">
                                    <input type="hidden" name="activo" value="<?= $banner['activo'] ? 0 : 1 ?>">
                                    <input type="hidden" name="cambiar_estado" value="1">
                                    <button type="submit" class="btn-icon btn-toggle" style="width: 100%;">
                                        <?= $banner['activo'] ? 'Desactivar' : 'Activar' ?>
                                    </button>
                                </form>
                                
                                <button onclick="confirmarEliminar(<?= $banner['id'] ?>, '<?= addslashes($banner['titulo']) ?>')" class="btn-icon btn-delete">
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
        function confirmarEliminar(id, titulo) {
            if (confirm('¿Estás seguro de eliminar el banner "' + titulo + '"?\n\nEsta acción no se puede deshacer.')) {
                window.location.href = 'banners.php?eliminar=' + id;
            }
        }
    </script>
</body>
</html>