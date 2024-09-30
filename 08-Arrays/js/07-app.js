//eliminar ultimo elemento del arreglo

carrito.pop();

console.table(carrito);

//eliminar del inicio del arreglo

carrito.shift();

console.table(carrito);

carrito.splice(1, 1); //lleva dos parametros el primero es donde va a iniciar
//a cortar y el segundo es cuantos elementos queremos eliminar
console.table(carrito);