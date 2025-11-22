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
    height: 750px;
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

.slide-link {
    display: block;
    width: 100%;
    height: 100%;
    position: relative;
    text-decoration: none;
}

.slide-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.slide-content {
    position: absolute;
    color: var(--white);
    max-width: 600px;
    padding: 40px;
    animation: slideUp 0.8s ease-out;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    z-index: 1;
}

/* Posiciones Horizontales */
.slide-content.position-izquierda-arriba,
.slide-content.position-izquierda-centro,
.slide-content.position-izquierda-abajo {
    left: 5%;
    text-align: left;
}

.slide-content.position-centro-arriba,
.slide-content.position-centro-centro,
.slide-content.position-centro-abajo {
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
}

.slide-content.position-derecha-arriba,
.slide-content.position-derecha-centro,
.slide-content.position-derecha-abajo {
    right: 5%;
    text-align: right;
}

/* Posiciones Verticales */
.slide-content.position-izquierda-arriba,
.slide-content.position-centro-arriba,
.slide-content.position-derecha-arriba {
    top: 10%;
}

.slide-content.position-izquierda-centro,
.slide-content.position-centro-centro,
.slide-content.position-derecha-centro {
    top: 50%;
    transform: translateY(-50%);
}

.slide-content.position-centro-centro {
    transform: translate(-50%, -50%);
}

.slide-content.position-izquierda-abajo,
.slide-content.position-centro-abajo,
.slide-content.position-derecha-abajo {
    bottom: 10%;
    top: auto;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.slide-content h1 {
    font-size: 40px;
    font-weight: 900;
    margin-bottom: 5px;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
    line-height: 1.1;
}

.slide-content h2 {
    font-size: 18px;
    font-weight: 400;
    margin-bottom: 7px;
    text-shadow: 1px 1px 4px rgba(0,0,0,0.5);
}

.slide-content p {
    font-size: 10px;
    margin-bottom: 25px;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
    line-height: 1.6;
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
    z-index: 10;
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

/* Responsive Slider */
@media (max-width: 968px) {
    .hero-slider {
        height: 400px;
    }
    
    .slide-content {
        max-width: 90%;
        padding: 20px;
    }
    
    .slide-content h1 {
        font-size: 32px;
    }
    
    .slide-content h2 {
        font-size: 18px;
    }
    
    .slide-content p {
        font-size: 14px;
    }
    
    .slide-content.position-centro-arriba,
    .slide-content.position-centro-centro,
    .slide-content.position-centro-abajo {
        left: 50%;
        right: auto;
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
}
    
/* Sections */
.section {
    padding: 80px 0;
    position: relative;
    z-index: 1;
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
    margin-bottom: 40px;
}

.section-header h2 {
    font-size: 35px;
    font-weight: 400;
    color: var(--text-dark);
    margin-bottom: 5px;
    position: relative;
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 2px;
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

/* Products Grid - Grid Asimétrico */
.products-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    grid-auto-rows: 200px;
    gap: 15px;
    max-width: 1400px;
    margin: 0 auto;
}

/* Posicionamiento asimétrico de cada producto */
.product-card:nth-child(1) {
    grid-column: 1 / 7;
    grid-row: 1 / 5;
}

.product-card:nth-child(2) {
    grid-column: 7 / 13;
    grid-row: 1 / 4;
}

.product-card:nth-child(3) {
    grid-column: 7 / 13;
    grid-row: 4 / 7;
}

.product-card:nth-child(4) {
    grid-column: 1 / 4;
    grid-row: 5 / 7;
}

.product-card:nth-child(5) {
    grid-column: 4 / 7;
    grid-row: 5 / 7;
}

/* Ocultar productos adicionales */
.product-card:nth-child(n+6) {
    display: none;
}

/* Card minimalista */
.product-card {
    background: transparent;
    cursor: pointer;
    position: relative;
    overflow: hidden; 
    display: block;
}

.product-image-container {
    position: relative;
    width: 100%;
    padding-top: 140%;
    overflow: hidden;
    background: var(--bg-light);
    margin-bottom: 12px;
}

.product-image-container img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.product-card:hover .product-image-container img {
    transform: scale(1.05);
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


/* Hover overlay sutil */
.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0);
    transition: background 0.3s ease;
    pointer-events: none;
}

.product-card:hover .product-overlay {
    background: rgba(0, 0, 0, 0.03);
}


/* Botón Ver Más */
.view-more-container {
    text-align: center;
    margin-top: 50px;
}

.btn-view-more {
    display: inline-block;
    padding: 14px 40px;
    background: transparent;
    color: var(--text-dark);
    border: 1px solid var(--text-dark);
    border-radius: 3px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.btn-view-more:hover {
    background: var(--primary);
    color: var(--white);
    border-color: var(--primary);
}

/* Responsive */
@media (max-width: 968px) {
    .section {
        padding: 50px 0;
    }
    
    .section-header h2 {
        font-size: 26px;
    }
    
    .categories-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .products-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }
}

@media (max-width: 640px) {
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .product-name {
        font-size: 13px;
        min-height: 34px;
    }
    
    .price-current {
        font-size: 14px;
    }
    
    .quick-view {
        display: none;
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
            <?php if ($banner['enlace']): ?>
                <a href="<?= htmlspecialchars($banner['enlace']) ?>" class="slide-link">
            <?php endif; ?>
            
            <img src="<?= UPLOAD_URL . $banner['imagen'] ?>" alt="<?= htmlspecialchars($banner['titulo']) ?>" class="slide-image">
            
            <?php if ($banner['mostrar_contenido']): ?>
                <div class="slide-content position-<?= $banner['posicion_texto'] ?>">
                    <?php if ($banner['titulo']): ?>
                        <h1><?= htmlspecialchars($banner['titulo']) ?></h1>
                    <?php endif; ?>
                    
                    <?php if ($banner['subtitulo']): ?>
                        <h2><?= htmlspecialchars($banner['subtitulo']) ?></h2>
                    <?php endif; ?>
                    
                    <?php if ($banner['descripcion']): ?>
                        <p><?= htmlspecialchars($banner['descripcion']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($banner['enlace']): ?>
                </a>
            <?php endif; ?>
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
                    <img src="imagenes/pants.png" 
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
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <h2>Destacados</h2>
        </div>
        
        <div class="products-grid">
            <?php 
            // Mostrar hasta 8 productos
            $productos_grid = array_slice($productos_destacados, 0, 8);
            foreach ($productos_grid as $producto): 
            ?>
                <a href="producto.php?id=<?= $producto['id'] ?>" class="product-card">
                    <div class="product-image-container">
                        <img src="<?= $producto['imagen'] ? UPLOAD_URL . $producto['imagen'] : 'https://via.placeholder.com/800x1000?text=Sin+Imagen' ?>" 
                             alt="<?= htmlspecialchars($producto['nombre']) ?>">
                        <div class="product-overlay"></div>
                        
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
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="view-more-container">
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