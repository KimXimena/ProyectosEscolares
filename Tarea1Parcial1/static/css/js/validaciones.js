document.addEventListener("DOMContentLoaded", function () {
    const formulario = document.getElementById("formulario");
    const alerta = document.getElementById("alerta");
    const resultado = document.getElementById("resultado");
    const btnLimpiar = document.getElementById("btnLimpiar");

    const campos = {
        nombre: document.getElementById("nombre"),
        curp: document.getElementById("curp"),
        correo: document.getElementById("correo"),
        telefono: document.getElementById("telefono"),
        celular: document.getElementById("celular"),
        nivel: document.getElementById("nivel"),
        municipio: document.getElementById("municipio"),
        asunto: document.getElementById("asunto")
    };

    function mostrarAlerta(tipo, mensaje) {
        alerta.innerHTML = `<div class="alert ${tipo}">${mensaje}</div>`;
    }

    function limpiarAlerta() {
        alerta.innerHTML = "";
    }

    function setEstadoCampo(input, estado, mensaje = "") {
        const field = input.closest(".field");
        if (!field) return;

        const errorText = field.querySelector(".error-text");
        field.classList.remove("error", "success");

        if (estado) {
            field.classList.add(estado);
        }

        if (errorText) {
            errorText.textContent = mensaje;
        }
    }

    function validarNombre() {
        const valor = campos.nombre.value.trim();
        if (valor.length < 8) {
            setEstadoCampo(campos.nombre, "error", "Ingresa nombre y apellidos.");
            return false;
        }
        setEstadoCampo(campos.nombre, "success", "");
        return true;
    }

    function validarCURP() {
        const valor = campos.curp.value.trim().toUpperCase();
        campos.curp.value = valor;

        if (valor.length !== 18) {
            setEstadoCampo(campos.curp, "error", "La CURP debe tener 18 caracteres.");
            return false;
        }
        setEstadoCampo(campos.curp, "success", "");
        return true;
    }

    function validarCorreo() {
        const valor = campos.correo.value.trim();
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!regex.test(valor)) {
            setEstadoCampo(campos.correo, "error", "Ingresa un correo válido.");
            return false;
        }
        setEstadoCampo(campos.correo, "success", "");
        return true;
    }

    function validarTelefono(input) {
        const valor = input.value.trim();
        if (!/^\d{10}$/.test(valor)) {
            setEstadoCampo(input, "error", "Debe contener 10 dígitos.");
            return false;
        }
        setEstadoCampo(input, "success", "");
        return true;
    }

    function validarSelect(input, mensaje) {
        if (!input.value) {
            setEstadoCampo(input, "error", mensaje);
            return false;
        }
        setEstadoCampo(input, "success", "");
        return true;
    }

    function validarFormulario() {
        const v1 = validarNombre();
        const v2 = validarCURP();
        const v3 = validarCorreo();
        const v4 = validarTelefono(campos.telefono);
        const v5 = validarTelefono(campos.celular);
        const v6 = validarSelect(campos.nivel, "Selecciona un nivel.");
        const v7 = validarSelect(campos.municipio, "Selecciona un municipio.");
        const v8 = validarSelect(campos.asunto, "Selecciona un asunto.");

        return v1 && v2 && v3 && v4 && v5 && v6 && v7 && v8;
    }

    campos.nombre.addEventListener("blur", validarNombre);
    campos.curp.addEventListener("blur", validarCURP);
    campos.correo.addEventListener("blur", validarCorreo);
    campos.telefono.addEventListener("blur", function () {
        validarTelefono(campos.telefono);
    });
    campos.celular.addEventListener("blur", function () {
        validarTelefono(campos.celular);
    });
    campos.nivel.addEventListener("change", function () {
        validarSelect(campos.nivel, "Selecciona un nivel.");
    });
    campos.municipio.addEventListener("change", function () {
        validarSelect(campos.municipio, "Selecciona un municipio.");
    });
    campos.asunto.addEventListener("change", function () {
        validarSelect(campos.asunto, "Selecciona un asunto.");
    });

    formulario.addEventListener("submit", async function (e) {
        e.preventDefault();
        limpiarAlerta();
        resultado.classList.add("hidden");

        if (!validarFormulario()) {
            mostrarAlerta("error", "Corrige los campos marcados antes de continuar.");
            return;
        }

        const payload = {
            nombre: campos.nombre.value.trim(),
            curp: campos.curp.value.trim(),
            correo: campos.correo.value.trim(),
            telefono: campos.telefono.value.trim(),
            celular: campos.celular.value.trim(),
            nivel: campos.nivel.value,
            municipio: campos.municipio.value,
            asunto: campos.asunto.value
        };

        try {
            const response = await fetch("/generar_turno", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                mostrarAlerta("error", data.error || "Ocurrió un error al generar el turno.");
                return;
            }

            mostrarAlerta("success", data.mensaje);

            document.getElementById("ticketTurno").textContent = data.turno;
            document.getElementById("ticketFolio").textContent = data.folio;
            document.getElementById("ticketFecha").textContent = data.fecha;
            document.getElementById("ticketHora").textContent = data.hora;
            document.getElementById("ticketOficina").textContent = data.oficina;
            document.getElementById("ticketNombre").textContent = data.nombre;
            document.getElementById("ticketMunicipio").textContent = data.municipio;
            document.getElementById("ticketNivel").textContent = data.nivel;
            document.getElementById("ticketAsunto").textContent = data.asunto;

            resultado.classList.remove("hidden");
            resultado.scrollIntoView({ behavior: "smooth", block: "start" });

        } catch (error) {
            console.error(error);
            mostrarAlerta("error", "No fue posible conectar con el servidor.");
        }
    });

    btnLimpiar.addEventListener("click", function () {
        formulario.reset();
        limpiarAlerta();
        resultado.classList.add("hidden");

        document.querySelectorAll(".field").forEach(function (field) {
            field.classList.remove("error", "success");
            const errorText = field.querySelector(".error-text");
            if (errorText) {
                errorText.textContent = "";
            }
        });

        document.getElementById("ticketTurno").textContent = "---";
        document.getElementById("ticketFolio").textContent = "---";
        document.getElementById("ticketFecha").textContent = "---";
        document.getElementById("ticketHora").textContent = "---";
        document.getElementById("ticketOficina").textContent = "---";
        document.getElementById("ticketNombre").textContent = "---";
        document.getElementById("ticketMunicipio").textContent = "---";
        document.getElementById("ticketNivel").textContent = "---";
        document.getElementById("ticketAsunto").textContent = "---";
    });
});