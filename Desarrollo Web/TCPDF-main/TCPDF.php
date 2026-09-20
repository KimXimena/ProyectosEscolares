<?php
require_once('TCPDF-main/TCPDF-main/tcpdf.php');

$pdf = new TCPDF();

$pdf->AddPage();

$pdf->SetFont('helvetica', '', 14);

$html = "
<h1 style='text-align:center;'>Reporte de Estudiantes</h1>

<table border='1' cellpadding='5'>
<tr>
    <th>Nombre</th>
    <th>Matricula</th>
    <th>Promedio</th>
</tr>

<tr>
    <td>Juan Perez</td>
    <td>2023001</td>
    <td>9.2</td>
</tr>

<tr>
    <td>Maria Lopez</td>
    <td>2023002</td>
    <td>8.8</td>
</tr>

<tr>
    <td>Carlos Ruiz</td>
    <td>2023003</td>
    <td>9.5</td>
</tr>
</table>
";

$pdf->writeHTML($html);

$pdf->Output();
?>