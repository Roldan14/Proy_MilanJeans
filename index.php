<?php
require_once 'includes/config.php';

$page_title = $config_sitio['nombre'] . ' - Los mejores jeans para tu estilo';
$page_description = 'Encuentra los mejores jeans y ropa casual. Calidad, comodidad y diseño en cada prenda.';

// Obtener banners activos
$banners = $pdo->query("
    SELECT * FROM banners 
    WHERE activo = 1 
    AND posicion = 'principal'
    AND (fecha_inicio IS NULL OR fecha_inicio <= CURDATE())
    AND (fecha_fin IS NULL OR fecha_fin >= CURDATE())
    ORDER BY orden ASC
")->fetchAll();

// Obtener productos destacados
$productos_destacados = $pdo->query("
    SELECT p.*, 
    (SELECT ruta_imagen FROM producto_imagenes WHERE producto_id = p.id AND es_principal = 1 LIMIT 1) as imagen
    FROM productos p
    WHERE p.activo = 1 AND p.destacado = 1
    ORDER BY p.fecha_creacion DESC
    LIMIT 8
")->fetchAll();

// Obtener productos nuevos
$productos_nuevos = $pdo->query("
    SELECT p.*, 
    (SELECT ruta_imagen FROM producto_imagenes WHERE producto_id = p.id AND es_principal = 1 LIMIT 1) as imagen
    FROM productos p
    WHERE p.activo = 1 AND p.es_nuevo = 1
    ORDER BY p.fecha_creacion DESC
    LIMIT 8
")->fetchAll();

// Obtener productos más vendidos
$mas_vendidos = $pdo->query("
    SELECT p.*, 
    (SELECT ruta_imagen FROM producto_imagenes WHERE producto_id = p.id AND es_principal = 1 LIMIT 1) as imagen
    FROM productos p
    WHERE p.activo = 1 AND p.es_mas_vendido = 1
    ORDER BY p.ventas DESC
    LIMIT 8
")->fetchAll();

// Obtener categorías destacadas
$categorias = $pdo->query("
    SELECT * FROM categorias 
    WHERE activo = 1 
    ORDER BY orden, nombre 
    LIMIT 6
")->fetchAll();

include 'includes/header.php';
?>

<style>
    /* Hero Slider */
    .hero-slider {
        position: relative;
        height: 600px;
        overflow: hidden;
        background: var(--secondary);
    }
    
    .slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 1s ease-in-out;
    }
    
    .slide.active {
        opacity: 1;
    }
    
    .slide-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .slide-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: var(--white);
        max-width: 800px;
        padding: 20px;
        animation: slideUp 0.8s ease-out;
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translate(-50%, -40%);
        }
        to {
            opacity: 1;
            transform: translate(-50%, -50%);
        }
    }
    
    .slide-content h1 {
        font-size: 64px;
        font-weight: 900;
        margin-bottom: 15px;
        text-shadow: 2px 2px 10px rgba(0,0,0,0.3);
        line-height: 1.1;
    }
    
    .slide-content h2 {
        font-size: 28px;
        font-weight: 400;
        margin-bottom: 20px;
        text-shadow: 1px 1px 5px rgba(0,0,0,0.3);
    }
    
    .slide-content p {
        font-size: 18px;
        margin-bottom: 30px;
        text-shadow: 1px 1px 5px rgba(0,0,0,0.3);
    }
    
    .btn-hero {
        display: inline-block;
        padding: 18px 40px;
        background: var(--primary);
        color: var(--white);
        text-decoration: none;
        border-radius: 50px;
        font-weight: 700;
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s;
        box-shadow: 0 10px 30px rgba(220, 20, 60, 0.3);
    }
    
    .btn-hero:hover {
        background: var(--primary-dark);
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(220, 20, 60, 0.4);
    }
    
    .slider-controls {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        z-index: 10;
    }
    
    .slider-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255,255,255,0.5);
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .slider-dot.active {
        background: var(--primary);
        width: 40px;
        border-radius: 6px;
    }
    
    .slider-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255,255,255,0.2);
        color: var(--white);
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s;
        backdrop-filter: blur(10px);
    }
    
    .slider-arrow:hover {
        background: var(--primary);
    }
    
    .slider-arrow.prev {
        left: 30px;
    }
    
    .slider-arrow.next {
        right: 30px;
    }
    
    /* Sections */
    .section {
        padding: 80px 0;
    }
    
    .section-light {
        background: var(--white);
    }
    
    .section-dark {
        background: var(--bg-light);
    }
    
    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .section-header {
        text-align: center;
        margin-bottom: 60px;
    }
    
    .section-header h2 {
        font-size: 42px;
        font-weight: 900;
        color: var(--text-dark);
        margin-bottom: 15px;
        position: relative;
        display: inline-block;
    }
    
    .section-header h2::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 4px;
        background: var(--primary);
        border-radius: 2px;
    }
    
    .section-header p {
        color: var(--text-gray);
        font-size: 16px;
        max-width: 600px;
        margin: 20px auto 0;
    }
    
    /* Categories Grid */
    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .category-card {
        position: relative;
        height: 250px;
        border-radius: 15px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .category-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.7));
        z-index: 1;
    }
    
    .category-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    
    .category-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    
    .category-card:hover .category-image {
        transform: scale(1.1);
    }
    
    .category-name {
        position: absolute;
        bottom: 20px;
        left: 20px;
        color: var(--white);
        font-size: 22px;
        font-weight: 700;
        z-index: 2;
    }
    
    /* Products Grid */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
    }
    
    .product-card {
        background: var(--white);
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s;
        position: relative;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .product-image-wrapper {
        position: relative;
        padding-top: 125%;
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
        transform: scale(1.1);
    }
    
    .product-badges {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 2;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .product-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge-nuevo {
        background: var(--primary);
        color: var(--white);
    }
    
    .badge-oferta {
        background: #FFD700;
        color: var(--secondary);
    }
    
    .product-info {
        padding: 20px;
    }
    
    .product-name {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-dark);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 30px;
    }
    
    .product-price {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .price-current {
        font-size: 24px;
        font-weight: 900;
        color: var(--primary);
    }
    
    .price-old {
        font-size: 16px;
        color: var(--text-light);
        text-decoration: line-through;
    }
    
    .btn-add-cart {
        width: 100%;
        padding: 12px;
        background: var(--secondary);
        color: var(--white);
        text-decoration: none;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-add-cart:hover {
        background: var(--primary);
        transform: translateY(-2px);
    }
    
    /* View More Button */
    .btn-view-more {
        display: inline-block;
        padding: 15px 40px;
        background: var(--white);
        color: var(--primary);
        text-decoration: none;
        border-radius: 50px;
        font-weight: 700;
        border: 2px solid var(--primary);
        transition: all 0.3s;
        margin-top: 40px;
    }
    
    .btn-view-more:hover {
        background: var(--primary);
        color: var(--white);
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(220, 20, 60, 0.3);
    }
    
    .text-center {
        text-align: center;
    }
    
    /* Responsive */
    @media (max-width: 968px) {
        .hero-slider {
            height: 400px;
        }
        
        .slide-content h1 {
            font-size: 36px;
        }
        
        .slide-content h2 {
            font-size: 20px;
        }
        
        .slide-content p {
            font-size: 14px;
        }
        
        .slider-arrow {
            width: 40px;
            height: 40px;
        }
        
        .slider-arrow.prev {
            left: 15px;
        }
        
        .slider-arrow.next {
            right: 15px;
        }
        
        .section {
            padding: 50px 0;
        }
        
        .section-header h2 {
            font-size: 32px;
        }
        
        .categories-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .products-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
    }
    
    @media (max-width: 640px) {
        .products-grid {
            grid-template-columns: 1fr;
        }
        
        .categories-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php if (!empty($banners)): ?>
<!-- Hero Slider -->
<section class="hero-slider">
    <?php foreach ($banners as $index => $banner): ?>
        <div class="slide <?= $index === 0 ? 'active' : '' ?>">
            <img src="<?= UPLOAD_URL . $banner['imagen'] ?>" alt="<?= htmlspecialchars($banner['titulo']) ?>" class="slide-image">
            <div class="slide-content">
                <h1><?= htmlspecialchars($banner['titulo']) ?></h1>
                <?php if ($banner['subtitulo']): ?>
                    <h2><?= htmlspecialchars($banner['subtitulo']) ?></h2>
                <?php endif; ?>
                <?php if ($banner['descripcion']): ?>
                    <p><?= htmlspecialchars($banner['descripcion']) ?></p>
                <?php endif; ?>
                <?php if ($banner['enlace'] && $banner['texto_boton']): ?>
                    <a href="<?= htmlspecialchars($banner['enlace']) ?>" class="btn-hero">
                        <?= htmlspecialchars($banner['texto_boton']) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if (count($banners) > 1): ?>
        <button class="slider-arrow prev" onclick="changeSlide(-1)">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </button>
        <button class="slider-arrow next" onclick="changeSlide(1)">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </button>
        
        <div class="slider-controls">
            <?php foreach ($banners as $index => $banner): ?>
                <span class="slider-dot <?= $index === 0 ? 'active' : '' ?>" onclick="goToSlide(<?= $index ?>)"></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<!-- Categorías -->
<?php if (!empty($categorias)): ?>
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <h2>Categorías</h2>
            <p>Encuentra exactamente lo que buscas</p>
        </div>
        
        <div class="categories-grid">
            <?php foreach ($categorias as $cat): ?>
                <a href="productos.php?categoria=<?= $cat['id'] ?>" class="category-card" style="text-decoration: none;">
                    <img src="imagenes/pants.png?= urlencode($cat['nombre']) ?>" 
                         alt="<?= htmlspecialchars($cat['nombre']) ?>" 
                         class="category-image">
                    <div class="category-name"><?= htmlspecialchars($cat['nombre']) ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Productos Destacados -->
<?php if (!empty($productos_destacados)): ?>
<section class="section section-dark">
    <div class="container">
        <div class="section-header">
            <h2>Destacados</h2>
            <p>Los productos que no te puedes perder</p>
        </div>
        
        <div class="products-grid">
            <?php foreach ($productos_destacados as $producto): ?>
                <div class="product-card">
                    <div class="product-image-wrapper">
                        <a href="producto.php?id=<?= $producto['id'] ?>">
                            <img src="<?= $producto['imagen'] ? UPLOAD_URL . $producto['imagen'] : 'https://via.placeholder.com/300x400?text=Sin+Imagen' ?>" 
                                 alt="<?= htmlspecialchars($producto['nombre']) ?>" 
                                 class="product-image">
                        </a>
                        <div class="product-badges">
                            <?php if ($producto['es_nuevo']): ?>
                                <span class="product-badge badge-nuevo">Nuevo</span>
                            <?php endif; ?>
                            <?php if ($producto['precio_oferta']): ?>
                                <span class="product-badge badge-oferta">Oferta</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="product-info">
                        <a href="producto.php?id=<?= $producto['id'] ?>" style="text-decoration: none;">
                            <h3 class="product-name"><?= htmlspecialchars($producto['nombre']) ?></h3>
                        </a>
                        <div class="product-price">
                            <span class="price-current"><?= formatPrice($producto['precio_oferta'] ?: $producto['precio']) ?></span>
                            <?php if ($producto['precio_oferta']): ?>
                                <span class="price-old"><?= formatPrice($producto['precio']) ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="producto.php?id=<?= $producto['id'] ?>" class="btn-add-cart">Ver Producto</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center">
            <a href="productos.php" class="btn-view-more">Ver Todos los Productos</a>
        </div>
    </div>
</section>
<?php endif; ?>


<script>
    // Slider functionality
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.slider-dot');
    
    function showSlide(n) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        currentSlide = (n + slides.length) % slides.length;
        
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }
    
    function changeSlide(direction) {
        showSlide(currentSlide + direction);
    }
    
    function goToSlide(n) {
        showSlide(n);
    }
    
    // Auto slide every 5 seconds
    <?php if (count($banners) > 1): ?>
    setInterval(() => {
        changeSlide(1);
    }, 5000);
    <?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>