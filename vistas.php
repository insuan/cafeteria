<?php
include_once("conexion_pruebas.php");
function consulta_test2(){
	$id_con = conectar();
	mysqli_select_db($id_con,"logueo");
	$consulta="SELECT * FROM datos";
	$resultado=mysqli_query($id_con, $consulta);
	
	while($fila = mysqli_fetch_array($resultado,MYSQLI_ASSOC))
	{
		$array_salida[] = $fila;
		echo(json_encode($array_salida));
	}
	if (!empty($array_salida))
	{	
		return json_encode($array_salida);

	}else{
		$array_salida = 0; 
		return $array_salida;
	}
	
	
	mysqli_close($id_con);	
}

consulta_test2();

?>