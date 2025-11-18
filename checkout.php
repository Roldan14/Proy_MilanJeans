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
    
    /* Métodos de pago */
    .metodos-pago {
        display: grid;
        gap: 15px;
    }
    
    .metodo-pago {
        position: relative;
    }
    
    .metodo-pago input[type="radio"] {
        display: none;
    }
    
    .metodo-label {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        border: 2px solid var(--border);
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .metodo-pago input[type="radio"]:checked + .metodo-label {
        border-color: var(--primary);
        background: rgba(220, 20, 60, 0.05);
    }
    
    .metodo-icon {
        width: 50px;
        height: 50px;
        background: var(--bg-light);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .metodo-pago input[type="radio"]:checked + .metodo-label .metodo-icon {
        background: var(--primary);
        color: var(--white);
    }
    
    .metodo-info h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 4px;
    }
    
    .metodo-info p {
        font-size: 13px;
        color: var(--text-gray);
    }
    
    .yape-info {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 15px;
        margin-top: 10px;
        display: none;
    }
    
    .metodo-pago input[type="radio"]:checked ~ .yape-info {
        display: block;
    }
    
    .yape-info h4 {
        font-size: 14px;
        font-weight: 700;
        color: #856404;
        margin-bottom: 10px;
    }
    
    .yape-info p {
        font-size: 13px;
        color: #856404;
        margin-bottom: 8px;
    }
    
    .yape-numero {
        font-size: 20px;
        font-weight: 900;
        color: #856404;
        background: var(--white);
        padding: 10px;
        border-radius: 5px;
        text-align: center;
        margin: 10px 0;
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
        border-radius: 10px;
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
        box-shadow: 0 10px 30px rgba(220, 20, 60, 0.3);
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
                
                <!-- Método de Pago -->
                <div class="form-section">
                    <h2>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                        Método de Pago
                    </h2>
                    
                    <div class="metodos-pago">
                        <!-- Yape -->
                        <div class="metodo-pago">
                            <input type="radio" name="metodo_pago" id="yape" value="Yape" required>
                            <label for="yape" class="metodo-label">
                                <div class="metodo-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                                    </svg>
                                </div>
                                <div class="metodo-info">
                                    <h3>Yape</h3>
                                    <p>Pago instantáneo con código QR</p>
                                </div>
                            </label>
                            <div class="yape-info">
                                <h4>Instrucciones de pago:</h4>
                                <p>1. Realiza el Yape al siguiente número:</p>
                                <div class="yape-numero"><?= getConfig('yape_numero', '999 999 999') ?></div>
                                <p>A nombre de: <strong><?= getConfig('yape_nombre', 'Milan Jeans') ?></strong></p>
                                <p>2. Sube tu comprobante de pago abajo</p>
                            </div>
                        </div>
                        
                        <!-- Transferencia -->
                        <div class="metodo-pago">
                            <input type="radio" name="metodo_pago" id="transferencia" value="Transferencia Bancaria">
                            <label for="transferencia" class="metodo-label">
                                <div class="metodo-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="1" x2="12" y2="23"/>
                                        <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                                    </svg>
                                </div>
                                <div class="metodo-info">
                                    <h3>Transferencia Bancaria</h3>
                                    <p>Pago por banco (BCP, BBVA, etc.)</p>
                                </div>
                            </label>
                        </div>
                        
                        <!-- Contra Entrega -->
                        <div class="metodo-pago">
                            <input type="radio" name="metodo_pago" id="contraentrega" value="Pago contra entrega">
                            <label for="contraentrega" class="metodo-label">
                                <div class="metodo-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="1" y="3" width="15" height="13"/>
                                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                        <circle cx="5.5" cy="18.5" r="2.5"/>
                                        <circle cx="18.5" cy="18.5" r="2.5"/>
                                    </svg>
                                </div>
                                <div class="metodo-info">
                                    <h3>Pago contra entrega</h3>
                                    <p>Paga cuando recibas tu pedido</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Subir comprobante (solo visible si se selecciona Yape o Transferencia) -->
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
    
    // Mostrar campo de comprobante según método de pago
    const metodoPago = document.querySelectorAll('input[name="metodo_pago"]');
    const comprobanteGroup = document.getElementById('comprobanteGroup');
    const comprobanteInput = document.getElementById('comprobante');
    
    metodoPago.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'Yape' || this.value === 'Transferencia Bancaria') {
                comprobanteGroup.style.display = 'block';
                comprobanteInput.required = true;
            } else {
                comprobanteGroup.style.display = 'none';
                comprobanteInput.required = false;
            }
        });
    });
    
    // Validación del formulario
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const metodoPagoSeleccionado = document.querySelector('input[name="metodo_pago"]:checked');
        
        if (!metodoPagoSeleccionado) {
            e.preventDefault();
            alert('Por favor selecciona un método de pago');
            return false;
        }
        
        // Validar comprobante si es necesario
        if ((metodoPagoSeleccionado.value === 'Yape' || metodoPagoSeleccionado.value === 'Transferencia Bancaria') 
            && !comprobanteInput.files.length) {
            e.preventDefault();
            alert('Por favor sube tu comprobante de pago');
            return false;
        }
        
        // Deshabilitar botón para evitar doble envío
        document.getElementById('btnFinalizar').disabled = true;
        document.getElementById('btnFinalizar').textContent = 'Procesando...';
    });
</script>

<?php include 'includes/footer.php'; ?>