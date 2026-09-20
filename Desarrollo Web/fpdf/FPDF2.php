<?php

require('fpdf186/fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();

// Título
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Reporte de Estudiantes', 0, 1, 'C');

$pdf->Ln(10);

// Encabezados de tabla
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, 'Nombre', 1, 0, 'C');
$pdf->Cell(60, 10, 'Matricula', 1, 0, 'C');
$pdf->Cell(60, 10, 'Promedio', 1, 1, 'C');

// Datos
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 10, 'Juan Perez', 1, 0, 'C');
$pdf->Cell(60, 10, '2023001', 1, 0, 'C');
$pdf->Cell(60, 10, '9.2', 1, 1, 'C');

$pdf->Cell(60, 10, 'Maria Lopez', 1, 0, 'C');
$pdf->Cell(60, 10, '2023002', 1, 0, 'C');
$pdf->Cell(60, 10, '8.8', 1, 1, 'C');

$pdf->Cell(60, 10, 'Carlos Ruiz', 1, 0, 'C');
$pdf->Cell(60, 10, '2023003', 1, 0, 'C');
$pdf->Cell(60, 10, '9.5', 1, 1, 'C');

$pdf->Output();

?>