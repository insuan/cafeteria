<?php

        $host = "localhost";
        $db = "logueo";
        $user = "root";
        $pass = "";

        $id_con = new mysqli($host,$user,$pass);

        if(mysqli_connect_errno()){
            echo "Fallo la conexion con la BD" . mysqli_connect_errno();
            exit();
        }else{
            // echo "No hay ningun problema con la conexion..." . $db;
        }

        mysqli_set_charset($id_con,"utf8");

        mysqli_select_db($id_con,$db) or die("No se encontro la BD");

        $consulta="SELECT * FROM datos";
	    $resultado=mysqli_query($id_con, $consulta);

        while($fila = mysqli_fetch_array($resultado, MYSQLI_ASSOC)){

            
            // echo $fila["usuario"] . "  ";
            // echo $fila["password"] . "  ";
            // echo $fila["edad"] . "  ";
            // echo $fila["email"] . "<br>";
            $array_salida[]=$fila;

        }

        // echo JSON_encode($fila);
        $prueba = JSON_encode($array_salida);
        printf($prueba);

        mysqli_close($id_con);


?>