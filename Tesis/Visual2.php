<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SID - Panel Docente</title>
  <style>
    :root{
      --primary:#1f4e79;
      --primary-soft:#2f628f;
      --bg:#eef3f8;
      --panel:#ffffff;
      --line:#d8e1ea;
      --text:#243748;
      --muted:#6c7f92;
      --success:#4f7d61;
      --warning:#a87a2c;
      --danger:#9a4a4a;
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

    .top-actions{
      display:flex;
      align-items:center;
      gap:12px;
    }

    .avatar{
      width:38px;
      height:38px;
      border-radius:50%;
      background:#c8d4df;
      border:1px solid #b9c6d2;
    }

    .chip{
      padding:8px 12px;
      border-radius:8px;
      border:1px solid var(--line);
      background:#fff;
      font-size:13px;
      color:var(--muted);
      font-weight:600;
    }

    .inner{
      padding:20px;
      flex:1;
    }

    .stats{
      display:grid;
      grid-template-columns:repeat(4,1fr);
      gap:14px;
      margin-bottom:18px;
    }

    .stat{
      background:#ffffff;
      border:1px solid var(--line);
      border-radius:12px;
      padding:16px;
      box-shadow:0 2px 8px rgba(30,55,90,0.04);
    }

    .stat h4{
      font-size:13px;
      color:var(--muted);
      margin-bottom:10px;
      font-weight:600;
    }

    .stat .value{
      font-size:28px;
      font-weight:700;
      color:var(--primary);
    }

    .panel{
      background:#ffffff;
      border:1px solid var(--line);
      border-radius:12px;
      box-shadow:0 2px 8px rgba(30,55,90,0.04);
      overflow:hidden;
    }

    .panel-header{
      padding:14px 18px;
      border-bottom:1px solid #e6edf3;
      font-size:16px;
      font-weight:700;
      color:var(--primary);
    }

    .table{
      width:100%;
      border-collapse:collapse;
    }

    .table th,
    .table td{
      text-align:left;
      padding:14px 16px;
      border-bottom:1px solid #eef3f7;
      font-size:14px;
    }

    .table th{
      background:#f8fbfd;
      color:#5f7387;
      font-weight:700;
    }

    .status{
      display:inline-block;
      padding:6px 10px;
      border-radius:999px;
      font-size:12px;
      font-weight:700;
    }

    .status.low{
      background:#e8f1eb;
      color:var(--success);
    }

    .status.medium{
      background:#f5eedf;
      color:var(--warning);
    }

    .status.high{
      background:#f5e7e7;
      color:var(--danger);
    }

    @media (max-width: 850px){
      .layout{
        grid-template-columns:1fr;
      }

      .menu{
        display:grid;
        grid-template-columns:1fr 1fr;
      }

      .stats{
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
        <small>Sistema de Integración Diagnóstica</small>
      </div>

      <nav class="menu">
        <div class="item active">Dashboard</div>
        <div class="item">Evaluación</div>
        <div class="item">Habilidades SISAT</div>
        <div class="item">Estilo de aprendizaje</div>
        <div class="item">Alumnos</div>
        <div class="item">Reportes</div>
        <div class="item">Alertas</div>
      </nav>
    </aside>

    <main class="content">
      <div class="topbar">
        <div class="title">Docente</div>
        <div class="top-actions">
          <div class="avatar"></div>
          <div class="chip">Grupo 6° A</div>
        </div>
      </div>

      <div class="inner">
        <div class="stats">
          <div class="stat">
            <h4>Mis alumnos</h4>
            <div class="value">28</div>
          </div>

          <div class="stat">
            <h4>En riesgo</h4>
            <div class="value">5</div>
          </div>

          <div class="stat">
            <h4>Promedio diagnóstico</h4>
            <div class="value">72</div>
          </div>

          <div class="stat">
            <h4>Nivel de lectura</h4>
            <div class="value">Medio</div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-header">Lista de alumnos</div>

          <table class="table">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Grado</th>
                <th>Puntaje</th>
                <th>Riesgo</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Ana López</td>
                <td>6° A</td>
                <td>73</td>
                <td><span class="status low">Bajo</span></td>
              </tr>
              <tr>
                <td>Carlos Ruiz</td>
                <td>6° A</td>
                <td>72</td>
                <td><span class="status medium">Medio</span></td>
              </tr>
              <tr>
                <td>Sofía Gómez</td>
                <td>6° A</td>
                <td>72</td>
                <td><span class="status high">Alto</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

</body>
</html>