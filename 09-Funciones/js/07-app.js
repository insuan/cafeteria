iniciarAPP();

function iniciarAPP(){
    console.log("Iniciando APP...")

    segundaFuncion();
}

function segundaFuncion(){
    console.log("Desde la segunda funcion");

    usuarioAutenticado("Nicolas");
}

function usuarioAutenticado(usuario){
    console.log("Autenticando usuario... espere...");
    console.log(`usuario autenticado exitosamente: ${usuario}`);
}