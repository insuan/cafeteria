<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
    .fondo{
        background-color: red;
    }
</style>
<body class="body">
    <table style="border: solid black 1px;">
        <thead>
            <tr>
                <td>Nombre</td>
                <td>Apellido</td>
                <td>Direccion</td>
                <td>Telefono</td>
            </tr>
        </thead>
        <tbody id="tabla">

        </tbody>
    </table>
<?php

include("nuevaPrueba.php");


?>
    <form action="">
        <input type="button" id="btn" value="Boton">
        <input type="button" id="btn2" value="Boton2">
        <input type="button" id="btn3" value="Hiden">
    </form>
</body>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script>
    $(document).ready(function(){

        $.post('nuevaPrueba.php', function(respuestajson){
            array_usuario = JSON.parse(respuestajson);
            // console.log(array_usuario.length);

            for(i=0; i<array_usuario.length; i++){
                // console.log("hola");
                console.log(array_usuario[i]);

        // $("#btn").click(function(){
        //     $(".body").addClass("fondo");
        //     if($("#tabla").hasClass("hide") === true){
        //         $("#tabla").show();
        //     }else{
        //         $("#tabla").append(miHTML);
        //     }
        // });

        // $("#btn2").click(function(){
        //     $(".body").removeClass("fondo");
        // });

        // $("#btn3").click(function(){
        //     $("#tabla").hide();
        // });


            var miHtml = "<tr>"+
                    "<td>"+array_usuario[i]['usuario']+"</td>"+
                    "<td>"+array_usuario[i]['password']+"</td>"+
                    "<td>"+array_usuario[i]['edad']+"</td>"+
                    "<td>"+array_usuario[i]['email']+"</td>"+
                    "</tr>";


            $('#tabla').append(miHtml);

        }


        });

    });
</script>
</html>