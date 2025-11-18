<?php
// ELIMINACIÓN (DELETE) DE PROVEEDORES
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php'; 

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (empty($id)) {
    header("Location: index.php?msg=ID de proveedor no especificado.");
    exit;
}

$pdo = conectarDB();
$ruta_base = '../../'; // La ruta base para la función unlink()

try {
    // 1. Obtener las rutas de los archivos antes de eliminar el registro
    $stmt = $pdo->prepare("SELECT ruta_rut, ruta_camara_comercio FROM proveedores WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $rutas = $stmt->fetch();

    // 2. Eliminar el registro de la base de datos
    $stmt = $pdo->prepare("DELETE FROM proveedores WHERE id = :id");
    $stmt->execute(['id' => $id]);

    // 3. Eliminar los archivos físicos del servidor (BUENA PRÁCTICA)
    if ($rutas) {
        if (file_exists($ruta_base . $rutas['ruta_rut'])) {
            unlink($ruta_base . $rutas['ruta_rut']);
        }
        if (file_exists($ruta_base . $rutas['ruta_camara_comercio'])) {
            unlink($ruta_base . $rutas['ruta_camara_comercio']);
        }
    }

    header("Location: index.php?msg=Proveedor y sus documentos eliminados exitosamente.");
    exit;

} catch (PDOException $e) {
    // Manejar errores si hay llaves foráneas (si el proveedor tiene compras asociadas)
    if ($e->getCode() == '23000') {
        $error_msg = "ERROR: No se puede eliminar el proveedor porque tiene registros de compras asociados.";
    } else {
        $error_msg = "ERROR en la base de datos al eliminar.";
    }
    header("Location: index.php?msg=" . urlencode($error_msg));
    exit;
}
?>