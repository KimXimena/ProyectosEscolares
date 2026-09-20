document.addEventListener("DOMContentLoaded", () => {
    const contadores = document.querySelectorAll(".contador-regresivo");

    function actualizarContadores() {
        contadores.forEach(contador => {
            const fechaTexto = contador.dataset.fecha;

            if (!fechaTexto) {
                contador.textContent = "Fecha por definir";
                return;
            }

            const fechaObjetivo = new Date(fechaTexto.replace(" ", "T")).getTime();
            const ahora = new Date().getTime();
            const diferencia = fechaObjetivo - ahora;

            if (diferencia <= 0) {
                contador.textContent = "Rifa cerrada";
                return;
            }

            const dias = Math.floor(diferencia / (1000 * 60 * 60 * 24));
            const horas = Math.floor((diferencia / (1000 * 60 * 60)) % 24);
            const minutos = Math.floor((diferencia / (1000 * 60)) % 60);
            const segundos = Math.floor((diferencia / 1000) % 60);

            contador.innerHTML = `
                <span>${dias}d</span>
                <span>${horas}h</span>
                <span>${minutos}m</span>
                <span>${segundos}s</span>
            `;
        });
    }

    actualizarContadores();
    setInterval(actualizarContadores, 1000);
});