<?php

require_once __DIR__ . '/mpdf/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf();

$html = "
<h1 style='text-align:center;'>Reporte de Estudiantes</h1>

<table border='1' style='width:100%; border-collapse: collapse; text-align:center;'>

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

$mpdf->WriteHTML($html);

$mpdf->Output();