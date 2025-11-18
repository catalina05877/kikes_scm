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
$nit = filter_input(INPUT_POST, 'nit', FILTER_SANITIZE_STRING);
$telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
$direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);

$ruta_base_uploads = '../../uploads/proveedores/';

// Inicializar variables de archivo
$ruta_db_rut = null;
$ruta_db_cc = null;
$mensajes_error = [];

// =======================================================
// FUNCIÓN PARA PROCESAR LA CARGA DE UN ARCHIVO
// =======================================================
function procesar_archivo($file_key, $tipo_doc, $nit, $es_edicion, $pdo, $id) {
    global $ruta_base_uploads;
    
    if (empty($_FILES[$file_key]['name'])) {
        // Si no se sube un archivo, y NO estamos en CREACIÓN (es Edición), se mantiene el archivo actual
        if ($es_edicion) {
            $stmt = $pdo->prepare("SELECT ruta_{$tipo_doc} FROM proveedores WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetchColumn(); // Retorna la ruta existente
        }
        return null; // En creación, si es requerido, fallará más adelante
    }

    $nombre_base = $nit . '_' . $tipo_doc;
    $extension = pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION);
    $nombre_final = $nombre_base . '.' . $extension;
    $ruta_final = $ruta_base_uploads . $nombre_final;

    if ($_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        return "ERROR: Falló la subida del archivo {$tipo_doc}.";
    }

    if (!move_uploaded_file($_FILES[$file_key]['tmp_name'], $ruta_final)) {
        return "ERROR: No se pudo mover el archivo {$tipo_doc} al servidor.";
    }

    // Retorna la ruta relativa para guardar en la base de datos
    return 'uploads/proveedores/' . $nombre_final;
}

// =======================================================
// PROCESAMIENTO
// =======================================================

// Procesar RUT
$resultado_rut = procesar_archivo('rut', 'rut', $nit, $es_edicion, $pdo, $id);
if (is_string($resultado_rut) && strpos($resultado_rut, 'ERROR') !== false) {
    $mensajes_error[] = $resultado_rut;
} else {
    $ruta_db_rut = $resultado_rut;
}

// Procesar Cámara y Comercio
$resultado_cc = procesar_archivo('camara_comercio', 'camara_comercio', $nit, $es_edicion, $pdo, $id);
if (is_string($resultado_cc) && strpos($resultado_cc, 'ERROR') !== false) {
    $mensajes_error[] = $resultado_cc;
} else {
    $ruta_db_cc = $resultado_cc;
}

// Validación final de archivos en CREACIÓN
if (!$es_edicion && (empty($ruta_db_rut) || empty($ruta_db_cc))) {
     $mensajes_error[] = "Debe cargar el RUT y la Cámara y Comercio.";
}

if (!empty($mensajes_error)) {
    die("<h1>Errores en el registro:</h1><ul><li>" . implode("</li><li>", $mensajes_error) . "</li></ul><a href='index.php'>Volver</a>");
}

try {
    if ($es_edicion) {
        // OPERACIÓN: UPDATE (Actualizar)
        $sql = "UPDATE proveedores SET nombre = :nombre, nit = :nit, telefono = :telefono, direccion = :direccion";
        
        if ($ruta_db_rut) $sql .= ", ruta_rut = :ruta_rut";
        if ($ruta_db_cc) $sql .= ", ruta_camara_comercio = :ruta_cc";
        
        $sql .= " WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $params = [
            'nombre' => $nombre,
            'nit' => $nit,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'id' => $id
        ];
        if ($ruta_db_rut) $params['ruta_rut'] = $ruta_db_rut;
        if ($ruta_db_cc) $params['ruta_cc'] = $ruta_db_cc;
        
        $stmt->execute($params);
        $msg = "Proveedor ID {$id} actualizado exitosamente.";
        
    } else {
        // OPERACIÓN: INSERT (Crear)
        $sql = "INSERT INTO proveedores (nombre, nit, telefono, direccion, ruta_rut, ruta_camara_comercio) 
                VALUES (:nombre, :nit, :telefono, :direccion, :ruta_rut, :ruta_cc)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'nit' => $nit,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'ruta_rut' => $ruta_db_rut,
            'ruta_cc' => $ruta_db_cc
        ]);
        $msg = "Proveedor '{$nombre}' registrado exitosamente.";
    }

    header("Location: index.php?msg=" . urlencode($msg));
    exit;

} catch (PDOException $e) {
    // Manejo de error de NIT duplicado (Código 23000)
    $error_msg = ($e->getCode() == '23000') ? "ERROR: El NIT {$nit} ya se encuentra registrado." : "ERROR: Fallo en la base de datos.";
    header("Location: index.php?msg=" . urlencode($error_msg));
    exit;
}
?>