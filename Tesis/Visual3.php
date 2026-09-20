<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<title>SID - Panel Director</title>

<style>

body{
background:#eef3f8;
font-family:Segoe UI, Arial;
margin:0;
}

.layout{
display:grid;
grid-template-columns:220px 1fr;
min-height:100vh;
}

.sidebar{
background:#1f4e79;
color:white;
padding:20px;
}

.brand{
font-size:22px;
font-weight:bold;
margin-bottom:20px;
}

.menu div{
margin-bottom:10px;
padding:10px;
border-radius:6px;
}

.menu div:hover{
background:rgba(255,255,255,0.15);
}

.content{
background:#f8fbfd;
}

.topbar{
background:white;
padding:15px;
border-bottom:1px solid #ddd;
display:flex;
justify-content:space-between;
}

.inner{
padding:20px;
}

.stats{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:15px;
margin-bottom:20px;
}

.stat{
background:white;
padding:15px;
border-radius:10px;
border:1px solid #ddd;
}

.stat h4{
font-size:13px;
color:#777;
}

.stat .value{
font-size:28px;
font-weight:bold;
color:#1f4e79;
}

.panel{
background:white;
border-radius:10px;
border:1px solid #ddd;
overflow:hidden;
}

.panel-header{
padding:15px;
font-weight:bold;
border-bottom:1px solid #eee;
}

table{
width:100%;
border-collapse:collapse;
}

th,td{
padding:12px;
border-bottom:1px solid #eee;
text-align:left;
}

th{
background:#f5f7fa;
}

.low{color:green;}
.medium{color:orange;}
.high{color:red;}

</style>

</head>

<body>

<div class="layout">

<aside class="sidebar">

<div class="brand">SID</div>

<div class="menu">
<div>Dashboard</div>
<div>Escuelas</div>
<div>Rendimiento</div>
<div>Alertas</div>
<div>Reportes</div>
</div>

</aside>

<main class="content">

<div class="topbar">

<div>Director</div>
<div>Ciclo 2025</div>

</div>

<div class="inner">

<div class="stats">

<div class="stat">
<h4>Escuelas registradas</h4>
<div class="value">24</div>
</div>

<div class="stat">
<h4>Total alumnos</h4>
<div class="value">12,450</div>
</div>

<div class="stat">
<h4>Promedio estatal</h4>
<div class="value">74</div>
</div>

<div class="stat">
<h4>Alumnos en riesgo</h4>
<div class="value">1,230</div>
</div>

</div>

<div class="panel">

<div class="panel-header">
Rendimiento por escuela
</div>

<table>

<thead>
<tr>
<th>Escuela</th>
<th>Municipio</th>
<th>Promedio</th>
<th>Riesgo</th>
</tr>
</thead>

<tbody>

<tr>
<td>Primaria Benito Juárez</td>
<td>Saltillo</td>
<td>76</td>
<td class="low">Bajo</td>
</tr>

<tr>
<td>Primaria Miguel Hidalgo</td>
<td>Saltillo</td>
<td>71</td>
<td class="medium">Medio</td>
</tr>

<tr>
<td>Primaria Independencia</td>
<td>Torreón</td>
<td>65</td>
<td class="high">Alto</td>
</tr>

</tbody>

</table>

</div>

</div>

</main>

</div>

</body>
</html>