//Switch case

const metodoPago = "Cheque";

switch(metodoPago){
    case "efectivo":
        console.log(`pagaste con ${efectivo}`);
        break;
    case "Cheque":
        console.log(`pagaste con ${metodoPago}`);
        break;
    default:
        console.log("Aun no has seleccionado un metodo de pago");
        break;    
}