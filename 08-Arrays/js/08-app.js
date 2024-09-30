const producto = {
    nombre: "Monitor 20 pulgadas",
    precio: 300,
    disponible: true,
}

//const nombre = producto.nombre;
// console.log(nombre);

//destructering en objetos
const{nombre} = producto;

//destructuring en arreglos
const numeros = [10,20,30,40,50];

const [, , tercero] = numeros;

console.log(tercero)