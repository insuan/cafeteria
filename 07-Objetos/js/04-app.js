const producto = {
    nombre: "Monitor 20 pulgadas",
    precio: 300,
    disponible: true,
}

//const nombre = producto.nombre;
// console.log(nombre);

//destructering
const{nombre, precio, disponible} = producto;

console.log(nombre);
console.log(precio);
console.log(disponible);