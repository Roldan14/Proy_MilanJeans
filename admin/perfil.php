<?php
// perfil.php - Perfil del administrador
require_once 'config.php';
requireLogin();

$admin_id = $_SESSION['admin_id'];

// Obtener datos del administrador
$stmt = $pdo->prepare("SELECT * FROM administradores WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

if (!$admin) {
    header('Location: logout.php');
    exit;
}

// Actualizar perfil
if (isset($_POST['actualizar_perfil'])) {
    $nombre = sanitize($_POST['nombre']);
    $email = sanitize($_POST['email']);
    $usuario = sanitize($_POST['usuario']);
    
    try {
        $stmt = $pdo->prepare("UPDATE administradores SET nombre = ?, email = ?, usuario = ? WHERE id = ?");
        $stmt->execute([$nombre, $email, $usuario, $admin_id]);
        
        $_SESSION['admin_nombre'] = $nombre;
        $_SESSION['admin_usuario'] = $usuario;
        
        $success = "Perfil actualizado correctamente";
        
        // Recargar datos
        $stmt = $pdo->prepare("SELECT * FROM administradores WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();
        
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Cambiar contraseña
if (isset($_POST['cambiar_password'])) {
    $password_actual = $_POST['password_actual'];
    $password_nueva = $_POST['password_nueva'];
    $password_confirmar = $_POST['password_confirmar'];
    
    if ($password_actual !== $admin['password']) {
        $error_pass = "La contraseña actual es incorrecta";
    } elseif (strlen($password_nueva) < 6) {
        $error_pass = "La nueva contraseña debe tener al menos 6 caracteres";
    } elseif ($password_nueva !== $password_confirmar) {
        $error_pass = "Las contraseñas no coinciden";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE administradores SET password = ? WHERE id = ?");
            $stmt->execute([$password_nueva, $admin_id]);
            
            $success_pass = "Contraseña actualizada correctamente";
        } catch (PDOException $e) {
            $error_pass = "Error al cambiar la contraseña";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Milan Jeans Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .profile-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }
        
        .profile-info h2 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .profile-info p {
            color: var(--gray);
            font-size: 14px;
        }
        
        .profile-badge {
            display: inline-block;
            padding: 4px 12px;
            background: var(--success);
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
        }
        
        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .profile-card h3 {
            font-size: 18px;
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .profile-card h3 svg {
            color: var(--primary);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-box {
            background: #fff3cd;
            border-left: 4px solid var(--warning);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-box p {
            font-size: 13px;
            color: #856404;
            margin: 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            background: var(--light);
            border-radius: 8px;
        }
        
        .stat-item h4 {
            font-size: 28px;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .stat-item p {
            font-size: 13px;
            color: var(--gray);
        }
        
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Mi Perfil</h1>
                <p>Gestiona tu información personal y configuración de cuenta</p>
            </div>
            
            <div class="profile-container">
                <!-- Header del Perfil -->
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?= strtoupper(substr($admin['nombre'], 0, 1)) ?>
                    </div>
                    <div class="profile-info">
                        <h2><?= htmlspecialchars($admin['nombre']) ?></h2>
                        <p>@<?= htmlspecialchars($admin['usuario']) ?> • <?= htmlspecialchars($admin['email']) ?></p>
                        <span class="profile-badge">Administrador</span>
                        <p style="font-size: 12px; margin-top: 10px;">
                            Miembro desde: <?= date('d/m/Y', strtotime($admin['fecha_creacion'])) ?>
                            <?php if ($admin['ultimo_acceso']): ?>
                                <br>Último acceso: <?= date('d/m/Y H:i', strtotime($admin['ultimo_acceso'])) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <!-- Estadísticas Rápidas -->
                <div class="profile-card">
                    <h3>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                        Actividad
                    </h3>
                    
                    <div class="stats-grid">
                        <div class="stat-item">
                            <h4><?= $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn() ?></h4>
                            <p>Productos</p>
                        </div>
                        <div class="stat-item">
                            <h4><?= $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn() ?></h4>
                            <p>Pedidos</p>
                        </div>
                        <div class="stat-item">
                            <h4><?= $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn() ?></h4>
                            <p>Clientes</p>
                        </div>
                    </div>
                </div>
                
                <!-- Información Personal -->
                <div class="profile-card">
                    <h3>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        Información Personal
                    </h3>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($admin['nombre']) ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Usuario</label>
                                <input type="text" name="usuario" value="<?= htmlspecialchars($admin['usuario']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
                            </div>
                        </div>
                        
                        <button type="submit" name="actualizar_perfil" class="btn btn-primary">
                            Actualizar Perfil
                        </button>
                    </form>
                </div>
                
                <!-- Cambiar Contraseña -->
                <div class="profile-card">
                    <h3>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        Cambiar Contraseña
                    </h3>
                    
                    <?php if (isset($success_pass)): ?>
                        <div class="alert alert-success"><?= $success_pass ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_pass)): ?>
                        <div class="alert alert-danger"><?= $error_pass ?></div>
                    <?php endif; ?>
                    
                    <div class="info-box">
                        <p><strong>Importante:</strong> La contraseña debe tener al menos 6 caracteres. Te recomendamos usar una combinación de letras y números.</p>
                    </div>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Contraseña Actual</label>
                            <input type="password" name="password_actual" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Nueva Contraseña</label>
                            <input type="password" name="password_nueva" minlength="6" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Confirmar Nueva Contraseña</label>
                            <input type="password" name="password_confirmar" minlength="6" required>
                        </div>
                        
                        <button type="submit" name="cambiar_password" class="btn btn-warning">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 5px;">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                            Cambiar Contraseña
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>