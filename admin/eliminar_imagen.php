<?php
// eliminar_imagen.php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

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
            
            echo json_encode([
                'success' => true,
                'message' => 'Imagen eliminada correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Imagen no encontrada'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar la imagen'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID inválido'
    ]);
}
?>