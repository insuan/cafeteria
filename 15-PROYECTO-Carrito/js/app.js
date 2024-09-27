const carrito = document.querySelector("#carrito");
const contenedorCarrito = document.querySelector("#lista-carrito tbody");
const vaciarCarritoBtn = document.querySelector("#vaciar-carrito");
const cursos = document.querySelector("#lista-cursos");

let articulosCarrito = [];

document.addEventListener("DOMContentLoaded", () => {
    cursos.addEventListener("click", agregarCurso);

})

    function agregarCurso(e){
        e.preventDefault();

        const curso = e.target.parentElement.parentElement;

        crearObjeto(curso);
         
        // console.log(curso);
    }


    function crearObjeto(informacion){
        const infoCurso = {
            imagen : informacion.querySelector("img").src,
            titulo : informacion.querySelector("h4").textContent,
            precio : informacion.querySelector(".precio span").textContent,
            id : informacion.querySelector("a").getAttribute("data-id"),
            cantidad : 1
        }


        articulosCarrito = [...articulosCarrito, infoCurso]

        carritoHTML();

    }

    function carritoHTML(){

        limpiarHTML();
        
        articulosCarrito.forEach(element => {
            const row = document.createElement("tr");

            row.innerHTML = `
                            <td><img src="${element.imagen}"</td>,
                            <td>${element.titulo}</td>,
                            <td>${element.precio}</td>,
                            <td>${element.cantidad}</td>,
                            <td>
                            <a href="#" class="borrar-curso" data-id="${element.cantidad}"> X </a>
                            </td>

            `

            contenedorCarrito.appendChild(row);
        });

    function limpiarHTML(){
        while(contenedorCarrito.firstChild){
            contenedorCarrito.removeChild(contenedorCarrito.firstChild);
        }
    }    
    }