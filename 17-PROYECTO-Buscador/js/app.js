const resultado = document.querySelector("#resultado");

document.addEventListener("DOMContentLoaded", () => {
    mostrarAutos();
})

function mostrarAutos() {
    autos.forEach(auto => {
        const autoHTML = document.createElement("P");
        autoHTML.textContent = `
        ${auto.marca} ${auto.modelo} - ${auto.year} - ${auto.puertas} Puertas - Transmision: ${auto.transmision} - 
        Precio: ${auto.precio} - Color: ${auto.color}
        `

        resultado.appendChild(autoHTML);
    })
}