<?php
require_once 'includes/config.php';

// Verificar que hay productos en el carrito
if (empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit;
}

$page_title = 'Finalizar Compra - ' . $config_sitio['nombre'];

// Calcular totales
$subtotal = 0;
$total_items = 0;

foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
    $total_items += $item['cantidad'];
}

// Obtener configuración de envío
$costo_envio_lima = (float)getConfig('costo_envio_lima', 10.00);
$costo_envio_provincia = (float)getConfig('costo_envio_provincia', 15.00);

// Por defecto, usar costo de Lima
$costo_envio = $costo_envio_lima;
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
    
    .checkout-page {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    
    .page-title {
        font-size: 36px;
        font-weight: 900;
        color: var(--text-dark);
        margin-bottom: 10px;
    }
    
    .checkout-steps {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin: 40px 0;
        padding: 0 20px;
    }
    
    .step {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        background: var(--bg-light);
        border-radius: 50px;
        font-weight: 600;
        font-size: 14px;
        color: var(--text-gray);
    }
    
    .step.active {
        background: var(--primary);
        color: var(--white);
    }
    
    .step-number {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--white);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }
    
    .step.active .step-number {
        background: var(--white);
        color: var(--primary);
    }
    
    .checkout-layout {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 30px;
    }
    
    /* Formulario */
    .checkout-form {
        background: var(--white);
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .form-section {
        margin-bottom: 35px;
    }
    
    .form-section:last-child {
        margin-bottom: 0;
    }
    
    .form-section h2 {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--bg-light);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-section h2 svg {
        color: var(--primary);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 14px;
        color: var(--text-dark);
    }
    
    .form-group label .required {
        color: var(--danger);
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid var(--border);
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        transition: all 0.3s;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(220, 20, 60, 0.1);
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }
    
    .form-group small {
        display: block;
        margin-top: 5px;
        font-size: 12px;
        color: var(--text-gray);
    }
    
/* Métodos de Pago Modernos */
.metodos-pago-modern {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.metodo-pago-card {
    border: 2px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s;
}

.metodo-pago-card input[type="radio"] {
    display: none;
}

.metodo-pago-card input[type="radio"]:checked ~ .metodo-content {
    display: block;
}

.metodo-card-label {
    display: block;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s;
    border-bottom: 2px solid transparent;
}

.metodo-card-label:hover {
    background: var(--bg-light);
}

.metodo-header {
    display: flex;
    align-items: center;
    gap: 15px;
}

.metodo-icon-modern {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.yape-icon {
    background: linear-gradient(135deg, #722C8A, #9B4DB8);
    color: white;
}

.bcp-icon {
    background: linear-gradient(135deg, #002A8D, #0047BB);
    color: white;
}

.efectivo-icon {
    background: linear-gradient(135deg, #43e97b, #38f9d7);
    color: white;
}

.metodo-title h3 {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 4px;
}

.metodo-title p {
    font-size: 13px;
    color: var(--text-gray);
    margin: 0;
}

.metodo-content {
    display: none;
    padding: 0 20px 20px;
}

/* Yape Box */
.yape-box {
    background: linear-gradient(135deg, #722C8A, #9B4DB8);
    color: white;
    border-radius: 12px;
    padding: 25px;
}

.info-row-modern {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 25px;
    margin-bottom: 20px;
}

.qr-container {
    background: white;
    padding: 15px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.qr-image {
    width: 100%;
    height: auto;
    border-radius: 8px;
}

.qr-placeholder {
    width: 170px;
    height: 170px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-light);
}

.info-details {
    display: flex;
    flex-direction: column;
    gap: 12px;
    letter-spacing: 1px;
}

.logo-yape {
    margin-bottom: 10px;
    letter-spacing: 2px;
}

.info-item-modern {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.info-item-modern.highlight {
    background: rgba(255,255,255,0.1);
    padding: 12px 15px;
    border-radius: 8px;
    border: none;
}

.info-label-modern {
    font-size: 13px;
    opacity: 0.9;
}

.info-value-modern {
    font-size: 15px;
    font-weight: 700;
}

.btn-add-contact:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* BCP Box */
.bcp-box {
    background: linear-gradient(135deg, #002A8D, #0047BB);
    color: white;
    border-radius: 12px;
    padding: 25px;
}

.bcp-header {
    margin-bottom:20px;
padding-bottom: 15px;
border-bottom: 1px solid rgba(255,255,255,0.2);
}

.bcp-instruction {
background: rgba(255,255,255,0.1);
padding: 15px;
border-radius: 8px;
font-size: 13px;
line-height: 1.6;
margin-bottom: 20px;
}
.cuenta-info-grid {
display: grid;
gap: 15px;
margin-bottom: 20px;
letter-spacing: 1px;
}
.cuenta-item {
background: rgba(255,255,255,0.1);
padding: 15px;
border-radius: 8px;
display: flex;
flex-direction: column;
gap: 8px;
position: relative;
}
.cuenta-label {
font-size: 11px;
font-weight: 700;
text-transform: uppercase;
opacity: 0.9;
}
.cuenta-value {
font-size: 20px;
font-weight: 700;
letter-spacing: 1px;
}
.btn-copy {
position: absolute;
top: 15px;
right: 15px;
background: rgba(255,255,255,0.2);
border: none;
padding: 8px;
border-radius: 5px;
cursor: pointer;
color: white;
transition: all 0.3s;
}
.btn-copy:hover {
background: rgba(255,255,255,0.3);
transform: scale(1.1);
}
.titular-info {
display: grid;
grid-template-columns: 1fr 1fr;
gap: 10px;
margin-bottom: 20px;
letter-spacing: 1px;
}
/* Efectivo Box */
.efectivo-box {
background: var(--bg-light);
border-radius: 12px;
padding: 25px;
}
/* Security Badges */
.security-badges {
display: flex;
justify-content: space-around;
gap: 15px;
padding-top: 20px;
border-top: 1px solid rgba(255,255,255,0.2);
}
.badge-item {
display: flex;
flex-direction: column;
align-items: center;
gap: 8px;
font-size: 11px;
text-align: center;
}
.badge-item svg {
opacity: 0.9;
}
/* Responsive */
@media (max-width: 768px) {
.info-row-modern {
grid-template-columns: 1fr;
}
.titular-info {
    grid-template-columns: 1fr;
}

.security-badges {
    flex-direction: column;
}
}
    
    /* Resumen */
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
    
    .resumen-productos {
        max-height: 300px;
        overflow-y: auto;
        margin-bottom: 20px;
    }
    
    .resumen-item {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--bg-light);
    }
    
    .resumen-item:last-child {
        border-bottom: none;
    }
    
    .resumen-item-img {
        width: 60px;
        height: 75px;
        border-radius: 6px;
        overflow: hidden;
        background: var(--bg-light);
        flex-shrink: 0;
    }
    
    .resumen-item-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .resumen-item-info {
        flex: 1;
    }
    
    .resumen-item-info h4 {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 4px;
        line-height: 1.3;
    }
    
    .resumen-item-info p {
        font-size: 11px;
        color: var(--text-gray);
        margin-bottom: 2px;
    }
    
    .resumen-item-precio {
        font-size: 14px;
        font-weight: 700;
        color: var(--primary);
    }
    
    .resumen-totales {
        border-top: 2px solid var(--bg-light);
        padding-top: 15px;
    }
    
    .resumen-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        font-size: 15px;
    }
    
    .resumen-row.total {
        font-size: 24px;
        font-weight: 900;
        color: var(--text-dark);
        padding-top: 15px;
        border-top: 1px solid var(--bg-light);
    }
    
    .resumen-row.total .precio {
        color: var(--primary);
    }
    
    .btn-finalizar {
        width: 100%;
        padding: 16px;
        background: var(--primary);
        color: var(--white);
        border: none;
        border-radius: 3px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        margin-top: 20px;
        transition: all 0.3s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .btn-finalizar:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }
    
    .btn-finalizar:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    
    .seguridad {
        margin-top: 15px;
        padding: 15px;
        background: var(--bg-light);
        border-radius: 8px;
        text-align: center;
    }
    
    .seguridad svg {
        color: var(--success);
        margin-bottom: 8px;
    }
    
    .seguridad p {
        font-size: 12px;
        color: var(--text-gray);
        margin: 0;
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    /* Responsive */
    @media (max-width: 968px) {
        .checkout-layout {
            grid-template-columns: 1fr;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .checkout-steps {
            flex-direction: column;
            gap: 10px;
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
        <a href="carrito.php">Carrito</a>
        <span>›</span>
        <span style="color: var(--primary); font-weight: 600;">Checkout</span>
    </div>
</div>

<div class="checkout-page">
    <h1 class="page-title">Finalizar Compra</h1>
    
    <!-- Pasos -->
    <div class="checkout-steps">
        <div class="step active">
            <span class="step-number">1</span>
            <span>Información</span>
        </div>
        <div class="step">
            <span class="step-number">2</span>
            <span>Pago</span>
        </div>
        <div class="step">
            <span class="step-number">3</span>
            <span>Confirmación</span>
        </div>
    </div>
    
    <form action="procesar_pedido.php" method="POST" enctype="multipart/form-data" id="checkoutForm">
        <div class="checkout-layout">
            <!-- Formulario -->
            <div class="checkout-form">
                
                <!-- Información Personal -->
                <div class="form-section">
                    <h2>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        Información Personal
                    </h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nombre <span class="required">*</span></label>
                            <input type="text" name="nombre" required placeholder="Tu nombre">
                        </div>
                        
                        <div class="form-group">
                            <label>Apellido <span class="required">*</span></label>
                            <input type="text" name="apellido" required placeholder="Tu apellido">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email <span class="required">*</span></label>
                            <input type="email" name="email" required placeholder="tu@email.com">
                            <small>Para enviarte la confirmación del pedido</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Teléfono <span class="required">*</span></label>
                            <input type="tel" name="telefono" required placeholder="999 999 999">
                            <small>Para coordinar la entrega</small>
                        </div>
                    </div>
                </div>
                
                <!-- Dirección de Entrega -->
                <div class="form-section">
                    <h2>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        Dirección de Entrega
                    </h2>
                    
                    <div class="form-group">
                        <label>Dirección <span class="required">*</span></label>
                        <input type="text" name="direccion" required placeholder="Calle, Av., Jr., número">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Ciudad <span class="required">*</span></label>
                            <select name="ciudad" id="ciudad" required onchange="calcularEnvio()">
                                <option value="">Selecciona tu ciudad</option>
                                <option value="Lima">Lima</option>
                                <option value="Provincia">Provincia</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Distrito <span class="required">*</span></label>
                            <input type="text" name="distrito" required placeholder="Ej: San Isidro">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Referencia</label>
                        <textarea name="referencia" placeholder="Punto de referencia para encontrar tu dirección (opcional)"></textarea>
                    </div>
                </div>
                
               <!-- Método de Pago MODERNO -->
<div class="form-section">
    <h2>
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
            <line x1="1" y1="10" x2="23" y2="10"/>
        </svg>
        Método de Pago
    </h2>
    
    <div class="metodos-pago-modern">
        <!-- Yape -->
        <div class="metodo-pago-card">
            <input type="radio" name="metodo_pago" id="yape" value="Yape" required>
            <label for="yape" class="metodo-card-label">
                <div class="metodo-header">
                    <div class="metodo-icon-modern yape-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                    </div>
                    <div class="metodo-title">
                        <h3>Paga con Yape</h3>
                        <p>Escanea el QR y paga al instante</p>
                    </div>
                </div>
            </label>
            
            <div class="metodo-content">
                <div class="metodo-info-box yape-box">
                    <div class="info-row-modern">
                        <div class="qr-container">
                            <?php 
                            $yape_qr = getConfig('yape_qr');
                            if ($yape_qr): 
                            ?>
                                <img src="<?= UPLOAD_URL . $yape_qr ?>" alt="QR Yape" class="qr-image">
                            <?php else: ?>
                                <div class="qr-placeholder">
                                    <svg width="150" height="150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="7" height="7"/>
                                        <rect x="14" y="3" width="7" height="7"/>
                                        <rect x="14" y="14" width="7" height="7"/>
                                        <rect x="3" y="14" width="7" height="7"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="info-details">
                            <div class="logo-yape">
                                <svg width="60" height="20" viewBox="0 0 80 30" fill="#ffffffff">
                                    <text x="0" y="20" font-family="Arial" font-size="20" font-weight="bold" >YAPE</text>
                                </svg>
                            </div>
                            
                            <div class="info-item-modern">
                                <span class="info-label-modern">Empresa:</span>
                                <span class="info-value-modern"><?= getConfig('yape_empresa', 'Milan Jeans') ?></span>
                            </div>
                            
                            <?php if (getConfig('yape_ruc')): ?>
                            <div class="info-item-modern">
                                <span class="info-label-modern">RUC:</span>
                                <span class="info-value-modern"><?= getConfig('yape_ruc') ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="info-item-modern highlight">
                                <span class="info-label-modern">Celular Yape:</span>
                                <span class="info-value-modern"><?= getConfig('yape_celular', getConfig('yape_numero')) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="security-badges">
                        <div class="badge-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            <span>Pago Seguro</span>
                        </div>
                        <div class="badge-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span>Satisfacción Garantizada</span>
                        </div>
                        <div class="badge-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            <span>Privacidad Protegida</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- BCP / Transferencia -->
        <div class="metodo-pago-card">
            <input type="radio" name="metodo_pago" id="transferencia" value="Transferencia Bancaria">
            <label for="transferencia" class="metodo-card-label">
                <div class="metodo-header">
                    <div class="metodo-icon-modern bcp-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                        </svg>
                    </div>
                    <div class="metodo-title">
                        <h3>Paga con BCP</h3>
                        <p>Transferencia bancaria o agentes</p>
                    </div>
                </div>
            </label>
            
            <div class="metodo-content">
                <div class="metodo-info-box bcp-box">
                    <div class="bcp-header">
                        <div class="bcp-logo">
                            <svg width="80" height="30" viewBox="0 0 100 40" fill="#f8f8f8ff">
                                <text x="0" y="25" font-family="Arial" font-size="20" font-weight="bold" >BCP</text>
                            </svg>
                        </div>
                    </div>
                    
                    <p class="bcp-instruction">
                        Paga en BANCA VIRTUAL (apps y web del mismo banco) o AGENTES (farmacias o tiendas). 
                        No use ventanilla de banco por exceso de comisión. Realice la transferencia BCP, 
                        su pedido será verificado y validado para su entrega.
                    </p>
                    
                    <div class="cuenta-info-grid">
                        <div class="cuenta-item">
                            <span class="cuenta-label">AHORRO SOLES</span>
                            <span class="cuenta-value"><?= getConfig('bcp_cuenta_ahorro', '191-37233892-0-71') ?></span>
                            <button type="button" class="btn-copy" onclick="copiarTexto('<?= getConfig('bcp_cuenta_ahorro') ?>')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                    <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="cuenta-item">
                            <span class="cuenta-label">CCI SOLES</span>
                            <span class="cuenta-value"><?= getConfig('bcp_cuenta_cci', '00219113723389207117') ?></span>
                            <button type="button" class="btn-copy" onclick="copiarTexto('<?= getConfig('bcp_cuenta_cci') ?>')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                    <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="titular-info">
                        <div class="info-item-modern">
                            <span class="info-label-modern">Titular:</span>
                            <span class="info-value-modern"><?= getConfig('bcp_titular', 'Tracy Scamarone') ?></span>
                        </div>
                        
                        <div class="info-item-modern">
                            <span class="info-label-modern">Empresa:</span>
                            <span class="info-value-modern"><?= getConfig('bcp_empresa', 'Milan Jeans') ?></span>
                        </div>
                        
                        <?php if (getConfig('bcp_ruc')): ?>
                        <div class="info-item-modern">
                            <span class="info-label-modern">RUC:</span>
                            <span class="info-value-modern"><?= getConfig('bcp_ruc') ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (getConfig('bcp_celular')): ?>
                        <div class="info-item-modern">
                            <span class="info-label-modern">Celular:</span>
                            <span class="info-value-modern"><?= getConfig('bcp_celular') ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="security-badges">
                        <div class="badge-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            <span>Pago Seguro</span>
                        </div>
                        <div class="badge-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span>Satisfacción Garantizada</span>
                        </div>
                        <div class="badge-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            <span>Privacidad Protegida</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contra Entrega -->
        <div class="metodo-pago-card">
            <input type="radio" name="metodo_pago" id="contraentrega" value="Pago contra entrega">
            <label for="contraentrega" class="metodo-card-label">
                <div class="metodo-header">
                    <div class="metodo-icon-modern efectivo-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                        </svg>
                    </div>
                    <div class="metodo-title">
                        <h3>Pago contra entrega</h3>
                        <p>Paga cuando recibas tu pedido</p>
                    </div>
                </div>
            </label>
            
            <div class="metodo-content">
                <div class="metodo-info-box efectivo-box">
                    <p style="text-align: center; color: var(--text-gray); margin: 20px 0;">
                        Paga en efectivo cuando recibas tu pedido en la puerta de tu casa.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Subir comprobante -->
    <div class="form-group" id="comprobanteGroup" style="display: none; margin-top: 20px;">
        <label>Comprobante de Pago <span class="required">*</span></label>
        <input type="file" name="comprobante" id="comprobante" accept="image/*">
        <small>Sube una foto de tu comprobante de pago (JPG, PNG - máx 5MB)</small>
    </div>
</div>
                    
                
                <!-- Notas adicionales -->
                <div class="form-section">
                    <h2>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                        </svg>
                        Notas Adicionales
                    </h2>
                    
                    <div class="form-group">
                        <label>Comentarios (Opcional)</label>
                        <textarea name="notas" placeholder="¿Alguna indicación especial para tu pedido?"></textarea>
                    </div>
                </div>
                
            </div>
            
            <!-- Resumen -->
            <div class="resumen-pedido">
                <h2>Resumen del Pedido</h2>
                
                <div class="resumen-productos">
                    <?php foreach ($_SESSION['carrito'] as $item): ?>
                        <div class="resumen-item">
                            <div class="resumen-item-img">
                                <img src="<?= $item['imagen'] ? UPLOAD_URL . $item['imagen'] : 'https://via.placeholder.com/60x75?text=Sin+Imagen' ?>" 
                                     alt="<?= htmlspecialchars($item['nombre']) ?>">
                            </div>
                            <div class="resumen-item-info">
                                <h4><?= htmlspecialchars($item['nombre']) ?></h4>
                                <?php if (!empty($item['color'])): ?>
                                    <p>Color: <?= htmlspecialchars($item['color']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($item['talla'])): ?>
                                    <p>Talla: <?= htmlspecialchars($item['talla']) ?></p>
                                <?php endif; ?>
                                <p>Cantidad: <?= $item['cantidad'] ?></p>
                                <div class="resumen-item-precio">
                                    <?= formatPrice($item['precio'] * $item['cantidad']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="resumen-totales">
                    <div class="resumen-row">
                        <span>Subtotal:</span>
                        <span id="subtotal"><?= formatPrice($subtotal) ?></span>
                    </div>
                    
                    <div class="resumen-row">
                        <span>Envío:</span>
                        <span id="costoEnvio"><?= formatPrice($costo_envio) ?></span>
                    </div>
                    
                    <div class="resumen-row total">
                        <span>Total:</span>
                        <span class="precio" id="total"><?= formatPrice($total) ?></span>
                    </div>
                </div>
                
                <button type="submit" class="btn-finalizar" id="btnFinalizar">
                    Finalizar Compra
                </button>
                
                <div class="seguridad">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <p><strong>Compra 100% Segura</strong><br>
                    Tus datos están protegidos</p>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Calcular envío según ciudad
    const subtotal = <?= $subtotal ?>;
    const costoEnvioLima = <?= $costo_envio_lima ?>;
    const costoEnvioProvincia = <?= $costo_envio_provincia ?>;
    
    function calcularEnvio() {
        const ciudad = document.getElementById('ciudad').value;
        let costoEnvio = 0;
        
        if (ciudad === 'Lima') {
            costoEnvio = costoEnvioLima;
        } else if (ciudad === 'Provincia') {
            costoEnvio = costoEnvioProvincia;
        }
        
        const total = subtotal + costoEnvio;
        
        document.getElementById('costoEnvio').textContent = 'S/ ' + costoEnvio.toFixed(2);
        document.getElementById('total').textContent = 'S/ ' + total.toFixed(2);
    }
    
    
    // Validación del formulario
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const metodoPagoSeleccionado = document.querySelector('input[name="metodo_pago"]:checked');
        
        if (!metodoPagoSeleccionado) {
            e.preventDefault();
            alert('Por favor selecciona un método de pago');
            return false;
        }
        
        
        // Deshabilitar botón para evitar doble envío
        document.getElementById('btnFinalizar').disabled = true;
        document.getElementById('btnFinalizar').textContent = 'Procesando...';
    });

    function copiarTexto(texto) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(texto).then(() => {
            // Mostrar feedback visual
            const btn = event.currentTarget;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>';
            btn.style.background = 'rgba(67, 233, 123, 0.3)';
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.style.background = '';
            }, 2000);
        });
    } else {
        // Fallback para navegadores antiguos
        const textArea = document.createElement('textarea');
        textArea.value = texto;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Copiado: ' + texto);
    }
}
</script>

<?php include 'includes/footer.php'; ?>