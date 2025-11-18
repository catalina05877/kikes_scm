<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

$pdo = conectarDB();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php?msg=ERROR: ID inválido.");
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM inventarios WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $msg = "Movimiento eliminado exitosamente.";
    } else {
        $msg = "ERROR: Movimiento no encontrado.";
    }

    header("Location: index.php?msg=" . urlencode($msg));
    exit;
} catch (Exception $e) {
    header("Location: index.php?msg=ERROR: " . urlencode($e->getMessage()));
    exit;
}
?>
