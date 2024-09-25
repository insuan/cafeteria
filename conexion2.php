<?php
function conectar(){
    $host = "localhost";
    $user = "root";
    $pass = "";
    $bd = "logueo";

	$id_con=new mysqli($host,$user,$pass,$bd);
	//$id_con=mysqli_connect("localhost","u553097451_benyi","root12345","u553097451_sitio");
	// Check connection
	if (mysqli_connect_errno()){
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	// Change character set to utf8
	mysqli_set_charset($id_con,"utf8");	 
	
	return($id_con);
}
function conectar_aws2(){
	$serverName = "database-2.cpslvbzjqr7g.us-east-1.rds.amazonaws.com"; 
	$uid = "admin";   
	$pwd = '$RDSb4s3$'; // contraseña
	$databaseName = "online"; 
	$connectionInfo = array( "UID"=>$uid,                            
							 "PWD"=>$pwd,                            
							 "Database"=>$databaseName,
                             'Encrypt' => 'no',
							 "ReturnDatesAsStrings" =>1); //"ReturnDatesAsStrings" =>1 funcion para que NO me devuelva el datetime como objeto y sea legible  
	$id_con = sqlsrv_connect( $serverName, $connectionInfo);  
	if( $id_con ){
	//   echo "Establecida la conexion";
	}else{
	 echo "NOOOOOOOOO conecto a: ".$databaseName;
	 echo'<pre>';
	 print_r(sqlsrv_errors());
	 echo'<pre>';
	}	
 	return $id_con;
}
function conectar_aws(){
    $serverName = "database-2.cpslvbzjqr7g.us-east-1.rds.amazonaws.com";
    $uid = "admin";
    $pwd = '$RDSb4s3$'; // contraseña
    $databaseName = "online";

    try {
        $conn = new PDO(
            "sqlsrv:Server=$serverName;Database=$databaseName;Encrypt=no",
            $uid,
            $pwd,
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            )
        );

        return $conn;
    } catch (PDOException $e) {
        echo "Error de conexión: " . $e->getMessage();
        return null;
    }
}


// function conectar_mssql(){
// 	$serverName = "richet.online";  // nombre de tu servidor SQL Server
// 	$connectionOptions = array(
// 		"Database" => "online",  // nombre de  base de datos
// 		"Uid" => "admin",                // nombre de usuario
// 		"PWD" => "$RDSb4s3$"                    // contraseña
// 	);	
// 	$id_con=sqlsrv_connect($serverName, $connectionOptions);
// 	// Check connection
// 	if ($conn === false) {
// 		die(print_r(sqlsrv_errors(), true));
// 	}
// 	return($id_con);
// }

			
?>