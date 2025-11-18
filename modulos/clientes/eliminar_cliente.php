<?php
// ELIMINACIÓN (DELETE) DE CLIENTES
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (empty($id)) {
    header("Location: index.php?msg=ID de cliente no especificado.");
    exit;
}

$pdo = conectarDB();

try {
    // Eliminar el registro de la base de datos
    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header("Location: index.php?msg=Cliente eliminado exitosamente.");
    exit;

} catch (PDOException $e) {
    // Manejar errores si hay llaves foráneas (si el cliente tiene compras asociadas)
    if ($e->getCode() == '23000') {
        $error_msg = "ERROR: No se puede eliminar el cliente porque tiene registros de compras asociados.";
    } else {
        $error_msg = "ERROR en la base de datos al eliminar.";
    }
    header("Location: index.php?msg=" . urlencode($error_msg));
    exit;
}
?>
