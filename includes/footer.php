<style>
        .footer {
            background: var(--secondary);
            color: var(--white);
            padding: 60px 0 0;
            margin-top: 80px;
        }
        
        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px 40px;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
        }
        
        .footer-column h3 {
            color: var(--white);
            font-size: 18px;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .footer-about {
            color: rgba(255,255,255,0.7);
            line-height: 1.8;
            font-size: 14px;
        }
        
        .footer-logo {
            font-size: 32px;
            font-weight: 900;
            color: var(--primary);
            margin-bottom: 15px;
            display: block;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .footer-links a:hover {
            color: var(--primary-light);
            padding-left: 5px;
        }
        
        .footer-contact {
            list-style: none;
        }
        
        .footer-contact li {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: rgba(255,255,255,0.7);
            font-size: 14px;
        }
        
        .footer-contact svg {
            flex-shrink: 0;
            color: var(--primary);
        }
        
        .footer-social {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .social-icon:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 25px 0;
            text-align: center;
        }
        
        .footer-bottom-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: rgba(255,255,255,0.5);
            font-size: 13px;
        }
        
        .payment-methods {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .payment-icon {
            background: var(--white);
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 700;
            color: var(--secondary);
        }
        
        .whatsapp-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: #25D366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
            z-index: 999;
            text-decoration: none;
            transition: all 0.3s;
            animation: pulse 2s infinite;
        }
        
        .whatsapp-float:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 30px rgba(37, 211, 102, 0.6);
        }
        
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
            }
            50% {
                box-shadow: 0 4px 30px rgba(37, 211, 102, 0.7);
            }
        }
        
        @media (max-width: 968px) {
            .footer-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .footer-bottom-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .whatsapp-float {
                width: 50px;
                height: 50px;
                bottom: 20px;
                right: 20px;
            }
        }
    </style>
    
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-column">
                <span class="footer-logo"><?= $config_sitio['nombre'] ?></span>
                <p class="footer-about">
                    <?= getConfig('mensaje_pie_pagina', 'Encuentra los mejores jeans y ropa casual para tu estilo. Calidad, comodidad y diseño en cada prenda.') ?>
                </p>
                <div class="footer-social">
                    <?php if ($config_sitio['facebook']): ?>
                        <a href="<?= $config_sitio['facebook'] ?>" target="_blank" class="social-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                    <?php if ($config_sitio['instagram']): ?>
                        <a href="<?= $config_sitio['instagram'] ?>" target="_blank" class="social-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="footer-column">
                <h3>Enlaces Rápidos</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="productos.php">Productos</a></li>
                    <?php
                    $cats = $pdo->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY orden LIMIT 3")->fetchAll();
                    foreach ($cats as $cat):
                    ?>
                        <li><a href="productos.php?categoria=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="contacto.php">Contacto</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Información</h3>
                <ul class="footer-links">
                    <li><a href="carrito.php">Mi Carrito</a></li>
                    <li><a href="#">Cómo Comprar</a></li>
                    <li><a href="#">Métodos de Pago</a></li>
                    <li><a href="#">Envíos</a></li>
                    <li><a href="#">Cambios y Devoluciones</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Contacto</h3>
                <ul class="footer-contact">
                    <?php if ($config_sitio['telefono']): ?>
                        <li>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                            </svg>
                            <?= $config_sitio['telefono'] ?>
                        </li>
                    <?php endif; ?>
                    <?php if ($config_sitio['email']): ?>
                        <li>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <?= $config_sitio['email'] ?>
                        </li>
                    <?php endif; ?>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        Lun - Sáb: 9:00 - 20:00
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?= date('Y') ?> <?= $config_sitio['nombre'] ?>. Todos los derechos reservados.</p>
                <div class="payment-methods">
                    <span>Aceptamos:</span>
                    <span class="payment-icon">YAPE</span>
                    <span class="payment-icon">VISA</span>
                    <span class="payment-icon">MC</span>
                </div>
            </div>
        </div>
    </footer>
    
    <?php if ($config_sitio['whatsapp']): ?>
        <a href="https://wa.me/<?= $config_sitio['whatsapp'] ?>?text=Hola, tengo una consulta sobre los productos" 
           target="_blank" 
           class="whatsapp-float"
           title="Chatea con nosotros">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="white">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
        </a>
    <?php endif; ?>
</body>
</html>