<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item <?= $current_page == 'index.php' ? 'active' : '' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span>Dashboard</span>
        </a>
        
        <div class="nav-section">
            <h3>Catálogo</h3>
        </div>
        
        <a href="productos.php" class="nav-item <?= $current_page == 'productos.php' || $current_page == 'producto_form.php' ? 'active' : '' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 7h-9M14 17H5M20 17h-3M10 7H5M7 7a2 2 0 100-4 2 2 0 000 4zM17 17a2 2 0 100-4 2 2 0 000 4z"/>
            </svg>
            <span>Productos</span>
        </a>
        
        <a href="categorias.php" class="nav-item <?= $current_page == 'categorias.php' ? 'active' : '' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/>
                <rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/>
            </svg>
            <span>Categorías</span>
        </a>
        
        <a href="colores.php" class="nav-item <?= $current_page == 'colores.php' ? 'active' : '' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 2a10 10 0 010 20"/>
            </svg>
            <span>Colores</span>
        </a>
        
        <a href="tallas.php" class="nav-item <?= $current_page == 'tallas.php' ? 'active' : '' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
            </svg>
            <span>Tallas</span>
        </a>
        
        <div class="nav-section">
            <h3>Diseño Web</h3>
        </div>
        
        <a href="banners.php" class="nav-item <?= $current_page == 'banners.php' || $current_page == 'banner_form.php' ? 'active' : '' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
            <span>Banners</span>
        </a>
        
        <div class="nav-section">
            <h3>Ventas</h3>
        </div>
        
        <a href="pedidos.php" class="nav-item <?= $current_page == 'pedidos.php' || $current_page == 'pedido_detalle.php' ? 'active' : '' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
            <span>Pedidos</span>
            <?php
            $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'");
            $pendientes = $stmt->fetchColumn();
            if ($pendientes > 0):
            ?>
                <span class="badge"><?= $pendientes ?></span>
            <?php endif; ?>
        </a>
        
        <a href="clientes.php" class="nav-item <?= $current_page == 'clientes.php' ? 'active' : '' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                <path d="M16 3.13a4 4 0 010 7.75"/>
            </svg>
            <span>Clientes</span>
        </a>
        
        <div class="nav-section">
            <h3>Configuración</h3>
        </div>
        
        <a href="configuracion.php" class="nav-item <?= $current_page == 'configuracion.php' ? 'active' : '' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m7.08 7.08l4.24 4.24M1 12h6m6 0h6M4.22 19.78l4.24-4.24m7.08-7.08l4.24-4.24"/>
            </svg>
            <span>Configuración</span>
        </a>
    </nav>
</aside>