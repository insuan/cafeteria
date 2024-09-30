// const navegacion = document.querySelector(".navegacion");
// console.log(navegacion.children[2].innerHTML);


const tarjetas = document.querySelector(".card");
// console.log(tarjetas.children[1].children[2].innerHTML);

// console.log(tarjetas.nextElementSibling.children[1].children[2].innerHTML);

// console.log(tarjetas.nextElementSibling.nextElementSibling.children[1].children[2].innerHTML);

console.log(tarjetas);

const sorteo = document.createElement("P");

sorteo.textContent = "Sorteo";

sorteo.style.textAlign = "center";

tarjetas.parentElement.appendChild(sorteo);


