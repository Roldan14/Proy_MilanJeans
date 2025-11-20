<?php
require_once 'includes/config.php';

$page_title = 'Contacto - ' . $config_sitio['nombre'];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitize($_POST['nombre']);
    $email = sanitize($_POST['email']);
    $telefono = sanitize($_POST['telefono'] ?? '');
    $asunto = sanitize($_POST['asunto']);
    $mensaje = sanitize($_POST['mensaje']);
    
    // Validar campos
    if (empty($nombre) || empty($email) || empty($asunto) || empty($mensaje)) {
        $error = 'Por favor completa todos los campos obligatorios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido';
    } else {
        try {
            // Guardar mensaje en la base de datos
            $stmt = $pdo->prepare("
                INSERT INTO mensajes_contacto (nombre, email, telefono, asunto, mensaje)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nombre, $email, $telefono, $asunto, $mensaje]);
            
            $success = '¡Mensaje enviado correctamente! Te contactaremos pronto.';
            
            // Limpiar campos después de enviar
            $_POST = [];
        } catch (PDOException $e) {
            $error = 'Error al enviar el mensaje. Por favor intenta nuevamente.';
        }
    }
}

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
    
    .contacto-page {
        max-width: 1400px;
        margin: 0 auto;
        padding: 60px 20px;
    }
    
    .page-title {
        font-size: 42px;
        font-weight: 900;
        color: var(--text-dark);
        text-align: center;
        margin-bottom: 15px;
    }
    
    .page-subtitle {
        text-align: center;
        color: var(--text-gray);
        font-size: 18px;
        margin-bottom: 60px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .contacto-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        margin-bottom: 80px;
    }
    
    /* Info Cards */
    .contacto-info {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }
    
    .info-card {
        background: var(--white);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        transition: all 0.3s;
    }
    
    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .info-card-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .info-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        flex-shrink: 0;
    }
    
    .info-card h3 {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }
    
    .info-card p {
        color: var(--text-gray);
        line-height: 1.6;
        margin: 0;
    }
    
    .info-card a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s;
    }
    
    .info-card a:hover {
        color: var(--primary-dark);
    }
    
    /* Formulario */
    .contacto-form {
        background: var(--white);
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    
    .contacto-form h2 {
        font-size: 28px;
        font-weight: 900;
        color: var(--text-dark);
        margin-bottom: 25px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-dark);
        font-size: 14px;
    }
    
    .form-group .required {
        color: var(--primary);
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid var(--border);
        border-radius: 10px;
        font-size: 15px;
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
        min-height: 120px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    
    .btn-submit {
        width: 100%;
        padding: 16px;
        background: var(--primary);
        color: var(--white);
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 10px;
    }
    
    .btn-submit:hover {
        background: var(--primary-dark);
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(220, 20, 60, 0.3);
    }
    
    .alert {
        padding: 16px 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 2px solid #c3e6cb;
    }
    
    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 2px solid #f5c6cb;
    }
    
    .alert svg {
        flex-shrink: 0;
    }
    
    /* Mapa o Redes Sociales */
    .social-section {
        background: var(--white);
        padding: 50px 20px;
        text-align: center;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    
    .social-section h2 {
        font-size: 32px;
        font-weight: 900;
        color: var(--text-dark);
        margin-bottom: 15px;
    }
    
    .social-section p {
        color: var(--text-gray);
        margin-bottom: 30px;
        font-size: 16px;
    }
    
    .social-links {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .social-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 30px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 15px;
        transition: all 0.3s;
    }
    
    .social-link.facebook {
        background: #1877f2;
        color: white;
    }
    
    .social-link.instagram {
        background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
        color: white;
    }
    
    .social-link.whatsapp {
        background: #25D366;
        color: white;
    }
    
    .social-link:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    
    .horarios-section {
        background: var(--bg-light);
        padding: 30px;
        border-radius: 15px;
        margin-top: 25px;
    }
    
    .horarios-section h3 {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 15px;
        text-align: center;
    }
    
    .horario-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
    }
    
    .horario-item:last-child {
        border-bottom: none;
    }
    
    .horario-dia {
        font-weight: 600;
        color: var(--text-dark);
    }
    
    .horario-hora {
        color: var(--text-gray);
    }
    
    /* Responsive */
    @media (max-width: 968px) {
        .contacto-layout {
            grid-template-columns: 1fr;
            gap: 40px;
        }
        
        .page-title {
            font-size: 32px;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .contacto-form {
            padding: 30px 20px;
        }
        
        .social-links {
            flex-direction: column;
        }
        
        .social-link {
            justify-content: center;
        }
    }
</style>

<!-- Breadcrumb -->


<div class="contacto-page">
    <h1 class="page-title">Contáctanos</h1>
    <p class="page-subtitle">¿Tienes alguna pregunta o consulta? Estamos aquí para ayudarte. Escríbenos y te responderemos lo antes posible.</p>
    
    <div class="contacto-layout">
        <!-- Información de Contacto -->
        <div class="contacto-info">
            <!-- Teléfono -->
            <?php if ($config_sitio['telefono']): ?>
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                        </svg>
                    </div>
                    <h3>Teléfono</h3>
                </div>
                <p>Llámanos de Lunes a Sábado<br>
                <a href="tel:<?= $config_sitio['telefono'] ?>"><?= $config_sitio['telefono'] ?></a></p>
            </div>
            <?php endif; ?>
            
            <!-- Email -->
            <?php if ($config_sitio['email']): ?>
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <h3>Email</h3>
                </div>
                <p>Escríbenos tus consultas<br>
                <a href="mailto:<?= $config_sitio['email'] ?>"><?= $config_sitio['email'] ?></a></p>
            </div>
            <?php endif; ?>
            
            <!-- WhatsApp -->
            <?php if ($config_sitio['whatsapp']): ?>
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </div>
                    <h3>WhatsApp</h3>
                </div>
                <p>Chatea con nosotros<br>
                <a href="https://wa.me/<?= $config_sitio['whatsapp'] ?>" target="_blank">+<?= $config_sitio['whatsapp'] ?></a></p>
            </div>
            <?php endif; ?>
            
            <!-- Horarios -->
            <div class="horarios-section">
                <h3>Horario de Atención</h3>
                <div class="horario-item">
                    <span class="horario-dia">Lunes - Viernes</span>
                    <span class="horario-hora">9:00 AM - 8:00 PM</span>
                </div>
                <div class="horario-item">
                    <span class="horario-dia">Sábado</span>
                    <span class="horario-hora">9:00 AM - 6:00 PM</span>
                </div>
                <div class="horario-item">
                    <span class="horario-dia">Domingo</span>
                    <span class="horario-hora">Cerrado</span>
                </div>
            </div>
        </div>
        
        <!-- Formulario de Contacto -->
        <div class="contacto-form">
            <h2>Envíanos un Mensaje</h2>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <span><?= $success ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre Completo <span class="required">*</span></label>
                        <input type="text" name="nombre" value="<?= $_POST['nombre'] ?? '' ?>" required placeholder="Tu nombre">
                    </div>
                    
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="tel" name="telefono" value="<?= $_POST['telefono'] ?? '' ?>" placeholder="999 999 999">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" value="<?= $_POST['email'] ?? '' ?>" required placeholder="tu@email.com">
                </div>
                
                <div class="form-group">
                    <label>Asunto <span class="required">*</span></label>
                    <select name="asunto" required>
                        <option value="">Selecciona un asunto</option>
                        <option value="Consulta sobre productos" <?= ($_POST['asunto'] ?? '') == 'Consulta sobre productos' ? 'selected' : '' ?>>Consulta sobre productos</option>
                        <option value="Estado de pedido" <?= ($_POST['asunto'] ?? '') == 'Estado de pedido' ? 'selected' : '' ?>>Estado de pedido</option>
                        <option value="Cambios y devoluciones" <?= ($_POST['asunto'] ?? '') == 'Cambios y devoluciones' ? 'selected' : '' ?>>Cambios y devoluciones</option>
                        <option value="Envíos" <?= ($_POST['asunto'] ?? '') == 'Envíos' ? 'selected' : '' ?>>Envíos</option>
                        <option value="Sugerencias" <?= ($_POST['asunto'] ?? '') == 'Sugerencias' ? 'selected' : '' ?>>Sugerencias</option>
                        <option value="Otro" <?= ($_POST['asunto'] ?? '') == 'Otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Mensaje <span class="required">*</span></label>
                    <textarea name="mensaje" required placeholder="Escribe tu mensaje aquí..."><?= $_POST['mensaje'] ?? '' ?></textarea>
                </div>
                
                <button type="submit" class="btn-submit">
                    Enviar Mensaje
                </button>
            </form>
        </div>
    </div>
    
    <!-- Redes Sociales -->
    <div class="social-section">
        <h2>Síguenos en Redes Sociales</h2>
        <p>Mantente al día con nuestras últimas colecciones y ofertas</p>
        
        <div class="social-links">
            <?php if ($config_sitio['facebook']): ?>
            <a href="<?= $config_sitio['facebook'] ?>" target="_blank" class="social-link facebook">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                Facebook
            </a>
            <?php endif; ?>
            
            <?php if ($config_sitio['instagram']): ?>
            <a href="<?= $config_sitio['instagram'] ?>" target="_blank" class="social-link instagram">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
                Instagram
            </a>
            <?php endif; ?>
            
            <?php if ($config_sitio['whatsapp']): ?>
            <a href="https://wa.me/<?= $config_sitio['whatsapp'] ?>" target="_blank" class="social-link whatsapp">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                WhatsApp
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>