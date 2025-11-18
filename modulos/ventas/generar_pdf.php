<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

// Incluir FPDF (descargar manualmente si no funciona composer)
require '../../vendor/setasign/fpdf/fpdf.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die('ID de venta no especificado');
}

$pdo = conectarDB();

// Obtener datos de la venta
$stmt = $pdo->prepare("SELECT v.*, c.nombre as cliente_nombre, c.direccion, c.telefono,
    u.nombre as usuario_nombre
    FROM ventas v
    JOIN clientes c ON v.cliente_id = c.id
    JOIN usuarios u ON v.usuario_id = u.id
    WHERE v.id = ?");
$stmt->execute([$id]);
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    die('Venta no encontrada');
}

// Obtener detalle de la venta
$stmt_detalle = $pdo->prepare("SELECT dv.*, th.tipo, th.presentacion
    FROM detalle_venta dv
    JOIN tipos_huevos th ON dv.producto_id = th.id
    WHERE dv.venta_id = ?");
$stmt_detalle->execute([$id]);
$detalles = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);

// Crear PDF
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'Factura de Venta - Huevos Kikes',0,1,'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Página '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// Datos de la empresa
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Huevos Kikes SCM',0,1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,6,'Dirección: [Dirección de la empresa]',0,1);
$pdf->Cell(0,6,'Teléfono: [Teléfono de la empresa]',0,1);
$pdf->Cell(0,6,'Email: [Email de la empresa]',0,1);
$pdf->Ln(10);

// Datos de la factura
$pdf->SetFont('Arial','B',12);
$pdf->Cell(95,8,'Factura N°: ' . str_pad($venta['id'], 6, '0', STR_PAD_LEFT),1);
$pdf->Cell(95,8,'Fecha: ' . date('d/m/Y H:i', strtotime($venta['fecha_venta'])),1,1);
$pdf->Ln(5);

// Datos del cliente
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'Datos del Cliente:',0,1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,6,'Nombre: ' . $venta['cliente_nombre'],0,1);
$pdf->Cell(0,6,'Dirección: ' . $venta['direccion'],0,1);
$pdf->Cell(0,6,'Teléfono: ' . $venta['telefono'],0,1);
$pdf->Cell(0,6,'Teléfono: ' . $venta['telefono'],0,1);
$pdf->Ln(5);

// Vendedor
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'Vendedor: ' . $venta['usuario_nombre'],0,1);
$pdf->Ln(10);

// Detalle de productos
$pdf->SetFont('Arial','B',10);
$pdf->Cell(40,8,'Producto',1);
$pdf->Cell(30,8,'Presentación',1);
$pdf->Cell(25,8,'Cantidad',1);
$pdf->Cell(30,8,'Precio Unit.',1);
$pdf->Cell(30,8,'Subtotal',1,1);

$pdf->SetFont('Arial','',10);
foreach ($detalles as $detalle) {
    $pdf->Cell(40,8,'Tipo ' . $detalle['tipo'],1);
    $pdf->Cell(30,8,$detalle['presentacion'],1);
    $pdf->Cell(25,8,$detalle['cantidad_cubetas'] . ' cubetas',1);
    $pdf->Cell(30,8,'$' . number_format($detalle['precio_unitario'], 0, ',', '.'),1);
    $pdf->Cell(30,8,'$' . number_format($detalle['subtotal'], 0, ',', '.'),1,1);
}

// Total
$pdf->Ln(5);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(125,10,'TOTAL:',0,0,'R');
$pdf->Cell(30,10,'$' . number_format($venta['total'], 0, ',', '.'),1,1,'R');

// Pie de página
$pdf->Ln(20);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6,'Gracias por su compra. Esta factura es un comprobante válido de la transacción realizada.',0,'C');

// Salida del PDF
$pdf->Output('D', 'Factura_Venta_' . $venta['id'] . '.pdf');
?>
