from flask import Flask, render_template, request, jsonify
from datetime import datetime
import random
import re

app = Flask(__name__)

OFICINAS = {
    "Saltillo": "Oficina Regional Saltillo",
    "Ramos Arizpe": "Oficina Regional Ramos Arizpe",
    "Arteaga": "Oficina Regional Arteaga",
    "Monclova": "Oficina Regional Monclova",
    "Torreón": "Oficina Regional Torreón"
}

ASUNTOS = {
    "Inscripción": "INS",
    "Reinscripción": "REI",
    "Constancia": "CON",
    "Cambio de plantel": "CAM",
    "Aclaración": "ACL",
    "Otro trámite": "OTR"
}


def validar_curp(curp: str) -> bool:
    patron = r'^[A-Z][AEIOU][A-Z]{2}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[HM](AS|BC|BS|CC|CL|CM|CS|CH|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d]\d$'
    return re.match(patron, curp) is not None


def validar_correo(correo: str) -> bool:
    patron = r'^[^\s@]+@[^\s@]+\.[^\s@]+$'
    return re.match(patron, correo) is not None


def generar_numero_turno(municipio: str, asunto: str) -> str:
    prefijo_municipio = municipio[:3].upper()
    prefijo_asunto = ASUNTOS.get(asunto, "GEN")
    fecha = datetime.now().strftime("%d%m%y")
    consecutivo = random.randint(100, 999)
    return f"{prefijo_municipio}-{prefijo_asunto}-{fecha}-{consecutivo}"


@app.route("/")
def index():
    municipios = list(OFICINAS.keys())
    niveles = ["Preescolar", "Primaria", "Secundaria", "Media Superior", "Superior"]
    asuntos = list(ASUNTOS.keys())
    return render_template(
        "index.html",
        municipios=municipios,
        niveles=niveles,
        asuntos=asuntos
    )


@app.route("/generar_turno", methods=["POST"])
def generar_turno():
    data = request.get_json()

    nombre = data.get("nombre", "").strip()
    curp = data.get("curp", "").strip().upper()
    telefono = data.get("telefono", "").strip()
    celular = data.get("celular", "").strip()
    correo = data.get("correo", "").strip()
    nivel = data.get("nivel", "").strip()
    municipio = data.get("municipio", "").strip()
    asunto = data.get("asunto", "").strip()

    if not all([nombre, curp, telefono, celular, correo, nivel, municipio, asunto]):
        return jsonify({"ok": False, "error": "Todos los campos son obligatorios."}), 400

    if len(nombre) < 8:
        return jsonify({"ok": False, "error": "El nombre completo debe tener al menos 8 caracteres."}), 400

    if len(curp) != 18:
      return jsonify({"ok": False, "error": "La CURP debe tener 18 caracteres."}), 400

    if not telefono.isdigit() or len(telefono) != 10:
        return jsonify({"ok": False, "error": "El teléfono debe contener exactamente 10 dígitos."}), 400

    if not celular.isdigit() or len(celular) != 10:
        return jsonify({"ok": False, "error": "El celular debe contener exactamente 10 dígitos."}), 400

    if not validar_correo(correo):
        return jsonify({"ok": False, "error": "El correo electrónico no es válido."}), 400

    oficina = OFICINAS.get(municipio, "Oficina General")
    turno = generar_numero_turno(municipio, asunto)

    fecha = datetime.now().strftime("%d/%m/%Y")
    hora = datetime.now().strftime("%H:%M")
    folio = f"FOL-{random.randint(10000, 99999)}"

    return jsonify({
        "ok": True,
        "mensaje": "Turno generado correctamente.",
        "turno": turno,
        "folio": folio,
        "oficina": oficina,
        "fecha": fecha,
        "hora": hora,
        "nombre": nombre,
        "municipio": municipio,
        "nivel": nivel,
        "asunto": asunto
    })


if __name__ == "__main__":
    app.run(debug=True)