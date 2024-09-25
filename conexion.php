<?php

        $host = "localhost";
        $db = "logueo";
        $user = "root";
        $pass = "";
        
        $conexion = new mysqli($host,$user,$pass);

        if(mysqli_connect_errno()){
            echo "Fallo la conexion con la BD";
            exit();
        }

        mysqli_select_db($conexion,$db) or die("No se encontro la BD");

        $consulta = "SELECT * FROM datos";

        $resultado = mysqli_query($conexion,$consulta);

        while($fila = mysqli_fetch_array($resultado, MYSQLI_ASSOC)){

            
            // echo $fila["usuario"] . "  ";
            // echo $fila["password"] . "  ";
            // echo $fila["edad"] . "  ";
            // echo $fila["email"] . "<br>";
            $array_salida[]=$fila;

        }

        // echo JSON_encode($fila);
        echo JSON_encode($array_salida);



        mysqli_close($conexion);

?>
