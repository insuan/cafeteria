const carrito = [
    {nombre: "Monitor 27 pulgadas", precio:500},
    {nombre: "Monitor 27 pulgadas", precio:750},
    {nombre: "Monitor 27 pulgadas", precio:820},
    {nombre: "Monitor 27 pulgadas", precio:1100},
    {nombre: "Monitor 27 pulgadas", precio:650},
    {nombre: "Monitor 27 pulgadas", precio:256},

]


for(let i=0; i < carrito.length; i++){
    console.log(carrito[i].nombre);
}


const nuevoarreglo = carrito.map( (producto) =>{
    return `${producto.nombre} y ${producto.precio}`;
})


carrito.forEach((producto) =>{
    console.log (`${producto.nombre} y ${producto.precio}`);
})

console.log(nuevoarreglo)