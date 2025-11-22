<?php
// productos.php - Listado de productos
require_once 'config.php';
requireLogin();

// Eliminar producto
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    
    try {
        // Obtener imágenes para eliminarlas
        $stmt = $pdo->prepare("SELECT ruta_imagen FROM producto_imagenes WHERE producto_id = ?");
        $stmt->execute([$id]);
        $imagenes = $stmt->fetchAll();
        
        // Eliminar producto (las imágenes se eliminan por CASCADE)
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        
        // Eliminar archivos físicos
        foreach ($imagenes as $img) {
            deleteImage($img['ruta_imagen']);
        }
        
        $success = "Producto eliminado correctamente";
    } catch (PDOException $e) {
        $error = "Error al eliminar el producto: " . $e->getMessage();
    }
}

// Filtros y búsqueda
$search = $_GET['search'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$estado = $_GET['estado'] ?? '';

// Construir query
$sql = "SELECT p.*, c.nombre as categoria_nombre,
        (SELECT ruta_imagen FROM producto_imagenes WHERE producto_id = p.id AND es_principal = 1 LIMIT 1) as imagen_principal
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE 1=1";

$params = [];

if ($search) {
    $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoria) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $categoria;
}

if ($estado === 'activo') {
    $sql .= " AND p.activo = 1";
} elseif ($estado === 'inactivo') {
    $sql .= " AND p.activo = 0";
}

$sql .= " ORDER BY p.fecha_creacion DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

// Obtener categorías para el filtro
$categorias = $pdo->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Milan Jeans Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .toolbar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .toolbar-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .toolbar-filters {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 15px;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .search-box button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        select {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: var(--light);
        }
        
        .product-body {
            padding: 15px;
        }
        
        .product-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .product-price {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .product-stock {
            font-size: 13px;
            color: var(--gray);
        }
        
        .product-stock.low {
            color: var(--danger);
            font-weight: 600;
        }
        
        .product-badges {
            display: flex;
            gap: 5px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        
        .product-badge {
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-nuevo {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-vendido {
            background: #fff3e0;
            color: #e65100;
        }
        
        .badge-destacado {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .badge-inactivo {
            background: #ffebee;
            color: #c62828;
        }
        
        .product-actions {
            display: flex;
            gap: 8px;
        }
        
        .product-actions a,
        .product-actions button {
            flex: 1;
            padding: 8px;
            text-align: center;
            border-radius: 5px;
            font-size: 13px;
            text-decoration: none;
            border: none;
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
        
        .btn-edit:hover {
            background: #3b9aee;
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
        
        .empty-state h3 {
            font-size: 20px;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: var(--gray);
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .toolbar-filters {
                grid-template-columns: 1fr;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .productos-grid {
               grid-template-columns: 1fr;
               gap: 15px;
            }
    
            .product-card {
               max-width: 100%;
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
                <h1>Productos</h1>
                <p>Gestiona el catálogo de productos de tu tienda</p>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="toolbar">
                <div class="toolbar-top">
                    <h2 style="font-size: 18px; color: var(--dark);">
                        <?= count($productos) ?> productos encontrados
                    </h2>
                    <a href="producto_form.php" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 5px;">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Nuevo Producto
                    </a>
                </div>
                
                <form method="GET" class="toolbar-filters">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Buscar productos..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                        </button>
                    </div>
                    
                    <select name="categoria" onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoria == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="estado" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activos</option>
                        <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </form>
            </div>
            
            <?php if (empty($productos)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 7h-9M14 17H5M20 17h-3M10 7H5M7 7a2 2 0 100-4 2 2 0 000 4zM17 17a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                    <h3>No hay productos</h3>
                    <p>Comienza agregando tu primer producto al catálogo</p>
                    <a href="producto_form.php" class="btn btn-primary">Agregar Producto</a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($productos as $producto): ?>
                        <div class="product-card">
                            <img src="<?= $producto['imagen_principal'] ? UPLOAD_URL . $producto['imagen_principal'] : 'https://via.placeholder.com/300x250?text=Sin+Imagen' ?>" 
                                 alt="<?= htmlspecialchars($producto['nombre']) ?>" 
                                 class="product-image">
                            
                            <div class="product-body">
                                <div class="product-badges">
                                    <?php if ($producto['es_nuevo']): ?>
                                        <span class="product-badge badge-nuevo">Nuevo</span>
                                    <?php endif; ?>
                                    <?php if ($producto['es_mas_vendido']): ?>
                                        <span class="product-badge badge-vendido">Más Vendido</span>
                                    <?php endif; ?>
                                    <?php if ($producto['destacado']): ?>
                                        <span class="product-badge badge-destacado">Destacado</span>
                                    <?php endif; ?>
                                    <?php if (!$producto['activo']): ?>
                                        <span class="product-badge badge-inactivo">Inactivo</span>
                                    <?php endif; ?>
                                </div>
                                
                                <h3 class="product-title"><?= htmlspecialchars($producto['nombre']) ?></h3>
                                
                                <div class="product-meta">
                                    <span class="product-price"><?= formatPrice($producto['precio']) ?></span>
                                    <span class="product-stock <?= $producto['stock'] < 5 ? 'low' : '' ?>">
                                        Stock: <?= $producto['stock'] ?>
                                    </span>
                                </div>
                                
                                <?php if ($producto['categoria_nombre']): ?>
                                    <p style="font-size: 12px; color: var(--gray); margin-bottom: 10px;">
                                        <?= htmlspecialchars($producto['categoria_nombre']) ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="product-actions">
                                    <a href="producto_form.php?id=<?= $producto['id'] ?>" class="btn-edit">
                                        Editar
                                    </a>
                                    <button onclick="confirmarEliminar(<?= $producto['id'] ?>, '<?= addslashes($producto['nombre']) ?>')" class="btn-delete">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script>
        function confirmarEliminar(id, nombre) {
            if (confirm('¿Estás seguro de eliminar el producto "' + nombre + '"?\n\nEsta acción no se puede deshacer.')) {
                window.location.href = 'productos.php?eliminar=' + id;
            }
        }
    </script>
</body>
</html>