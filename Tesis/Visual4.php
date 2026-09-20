<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<title>SID - Panel Padre de Familia</title>

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

.student-title{
font-size:24px;
font-weight:bold;
color:#1f4e79;
margin-bottom:20px;
}

.grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:20px;
}

.card{
background:white;
padding:20px;
border-radius:10px;
border:1px solid #ddd;
}

.card h3{
margin-bottom:15px;
color:#1f4e79;
}

.score{
font-size:40px;
font-weight:bold;
color:#1f4e79;
margin-bottom:10px;
}

.level{
display:inline-block;
padding:6px 10px;
border-radius:6px;
background:#f0f3f7;
border:1px solid #ddd;
margin-bottom:10px;
}

.recommendation{
margin-bottom:10px;
color:#444;
}

.progress{
margin-bottom:20px;
}

.progress-bar{
width:100%;
height:10px;
background:#ddd;
border-radius:10px;
overflow:hidden;
margin-bottom:10px;
}

.progress-bar div{
height:100%;
width:60%;
background:#2f628f;
}

.progress-text{
color:#666;
font-size:14px;
}

</style>

</head>

<body>

<div class="layout">

<aside class="sidebar">

<div class="brand">SID</div>

<div class="menu">
<div>Inicio</div>
<div>Progreso</div>
<div>Evaluaciones</div>
<div>Recomendaciones</div>
</div>

</aside>

<main class="content">

<div class="topbar">

<div>Padre de Familia</div>
<div>Alumno: Juan Pérez</div>

</div>

<div class="inner">

<div class="student-title">
Resumen del alumno
</div>

<div class="progress">

<div class="progress-bar">
<div></div>
</div>

<div class="progress-text">
Progreso: 3 de 5 actividades completadas
</div>

</div>

<div class="grid">

<div class="card">

<h3>Resultado diagnóstico</h3>

<div class="score">75</div>

<div class="level">
Nivel: Medio
</div>

<p>
El alumno presenta un desempeño adecuado en general,
pero se recomienda reforzar lectura y razonamiento matemático.
</p>

</div>

<div class="card">

<h3>Recomendaciones</h3>

<p class="recommendation">
• Leer al menos 15 minutos diarios.
</p>

<p class="recommendation">
• Practicar ejercicios matemáticos básicos.
</p>

<p class="recommendation">
• Completar las actividades de refuerzo sugeridas por el docente.
</p>

</div>

</div>

</div>

</main>

</div>

</body>
</html>