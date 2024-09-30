// operador mayor que y menor que

const dinero = 100;
const totalApagar = 300;
const tarjeta = false;
const cheque = false;

if(dinero >= totalApagar){
    console.log("si podemos pagar");
}else if(cheque){
    console.log("si tengo un cheque");
}
else if(tarjeta){
    console.log("Si puedo pagar porque tengo la tarjeta");
}else{
    console.log("Fondos insuficientes");
}