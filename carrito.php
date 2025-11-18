<?php
require_once 'includes/config.php';

$page_title = 'Carrito de Compras - ' . $config_sitio['nombre'];

// Procesar acciones del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Actualizar cantidad
    if (isset($_POST['actualizar_cantidad'])) {
        $item_key = $_POST['item_key'];
        $nueva_cantidad = (int)$_POST['cantidad'];
        
        if (isset($_SESSION['carrito'][$item_key]) && $nueva_cantidad > 0) {
            $_SESSION['carrito'][$item_key]['cantidad'] = $nueva_cantidad;
            $success = 'Cantidad actualizada';
        }
    }
    
    // Eliminar producto
    if (isset($_POST['eliminar_item'])) {
        $item_key = $_POST['item_key'];
        if (isset($_SESSION['carrito'][$item_key])) {
            unset($_SESSION['carrito'][$item_key]);
            $success = 'Producto eliminado del carrito';
        }
    }
    
    // Vaciar carrito
    if (isset($_POST['vaciar_carrito'])) {
        $_SESSION['carrito'] = [];
        $success = 'Carrito vaciado';
    }
}

// Calcular totales
$subtotal = 0;
$total_items = 0;

foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
    $total_items += $item['cantidad'];
}

// Calcular costo de envío (puedes personalizarlo)
$costo_envio = $subtotal > 0 ? 10.00 : 0;
$total = $subtotal + $costo_envio;

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
    
    .carrito-page {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 20px;
        min-height: 60vh;
    }
    
    .page-title {
        font-size: 36px;
        font-weight: 900;
        color: var(--text-dark);
        margin-bottom: 10px;
    }
    
    .page-subtitle {
        color: var(--text-gray);
        font-size: 16px;
        margin-bottom: 40px;
    }
    
    .carrito-layout {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 30px;
    }
    
    /* Tabla de productos */
    .carrito-items {
        background: var(--white);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .carrito-item {
        display: grid;
        grid-template-columns: 100px 1fr 150px 120px 80px;
        gap: 20px;
        align-items: center;
        padding: 20px 0;
        border-bottom: 1px solid var(--bg-light);
    }
    
    .carrito-item:last-child {
        border-bottom: none;
    }
    
    .item-imagen {
        width: 100px;
        height: 130px;
        border-radius: 8px;
        overflow: hidden;
        background: var(--bg-light);
    }
    
    .item-imagen img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .item-info h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
        line-height: 1.3;
    }
    
    .item-info p {
        font-size: 13px;
        color: var(--text-gray);
        margin-bottom: 4px;
    }
    
    .item-precio {
        font-size: 20px;
        font-weight: 700;
        color: var(--primary);
    }
    
    .item-cantidad {
        display: flex;
        align-items: center;
        border: 2px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
        width: fit-content;
    }
    
    .item-cantidad button {
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
    
    .item-cantidad button:hover {
        background: var(--primary);
        color: var(--white);
    }
    
    .item-cantidad input {
        width: 50px;
        text-align: center;
        border: none;
        font-size: 14px;
        font-weight: 600;
    }
    
    .item-acciones {
        text-align: right;
    }
    
    .btn-eliminar {
        background: none;
        border: none;
        color: var(--danger);
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-eliminar:hover {
        background: var(--danger);
        color: var(--white);
        transform: scale(1.1);
    }
    
    /* Resumen del pedido */
    .resumen-pedido {
        background: var(--white);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        height: fit-content;
        position: sticky;
        top: 100px;
    }
    
    .resumen-pedido h2 {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--bg-light);
    }
    
    .resumen-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        font-size: 15px;
    }
    
    .resumen-row.subtotal {
        color: var(--text-gray);
    }
    
    .resumen-row.envio {
        color: var(--text-gray);
        padding-bottom: 15px;
        border-bottom: 1px solid var(--bg-light);
    }
    
    .resumen-row.total {
        font-size: 24px;
        font-weight: 900;
        color: var(--text-dark);
        padding-top: 15px;
    }
    
    .resumen-row.total .precio {
        color: var(--primary);
    }
    
    .btn-checkout {
        width: 100%;
        padding: 16px;
        background: var(--primary);
        color: var(--white);
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        margin-top: 20px;
        transition: all 0.3s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-decoration: none;
        display: block;
        text-align: center;
    }
    
    .btn-checkout:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(220, 20, 60, 0.3);
    }
    
    .btn-continuar {
        width: 100%;
        padding: 12px;
        background: var(--white);
        color: var(--text-dark);
        border: 2px solid var(--border);
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
        transition: all 0.3s;
        text-decoration: none;
        display: block;
        text-align: center;
    }
    
    .btn-continuar:hover {
        border-color: var(--primary);
        color: var(--primary);
    }
    
    .garantias {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid var(--bg-light);
    }
    
    .garantia-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        font-size: 12px;
        color: var(--text-gray);
    }
    
    .garantia-item svg {
        color: var(--primary);
        flex-shrink: 0;
    }
    
    /* Estado vacío */
    .carrito-vacio {
        text-align: center;
        padding: 80px 20px;
        background: var(--white);
        border-radius: 12px;
    }
    
    .carrito-vacio svg {
        width: 100px;
        height: 100px;
        color: var(--text-light);
        margin-bottom: 20px;
    }
    
    .carrito-vacio h2 {
        font-size: 28px;
        font-weight: 900;
        color: var(--text-dark);
        margin-bottom: 10px;
    }
    
    .carrito-vacio p {
        color: var(--text-gray);
        margin-bottom: 30px;
        font-size: 16px;
    }
    
    .btn-comprar {
        display: inline-block;
        padding: 15px 40px;
        background: var(--primary);
        color: var(--white);
        text-decoration: none;
        border-radius: 50px;
        font-weight: 700;
        font-size: 16px;
        transition: all 0.3s;
    }
    
    .btn-comprar:hover {
        background: var(--primary-dark);
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(220, 20, 60, 0.3);
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .btn-vaciar {
        background: var(--white);
        color: var(--danger);
        border: 2px solid var(--danger);
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 14px;
    }
    
    .btn-vaciar:hover {
        background: var(--danger);
        color: var(--white);
    }
    
    .carrito-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .carrito-header h2 {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-dark);
    }
    
    /* Responsive */
    @media (max-width: 968px) {
        .carrito-layout {
            grid-template-columns: 1fr;
        }
        
        .carrito-item {
            grid-template-columns: 80px 1fr 60px;
            gap: 15px;
        }
        
        .item-imagen {
            width: 80px;
            height: 100px;
        }
        
        .item-precio,
        .item-cantidad {
            grid-column: 2;
            justify-self: start;
        }
        
        .item-acciones {
            grid-column: 3;
            grid-row: 1 / 3;
        }
        
        .resumen-pedido {
            position: static;
        }
    }
</style>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="breadcrumb-content">
        <a href="index.php">Inicio</a>
        <span>›</span>
        <span style="color: var(--primary); font-weight: 600;">Carrito de Compras</span>
    </div>
</div>

<div class="carrito-page">
    <h1 class="page-title">Carrito de Compras</h1>
    <p class="page-subtitle">
        <?= $total_items ?> <?= $total_items == 1 ? 'producto' : 'productos' ?> en tu carrito
    </p>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <?= $success ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($_SESSION['carrito'])): ?>
        <!-- Carrito vacío -->
        <div class="carrito-vacio">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
            </svg>
            <h2>Tu carrito está vacío</h2>
            <p>¡Agrega productos increíbles y comienza a comprar!</p>
            <a href="productos.php" class="btn-comprar">Explorar Productos</a>
        </div>
    <?php else: ?>
        <!-- Carrito con productos -->
        <div class="carrito-layout">
            <!-- Lista de productos -->
            <div class="carrito-items">
                <div class="carrito-header">
                    <h2>Productos (<?= count($_SESSION['carrito']) ?>)</h2>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de vaciar el carrito?')">
                        <button type="submit" name="vaciar_carrito" class="btn-vaciar">Vaciar Carrito</button>
                    </form>
                </div>
                
                <?php foreach ($_SESSION['carrito'] as $key => $item): ?>
                    <div class="carrito-item">
                        <!-- Imagen -->
                        <div class="item-imagen">
                            <img src="<?= $item['imagen'] ? UPLOAD_URL . $item['imagen'] : 'https://via.placeholder.com/100x130?text=Sin+Imagen' ?>" 
                                 alt="<?= htmlspecialchars($item['nombre']) ?>">
                        </div>
                        
                        <!-- Información -->
                        <div class="item-info">
                            <h3><?= htmlspecialchars($item['nombre']) ?></h3>
                            <?php if (!empty($item['color'])): ?>
                                <p><strong>Color:</strong> <?= htmlspecialchars($item['color']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($item['talla'])): ?>
                                <p><strong>Talla:</strong> <?= htmlspecialchars($item['talla']) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Precio -->
                        <div class="item-precio">
                            <?= formatPrice($item['precio']) ?>
                        </div>
                        
                        <!-- Cantidad -->
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="item_key" value="<?= $key ?>">
                            <div class="item-cantidad">
                                <button type="submit" name="actualizar_cantidad" 
                                        onclick="this.form.cantidad.value = Math.max(1, parseInt(this.form.cantidad.value) - 1)">−</button>
                                <input type="number" name="cantidad" value="<?= $item['cantidad'] ?>" min="1" readonly>
                                <button type="submit" name="actualizar_cantidad"
                                        onclick="this.form.cantidad.value = parseInt(this.form.cantidad.value) + 1">+</button>
                            </div>
                        </form>
                        
                        <!-- Eliminar -->
                        <div class="item-acciones">
                            <form method="POST" style="margin: 0;" onsubmit="return confirm('¿Eliminar este producto?')">
                                <input type="hidden" name="item_key" value="<?= $key ?>">
                                <button type="submit" name="eliminar_item" class="btn-eliminar" title="Eliminar">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                        <line x1="10" y1="11" x2="10" y2="17"/>
                                        <line x1="14" y1="11" x2="14" y2="17"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Resumen del pedido -->
            <div class="resumen-pedido">
                <h2>Resumen del Pedido</h2>
                
                <div class="resumen-row subtotal">
                    <span>Subtotal (<?= $total_items ?> items):</span>
                    <span><?= formatPrice($subtotal) ?></span>
                </div>
                
                <div class="resumen-row envio">
                    <span>Envío:</span>
                    <span><?= formatPrice($costo_envio) ?></span>
                </div>
                
                <div class="resumen-row total">
                    <span>Total:</span>
                    <span class="precio"><?= formatPrice($total) ?></span>
                </div>
                
                <a href="checkout.php" class="btn-checkout">
                    Proceder al Pago
                </a>
                
                <a href="productos.php" class="btn-continuar">
                    Continuar Comprando
                </a>
                
                <div class="garantias">
                    <div class="garantia-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="3" width="15" height="13"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                        <span>Envío a todo el Perú</span>
                    </div>
                    <div class="garantia-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        <span>Compra 100% segura</span>
                    </div>
                    <div class="garantia-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span>Atención rápida</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>