<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';
require '../../vendor/autoload.php'; // Para TCPDF o FPDF

use \TCPDF;

// Crear instancia de TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configurar PDF
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Huevos Kikes SCM');
$pdf->SetTitle('Factura de Compra');
$pdf->SetSubject('Factura de Compra');
$pdf->SetKeywords('Factura, Compra, Huevos');

// Configurar márgenes
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Agregar página
$pdf->AddPage();

// Obtener datos de la compra
$pdo = conectarDB();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$stmt = $pdo->prepare("SELECT c.*, p.nombre as proveedor_nombre, p.nit, p.telefono, p.direccion, u.nombre as usuario_nombre
    FROM compras c
    JOIN proveedores p ON c.proveedor_id = p.id
    JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.id = ?");
$stmt->execute([$id]);
$compra = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener detalles de la compra
$stmt = $pdo->prepare("SELECT cd.*, th.tipo, th.presentacion
    FROM compra_detalles cd
    JOIN tipos_huevos th ON cd.tipo_huevo_id = th.id
    WHERE cd.compra_id = ?");
$stmt->execute([$id]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contenido del PDF
$html = '
<style>
    h1 {
        color: #D2B48C;
        text-align: center;
        font-size: 24px;
        margin-bottom: 20px;
    }
    .header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        padding-bottom: 10px;
        border-bottom: 2px solid #FFD700;
    }
    .proveedor-info {
        margin-bottom: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    th, td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    th {
        background-color: #FFD700;
        color: #333;
        font-weight: bold;
    }
    .total-row {
        background-color: #FFF8DC;
        font-weight: bold;
    }
</style>

<h1>Factura de Compra - Huevos Kikes SCM</h1>

<div class="header">
    <div>
        <strong>Factura N°:</strong> ' . htmlspecialchars($compra['id']) . '<br>
        <strong>Fecha:</strong> ' . htmlspecialchars(date('d/m/Y H:i', strtotime($compra['fecha_compra']))) . '<br>
        <strong>Usuario:</strong> ' . htmlspecialchars($compra['usuario_nombre']) . '<br>
        <strong>Medio de Pago:</strong> ' . htmlspecialchars($compra['medio_pago']) . '
    </div>
</div>

<div class="proveedor-info">
    <h3>Datos del Proveedor</h3>
    <strong>Nombre:</strong> ' . htmlspecialchars($compra['proveedor_nombre']) . '<br>
    <strong>NIT:</strong> ' . htmlspecialchars($compra['nit']) . '<br>
    <strong>Teléfono:</strong> ' . htmlspecialchars($compra['telefono']) . '<br>
    <strong>Dirección:</strong> ' . htmlspecialchars($compra['direccion']) . '
</div>

<table>
    <thead>
        <tr>
            <th>Tipo de Huevo</th>
            <th>Cantidad</th>
            <th>Precio Unitario</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>';

foreach ($detalles as $detalle) {
    $html .= '
        <tr>
            <td>' . htmlspecialchars($detalle['tipo'] . ' - ' . $detalle['presentacion']) . '</td>
            <td>' . htmlspecialchars($detalle['cantidad']) . ' cubetas</td>
            <td>$' . number_format($detalle['precio_unitario'], 0, ',', '.') . '</td>
            <td>$' . number_format($detalle['cantidad'] * $detalle['precio_unitario'], 0, ',', '.') . '</td>
        </tr>';
}

$html .= '
        <tr class="total-row">
            <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
            <td>$' . number_format($compra['total'], 0, ',', '.') . '</td>
        </tr>
    </tbody>
</table>';

// Escribir HTML en el PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Salida del PDF
$pdf->Output('factura_compra_' . $compra['id'] . '.pdf', 'I');
?>
