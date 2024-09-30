const carrito = [];

//Definir un producto modo imperativo
const producto = {
    nombre: "Monitor 32 pulgadas",
    precio: 400
}

const producto2 = {
    nombre: "celular",
    precio: 800
}

const producto3 = {
    nombre: "teclado",
    precio: 50
}

//modo declarativo
let resultado;

resultado = [...carrito, producto]
resultado = [...carrito, producto2]
resultado = [...carrito, producto3]

console.log(resultado)