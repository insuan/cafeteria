//Variables
const formulario = document.querySelector("#formulario");
const listaTweets = document.querySelector("#lista-tweets");
const contenedor = document.querySelector(".container");
let tweets = [];


//event Listeners
eventListeners();

function eventListeners(){
    formulario.addEventListener("submit",agregarTweet)
}


//Funciones

function agregarTweet(e){
    e.preventDefault();

    const mensaje = document.querySelector("#tweet").value;

    if(mensaje === ""){
        mostrarMensaje("Debe ingresar algo");
        return;
    }

    const tweetObj = {
        id:Date.now(),
        tweet:mensaje
    }

    tweets = [...tweets, tweetObj];
    console.log(tweets)

    crearHTML();


    formulario.reset();
}

function mostrarMensaje(mensaje){
    const alerta = document.querySelector(".error");
    if(alerta){
        alerta.remove();
    }

    const error = document.createElement("P");
    error.textContent = mensaje;
    error.classList.add("error");
    
    const contenedor = document.querySelector(".container");

    contenedor.appendChild(error);

    setTimeout(() => {
        error.remove();
    }, 3000);
}

function crearHTML(){
    limpiarHTML();

    if (tweets.length > 0) {
        tweets.forEach(element => {
            const btnEliminar = document.createElement("a");
            btnEliminar.classList.add("borrar-tweet");
            btnEliminar.textContent = "X";

            btnEliminar.onclick = () => {
                eliminarTweet(element.id);
            }

            const li = document.createElement("li");
            li.textContent = element.tweet;
            li.appendChild(btnEliminar);
            listaTweets.appendChild(li);
        });
    }
}

function eliminarTweet(id){
    tweets = tweets.filter(tweet => tweet.id !== id);
    
    crearHTML();
}

function limpiarHTML(){
    while(listaTweets.firstChild){
        listaTweets.removeChild(listaTweets.firstChild);
    }
}