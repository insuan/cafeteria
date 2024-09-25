<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.map"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.slim.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.slim.min.map"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <title>Document</title>
</head>
<body>
<table id="myTable" class="display">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Password</th>
            <th>Edad</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody id="body-tabla">
       
    </tbody>
</table>
<?php

    // include("conexion_pruebas.php");
    include_once("vistas.php");

    // $resultado = getBrowser("a");
    // echo($resultado);

    // $resultado = get_client_ip();
    // echo($resultado);

    // echo (conectar_nuevo());

?> 

</body>
<script type="text/javascript">
    $(document).ready( function () {

        // $.post('ajax.php', {tipo:'rol_usuario', usuario:usuario_activo}, function(respuestajson){
		// 	var rol_usuario = JSON.parse(respuestajson);


        $.post('vistas.php', function(respuestajson){
            array_usuario = JSON.parse(respuestajson);
            // console.log(array_usuario.length);

            for(i=0; i<array_usuario.length; i++){
                // console.log("hola");
                console.log(array_usuario[i]);

                var miHtml = "<tr>"+
                "<td>"+array_usuario[i]['usuario']+"</td>"+
                "<td>"+array_usuario[i]['password']+"</td>"+
                "<td>"+array_usuario[i]['edad']+"</td>"+
                "<td>"+array_usuario[i]['email']+"</td>"+
                "</tr>";


                $('#body-tabla').append(miHtml);
    

            }

           
        });



    $('#myTabla').DataTable();
} );
</script>
</html>