<?php
    function conectar(){
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

        return($id_con);


    }
        // $prueba = conectar();

?>