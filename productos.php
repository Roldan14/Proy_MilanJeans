<?php
require_once 'includes/config.php';

$page_title = 'Productos - ' . $config_sitio['nombre'];

// Parámetros de filtrado
$search = $_GET['search'] ?? '';
$categoria_id = $_GET['categoria'] ?? '';
$color_id = $_GET['color'] ?? '';
$talla_id = $_GET['talla'] ?? '';
$precio_min = $_GET['precio_min'] ?? '';
$precio_max = $_GET['precio_max'] ?? '';
$orden = $_GET['orden'] ?? 'reciente';

// Paginación
$productos_por_pagina = 12;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $productos_por_pagina;

// Construir query
$sql = "SELECT p.*, 
        (SELECT ruta_imagen FROM producto_imagenes WHERE producto_id = p.id AND es_principal = 1 LIMIT 1) as imagen,
        c.nombre as categoria_nombre
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE p.activo = 1";

$params = [];

// Aplicar filtros
if ($search) {
    $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoria_id) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $categoria_id;
}

if ($color_id) {
    $sql .= " AND EXISTS (SELECT 1 FROM producto_colores WHERE producto_id = p.id AND color_id = ?)";
    $params[] = $color_id;
}

if ($talla_id) {
    $sql .= " AND EXISTS (SELECT 1 FROM producto_tallas WHERE producto_id = p.id AND talla_id = ?)";
    $params[] = $talla_id;
}

if ($precio_min) {
    $sql .= " AND p.precio >= ?";
    $params[] = $precio_min;
}

if ($precio_max) {
    $sql .= " AND p.precio <= ?";
    $params[] = $precio_max;
}

// Contar total de productos (para paginación)
$count_sql = "SELECT COUNT(DISTINCT p.id) 
              FROM productos p
              LEFT JOIN categorias c ON p.categoria_id = c.id
              WHERE p.activo = 1";

$count_params = [];

// Aplicar los mismos filtros para el conteo
if ($search) {
    $count_sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
    $count_params[] = "%$search%";
    $count_params[] = "%$search%";
}

if ($categoria_id) {
    $count_sql .= " AND p.categoria_id = ?";
    $count_params[] = $categoria_id;
}

if ($color_id) {
    $count_sql .= " AND EXISTS (SELECT 1 FROM producto_colores WHERE producto_id = p.id AND color_id = ?)";
    $count_params[] = $color_id;
}

if ($talla_id) {
    $count_sql .= " AND EXISTS (SELECT 1 FROM producto_tallas WHERE producto_id = p.id AND talla_id = ?)";
    $count_params[] = $talla_id;
}

if ($precio_min) {
    $count_sql .= " AND p.precio >= ?";
    $count_params[] = $precio_min;
}

if ($precio_max) {
    $count_sql .= " AND p.precio <= ?";
    $count_params[] = $precio_max;
}

$stmt = $pdo->prepare($count_sql);
$stmt->execute($count_params);
$total_productos = $stmt->fetchColumn();
$total_paginas = ceil($total_productos / $productos_por_pagina);

// Ordenamiento
switch ($orden) {
    case 'precio_asc':
        $sql .= " ORDER BY p.precio ASC";
        break;
    case 'precio_desc':
        $sql .= " ORDER BY p.precio DESC";
        break;
    case 'nombre':
        $sql .= " ORDER BY p.nombre ASC";
        break;
    case 'mas_vendido':
        $sql .= " ORDER BY p.ventas DESC";
        break;
    default:
        $sql .= " ORDER BY p.fecha_creacion DESC";
}

$sql .= " LIMIT ? OFFSET ?";
$params[] = $productos_por_pagina;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

// Obtener datos para los filtros
$categorias = $pdo->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY orden, nombre")->fetchAll();
$colores = $pdo->query("SELECT * FROM colores WHERE activo = 1 ORDER BY nombre")->fetchAll();
$tallas = $pdo->query("SELECT * FROM tallas WHERE activo = 1 ORDER BY orden")->fetchAll();

// Obtener rango de precios
$precios = $pdo->query("SELECT MIN(precio) as min, MAX(precio) as max FROM productos WHERE activo = 1")->fetch();

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
    
    .breadcrumb-current {
        color: var(--primary);
        font-weight: 600;
    }
    
    .productos-page {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    
    .page-header-productos {
        margin-bottom: 30px;
    }
    
    .page-header-productos h1 {
        font-size: 36px;
        font-weight: 900;
        color: var(--text-dark);
        margin-bottom: 10px;
    }
    
    .productos-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 30px;
    }
    
    /* Sidebar Filters */
    .filters-sidebar {
        position: sticky;
        top: 100px;
        height: fit-content;
    }
    
    .filter-section {
        background: var(--white);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .filter-section h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--bg-light);
    }
    
    .filter-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .filter-option:hover {
        padding-left: 5px;
        color: var(--primary);
    }
    
    .filter-option input[type="checkbox"],
    .filter-option input[type="radio"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary);
    }
    
    .filter-option label {
        cursor: pointer;
        flex: 1;
        font-size: 14px;
    }
    
    .color-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 4px 0;
        cursor: pointer;
        font-size: 13px;
    }
    
    .color-swatch {
        width: 30px;
        height: 30px;
        border: 1px solid ;
        transition: all 0.3s;
    }
    
    .color-option:hover .color-swatch {
        border-color: var(--primary);
        transform: scale(1.1);
    }
    
    .price-inputs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 10px;
    }
    
    .price-inputs input {
        padding: 8px 12px;
        border: 2px solid var(--border);
        border-radius: 3px;
        font-size: 13px;
        width: 7rem;
    }
    
    .btn-filter {
        width: 100%;
        padding: 12px;
        background: var(--primary);
        color: var(--white);
        border: none;
        border-radius: 3px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 15px;
        transition: all 0.3s;
    }
    
    .btn-filter:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }
    
    .btn-clear {
        width: 100%;
        padding: 10px;
        background: var(--bg-light);
        color: var(--text-dark);
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
        transition: all 0.3s;
    }
    
    .btn-clear:hover {
        background: #e0e0e0;
    }
    
    /* Products Area */
    .productos-area {
        min-height: 400px;
    }
    
    .productos-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--white);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .results-info {
        font-size: 14px;
        color: var(--text-gray);
    }
    
    .results-info strong {
        color: var(--primary);
        font-size: 18px;
    }
    
    .sort-select {
        padding: 10px 15px;
        border: 2px solid var(--border);
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        background: var(--white);
    }
    
    .productos-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 40px;
    }
    
    .product-card {
        background: var(--white);
        border-radius: 2px;
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
    
/* Badges minimalistas */
.product-badges {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 2;
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-width: calc(100% - 24px);
}

.badge {
    padding: 4px 10px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: var(--white);
    color: var(--text-dark);
    border-radius: 2px;
    width: fit-content;
}

.badge.nuevo {
    background: var(--primary);
    color: var(--white);
}

.badge.oferta {
    background: #000;
    color: var(--white);
}
    
    .product-info {
        padding: 15px;
    }
    
    .product-category {
        font-size: 10px;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    
    .product-name {
        font-size: 17px;
        font-weight: 450;
        color: var(--text-dark);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 30px;
        line-height: 1.4;
    }
    
    .product-price {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .price-current {
        font-size: 20px;
        font-weight: 700;
        color: var(--primary);
    }
    
    .price-old {
        font-size: 13px;
        color: var(--text-light);
        text-decoration: line-through;
    }
    
    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 40px;
    }
    
    .pagination a,
    .pagination span {
        padding: 10px 16px;
        background: var(--white);
        color: var(--text-dark);
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .pagination a:hover {
        background: var(--primary);
        color: var(--white);
        transform: translateY(-2px);
    }
    
    .pagination .active {
        background: var(--primary);
        color: var(--white);
    }
    
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: var(--white);
        border-radius: 10px;
    }
    
    .empty-state svg {
        width: 80px;
        height: 80px;
        color: var(--text-light);
        margin-bottom: 20px;
    }
    
    .empty-state h3 {
        font-size: 24px;
        color: var(--text-dark);
        margin-bottom: 10px;
    }
    
    .empty-state p {
        color: var(--text-gray);
        margin-bottom: 20px;
    }
    
    .active-filters {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    
    .filter-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        background: var(--primary);
        color: var(--white);
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }
    
    .filter-tag a {
        color: var(--white);
        text-decoration: none;
        display: flex;
        align-items: center;
    }
    
    /* Responsive */
    @media (max-width: 968px) {
        .productos-layout {
            grid-template-columns: 1fr;
        }
        
        .filters-sidebar {
            position: static;
        }
        
        .productos-toolbar {
            flex-direction: column;
            gap: 15px;
        }
        
        .productos-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
    }
    
    @media (max-width: 640px) {
        .productos-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Breadcrumb -->


<div class="productos-page">
    <div class="page-header-productos">
        <?php if ($search): ?>
            <p style="color: var(--text-gray);">Resultados para: <strong>"<?= htmlspecialchars($search) ?>"</strong></p>
        <?php endif; ?>
    </div>
    
    <div class="productos-layout">
        <!-- Sidebar Filtros -->
        <aside class="filters-sidebar">
            <form method="GET" id="filterForm">
                <!-- Categorías -->
                <?php if (!empty($categorias)): ?>
                    <div class="filter-section">
                        <h3>Categorías</h3>
                        <?php foreach ($categorias as $cat): ?>
                            <div class="filter-option">
                                <input type="radio" 
                                       name="categoria" 
                                       value="<?= $cat['id'] ?>" 
                                       id="cat_<?= $cat['id'] ?>"
                                       <?= $categoria_id == $cat['id'] ? 'checked' : '' ?>
                                       onchange="this.form.submit()">
                                <label for="cat_<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Colores -->
                <?php if (!empty($colores)): ?>
                    <div class="filter-section">
                        <h3>Colores</h3>
                        <?php foreach ($colores as $color): ?>
                            <div class="color-option">
                                <input type="radio" 
                                       name="color" 
                                       value="<?= $color['id'] ?>" 
                                       id="color_<?= $color['id'] ?>"
                                       <?= $color_id == $color['id'] ? 'checked' : '' ?>
                                       onchange="this.form.submit()"
                                       style="display: none;">
                                <label for="color_<?= $color['id'] ?>" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                    <div class="color-swatch" style="background-color: <?= $color['codigo_hex'] ?>"></div>
                                    <span><?= htmlspecialchars($color['nombre']) ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Tallas -->
                <?php if (!empty($tallas)): ?>
                    <div class="filter-section">
                        <h3>Tallas</h3>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                            <?php foreach ($tallas as $talla): ?>
                                <div>
                                    <input type="radio" 
                                           name="talla" 
                                           value="<?= $talla['id'] ?>" 
                                           id="talla_<?= $talla['id'] ?>"
                                           <?= $talla_id == $talla['id'] ? 'checked' : '' ?>
                                           onchange="this.form.submit()"
                                           style="display: none;">
                                    <label for="talla_<?= $talla['id'] ?>" 
                                           style="display: block; padding: 8px; text-align: center; border: 2px solid var(--border); border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; <?= $talla_id == $talla['id'] ? 'background: var(--primary); color: var(--white); border-color: var(--primary);' : '' ?>">
                                        <?= htmlspecialchars($talla['nombre']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Rango de Precio -->
                <div class="filter-section">
                    <h3>Precio</h3>
                    <div class="price-inputs">
                        <input type="number" name="precio_min" placeholder="Mín" value="<?= $precio_min ?>" step="0.01">
                        <input type="number" name="precio_max" placeholder="Máx" value="<?= $precio_max ?>" step="0.01">
                    </div>
                    <button type="submit" class="btn-filter">Aplicar Filtros</button>
                </div>
                
                <!-- Limpiar Filtros -->
                <?php if ($categoria_id || $color_id || $talla_id || $precio_min || $precio_max): ?>
                    <a href="productos.php" class="btn-clear" style="text-decoration: none; display: block; text-align: center;">
                        Limpiar Filtros
                    </a>
                <?php endif; ?>
            </form>
        </aside>
        
        <!-- Área de Productos -->
        <div class="productos-area">
            <!-- Toolbar -->
            <div class="productos-toolbar">
                <div class="results-info">
                    <strong><?= $total_productos ?></strong> producto(s) encontrado(s)
                </div>
                <select class="sort-select" onchange="window.location.href='?<?= http_build_query(array_merge($_GET, ['orden' => ''])) ?>' + this.value">
                    <option value="reciente" <?= $orden == 'reciente' ? 'selected' : '' ?>>Más Recientes</option>
                    <option value="precio_asc" <?= $orden == 'precio_asc' ? 'selected' : '' ?>>Precio: Menor a Mayor</option>
                    <option value="precio_desc" <?= $orden == 'precio_desc' ? 'selected' : '' ?>>Precio: Mayor a Menor</option>
                    <option value="nombre" <?= $orden == 'nombre' ? 'selected' : '' ?>>Nombre A-Z</option>
                    <option value="mas_vendido" <?= $orden == 'mas_vendido' ? 'selected' : '' ?>>Más Vendidos</option>
                </select>
            </div>
            
            <?php if (empty($productos)): ?>
                <!-- Estado Vacío -->
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <h3>No se encontraron productos</h3>
                    <p>Intenta ajustar los filtros o buscar algo diferente</p>
                    <a href="productos.php" class="btn-filter" style="display: inline-block; width: auto; padding: 12px 30px;">
                        Ver Todos los Productos
                    </a>
                </div>
            <?php else: ?>
                <!-- Grid de Productos -->
                <div class="productos-grid">
                    <?php foreach ($productos as $producto): ?>
                        <div class="product-card">
                            <div class="product-image-wrapper">
                                <a href="producto.php?id=<?= $producto['id'] ?>">
                                    <img src="<?= $producto['imagen'] ? UPLOAD_URL . $producto['imagen'] : 'https://via.placeholder.com/300x400?text=Sin+Imagen' ?>" 
                                         alt="<?= htmlspecialchars($producto['nombre']) ?>" 
                                         class="product-image">
                                </a>
                                <div class="product-badges">
                                    <?php if ($producto['es_nuevo']): ?>
                                      <span class="badge nuevo">Nuevo</span>
                                    <?php endif; ?>
                                    <?php if ($producto['precio_oferta']): ?>
                                    <?php 
                                       $descuento = round((($producto['precio'] - $producto['precio_oferta']) / $producto['precio']) * 100);
                                    ?>
                                    <span class="badge oferta">-<?= $descuento ?>%</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="product-info">
                                <?php if ($producto['categoria_nombre']): ?>
                                    <div class="product-category"><?= htmlspecialchars($producto['categoria_nombre']) ?></div>
                                <?php endif; ?>
                                <a href="producto.php?id=<?= $producto['id'] ?>" style="text-decoration: none;">
                                    <h3 class="product-name"><?= htmlspecialchars($producto['nombre']) ?></h3>
                                </a>
                                <div class="product-price">
                                    <span class="price-current"><?= formatPrice($producto['precio_oferta'] ?: $producto['precio']) ?></span>
                                    <?php if ($producto['precio_oferta']): ?>
                                        <span class="price-old"><?= formatPrice($producto['precio']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                    <div class="pagination">
                        <?php if ($pagina_actual > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual - 1])) ?>">« Anterior</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <?php if ($i == $pagina_actual): ?>
                                <span class="active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($pagina_actual < $total_paginas): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual + 1])) ?>">Siguiente »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>