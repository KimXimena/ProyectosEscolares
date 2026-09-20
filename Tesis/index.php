<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<title>SID - Sistema de Integración Diagnóstica</title>

<link rel="stylesheet" href="estilos.css">

<style>

body{
display:flex;
justify-content:center;
align-items:center;
height:100vh;
background:#eef3f8;
}

.menu-panel{
background:white;
padding:40px;
border-radius:12px;
border:1px solid #d8e1ea;
box-shadow:0 6px 18px rgba(30,55,90,0.08);
text-align:center;
}

.menu-panel h1{
color:#1f4e79;
margin-bottom:30px;
}

.links{
display:flex;
flex-direction:column;
gap:12px;
}

.links a{
text-decoration:none;
padding:12px 20px;
border-radius:8px;
border:1px solid #d8e1ea;
background:#ffffff;
color:#1f4e79;
font-weight:600;
}

.links a:hover{
background:#f0f5fa;
}

</style>

</head>

<body>

<div class="menu-panel">

<h1>Sistema SID</h1>

<div class="links">

<a href="Visual3.php">Panel Docente</a>

<a href="Visual1.php">Panel Alumno</a>

<a href="Visual2.php">Panel Director</a>

<a href="Visual4.php">Panel Padre de Familia</a>

</div>

</div>

</body>
</html>