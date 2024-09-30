function sumar(a,b){
    return a + b;
}

const resultado = sumar(2,3);

console.log(resultado)

//Ejemplo mas avanzado

let total = 0;
function agregarCarrito(precio){
    return total += precio;
}

function calcularImpuesto(total){
    return total * 1.21;
}

total = agregarCarrito(500);
total = agregarCarrito(2000);
total = agregarCarrito(780);

const totalPagar = calcularImpuesto(total);

console.log(`el total a pagar es de ${totalPagar}`);

console.log(total);