<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

$pdo = conectarDB();

// Obtener tipos de huevos
$tipos = $pdo->query("SELECT * FROM tipos_huevos ORDER BY tipo")->fetchAll(PDO::FETCH_ASSOC);

// Calcular inventario actual por tipo
$inventario = [];
foreach ($tipos as $tipo) {
    $stmt = $pdo->prepare("SELECT
        SUM(CASE WHEN tipo_movimiento = 'entrada' THEN cantidad_cubetas ELSE 0 END) -
        SUM(CASE WHEN tipo_movimiento = 'salida' THEN cantidad_cubetas ELSE 0 END) as total_cubetas
        FROM inventarios WHERE tipo_huevo_id = ?");
    $stmt->execute([$tipo['id']]);
    $total = $stmt->fetchColumn() ?: 0;
    $inventario[$tipo['id']] = [
        'tipo' => $tipo,
        'total_cubetas' => $total,
        'total_unidades' => $total * 30,
        'valor_total' => $total * $tipo['precio_por_cubeta']
    ];
}

// Obtener movimientos
$movimientos = $pdo->query("SELECT i.*, th.tipo, th.precio_por_cubeta
    FROM inventarios i
    JOIN tipos_huevos th ON i.tipo_huevo_id = th.id
    ORDER BY i.fecha DESC")->fetchAll(PDO::FETCH_ASSOC);

// Generar CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inventario_huevos_' . date('Y-m-d_H-i-s') . '.csv');

$output = fopen('php://output', 'w');

// Encabezados
fputcsv($output, ['Reporte de Inventario - Huevos Kikes SCM']);
fputcsv($output, ['Generado el', date('d/m/Y H:i:s')]);
fputcsv($output, []);

// Inventario actual
fputcsv($output, ['INVENTARIO ACTUAL']);
fputcsv($output, ['Tipo', 'Cubetas', 'Unidades', 'Precio por Cubeta', 'Valor Total']);
foreach ($inventario as $item) {
    fputcsv($output, [
        'Tipo ' . $item['tipo']['tipo'],
        $item['total_cubetas'],
        $item['total_unidades'],
        '$' . number_format($item['tipo']['precio_por_cubeta'], 0, ',', '.'),
        '$' . number_format($item['valor_total'], 0, ',', '.')
    ]);
}
fputcsv($output, []);

// Movimientos
fputcsv($output, ['MOVIMIENTOS']);
fputcsv($output, ['Fecha', 'Tipo', 'Movimiento', 'Cubetas', 'Precio por Cubeta', 'Valor', 'Descripción']);
foreach ($movimientos as $mov) {
    fputcsv($output, [
        date('d/m/Y H:i:s', strtotime($mov['fecha'])),
        'Tipo ' . $mov['tipo'],
        ucfirst($mov['tipo_movimiento']),
        $mov['cantidad_cubetas'],
        '$' . number_format($mov['precio_por_cubeta'], 0, ',', '.'),
        '$' . number_format($mov['cantidad_cubetas'] * $mov['precio_por_cubeta'], 0, ',', '.'),
        $mov['descripcion'] ?: 'Sin descripción'
    ]);
}

fclose($output);
exit;
?>
