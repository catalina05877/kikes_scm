<?php
// CREACIÓN (CREATE) Y EDICIÓN (UPDATE) - Lógica de Procesamiento
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.php");
    exit;
}

$pdo = conectarDB();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$es_edicion = !empty($id);

// Datos del formulario
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
$identificacion = filter_input(INPUT_POST, 'identificacion', FILTER_SANITIZE_STRING);
$telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
$direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);
$ubicacion = filter_input(INPUT_POST, 'ubicacion', FILTER_SANITIZE_STRING);

if (empty($ubicacion)) {
    header("Location: index.php?msg=ERROR: Debe seleccionar una ubicación en el mapa.");
    exit;
}

try {
    if ($es_edicion) {
        // OPERACIÓN: UPDATE (Actualizar)
        $sql = "UPDATE clientes SET nombre = :nombre, identificacion = :identificacion, telefono = :telefono, direccion = :direccion, latitud = :latitud, longitud = :longitud WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $coords = explode(',', $ubicacion);
        $stmt->execute([
            'nombre' => $nombre,
            'identificacion' => $identificacion,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'latitud' => $coords[0],
            'longitud' => $coords[1],
            'id' => $id
        ]);
        $msg = "Cliente ID {$id} actualizado exitosamente.";

    } else {
        // OPERACIÓN: INSERT (Crear)
        $sql = "INSERT INTO clientes (nombre, identificacion, telefono, direccion, latitud, longitud) VALUES (:nombre, :identificacion, :telefono, :direccion, :latitud, :longitud)";
        $stmt = $pdo->prepare($sql);
        $coords = explode(',', $ubicacion);
        $stmt->execute([
            'nombre' => $nombre,
            'identificacion' => $identificacion,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'latitud' => $coords[0],
            'longitud' => $coords[1]
        ]);
        $msg = "Cliente '{$nombre}' registrado exitosamente.";
    }

    header("Location: index.php?msg=" . urlencode($msg));
    exit;

} catch (PDOException $e) {
    // Manejo de error de identificación duplicada (Código 23000)
    $error_msg = ($e->getCode() == '23000') ? "ERROR: La identificación {$identificacion} ya se encuentra registrada." : "ERROR: Fallo en la base de datos.";
    header("Location: index.php?msg=" . urlencode($error_msg));
    exit;
}
?>
