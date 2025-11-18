<?php
// eliminar_imagen.php
require_once 'config.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$producto_id = isset($_GET['producto']) ? (int)$_GET['producto'] : 0;

if ($id > 0) {
    try {
        // Obtener la ruta de la imagen
        $stmt = $pdo->prepare("SELECT * FROM producto_imagenes WHERE id = ?");
        $stmt->execute([$id]);
        $imagen = $stmt->fetch();
        
        if ($imagen) {
            // Eliminar archivo físico
            deleteImage($imagen['ruta_imagen']);
            
            // Eliminar de la base de datos
            $stmt = $pdo->prepare("DELETE FROM producto_imagenes WHERE id = ?");
            $stmt->execute([$id]);
            
            // Si era la imagen principal, asignar otra como principal
            if ($imagen['es_principal']) {
                $stmt = $pdo->prepare("
                    UPDATE producto_imagenes 
                    SET es_principal = 1 
                    WHERE producto_id = ? 
                    ORDER BY id ASC 
                    LIMIT 1
                ");
                $stmt->execute([$imagen['producto_id']]);
            }
        }
    } catch (PDOException $e) {
        // Manejar error silenciosamente
    }
}

// Redirigir de vuelta al formulario
header('Location: producto_form.php?id=' . $producto_id);
exit;
?>