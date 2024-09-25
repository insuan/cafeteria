<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="contenedor">

    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const url = "http://localhost:8081/conexion_datatable.php"

            fetch(url)
                .then(respuesta => respuesta.json())
                .then(datos => mostrarDatos(datos))

                .catch(error => {
                    console.log(error);
                })
        function mostrarDatos(informacion){
            const contenido = document.querySelector(".contenedor");

            let html = ''

            informacion.forEach(element => {
                const {id, Nombre, Nacionalidad, Ranking} = element;

                html += `
                        <p>id:${id}
                        Nombre:${Nombre} 
                        Nacionalidad:${Nacionalidad} 
                        Ranking:${Ranking}
                        </p>
                `

                contenido.innerHTML = html;
            });
        }    
                
        })
    </script>
</body>
</html>