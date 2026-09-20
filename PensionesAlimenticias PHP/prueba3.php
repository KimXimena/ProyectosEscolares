<?php /* Archivo sin lógica PHP: interfaz principal */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Sistema de Pensión Alimenticia y Juicio Mercantil</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    *{box-sizing:border-box}
    body{margin:0;font-family:"Segoe UI",system-ui,sans-serif;background:#f5f7fb;color:#222}
    .topbar{background:#fff;border-bottom:3px solid #e5e7eb;padding:1rem 2.5rem .75rem}
    .topbar-title{font-size:1.5rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
    .topbar-title span{color:#ff007c}
    .topbar-subtitle{font-size:.8rem;color:#9ca3af;margin-top:.1rem}
    .wrapper{max-width:1200px;margin:0 auto;padding:0 1.5rem 2.5rem}
    .filter-panel,.section-block{background:#fff;border:1px solid #e5e7eb;border-radius:4px;box-shadow:0 1px 2px rgba(15,23,42,.04)}
    .filter-panel{background:#f9fafc;padding:1rem 1.25rem 1.25rem;margin-top:1rem}
    .filter-grid{display:grid;grid-template-columns:2.2fr 1.6fr 1.6fr 1.2fr 1.2fr;gap:.75rem}
    .field{margin-bottom:.45rem}
    .field label{display:block;font-size:.75rem;font-weight:600;text-transform:uppercase;color:#6b7280;margin-bottom:.15rem}
    input[type="text"],input[type="date"],input[type="number"],select{width:100%;border-radius:3px;border:1px solid #d1d5db;padding:.32rem .45rem;font-size:.82rem;font-family:inherit;background:#fff}
    input[type="file"]{width:100%;font-size:.8rem}
    .btn,button{border-radius:3px;border:none;padding:.45rem .85rem;font-size:.82rem;cursor:pointer;font-weight:600;font-family:inherit}
    .btn-primary{background:#00a0ff;color:#fff}
    .btn-secondary{background:#f3f4f6;color:#111827;border:1px solid #d1d5db;text-decoration:none;display:inline-block}
    .filter-actions{margin-top:.9rem;text-align:center}
    .section-block{margin-top:1.5rem}
    .section-header{background:#f5f7fb;border-bottom:1px solid #e5e7eb;padding:.6rem 1.2rem;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:#4b5563}
    .section-body{padding:1rem 1.25rem 1.25rem}
    .grid{display:grid;gap:.8rem}
    .grid-2{grid-template-columns:repeat(2,minmax(0,1fr))}
    .section-subtitle{font-size:.9rem;font-weight:600;margin-bottom:.65rem;color:#111827}
    .helper{font-size:.78rem;color:#6b7280;margin-bottom:.8rem}
    .actions{margin-top:1rem;text-align:right}
    .row-2{display:grid;grid-template-columns:1fr 1fr;gap:.8rem}
    .subcard{border:1px solid #e5e7eb;border-radius:4px;padding:.8rem;background:#fff}
    .subhead{margin-bottom:.6rem}
    .subhead-title{font-weight:700;font-size:.85rem;color:#111827;text-transform:uppercase;letter-spacing:.06em}
    .radio-line{font-size:.8rem;text-transform:none!important;color:#222!important;font-weight:400!important;display:flex!important;align-items:center;gap:.35rem;margin-bottom:.25rem!important}
    #btnVerNomina,#btnCalcularDescuento{width:100%}
    #btnVerNomina{background:#f3f4f6;border:1px solid #d1d5db;color:#111827}
    #btnCalcularDescuento{background:#00a0ff;color:#fff}
    @media(max-width:980px){.filter-grid{grid-template-columns:1fr 1fr}}
    @media(max-width:880px){.grid-2,.row-2{grid-template-columns:1fr}}
    @media(max-width:640px){.filter-grid{grid-template-columns:1fr}}
  </style>
</head>

<body>

<div class="topbar">
  <div class="topbar-title">
    PENSIONES Y <span> JUICIO MERCANTIL.</span>
  </div>
  <div class="topbar-subtitle">
    Captura de movimientos de pensión alimenticia y juicio mercantil
  </div>
</div>

<div class="wrapper">

  <div class="filter-panel">
    <div class="filter-grid">

      <div class="field">
        <label>CURP o Nombre</label>
        <input type="text" id="buscarTrabajador" placeholder="Buscar trabajador por RFC / CURP / Nombre">
      </div>

      <div class="field">
        <label>Tipo de movimiento</label>
        <select id="tipoMovimientoClase" name="tipo_movimiento_clase">
          <option value="pension">Pensión alimenticia</option>
          <option value="juicio">Juicio mercantil</option>
        </select>
      </div>

      <div class="field">
        <label>Proceso</label>
        <select id="tipoMovimiento" name="tipo_movimiento">
          <option value="alta">Alta</option>
          <option value="cambio">Cambio</option>
          <option value="baja">Baja</option>
          <option value="reintegro">Reintegro</option>
        </select>
      </div>

      <div class="field">
        <label>Desde quincena</label>
        <input type="text" id="desdeQuincena" placeholder="2025-01">
      </div>

      <div class="field">
        <label>Hasta quincena</label>
        <input type="text" id="hastaQuincena" placeholder="2025-24">
      </div>

    </div>

    <div class="filter-actions">
      <button class="btn btn-primary" type="button" id="btnNuevaSolicitud">
        Nueva solicitud
      </button>
    </div>
  </div>

  <div class="section-block">
    <div class="section-header">CÉDULA · DATOS DEL TRABAJADOR</div>

    <div class="section-body">
      <div class="section-subtitle">1. Datos del trabajador</div>
      <div class="helper">Datos del trabajador de nómina sobre quien se aplicará el descuento.</div>

      <div class="grid grid-2">

        <div>
          <div class="field">
            <label>RFC del trabajador</label>
            <input type="text" id="rfcTrabajador" name="rfc_trabajador" placeholder="RFC del trabajador">
          </div>

          <div class="field">
            <label>CURP del trabajador</label>
            <input type="text" id="curpTrabajador" name="curp_trabajador" placeholder="CURP del trabajador">
          </div>

          <div class="field">
            <label>Nombre del trabajador</label>
            <input type="text" id="nombreTrabajador" name="nombre_trabajador" placeholder="Nombre completo del trabajador">
          </div>
        </div>

        <div>
          <div class="field">
            <label>Sostenimiento</label>

            <label class="radio-line">
              <input type="radio" name="sostenimiento" value="Federal"> Federal
            </label>

            <label class="radio-line">
              <input type="radio" name="sostenimiento" value="Estatal"> Estatal
            </label>

            <label class="radio-line">
              <input type="radio" name="sostenimiento" value="Burocrata"> Burocrata
            </label>
          </div>

          <div class="field">
            <label>Centro de trabajo</label>
            <input type="text" id="centroTrabajo" name="centro_trabajo" placeholder="Clave o nombre del CT">
          </div>

          <div class="field">
            <label>Consulta de nómina</label>
            <button type="button" id="btnVerNomina">Ver nómina</button>
          </div>

          <div class="field">
            <label>Realizar cálculo automático</label>
            <button type="button" id="btnCalcularDescuento">Calcular monto a descontar</button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <div class="section-block">
    <div class="section-header">CÉDULA · DATOS DEL BENEFICIARIO</div>

    <div class="section-body">
      <div class="section-subtitle">2. Datos del beneficiario</div>
      <div class="helper">Información de la persona que recibirá la pensión o, en su caso, del abogado para Juicio Mercantil.</div>

      <div class="grid grid-2">

        <div class="subcard">
          <div class="subhead">
            <div class="subhead-title">Beneficiario</div>
          </div>

          <div class="field">
            <label>Nombre(s)</label>
            <input id="benNombres" type="text" name="ben_nombres">
          </div>

          <div class="row-2">
            <div class="field">
              <label>Apellido paterno</label>
              <input id="benApPaterno" type="text" name="ben_ap_paterno">
            </div>

            <div class="field">
              <label>Apellido materno</label>
              <input id="benApMaterno" type="text" name="ben_ap_materno">
            </div>
          </div>

          <div class="row-2">
            <div class="field">
              <label>RFC</label>
              <input id="rfcBeneficiario" type="text" name="rfc_beneficiario">
            </div>

            <div class="field">
              <label>CCT / Clave de pago del beneficiario</label>
              <select id="cctPagoBeneficiario" name="cct_pago_beneficiario" required>
                <option value="">Selecciona…</option>
                <option value="05KPA0035F">05KPA0035F</option>
                <option value="05KPA0030F">05KPA0030F</option>
                <option value="05KPA0017F">05KPA0017F</option>
                <option value="05KPA0033F">05KPA0033F</option>
                <option value="05KPA0032F">05KPA0032F</option>
                <option value="05KPA0025F">05KPA0025F</option>
                <option value="05KPA0024F">05KPA0024F</option>
                <option value="05KPA0028F">05KPA0028F</option>
                <option value="05KPA0002F">05KPA0002F</option>
                <option value="05KPA0003F">05KPA0003F</option>
              </select>
            </div>
          </div>

          <div class="field">
            <label>Celular del beneficiario</label>
            <input id="celularBeneficiario" type="text" name="celular_beneficiario" placeholder="10 dígitos">
          </div>

          <div class="row-2">
            <div class="field">
              <label>Forma de aplicación</label>
              <select id="formaAplicacion" name="forma_aplicacion">
                <option value="">Selecciona…</option>
                <option value="PORC_PENSION">Porcentaje (Pensión)</option>
                <option value="IMP_FIJO_PENSION">Importe fijo (Pensión)</option>
                <option value="JUICIO_MERCANTIL">Juicio Mercantil</option>
              </select>
            </div>

            <div class="field">
              <label>Porcentaje / Importe</label>
              <input id="montoDescuento" type="number" step="0.01" name="monto_descuento" placeholder="Ej. 20 o 1500">
            </div>
          </div>

          <div class="row-2">
            <div class="field">
              <label>Quincena inicio</label>
              <input id="quincenaInicio" type="text" name="quincena_inicio" placeholder="Ej. 2025-01">
            </div>

            <div class="field">
              <label>Quincena fin</label>
              <input id="quincenaFin" type="text" name="quincena_fin" placeholder="Ej. 2025-24">
            </div>
          </div>
        </div>

        <div class="subcard">
          <div class="subhead">
            <div class="subhead-title">Abogado</div>
          </div>

          <div class="field">
            <label>Nombre completo</label>
            <input id="nombreAbogado" type="text" name="nombre_abogado" placeholder="Nombre completo del abogado">
          </div>

          <div class="field">
            <label>Celular del abogado</label>
            <input id="celularAbogado" type="text" name="celular_abogado" placeholder="10 dígitos">
          </div>
        </div>

      </div>
    </div>
  </div>

  <div class="section-block">
    <div class="section-header">CÉDULA · DATOS DEL OFICIO</div>

    <div class="section-body">
      <div class="section-subtitle">3. Datos del oficio</div>

      <div class="grid grid-2">

        <div>
          <div class="field">
            <label>Tipo de trámite</label>
            <select id="tipoTramite" name="tipo_tramite">
              <option value="">Selecciona…</option>
              <option value="pension">Pensión alimenticia</option>
              <option value="juicio">Juicio mercantil</option>
            </select>
          </div>

          <div class="field">
            <label>Número de oficio</label>
            <input type="text" id="numeroOficio" name="numero_oficio" placeholder="Ej. 1234/2025">
          </div>

          <div class="field">
            <label>Fecha del oficio</label>
            <input type="date" id="fechaOficio" name="fecha_oficio">
          </div>

          <div class="field">
            <label>Fecha de recibido</label>
            <input type="date" id="fechaRecibido" name="fecha_recibido">
          </div>
        </div>

        <div>
          <div class="field">
            <label>Cargar archivo del oficio</label>
            <input type="file" id="archivoOficio" name="archivo_oficio" accept=".pdf,image/*">
          </div>

          <div class="field">
            <label>Número de expediente en el juzgado</label>
            <input type="text" id="numeroExpediente" name="numero_expediente">
          </div>

          <div class="field">
            <label>Juzgado</label>
            <select id="juzgado" name="juzgado">
              <option value="">Selecciona…</option>
              <option>Juzgado Primero de Primera Instancia en Materia Familiar</option>
              <option>Juzgado Segundo de Primera Instancia en Materia Familiar</option>
              <option>Juzgado Tercero de Primera Instancia en Materia Familiar</option>
              <option>Juzgado Cuarto de Primera Instancia en Materia Familiar</option>
              <option>Juzgado de Primera Instancia en Materia de Familia (Especializado en Violencia Familiar)</option>
              <option>Juzgado Primero de Primera Instancia en Materia de Juicio Letrado Civil y Familiar</option>
              <option>Juzgado Segundo de Primera Instancia en Materia de Juicio Letrado Civil y Familiar</option>
              <option>Externos</option>
            </select>
          </div>

          <div class="field">
            <label>Municipio de pago</label>
            <select id="municipio_pago" name="municipio_pago">
              <option value="">Selecciona…</option>
              <option value="030">030 — Saltillo</option>
              <option value="004">004 — Arteaga</option>
              <option value="027">027 — Ramos Arizpe</option>
              <option value="035">035 — Torreón</option>
              <option value="018">018 — Monclova</option>
              <option value="025">025 — Piedras Negras</option>
              <option value="000">000 — Externo</option>
            </select>
          </div>
        </div>

      </div>
    </div>
  </div>

  <div class="actions">
    <button class="btn btn-primary" type="button" id="btnGuardar">Guardar movimiento</button>
    <a href="prueba3.1.php" class="btn btn-secondary">Ir a seguimiento</a>
  </div>

</div>

<script>
function aviso(icon, title, text) {
  Swal.fire({
    icon: icon,
    title: title,
    text: text,
    confirmButtonText: "Aceptar",
    confirmButtonColor: "#00a0ff"
  });
}

/* NUEVA SOLICITUD / BUSCAR TRABAJADOR */
document.getElementById("btnNuevaSolicitud").addEventListener("click", async () => {
  const q = document.getElementById("buscarTrabajador").value.trim();

  if (!q) {
    aviso("warning", "Dato requerido", "Escribe RFC, CURP o nombre del trabajador.");
    return;
  }

  try {
    const resp = await fetch("api/buscar_trabajador.php?q=" + encodeURIComponent(q));
    const data = await resp.json();

    if (!data.ok) {
      aviso("error", "Sin resultados", data.msg || "No se encontró trabajador.");
      return;
    }

    const t = data.trabajador;

    document.getElementById("rfcTrabajador").value = t.rfc || "";
    document.getElementById("curpTrabajador").value = t.curp || "";
    document.getElementById("nombreTrabajador").value = t.nombre_completo || "";
    document.getElementById("centroTrabajo").value = t.centro_trabajo || "";

    if (t.sostenimiento) {
      const radio = document.querySelector(
        'input[name="sostenimiento"][value="' + t.sostenimiento + '"]'
      );
      if (radio) radio.checked = true;
    }

    aviso("success", "Trabajador cargado", "Trabajador encontrado y cargado correctamente.");

  } catch (err) {
    aviso("error", "Error", "Error al buscar trabajador: " + err.message);
    console.error(err);
  }
});

/* VER NÓMINA */
document.getElementById("btnVerNomina").addEventListener("click", () => {
  abrirNomina();
});

/* CALCULAR MONTO A DESCONTAR */
document.getElementById("btnCalcularDescuento").addEventListener("click", async () => {
  const rfc = document.getElementById("rfcTrabajador").value.trim();
  const forma = document.getElementById("formaAplicacion").value || "";
  const monto = Number(document.getElementById("montoDescuento").value || 0);

  if (!rfc) {
    aviso("warning", "Trabajador requerido", "Primero selecciona o captura un trabajador.");
    return;
  }

  try {
    const resp = await fetch("api/buscar_trabajador.php?q=" + encodeURIComponent(rfc));
    const data = await resp.json();

    if (!data.ok) {
      aviso("error", "Sin nómina", "No se encontró nómina del trabajador.");
      return;
    }

    const t = data.trabajador;

    const percepciones = Number(t.percepciones || 0);
    const deducciones = Number(t.deducciones_ley || 0);
    const sueldoNeto = Number(t.sueldo_neto || 0);

    if (!forma) {
      aviso("warning", "Forma requerida", "Selecciona la forma de aplicación en los datos del beneficiario.");
      return;
    }

    if (forma !== "JUICIO_MERCANTIL" && (!monto || isNaN(monto))) {
      aviso("warning", "Monto requerido", "Captura el porcentaje o importe en los datos del beneficiario.");
      return;
    }

    let descuento = 0;

    if (forma === "PORC_PENSION") {
      descuento = sueldoNeto * (monto / 100);
    } else if (forma === "IMP_FIJO_PENSION") {
      descuento = monto;
    } else if (forma === "JUICIO_MERCANTIL") {
      descuento = (percepciones - deducciones) * 0.30;
    }

    Swal.fire({
      title: "Cálculo automático",
      icon: "success",
      width: 520,
      confirmButtonText: "Aceptar",
      confirmButtonColor: "#00a0ff",
      html: `
        <div style="text-align:left;font-size:15px;line-height:1.9;margin-top:10px;">
          <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:14px;">
            <div style="display:flex;justify-content:space-between;">
              <span><b>Percepciones</b></span>
              <span>$${percepciones.toFixed(2)}</span>
            </div>
            <div style="display:flex;justify-content:space-between;">
              <span><b>Deducciones de ley</b></span>
              <span>$${deducciones.toFixed(2)}</span>
            </div>
            <div style="display:flex;justify-content:space-between;">
              <span><b>Sueldo neto</b></span>
              <span>$${sueldoNeto.toFixed(2)}</span>
            </div>
          </div>

          <div style="margin-top:18px;background:linear-gradient(135deg,#00a0ff,#0077ff);color:white;border-radius:12px;padding:18px;text-align:center;box-shadow:0 4px 10px rgba(0,0,0,.15);">
            <div style="font-size:14px;opacity:.9;margin-bottom:6px;">MONTO A DESCONTAR</div>
            <div style="font-size:32px;font-weight:700;letter-spacing:.03em;">$${descuento.toFixed(2)}</div>
          </div>
        </div>
      `
    });

  } catch (err) {
    aviso("error", "Error al calcular", err.message);
    console.error(err);
  }
});

/* GUARDAR MOVIMIENTO */
document.getElementById("btnGuardar").addEventListener("click", async () => {
  try {
    const formData = new FormData();

    const tipoMovimientoClase = document.getElementById("tipoMovimientoClase").value;
    const tipoMovimiento = document.getElementById("tipoMovimiento").value;
    const tipoTramite = document.getElementById("tipoTramite").value || tipoMovimientoClase;

    const rfcTrabajador = document.getElementById("rfcTrabajador").value.trim();
    const curpTrabajador = document.getElementById("curpTrabajador").value.trim();
    const nombreTrabajador = document.getElementById("nombreTrabajador").value.trim();

    const benNombres = document.getElementById("benNombres").value.trim();
    const benApPaterno = document.getElementById("benApPaterno").value.trim();
    const benApMaterno = document.getElementById("benApMaterno").value.trim();

    const formaAplicacion = document.getElementById("formaAplicacion").value;
    const montoDescuento = document.getElementById("montoDescuento").value.trim();
    const quincenaInicio = document.getElementById("quincenaInicio").value.trim();
    const quincenaFin = document.getElementById("quincenaFin").value.trim();
    const cctPago = document.getElementById("cctPagoBeneficiario").value.trim();

    if (!tipoMovimientoClase || !tipoMovimiento || !tipoTramite) {
      aviso("warning", "Faltan datos", "Selecciona tipo de movimiento, proceso y tipo de trámite.");
      return;
    }

    if (!rfcTrabajador || !nombreTrabajador) {
      aviso("warning", "Faltan datos del trabajador", "Carga o escribe RFC y nombre del trabajador.");
      return;
    }

    if (!benNombres || !benApPaterno) {
      aviso("warning", "Faltan datos del beneficiario", "Captura nombre y apellido paterno del beneficiario.");
      return;
    }

    if (!formaAplicacion) {
      aviso("warning", "Falta forma de aplicación", "Selecciona la forma de aplicación.");
      return;
    }

    if (montoDescuento === "" || isNaN(Number(montoDescuento))) {
      aviso("warning", "Monto inválido", "Captura un porcentaje o importe válido.");
      return;
    }

    if (!quincenaInicio || !quincenaFin) {
      aviso("warning", "Faltan quincenas", "Captura quincena inicio y quincena fin.");
      return;
    }

    if (!cctPago) {
      aviso("warning", "Falta CCT", "Selecciona la clave de pago del beneficiario.");
      return;
    }

    formData.append("tipo_movimiento_clase", tipoMovimientoClase);
    formData.append("tipo_movimiento", tipoMovimiento);
    formData.append("tipo_tramite", tipoTramite);

    formData.append("rfc_trabajador", rfcTrabajador);
    formData.append("curp_trabajador", curpTrabajador);
    formData.append("nombre_trabajador", nombreTrabajador);

    formData.append("ben_nombres", benNombres);
    formData.append("ben_ap_paterno", benApPaterno);
    formData.append("ben_ap_materno", benApMaterno);
    formData.append("rfc_beneficiario", document.getElementById("rfcBeneficiario").value.trim());
    formData.append("celular_beneficiario", document.getElementById("celularBeneficiario").value.trim());
    formData.append("cct_pago_beneficiario", cctPago);
    formData.append("forma_aplicacion", formaAplicacion);
    formData.append("monto_descuento", montoDescuento);
    formData.append("quincena_inicio", quincenaInicio);
    formData.append("quincena_fin", quincenaFin);

    formData.append("nombre_abogado", document.getElementById("nombreAbogado").value.trim());
    formData.append("celular_abogado", document.getElementById("celularAbogado").value.trim());

    formData.append("numero_oficio", document.getElementById("numeroOficio").value.trim());
    formData.append("fecha_oficio", document.getElementById("fechaOficio").value);
    formData.append("fecha_recibido", document.getElementById("fechaRecibido").value);
    formData.append("numero_expediente", document.getElementById("numeroExpediente").value.trim());
    formData.append("juzgado", document.getElementById("juzgado").value);
    formData.append("municipio_pago", document.getElementById("municipio_pago").value);

    const archivo = document.getElementById("archivoOficio").files[0];
    if (archivo) {
      formData.append("archivo_oficio", archivo);
    }

    const resp = await fetch("api/guardar_movimiento.php", {
      method: "POST",
      body: formData
    });

    const data = await resp.json();

    if (!data.ok) {
      aviso("error", "No se pudo guardar", data.msg || "Revisa los datos capturados.");
      return;
    }

    Swal.fire({
      icon: "success",
      title: "Movimiento guardado",
      text: "El movimiento se registró correctamente.",
      confirmButtonColor: "#00a0ff"
    });

  } catch (err) {
    aviso("error", "Error inesperado", err.message);
    console.error(err);
  }
});

/* ABRIR NÓMINA */
function abrirNomina() {
  const rfc = document.getElementById("rfcTrabajador").value.trim();
  const curp = document.getElementById("curpTrabajador").value.trim();
  const nom = document.getElementById("nombreTrabajador").value.trim();

  let url = "nomina.php";
  const params = new URLSearchParams();

  if (rfc) params.set("rfc", rfc);
  if (curp) params.set("curp", curp);
  if (nom) params.set("nombre", nom);

  const qs = params.toString();

  if (qs) {
    url += "?" + qs;
  }

  window.open(url, "_blank", "noopener");
}
</script>

</body>
</html>