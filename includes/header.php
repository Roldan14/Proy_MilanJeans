<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? $config_sitio['nombre'] ?></title>
    <meta name="description" content="<?= $page_description ?? 'Los mejores jeans y ropa casual' ?>">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #DC143C;
            --primary-dark: #B8102E;
            --primary-light: #FF5370;
            --secondary: #1a1a1a;
            --text-dark: #2c2c2c;
            --text-gray: #666;
            --text-light: #999;
            --bg-light: #f8f8f8;
            --white: #ffffff;
            --border: #e5e5e5;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        /* Top Bar */
        .top-bar {
            background: var(--secondary);
            color: var(--white);
            padding: 10px 0;
            font-size: 13px;
        }
        
        .top-bar-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .top-bar-left {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .top-bar-left a {
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s;
        }
        
        .top-bar-left a:hover {
            color: var(--primary-light);
        }
        
        .top-bar-right {
            display: flex;
            gap: 15px;
        }
        
        .social-link {
            color: var(--white);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .social-link:hover {
            color: var(--primary-light);
        }
        
        /* Main Header */
        .main-header {
            background: var(--white);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 200px 1fr 200px;
            gap: 40px;
            align-items: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 900;
            color: var(--primary);
            text-decoration: none;
            letter-spacing: -1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo-img {
            max-height: 50px;
            width: auto;
        }
        
        /* Search Bar - Centrado */
        .search-bar {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }
        
        .search-bar input {
            width: 100%;
            padding: 14px 50px 14px 20px;
            border: 2px solid var(--border);
            border-radius: 50px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .search-bar input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(220, 20, 60, 0.1);
        }
        
        .search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary);
            color: var(--white);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .search-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-50%) scale(1.05);
        }
        
        /* Search Results Dropdown */
        .search-results {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            right: 0;
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            max-height: 500px;
            overflow-y: auto;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .search-results.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .search-result-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 20px;
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.3s;
            border-bottom: 1px solid var(--bg-light);
        }
        
        .search-result-item:last-child {
            border-bottom: none;
        }
        
        .search-result-item:hover {
            background: var(--bg-light);
        }
        
        .search-result-img {
            width: 60px;
            height: 75px;
            object-fit: cover;
            border-radius: 8px;
            background: var(--bg-light);
            flex-shrink: 0;
        }
        
        .search-result-info {
            flex: 1;
        }
        
        .search-result-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .search-result-price {
            font-size: 16px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .search-no-results {
            padding: 30px 20px;
            text-align: center;
            color: var(--text-gray);
        }
        
        .search-no-results svg {
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
            color: var(--text-light);
        }
        
        .search-loading {
            padding: 20px;
            text-align: center;
            color: var(--text-gray);
        }
        
        .search-view-all {
            display: block;
            padding: 15px 20px;
            text-align: center;
            background: var(--bg-light);
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            border-radius: 0 0 15px 15px;
            transition: all 0.3s;
        }
        
        .search-view-all:hover {
            background: var(--primary);
            color: var(--white);
        }
        
        /* Header Actions */
        .header-actions {
            display: flex;
            gap: 20px;
            align-items: center;
            justify-content: flex-end;
        }
        
        .header-icon {
            position: relative;
            color: var(--text-dark);
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            transition: color 0.3s;
        }
        
        .header-icon:hover {
            color: var(--primary);
        }
        
        .header-icon svg {
            width: 28px;
            height: 28px;
        }
        
        .header-icon span {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -10px;
            background: var(--primary);
            color: var(--white);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }
        
        /* Navigation */
        .main-nav {
            background: var(--white);
            border-bottom: 1px solid var(--border);
        }
        
        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .nav-menu {
            display: flex;
            gap: 40px;
            list-style: none;
            justify-content: center;
        }
        
        .nav-menu li a {
            display: block;
            padding: 18px 0;
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            position: relative;
            transition: color 0.3s;
        }
        
        .nav-menu li a:hover {
            color: var(--primary);
        }
        
        .nav-menu li a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: var(--primary);
            transition: width 0.3s;
        }
        
        .nav-menu li a:hover::after,
        .nav-menu li a.active::after {
            width: 100%;
        }
        
        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 10px;
        }
        
        .mobile-menu-btn svg {
            width: 28px;
            height: 28px;
            stroke: var(--text-dark);
        }
        
        /* Mobile Menu */
        .mobile-menu {
            display: none;
            position: fixed;
            top: 0;
            left: -100%;
            width: 300px;
            height: 100vh;
            background: var(--white);
            box-shadow: 2px 0 20px rgba(0,0,0,0.1);
            transition: left 0.3s;
            z-index: 2000;
            overflow-y: auto;
        }
        
        .mobile-menu.active {
            left: 0;
        }
        
        .mobile-menu-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .mobile-menu-close {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 24px;
            color: var(--text-dark);
        }
        
        .mobile-menu-items {
            list-style: none;
            padding: 20px 0;
        }
        
        .mobile-menu-items li a {
            display: block;
            padding: 15px 20px;
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .mobile-menu-items li a:hover {
            background: var(--bg-light);
            color: var(--primary);
            padding-left: 30px;
        }
        
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1999;
        }
        
        .mobile-overlay.active {
            display: block;
        }
        
        /* Responsive */
        @media (max-width: 968px) {
            .top-bar-left span:not(:first-child) {
                display: none;
            }
            
            .header-content {
                grid-template-columns: auto 1fr auto;
                gap: 15px;
            }
            
            .logo {
                font-size: 24px;
            }
            
            .search-bar {
                max-width: 100%;
            }
            
            .header-icon span {
                display: none;
            }
            
            .main-nav {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .mobile-menu {
                display: block;
            }
            
            .header-actions {
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-content">
            <div class="top-bar-left">
                <?php if ($config_sitio['email']): ?>
                    <a href="mailto:<?= $config_sitio['email'] ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <span><?= $config_sitio['email'] ?></span>
                    </a>
                <?php endif; ?>
                <?php if ($config_sitio['telefono']): ?>
                    <a href="tel:<?= $config_sitio['telefono'] ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                        </svg>
                        <span><?= $config_sitio['telefono'] ?></span>
                    </a>
                <?php endif; ?>
            </div>
            <div class="top-bar-right">
                <?php if ($config_sitio['facebook']): ?>
                    <a href="<?= $config_sitio['facebook'] ?>" target="_blank" class="social-link" title="Facebook">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                <?php endif; ?>
                <?php if ($config_sitio['instagram']): ?>
                    <a href="<?= $config_sitio['instagram'] ?>" target="_blank" class="social-link" title="Instagram">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Main Header -->
    <header class="main-header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <?php if ($config_sitio['logo']): ?>
                    <img src="<?= UPLOAD_URL . $config_sitio['logo'] ?>" alt="<?= $config_sitio['nombre'] ?>" class="logo-img">
                <?php else: ?>
                    <?= $config_sitio['nombre'] ?>
                <?php endif; ?>
            </a>
            
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Buscar productos..." autocomplete="off">
                <button type="button" class="search-btn" onclick="performSearch()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                </button>
                <div class="search-results" id="searchResults"></div>
            </div>
            
            <div class="header-actions">
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                
                <a href="carrito.php" class="header-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                    </svg>
                    <?php if (contarCarrito() > 0): ?>
                        <span class="cart-badge"><?= contarCarrito() ?></span>
                    <?php endif; ?>
                    <span>Carrito</span>
                </a>
            </div>
        </div>
    </header>
    
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="nav-content">
            <ul class="nav-menu">
                <li><a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Inicio</a></li>
                <li><a href="productos.php" class="<?= basename($_SERVER['PHP_SELF']) == 'productos.php' ? 'active' : '' ?>">Productos</a></li>
                <?php
                $categorias = $pdo->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY orden, nombre LIMIT 4")->fetchAll();
                foreach ($categorias as $cat):
                ?>
                    <li><a href="productos.php?categoria=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></a></li>
                <?php endforeach; ?>
                <li><a href="contacto.php">Contacto</a></li>
            </ul>
        </div>
    </nav>
    
    <!-- Mobile Menu -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <span style="font-weight: 700; font-size: 18px;">Menú</span>
            <button class="mobile-menu-close" onclick="toggleMobileMenu()">×</button>
        </div>
        <ul class="mobile-menu-items">
            <li><a href="index.php">Inicio</a></li>
            <li><a href="productos.php">Todos los Productos</a></li>
            <?php foreach ($categorias as $cat): ?>
                <li><a href="productos.php?categoria=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></a></li>
            <?php endforeach; ?>
            <li><a href="carrito.php">Mi Carrito (<?= contarCarrito() ?>)</a></li>
            <li><a href="contacto.php">Contacto</a></li>
        </ul>
    </div>
    
    <script>
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        
        // Búsqueda en tiempo real
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.classList.remove('show');
                return;
            }
            
            searchTimeout = setTimeout(() => {
                buscarProductos(query);
            }, 300);
        });
        
        // Función para buscar productos
        async function buscarProductos(query) {
            try {
                searchResults.innerHTML = '<div class="search-loading">Buscando...</div>';
                searchResults.classList.add('show');
                
                const response = await fetch(`buscar_productos.php?q=${encodeURIComponent(query)}`);
                const productos = await response.json();
                
                if (productos.length === 0) {
                    searchResults.innerHTML = `
                        <div class="search-no-results">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                            <p>No se encontraron productos</p>
                        </div>
                    `;
                    return;
                }
                
                let html = '';
                productos.forEach(producto => {
                    html += `
                        <a href="${producto.url}" class="search-result-item">
                            <img src="${producto.imagen}" alt="${producto.nombre}" class="search-result-img">
                            <div class="search-result-info">
                                <div class="search-result-name">${producto.nombre}</div>
                                <div class="search-result-price">${producto.precio}</div>
                            </div>
                        </a>
                    `;
                });
                
                html += `<a href="productos.php?search=${encodeURIComponent(query)}" class="search-view-all">Ver todos los resultados</a>`;
                
                searchResults.innerHTML = html;
                
            } catch (error) {
                console.error('Error al buscar:', error);
                searchResults.innerHTML = '<div class="search-no-results"><p>Error al realizar la búsqueda</p></div>';
            }
        }
        
        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-bar')) {
                searchResults.classList.remove('show');
            }
        });
        
        // Búsqueda al presionar Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
        
        function performSearch() {
            const query = searchInput.value.trim();
            if (query.length > 0) {
                window.location.href = `productos.php?search=${encodeURIComponent(query)}`;
            }
        }
        
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileOverlay');
            menu.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    </script>