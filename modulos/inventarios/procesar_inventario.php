<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

$pdo = conectarDB();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$tipo_huevo_id = filter_input(INPUT_POST, 'tipo_huevo_id', FILTER_VALIDATE_INT);
$tipo_movimiento = filter_input(INPUT_POST, 'tipo_movimiento', FILTER_SANITIZE_STRING);
$cantidad_cubetas = filter_input(INPUT_POST, 'cantidad_cubetas', FILTER_VALIDATE_INT);
$descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);

if (!$tipo_huevo_id || !$tipo_movimiento || !$cantidad_cubetas || $cantidad_cubetas < 1) {
    header("Location: formulario.php?msg=ERROR: Datos inválidos.");
    exit;
}

if (!in_array($tipo_movimiento, ['entrada', 'salida'])) {
    header("Location: formulario.php?msg=ERROR: Tipo de movimiento inválido.");
    exit;
}

try {
    if ($id) {
        // Editar movimiento existente
        $stmt = $pdo->prepare("UPDATE inventarios SET tipo_huevo_id = ?, cantidad_cubetas = ?, tipo_movimiento = ?, descripcion = ? WHERE id = ?");
        $stmt->execute([$tipo_huevo_id, $cantidad_cubetas, $tipo_movimiento, $descripcion, $id]);
        $msg = "Movimiento actualizado exitosamente.";
    } else {
        // Crear nuevo movimiento
        $stmt = $pdo->prepare("INSERT INTO inventarios (tipo_huevo_id, cantidad_cubetas, tipo_movimiento, descripcion) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tipo_huevo_id, $cantidad_cubetas, $tipo_movimiento, $descripcion]);
        $msg = "Movimiento registrado exitosamente.";
    }

    header("Location: index.php?msg=" . urlencode($msg));
    exit;
} catch (Exception $e) {
    header("Location: formulario.php?msg=ERROR: " . urlencode($e->getMessage()));
    exit;
}
?>
