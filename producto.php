<?php
require_once 'includes/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener datos del producto
$stmt = $pdo->prepare("
    SELECT p.*, c.nombre as categoria_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    WHERE p.id = ? AND p.activo = 1
");
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: productos.php');
    exit;
}

// Incrementar vistas
$pdo->prepare("UPDATE productos SET vistas = vistas + 1 WHERE id = ?")->execute([$id]);

// Obtener imágenes del producto
$stmt = $pdo->prepare("SELECT * FROM producto_imagenes WHERE producto_id = ? ORDER BY es_principal DESC, orden ASC");
$stmt->execute([$id]);
$imagenes = $stmt->fetchAll();

// Definir imagen principal
$imagen_mostrar = '';
if (!empty($imagenes)) {
    $imagen_mostrar = $imagenes[0]['ruta_imagen'];
}

// Obtener colores disponibles
$colores = $pdo->prepare("
    SELECT c.* FROM colores c
    INNER JOIN producto_colores pc ON c.id = pc.color_id
    WHERE pc.producto_id = ? AND c.activo = 1
    ORDER BY c.nombre
");
$colores->execute([$id]);
$colores = $colores->fetchAll();

// Obtener tallas disponibles
$tallas = $pdo->prepare("
    SELECT t.* FROM tallas t
    INNER JOIN producto_tallas pt ON t.id = pt.talla_id
    WHERE pt.producto_id = ? AND t.activo = 1
    ORDER BY t.orden
");
$tallas->execute([$id]);
$tallas = $tallas->fetchAll();

// Productos relacionados (de la misma categoría)
$productos_relacionados = $pdo->prepare("
    SELECT p.*,
    (SELECT ruta_imagen FROM producto_imagenes WHERE producto_id = p.id AND es_principal = 1 LIMIT 1) as imagen
    FROM productos p
    WHERE p.activo = 1 
    AND p.id != ?
    AND (p.categoria_id = ? OR p.categoria_id IS NULL)
    ORDER BY RAND()
    LIMIT 4
");
$productos_relacionados->execute([$id, $producto['categoria_id']]);
$productos_relacionados = $productos_relacionados->fetchAll();

// Productos sugeridos (más vendidos o aleatorios)
$productos_sugeridos = $pdo->prepare("
    SELECT p.*,
    (SELECT ruta_imagen FROM producto_imagenes WHERE producto_id = p.id AND es_principal = 1 LIMIT 1) as imagen
    FROM productos p
    WHERE p.activo = 1 
    AND p.id != ?
    ORDER BY p.ventas DESC, RAND()
    LIMIT 4
");
$productos_sugeridos->execute([$id]);
$productos_sugeridos = $productos_sugeridos->fetchAll();

$page_title = $producto['nombre'] . ' - ' . $config_sitio['nombre'];
$page_description = substr(strip_tags($producto['descripcion']), 0, 160);

include 'includes/header.php';
?>

<style>
    .breadcrumb {
        background: var(--bg-light);
        padding: 20px 0;
    }
    
    .breadcrumb-content {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
    }
    
    .breadcrumb a {
        color: var(--text-gray);
        text-decoration: none;
    }
    
    .breadcrumb a:hover {
        color: var(--primary);
    }
    
    .producto-page {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    
    .producto-main {
        display: grid;
        grid-template-columns: 80px 450px 450px;
        gap: 30px;
        margin-bottom: 80px;
        max-width: 1100px;
        margin-left: auto;
        margin-right: auto;
    }
    
    /* Galería de Imágenes */
    .producto-galeria {
        display: contents;
    }
    
    .imagenes-thumbs {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .thumb {
        width: 80px;
        height: 100px;
        border-radius: 6px;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.3s;
        background: var(--bg-light);
    }
    
    .thumb:hover {
        border-color: var(--primary);
    }
    
    .thumb.active {
        border-color: var(--primary);
    }
    
    .thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .imagen-principal {
        width: 450px;
        height: 580px;
        border-radius: 12px;
        overflow: hidden;
        background: var(--bg-light);
        position: relative;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }
    
    .imagen-principal img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    /* Información del Producto */
    .producto-info {
        padding-top: 0;
        max-width: 500px;
    }
    
    .producto-categoria {
        font-size: 12px;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 2px;
    }
    
    .producto-titulo {
        font-size: 32px;
        font-weight: 900;
        color: var(--text-dark);
        line-height: 1.2;
        margin-bottom: 5px;
    }
    
    .producto-precio {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 5px;
    }
    
    .precio-actual {
        font-size: 36px;
        font-weight: 900;
        color: var(--primary);
    }
    
    .precio-anterior {
        font-size: 22px;
        color: var(--text-light);
        text-decoration: line-through;
    }
    
    .ahorro-badge {
        background: var(--primary);
        color: var(--white);
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 700;
    }
    
    .producto-descripcion {
        color: var(--text-gray);
        line-height: 1.7;
        font-size: 14px;
        padding-bottom: 25px;
        border-bottom: 1px solid var(--bg-light);
    }
    
    .opciones-section {
        margin-bottom: 15px;
    }
    
    .opciones-section h3 {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 8px;
    }
    
    /* Selector de Colores */
    .colores-grid {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .color-option {
        position: relative;
        cursor: pointer;
    }
    
    .color-option input {
        display: none;
    }
    
    .color-swatch {
        width: 30px;
        height: 30px;
        border: 1px solid;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        
    }
    
    .color-option:hover .color-swatch {
        border-color: var(--primary);
        transform: scale(1.1);
    }
    
    .color-option input:checked + .color-swatch {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(220, 20, 60, 0.2);
    }
    
    .color-option input:checked + .color-swatch::after {
        content: '✓';
        color: var(--white);
        font-weight: 900;
        font-size: 16px;
        text-shadow: 0 0 3px rgba(0,0,0,0.5);
    }
    
    /* Selector de Tallas */
    .tallas-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(55px, 1fr));
        gap: 6px;
    }
    
    .talla-option {
        position: relative;
    }
    
    .talla-option input {
        display: none;
    }
    
    .talla-label {
        display: block;
        padding: 10px;
        text-align: center;
        border: 2px solid var(--border);
        border-radius: 8px;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.3s;
        background: var(--white);
    }
    
    .talla-option:hover .talla-label {
        border-color: var(--primary);
        background: var(--bg-light);
    }
    
    .talla-option input:checked + .talla-label {
        background: var(--primary);
        color: var(--white);
        border-color: var(--primary);
    }
    
    /* Cantidad */
    .cantidad-selector {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cantidad-control {
        display: flex;
        border: 2px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
    }
    
    .cantidad-control button {
        width: 35px;
        height: 35px;
        border: none;
        background: var(--bg-light);
        color: var(--text-dark);
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .cantidad-control button:hover {
        background: var(--primary);
        color: var(--white);
    }
    
    .cantidad-control input {
        width: 50px;
        text-align: center;
        border: none;
        font-size: 14px;
        font-weight: 700;
    }
    
    /* Botones de Acción */
    .producto-acciones {
        display: flex;
        gap: 8px;
        margin-top: 15px;
        margin-bottom: 15px;
    }
    
    .btn-add-cart {
        flex: 1;
        padding: 12px 20px;
        background: var(--primary);
        color: var(--white);
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .btn-add-cart:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220, 20, 60, 0.3);
    }
    
    .btn-whatsapp {
        padding: 12px 18px;
        background: #25D366;
        color: var(--white);
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .btn-whatsapp:hover {
        background: #20BA5A;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37, 211, 102, 0.3);
    }
    
    .btn-whatsapp svg {
        width: 20px;
        height: 20px;
    }
    
    /* Info Extra */
    .producto-extra {
        margin-top: 0;
        padding-top: 15px;
        border-top: 1px solid var(--bg-light);
    }
    
    .info-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 0;
        color: var(--text-gray);
        font-size: 11px;
        line-height: 1.4;
    }
    
    .info-item svg {
        color: var(--primary);
        flex-shrink: 0;
        width: 16px;
        height: 16px;
    }
    
    /* Productos Relacionados */
    .relacionados-section {
        margin-top: 80px;
    }
    
    .relacionados-section h2 {
        font-size: 32px;
        font-weight: 900;
        color: var(--text-dark);
        margin-bottom: 40px;
        text-align: center;
    }
    
    .productos-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }
    
    .product-card {
        background: var(--white);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .product-image-wrapper {
        position: relative;
        padding-top: 130%;
        overflow: hidden;
        background: var(--bg-light);
    }
    
    .product-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }
    
    .product-card:hover .product-image {
        transform: scale(1.08);
    }
    
    .product-info {
        padding: 15px;
    }
    
    .product-name {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 40px;
    }
    
    .product-price {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 10px;
    }
    
    .price-current {
        font-size: 20px;
        font-weight: 900;
        color: var(--primary);
    }
    
    .btn-view {
        width: 100%;
        padding: 10px;
        background: var(--secondary);
        color: var(--white);
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        display: block;
        text-align: center;
        transition: all 0.3s;
    }
    
    .btn-view:hover {
        background: var(--primary);
    }
    
    .error-message {
        background: #fee;
        color: #c33;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: none;
    }
    
    /* Responsive */
    @media (max-width: 968px) {
        .producto-main {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .imagenes-thumbs {
            order: 2;
            flex-direction: row;
            overflow-x: auto;
        }
        
        .thumb {
            min-width: 80px;
            height: 100px;
        }
        
        .imagen-principal {
            order: 1;
            width: 100%;
            height: auto;
            aspect-ratio: 3/4;
        }
        
        .producto-info {
            order: 3;
            max-width: 100%;
        }
        
        .producto-titulo {
            font-size: 24px;
        }
        
        .precio-actual {
            font-size: 28px;
        }
        
        .producto-acciones {
            flex-direction: column;
        }
        
        .productos-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 640px) {
        .productos-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="breadcrumb-content">
        <a href="index.php">Inicio</a>
        <span>›</span>
        <a href="productos.php">Productos</a>
        <?php if ($producto['categoria_nombre']): ?>
            <span>›</span>
            <a href="productos.php?categoria=<?= $producto['categoria_id'] ?>"><?= htmlspecialchars($producto['categoria_nombre']) ?></a>
        <?php endif; ?>
        <span>›</span>
        <span style="color: var(--primary); font-weight: 600;"><?= htmlspecialchars($producto['nombre']) ?></span>
    </div>
</div>

<div class="producto-page">
    <div class="error-message" id="errorMessage"></div>
    
    <div class="producto-main">
        <!-- Miniaturas Laterales -->
        <div class="imagenes-thumbs">
            <?php 
            // Mostrar hasta 4 miniaturas
            $thumbs_mostrar = array_slice($imagenes, 0, 4);
            foreach ($thumbs_mostrar as $index => $img): 
            ?>
                <div class="thumb <?= $index === 0 ? 'active' : '' ?>" onclick="cambiarImagen('<?= UPLOAD_URL . $img['ruta_imagen'] ?>', this)">
                    <img src="<?= UPLOAD_URL . $img['ruta_imagen'] ?>" alt="Miniatura <?= $index + 1 ?>">
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Imagen Principal -->
        <div class="imagen-principal" id="imagenContainer">
            <img src="<?= $imagen_mostrar ? UPLOAD_URL . $imagen_mostrar : 'https://via.placeholder.com/500x650?text=Sin+Imagen' ?>" 
                 alt="<?= htmlspecialchars($producto['nombre']) ?>" 
                 id="imgPrincipal">
        </div>
        
        <!-- Información del Producto -->
        <div class="producto-info">
            <?php if ($producto['categoria_nombre']): ?>
                <div class="producto-categoria"><?= htmlspecialchars($producto['categoria_nombre']) ?></div>
            <?php endif; ?>
            
            <h1 class="producto-titulo"><?= htmlspecialchars($producto['nombre']) ?></h1>
            
            <div class="producto-precio">
                <span class="precio-actual"><?= formatPrice($producto['precio_oferta'] ?: $producto['precio']) ?></span>
                <?php if ($producto['precio_oferta']): ?>
                    <span class="precio-anterior"><?= formatPrice($producto['precio']) ?></span>
                    <?php 
                    $descuento = round((($producto['precio'] - $producto['precio_oferta']) / $producto['precio']) * 100);
                    ?>
                    <span class="ahorro-badge">-<?= $descuento ?>%</span>
                <?php endif; ?>
            </div>
            
            <?php if ($producto['descripcion']): ?>
                <div class="producto-descripcion">
                    <?= nl2br(htmlspecialchars($producto['descripcion'])) ?>
                </div>
            <?php endif; ?>
            
            <form id="addToCartForm">
                <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                <input type="hidden" name="producto_nombre" value="<?= htmlspecialchars($producto['nombre']) ?>">
                <input type="hidden" name="producto_precio" value="<?= $producto['precio_oferta'] ?: $producto['precio'] ?>">
                <input type="hidden" name="producto_imagen" value="<?= $imagen_mostrar ?>">
                
                <!-- Selector de Color -->
                <?php if (!empty($colores)): ?>
                    <div class="opciones-section">
                        <h3>Color: <span id="colorSeleccionado">Selecciona un color</span></h3>
                        <div class="colores-grid">
                            <?php foreach ($colores as $color): ?>
                                <label class="color-option">
                                    <input type="radio" name="color" value="<?= htmlspecialchars($color['nombre']) ?>" data-nombre="<?= htmlspecialchars($color['nombre']) ?>" required>
                                    <div class="color-swatch" style="background-color: <?= $color['codigo_hex'] ?>" title="<?= htmlspecialchars($color['nombre']) ?>"></div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Selector de Talla -->
                <?php if (!empty($tallas)): ?>
                    <div class="opciones-section">
                        <h3>Talla: <span id="tallaSeleccionada">Selecciona una talla</span></h3>
                        <div class="tallas-grid">
                            <?php foreach ($tallas as $talla): ?>
                                <label class="talla-option">
                                    <input type="radio" name="talla" value="<?= htmlspecialchars($talla['nombre']) ?>" data-nombre="<?= htmlspecialchars($talla['nombre']) ?>" required>
                                    <span class="talla-label"><?= htmlspecialchars($talla['nombre']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Selector de Cantidad -->
                <div class="opciones-section">
                    <h3>Cantidad</h3>
                    <div class="cantidad-selector">
                        <div class="cantidad-control">
                            <button type="button" onclick="cambiarCantidad(-1)">−</button>
                            <input type="number" name="cantidad" id="cantidad" value="1" min="1" max="<?= $producto['stock'] ?>" readonly>
                            <button type="button" onclick="cambiarCantidad(1)">+</button>
                        </div>
                        <span style="color: var(--text-gray);"><?= $producto['stock'] ?> disponibles</span>
                    </div>
                </div>
                
                <!-- Botones de Acción -->
                <div class="producto-acciones">
                    <button type="submit" class="btn-add-cart">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle; margin-right: 10px;">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                        </svg>
                        Agregar al Carrito
                    </button>
                    
                    <?php if ($config_sitio['whatsapp']): ?>
                        <a href="https://wa.me/<?= $config_sitio['whatsapp'] ?>?text=Hola, me interesa este producto: <?= urlencode($producto['nombre']) ?>" 
                           target="_blank" 
                           class="btn-whatsapp">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            WhatsApp
                        </a>
                    <?php endif; ?>
                </div>
            </form>
            
            <!-- Información Extra -->
            <div class="producto-extra">
                <div class="info-item">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13"/>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                        <circle cx="5.5" cy="18.5" r="2.5"/>
                        <circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                    <span><strong>Envío a todo el Perú</strong> - Delivery en Lima y provincia</span>
                </div>
                <div class="info-item">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <span><strong>Garantía de calidad</strong> - Productos 100% originales</span>
                </div>
                <div class="info-item">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span><strong>Atención rápida</strong> - Respuesta en menos de 24 horas</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Productos Relacionados -->
    <?php if (!empty($productos_relacionados)): ?>
        <div class="relacionados-section">
            <h2>También te puede interesar</h2>
            <div class="productos-grid">
                <?php foreach ($productos_relacionados as $prod): ?>
                    <div class="product-card">
                        <div class="product-image-wrapper">
                            <a href="producto.php?id=<?= $prod['id'] ?>">
                                <img src="<?= $prod['imagen'] ? UPLOAD_URL . $prod['imagen'] : 'https://via.placeholder.com/300x400?text=Sin+Imagen' ?>" 
                                     alt="<?= htmlspecialchars($prod['nombre']) ?>" 
                                     class="product-image">
                            </a>
                        </div>
                        <div class="product-info">
                            <a href="producto.php?id=<?= $prod['id'] ?>" style="text-decoration: none;">
                                <h3 class="product-name"><?= htmlspecialchars($prod['nombre']) ?></h3>
                            </a>
                            <div class="product-price">
                                <span class="price-current"><?= formatPrice($prod['precio_oferta'] ?: $prod['precio']) ?></span>
                            </div>
                            <a href="producto.php?id=<?= $prod['id'] ?>" class="btn-view">Ver Producto</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
</div>

<script>
    // Cambiar imagen principal
    function cambiarImagen(src, elemento) {
        const img = document.getElementById('imgPrincipal');
        img.src = src;
        
        document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
        elemento.classList.add('active');
    }
    
    // Cambiar cantidad
    function cambiarCantidad(delta) {
        const input = document.getElementById('cantidad');
        const max = parseInt(input.max);
        let valor = parseInt(input.value) + delta;
        
        if (valor < 1) valor = 1;
        if (valor > max) valor = max;
        
        input.value = valor;
    }
    
    // Actualizar texto de color seleccionado
    document.querySelectorAll('input[name="color"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('colorSeleccionado').textContent = this.dataset.nombre;
        });
    });
    
    // Actualizar texto de talla seleccionada
    document.querySelectorAll('input[name="talla"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('tallaSeleccionada').textContent = this.dataset.nombre;
        });
    });
    
    // Agregar al carrito
    document.getElementById('addToCartForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const errorMsg = document.getElementById('errorMessage');
        
        // Validar que se haya seleccionado color y talla si existen
        <?php if (!empty($colores)): ?>
        if (!formData.get('color')) {
            errorMsg.textContent = 'Por favor selecciona un color';
            errorMsg.style.display = 'block';
            setTimeout(() => errorMsg.style.display = 'none', 3000);
            return;
        }
        <?php endif; ?>
        
        <?php if (!empty($tallas)): ?>
        if (!formData.get('talla')) {
            errorMsg.textContent = 'Por favor selecciona una talla';
            errorMsg.style.display = 'block';
            setTimeout(() => errorMsg.style.display = 'none', 3000);
            return;
        }
        <?php endif; ?>
        
        // Enviar al servidor
        fetch('agregar_carrito.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar contador del carrito en el header
                const cartBadge = document.querySelector('.cart-badge');
                if (cartBadge) {
                    cartBadge.textContent = data.total_items;
                } else {
                    // Crear badge si no existe
                    const cartIcon = document.querySelector('.header-icon[href="carrito.php"]');
                    if (cartIcon) {
                        const badge = document.createElement('span');
                        badge.className = 'cart-badge';
                        badge.textContent = data.total_items;
                        cartIcon.appendChild(badge);
                    }
                }
                
                // Mostrar mensaje de éxito
                const btn = document.querySelector('.btn-add-cart');
                const originalText = btn.innerHTML;
                btn.innerHTML = '✓ Agregado al carrito';
                btn.style.background = '#4caf50';
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                }, 2000);
            } else {
                errorMsg.textContent = data.message || 'Error al agregar al carrito';
                errorMsg.style.display = 'block';
                setTimeout(() => errorMsg.style.display = 'none', 3000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorMsg.textContent = 'Error al procesar la solicitud';
            errorMsg.style.display = 'block';
            setTimeout(() => errorMsg.style.display = 'none', 3000);
        });
    });
</script>

<?php include 'includes/footer.php'; ?>