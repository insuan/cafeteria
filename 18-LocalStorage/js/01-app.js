localStorage.setItem("nombre", "Nicolas");


const producto = {
    nombre: "Playstation5",
    precio: 1500000
}

const productoString = JSON.stringify(producto);

localStorage.setItem("producto", productoString);

const nombre = localStorage.getItem("producto");

console.log(JSON.parse(nombre));