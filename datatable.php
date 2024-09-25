<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.6/css/dataTables.dataTables.css" />    <title>Document</title>
</head>
<style>
    .colores{
        background-color: lightblue;
    }
    #datos{
        background-color: gray;
        color: white;
    }

    #datos tr td{
        text-align: center;
    }
</style>
<body>
    <table id="myTable" class="cell-border hover" style="width:100%">
        <thead class="colores">
            <tr>
                <th>Id</th>
                <th>Nombre</th>
                <th>Nacionalidad</th>
                <th>Ranking</th>
            </tr>
        </thead>
        <tbody id="datos">

        </tbody>
    </table>
    <?php
    include("conexion_datatable.php");
    ?>
</body>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.1.6/js/dataTables.js"></script>
<script>
    $(document).ready( function () {

        $.post("conexion_datatable.php",function(respuestajson){
            array_usuario = JSON.parse(respuestajson);

            array_usuario.forEach(element => {
                const miHTML = "<tr>"+
                                "<td>"+element.id+"</td>"+
                                "<td>"+element.Nombre+"</td>"+
                                "<td>"+element.Nacionalidad+"</td>"+
                                "<td>"+element.Ranking+"</td>"
                                +"</tr>";

                // const miHTML = "<tr>"+
                //         "<td>"+element.id+"</td>"
                //         "<td>"+element.nombre+"</td>"
                //         "<td>"+element.nacionalidad+"</td>"
                //         "<td>"+element.ranking+"</td>"+
                //         "</tr>";
                
                        $("#datos").append(miHTML);
            });
                    });
        // });
        // const prueba = [
        //     { nombre: "Roger Federer", nacionalidad: "Suiza", grandSlams: 20 },
        //     { nombre: "Rafael Nadal", nacionalidad: "España", grandSlams: 22 },
        //     { nombre: "Novak Djokovic", nacionalidad: "Serbia", grandSlams: 24 },
        //     { nombre: "Pete Sampras", nacionalidad: "EE. UU.", grandSlams: 14 },
        //     { nombre: "Andre Agassi", nacionalidad: "EE. UU.", grandSlams: 8 },
        //     { nombre: "Bjoern Borg", nacionalidad: "Suecia", grandSlams: 11 },
        //     { nombre: "John McEnroe", nacionalidad: "EE. UU.", grandSlams: 7 },
        //     { nombre: "Jimmy Connors", nacionalidad: "EE. UU.", grandSlams: 8 },
        //     { nombre: "Ivan Lendl", nacionalidad: "República Checa", grandSlams: 8 },
        //     { nombre: "Marat Safin", nacionalidad: "Rusia", grandSlams: 2 },
        //     { nombre: "Lleyton Hewitt", nacionalidad: "Australia", grandSlams: 2 },
        //     { nombre: "Goran Ivanisevic", nacionalidad: "Croacia", grandSlams: 1 },
        //     { nombre: "Andy Murray", nacionalidad: "Reino Unido", grandSlams: 3 },
        //     { nombre: "David Ferrer", nacionalidad: "España", grandSlams: 0 },
        //     { nombre: "Thomas Muster", nacionalidad: "Austria", grandSlams: 1 },
        //     { nombre: "Marcel Granollers", nacionalidad: "España", grandSlams: 0 },
        //     { nombre: "Milos Raonic", nacionalidad: "Canadá", grandSlams: 0 },
        //     { nombre: "Stanislas Wawrinka", nacionalidad: "Suiza", grandSlams: 3 },
        //     { nombre: "Dominic Thiem", nacionalidad: "Austria", grandSlams: 1 },
        //     { nombre: "Alexander Zverev", nacionalidad: "Alemania", grandSlams: 0 },
        //     { nombre: "Daniil Medvedev", nacionalidad: "Rusia", grandSlams: 1 },
        //     { nombre: "Carlos Alcaraz", nacionalidad: "España", grandSlams: 1 },
        //     { nombre: "Kei Nishikori", nacionalidad: "Japón", grandSlams: 0 },
        //     { nombre: "Nicolas Mahut", nacionalidad: "Francia", grandSlams: 0 },
        //     { nombre: "Jack Sock", nacionalidad: "EE. UU.", grandSlams: 1 },
        //     { nombre: "Marin Cilic", nacionalidad: "Croacia", grandSlams: 1 },
        //     { nombre: "Juan Martín del Potro", nacionalidad: "Argentina", grandSlams: 1 },
        //     { nombre: "Richard Gasquet", nacionalidad: "Francia", grandSlams: 0 },
        //     { nombre: "David Nalbandian", nacionalidad: "Argentina", grandSlams: 0 },
        //     { nombre: "Mardy Fish", nacionalidad: "EE. UU.", grandSlams: 0 },
        //     { nombre: "Andy Roddick", nacionalidad: "EE. UU.", grandSlams: 1 },
        //     { nombre: "Fernando González", nacionalidad: "Chile", grandSlams: 1 },
        //     { nombre: "Andreas Seppi", nacionalidad: "Italia", grandSlams: 0 },
        //     { nombre: "Benoit Paire", nacionalidad: "Francia", grandSlams: 0 },
        //     { nombre: "David Goffin", nacionalidad: "Bélgica", grandSlams: 0 },
        //     { nombre: "Pablo Carreño Busta", nacionalidad: "España", grandSlams: 0 },
        //     { nombre: "Fabio Fognini", nacionalidad: "Italia", grandSlams: 0 },
        //     { nombre: "Alex de Minaur", nacionalidad: "Australia", grandSlams: 0 },
        //     { nombre: "Diego Schwartzman", nacionalidad: "Argentina", grandSlams: 0 },
        //     { nombre: "Jannik Sinner", nacionalidad: "Italia", grandSlams: 0 },
        //     { nombre: "Grigor Dimitrov", nacionalidad: "Bulgaria", grandSlams: 1 },
        //     { nombre: "Dusan Lajovic", nacionalidad: "Serbia", grandSlams: 0 },
        //     { nombre: "Boris Becker", nacionalidad: "Alemania", grandSlams: 6 },
        //     { nombre: "Michael Chang", nacionalidad: "EE. UU.", grandSlams: 1 },
        //     { nombre: "Arthur Ashe", nacionalidad: "EE. UU.", grandSlams: 3 },
        //     { nombre: "Bill Tilden", nacionalidad: "EE. UU.", grandSlams: 10 },
        //     { nombre: "Rod Laver", nacionalidad: "Australia", grandSlams: 11 },
        //     { nombre: "Tony Roche", nacionalidad: "Australia", grandSlams: 0 },
        //     { nombre: "Patrick Rafter", nacionalidad: "Australia", grandSlams: 2 },
        //     { nombre: "Lleyton Hewitt", nacionalidad: "Australia", grandSlams: 2 }
        // ];


        // const prueba2 = prueba.filter(datos => {
        //     const grandSlams = datos.grandSlams > 10
        //     return grandSlams;
        // })

        // console.log(prueba2);

        // prueba.forEach(element => {
        //     const html = "<tr>"+
        //                 "<td>"+element.nombre+"</td>"+
        //                 "<td>"+element.nacionalidad+"</td>"+
        //                 "<td>"+element.grandSlams+"</td>"+
        //                 "</tr>";

            // $("#datos").append(html);            
        // });

    $('#myTable').DataTable();
    } );
</script>

</html>