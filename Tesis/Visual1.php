<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SID - Panel Alumno</title>
  <style>
    :root{
      --primary:#1f4e79;
      --primary-soft:#2f628f;
      --bg:#eef3f8;
      --panel:#ffffff;
      --line:#d8e1ea;
      --text:#243748;
      --muted:#6c7f92;
      --shadow:0 6px 18px rgba(30, 55, 90, 0.08);
    }

    *{
      box-sizing:border-box;
      margin:0;
      padding:0;
      font-family:"Segoe UI", Arial, sans-serif;
    }

    body{
      background:var(--bg);
      color:var(--text);
    }

    .layout{
      display:grid;
      grid-template-columns:220px 1fr;
      min-height:100vh;
    }

    .sidebar{
      background:var(--primary);
      color:white;
      padding:22px 16px;
    }

    .brand{
      font-size:22px;
      font-weight:700;
      margin-bottom:28px;
    }

    .brand small{
      display:block;
      font-size:12px;
      font-weight:400;
      opacity:.85;
      margin-top:4px;
    }

    .menu{
      display:flex;
      flex-direction:column;
      gap:8px;
    }

    .item{
      padding:12px 14px;
      border-radius:10px;
      font-size:14px;
      font-weight:600;
      color:#eef5fb;
      background:transparent;
      border:1px solid transparent;
    }

    .item.active{
      background:rgba(255,255,255,0.12);
      border-color:rgba(255,255,255,0.14);
    }

    .content{
      background:#f8fbfd;
      display:flex;
      flex-direction:column;
    }

    .topbar{
      height:72px;
      border-bottom:1px solid var(--line);
      background:#ffffff;
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:0 22px;
    }

    .title{
      font-size:24px;
      font-weight:700;
      color:var(--primary);
    }

    .avatar{
      width:38px;
      height:38px;
      border-radius:50%;
      background:#c8d4df;
      border:1px solid #b9c6d2;
    }

    .inner{
      padding:20px;
      flex:1;
    }

    .student-welcome{
      font-size:26px;
      font-weight:700;
      color:var(--primary);
      margin-bottom:18px;
    }

    .actions{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:14px;
      margin-bottom:18px;
    }

    .btn{
      border:1px solid var(--line);
      background:#ffffff;
      color:var(--primary);
      padding:16px;
      border-radius:12px;
      font-size:15px;
      font-weight:700;
      text-align:center;
    }

    .progress-wrap{
      background:#ffffff;
      border:1px solid var(--line);
      border-radius:12px;
      padding:16px;
      margin-bottom:18px;
      box-shadow:0 2px 8px rgba(30,55,90,0.04);
    }

    .progress-bar{
      width:100%;
      height:10px;
      background:#e4ebf2;
      border-radius:10px;
      overflow:hidden;
      margin-bottom:10px;
    }

    .progress-bar div{
      width:60%;
      height:100%;
      background:var(--primary-soft);
    }

    .progress-text{
      color:var(--muted);
      font-size:14px;
      font-weight:600;
    }

    .student-grid{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:16px;
    }

    .box{
      background:#ffffff;
      border:1px solid var(--line);
      border-radius:12px;
      padding:18px;
      box-shadow:0 2px 8px rgba(30,55,90,0.04);
    }

    .box h3{
      font-size:16px;
      color:var(--primary);
      margin-bottom:14px;
      font-weight:700;
    }

    .result-number{
      font-size:36px;
      font-weight:700;
      color:var(--primary);
      margin-bottom:10px;
    }

    .level{
      display:inline-block;
      padding:6px 10px;
      border-radius:8px;
      border:1px solid var(--line);
      background:#f8fbfd;
      color:var(--muted);
      font-size:13px;
      font-weight:700;
      margin-bottom:12px;
    }

    .recommendation{
      margin-bottom:12px;
      color:#45596d;
      line-height:1.5;
      font-size:14px;
    }

    @media (max-width: 850px){
      .layout{
        grid-template-columns:1fr;
      }

      .menu{
        display:grid;
        grid-template-columns:1fr 1fr;
      }

      .actions,
      .student-grid{
        grid-template-columns:1fr;
      }
    }
  </style>
</head>
<body>

  <div class="layout">
    <aside class="sidebar">
      <div class="brand">
        SID
        <small>Panel del alumno</small>
      </div>

      <nav class="menu">
        <div class="item active">Inicio</div>
        <div class="item">Evaluación</div>
        <div class="item">Mi resultado</div>
        <div class="item">Recomendaciones</div>
      </nav>
    </aside>

    <main class="content">
      <div class="topbar">
        <div class="title">Alumno</div>
        <div class="avatar"></div>
      </div>

      <div class="inner">
        <div class="student-welcome">Hola, Juan</div>

        <div class="actions">
          <div class="btn">Realizar evaluación diagnóstica</div>
          <div class="btn">Responder test de estilos VARK</div>
        </div>

        <div class="progress-wrap">
          <div class="progress-bar">
            <div></div>
          </div>
          <div class="progress-text">Progreso: 3 de 5 actividades completadas</div>
        </div>

        <div class="student-grid">
          <div class="box">
            <h3>Mi resultado</h3>
            <div class="result-number">75</div>
            <div class="level">Nivel: Medio</div>
            <p class="recommendation">
              Tu desempeño general es adecuado, pero todavía puedes reforzar
              lectura y razonamiento matemático.
            </p>
          </div>

          <div class="box">
            <h3>Recomendaciones</h3>
            <p class="recommendation">• Repasar lectura 15 minutos al día.</p>
            <p class="recommendation">• Practicar ejercicios matemáticos básicos.</p>
            <p class="recommendation">• Completar el cuestionario de estilo de aprendizaje.</p>
          </div>
        </div>
      </div>
    </main>
  </div>

</body>
</html>