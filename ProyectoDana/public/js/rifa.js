const botones = document.querySelectorAll(".boleto.disponible");
const inputBoletos = document.getElementById("boletosSeleccionados");
const contador = document.getElementById("contadorSeleccionados");
const formCompra = document.getElementById("formCompra");

let seleccionados = [];

botones.forEach(boton => {
    boton.addEventListener("click", () => {
        const boletoId = boton.dataset.id;

        if (seleccionados.includes(boletoId)) {
            seleccionados = seleccionados.filter(id => id !== boletoId);
            boton.classList.remove("seleccionado");
        } else {
            seleccionados.push(boletoId);
            boton.classList.add("seleccionado");
        }

        inputBoletos.value = seleccionados.join(",");
        contador.textContent = seleccionados.length;
    });
});

formCompra.addEventListener("submit", (e) => {
    if (seleccionados.length === 0) {
        e.preventDefault();
        alert("Selecciona al menos un boleto.");
    }
});