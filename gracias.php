<?php
require_once 'includes/config.php';

$codigo_pedido = $_GET['pedido'] ?? $_SESSION['ultimo_pedido'] ?? null;

if (!$codigo_pedido) {
    header('Location: index.php');
    exit;
}

// Obtener datos del pedido
$stmt = $pdo->prepare("
    SELECT p.*, 
    (SELECT COUNT(*) FROM pedido_detalles WHERE pedido_id = p.id) as total_items
    FROM pedidos p
    WHERE p.codigo_pedido = ?
");
$stmt->execute([$codigo_pedido]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header('Location: index.php');
    exit;
}

// Obtener detalles del pedido
$stmt = $pdo->prepare("
    SELECT pd.*, 
    (SELECT ruta_imagen FROM producto_imagenes WHERE producto_id = pd.producto_id AND es_principal = 1 LIMIT 1) as imagen
    FROM pedido_detalles pd
    WHERE pd.pedido_id = ?
");
$stmt->execute([$pedido['id']]);
$detalles = $stmt->fetchAll();

$page_title = '¡Pedido Confirmado! - ' . $config_sitio['nombre'];

include 'includes/header.php';
?>

<style>
    .gracias-page {
        max-width: 900px;
        margin: 0 auto;
        padding: 60px 20px;
        text-align: center;
    }
    
    .success-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        animation: scaleIn 0.5s ease-out;
    }
    
    @keyframes scaleIn {
        from {
            transform: scale(0);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    .success-icon svg {
        width: 60px;
        height: 60px;
        color: white;
        stroke-width: 3;
    }
    
    .gracias-titulo {
        font-size: 42px;
        font-weight: 900;
        color: var(--text-dark);
        margin-bottom: 15px;
        animation: fadeInUp 0.6s ease-out 0.2s both;
    }
    
    .gracias-subtitulo {
        font-size: 18px;
        color: var(--text-gray);
        margin-bottom: 10px;
        animation: fadeInUp 0.6s ease-out 0.3s both;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .codigo-pedido {
        display: inline-block;
        padding: 15px 30px;
        background: var(--bg-light);
        border-radius: 50px;
        font-size: 20px;
        font-weight: 700;
        color: var(--primary);
        margin: 30px 0;
        animation: fadeInUp 0.6s ease-out 0.4s both;
    }
    
    .info-boxes {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin: 50px 0;
        animation: fadeInUp 0.6s ease-out 0.5s both;
    }
    
    .info-box {
        background: var(--white);
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .info-box svg {
        width: 40px;
        height: 40px;
        color: var(--primary);
        margin-bottom: 15px;
    }
    
    .info-box h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 8px;
    }
    
    .info-box p {
        font-size: 14px;
        color: var(--text-gray);
        line-height: 1.6;
    }
    
    .pedido-detalles {
        background: var(--white);
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        text-align: left;
        margin: 40px 0;
        animation: fadeInUp 0.6s ease-out 0.6s both;
    }
    
    .pedido-detalles h2 {
        font-size: 22px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 25px;
        text-align: center;
    }
    
    .detalle-section {
        margin-bottom: 25px;
        padding-bottom: 25px;
        border-bottom: 1px solid var(--bg-light);
    }
    
    .detalle-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .detalle-section h3 {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-gray);
        text-transform: uppercase;
        margin-bottom: 12px;
    }
    
    .detalle-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 15px;
    }
    
    .detalle-row strong {
        color: var(--text-dark);
    }
    
    .productos-lista {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .producto-item {
        display: flex;
        gap: 15px;
        padding: 15px;
        background: var(--bg-light);
        border-radius: 8px;
    }
    
    .producto-img {
        width: 70px;
        height: 90px;
        border-radius: 6px;
        overflow: hidden;
        background: white;
        flex-shrink: 0;
    }
    
    .producto-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .producto-info {
        flex: 1;
    }
    
    .producto-info h4 {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 6px;
    }
    
    .producto-info p {
        font-size: 13px;
        color: var(--text-gray);
        margin-bottom: 3px;
    }
    
    .producto-precio {
        font-size: 16px;
        font-weight: 700;
        color: var(--primary);
        text-align: right;
    }
    
    .total-box {
        background: var(--primary);
        color: var(--white);
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }
    
    .total-box .total-row {
        display: flex;
        justify-content: space-between;
        font-size: 24px;
        font-weight: 900;
    }
    
    .acciones {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 40px;
        animation: fadeInUp 0.6s ease-out 0.7s both;
    }
    
    .btn-primary-custom {
        padding: 15px 35px;
        background: var(--primary);
        color: var(--white);
        text-decoration: none;
        border-radius: 50px;
        font-weight: 700;
        font-size: 16px;
        transition: all 0.3s;
        display: inline-block;
    }
    
    .btn-primary-custom:hover {
        background: var(--primary-dark);
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(220, 20, 60, 0.3);
    }
    
    .btn-secondary-custom {
        padding: 15px 35px;
        background: var(--white);
        color: var(--text-dark);
        text-decoration: none;
        border-radius: 50px;
        font-weight: 700;
        font-size: 16px;
        border: 2px solid var(--border);
        transition: all 0.3s;
        display: inline-block;
    }
    
    .btn-secondary-custom:hover {
        border-color: var(--primary);
        color: var(--primary);
    }
    
    .whatsapp-contact {
        background: #25D366;
        color: white;
        padding: 20px;
        border-radius: 12px;
        margin: 30px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        text-decoration: none;
        transition: all 0.3s;
        animation: fadeInUp 0.6s ease-out 0.8s both;
    }
    
    .whatsapp-contact:hover {
        background: #20BA5A;
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(37, 211, 102, 0.3);
    }
    
    .whatsapp-contact svg {
        width: 35px;
        height: 35px;
    }
    
    .whatsapp-contact div {
        text-align: left;
    }
    
    .whatsapp-contact h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 3px;
    }
    
    .whatsapp-contact p {
        font-size: 14px;
        opacity: 0.9;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .gracias-titulo {
            font-size: 32px;
        }
        
        .info-boxes {
            grid-template-columns: 1fr;
        }
        
        .acciones {
            flex-direction: column;
        }
        
        .btn-primary-custom,
        .btn-secondary-custom {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="gracias-page">
    <!-- Ícono de éxito -->
    <div class="success-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
    </div>
    
    <!-- Título -->
    <h1 class="gracias-titulo">¡Pedido Confirmado!</h1>
    <p class="gracias-subtitulo">Gracias por tu compra, <?= htmlspecialchars($pedido['nombre_cliente']) ?></p>
    <p class="gracias-subtitulo">Hemos recibido tu pedido correctamente</p>
    
    <!-- Código de pedido -->
    <div class="codigo-pedido">
        Pedido: <?= $pedido['codigo_pedido'] ?>
    </div>
    
    <!-- Info boxes -->
    <div class="info-boxes">
        <div class="info-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
            <h3>Confirmación enviada</h3>
            <p>Te enviamos un email a<br><strong><?= htmlspecialchars($pedido['email_cliente']) ?></strong></p>
        </div>
        
        <div class="info-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="3" width="15" height="13"/>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                <circle cx="5.5" cy="18.5" r="2.5"/>
                <circle cx="18.5" cy="18.5" r="2.5"/>
            </svg>
            <h3>En proceso</h3>
            <p>Estamos preparando tu pedido para el envío</p>
        </div>
        
        <div class="info-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            <h3>Entrega estimada</h3>
            <p>2-5 días hábiles en Lima<br>5-7 días en provincia</p>
        </div>
    </div>
    
    <!-- Detalles del pedido -->
    <div class="pedido-detalles">
        <h2>Detalles de tu Pedido</h2>
        
        <!-- Productos -->
        <div class="detalle-section">
            <h3>Productos (<?= $pedido['total_items'] ?>)</h3>
            <div class="productos-lista">
                <?php foreach ($detalles as $detalle): ?>
                    <div class="producto-item">
                        <div class="producto-img">
                            <img src="<?= $detalle['imagen'] ? UPLOAD_URL . $detalle['imagen'] : 'https://via.placeholder.com/70x90?text=Sin+Imagen' ?>" 
                                 alt="<?= htmlspecialchars($detalle['nombre_producto']) ?>">
                        </div>
                        <div class="producto-info">
                            <h4><?= htmlspecialchars($detalle['nombre_producto']) ?></h4>
                            <?php if ($detalle['color']): ?>
                                <p>Color: <?= htmlspecialchars($detalle['color']) ?></p>
                            <?php endif; ?>
                            <?php if ($detalle['talla']): ?>
                                <p>Talla: <?= htmlspecialchars($detalle['talla']) ?></p>
                            <?php endif; ?>
                            <p>Cantidad: <?= $detalle['cantidad'] ?> x <?= formatPrice($detalle['precio_unitario']) ?></p>
                        </div>
                        <div class="producto-precio">
                            <?= formatPrice($detalle['subtotal']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Información de entrega -->
        <div class="detalle-section">
            <h3>Información de Entrega</h3>
            <div class="detalle-row">
                <span>Dirección:</span>
                <strong><?= htmlspecialchars($pedido['direccion_entrega']) ?></strong>
            </div>
            <div class="detalle-row">
                <span>Distrito:</span>
                <strong><?= htmlspecialchars($pedido['distrito']) ?></strong>
            </div>
            <div class="detalle-row">
                <span>Ciudad:</span>
                <strong><?= htmlspecialchars($pedido['ciudad']) ?></strong>
            </div>
            <?php if ($pedido['referencia']): ?>
                <div class="detalle-row">
                    <span>Referencia:</span>
                    <strong><?= htmlspecialchars($pedido['referencia']) ?></strong>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Método de pago -->
        <div class="detalle-section">
            <h3>Método de Pago</h3>
            <div class="detalle-row">
                <span>Método:</span>
                <strong><?= htmlspecialchars($pedido['metodo_pago']) ?></strong>
            </div>
            <div class="detalle-row">
                <span>Estado:</span>
                <strong style="color: var(--warning);">Pendiente de confirmación</strong>
            </div>
        </div>
        
        <!-- Total -->
        <div class="total-box">
            <div class="detalle-row" style="color: white; opacity: 0.9; font-size: 15px; margin-bottom: 5px;">
                <span>Subtotal:</span>
                <span><?= formatPrice($pedido['subtotal']) ?></span>
            </div>
            <div class="detalle-row" style="color: white; opacity: 0.9; font-size: 15px; margin-bottom: 10px;">
                <span>Envío:</span>
                <span><?= formatPrice($pedido['costo_envio']) ?></span>
            </div>
            <div class="total-row">
                <span>Total Pagado:</span>
                <span><?= formatPrice($pedido['total']) ?></span>
            </div>
        </div>
    </div>
    
    <!-- Contacto WhatsApp -->
    <?php if ($config_sitio['whatsapp']): ?>
        <a href="https://wa.me/<?= $config_sitio['whatsapp'] ?>?text=Hola, tengo una consulta sobre mi pedido <?= $pedido['codigo_pedido'] ?>" 
           target="_blank" 
           class="whatsapp-contact">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            <div>
                <h3>¿Tienes alguna duda?</h3>
                <p>Contáctanos por WhatsApp</p>
            </div>
        </a>
    <?php endif; ?>
    
    <!-- Botones de acción -->
    <div class="acciones">
        <a href="index.php" class="btn-primary-custom">Volver al Inicio</a>
        <a href="productos.php" class="btn-secondary-custom">Seguir Comprando</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>