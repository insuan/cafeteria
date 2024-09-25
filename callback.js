
const nombres = ["Nicolas", "Juan", "Diego"];

function agregarNombre(nombre,callback){
    setTimeout(() => {
        nombres.push(nombre);
        callback();
    }, 4000);
}

function verNombres(){
    setTimeout(() => {
        nombres.forEach(nombre => {
            console.log(nombre);
        })
    }, 2000);
}

verNombres();
agregarNombre("Eduardo",verNombres);
agregarNombre("Karina",verNombres);


