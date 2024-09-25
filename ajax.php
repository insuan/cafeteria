<?php
include("./vistas.php"); 	
include("./funciones.php");	
ini_set('memory_limit', '1024M');
//include the file that loads the PhpSpreadsheet classes
require 'spreadsheet/vendor/autoload.php';

//include the classes needed to create and write .xlsx file
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; 
use PhpOffice\PhpSpreadsheet\IOFactory;


switch($_POST['tipo']){
	//----------------------------------------------------------------------------------------
	case 'facturas':
		$array_facturas = facturas($_POST['fecha_desde'], $_POST['fecha_hasta']);
		// $array_facturas = canal_jefe_vendedor();
		echo json_encode($array_facturas);	
	break;		
	//----------------------------------------------------------------------------------------	
	//----------------------------------------------------------------------------------------
	case 'semi_proceso':
		$array_producto = semi_producto();
		$array_stk30 = semi_stk();
		$array_prd = prd();

		//primero remplaza los valores ordfscntya en ordfscntor y demas;
		update_semi_orfab();
		$array_orfab = semi_orfab(); 

		//Procesa datos
		$array_prueba = proceso_semi_orfab_stk(); 
		for($i=0; $i<count($array_stk30); $i++){
			$encontro=0;
			for($z=0; $z<count($array_orfab); $z++){
				if($array_stk30[$i]['stkentnrol'] ==  $array_orfab[$z]['OrdfNroLot'] 
				&& $array_stk30[$i]['PrdId'] ==  $array_orfab[$z]['OrdfIdRec']){
					$encontro++;
				}
			}
			if($encontro == 0){
				// Registro Nuevo
				$nuevoRegistro = array(
					OrdfCntOrd => "0",
					OrdfEst => "",
					OrdfFchEmi => "1900-01-01 00:00:00",
					OrdfId => "0",
					OrdfIdEta => "",
					OrdfIdRec => $array_stk30[$i]['PrdId'],
					OrdfIdTare => "0",
					OrdfNroLot => $array_stk30[$i]['stkentnrol'],
					OrdfsCntOr => "0",
					OrdfsCntSt => "0",
					OrdfsCntYa => "0",
					OrdfsIdPrd => "0",
					OrdfsIdRen => "0",
					OrdftEst => "",
					PrdClaId => "",
					PrdIdRub => "",
					PrdTxtAmp => $array_stk30[$i]['prdtxtamp'], 
					dife => "",
					fecha_m1 => "1900-01-01 00:00:00",
					fecha_m2 => "1900-01-01 00:00:00",
					fecha_m3 => "1900-01-01 00:00:00",
					id => "0",
					pan =>"",
					pt => "",
					sinalma => "",
					tempo => "",
					total => $array_stk30[$i]['sum_exp1']
				);				
				array_push($array_orfab, $nuevoRegistro);
			}
		}

		//sinmp por el momento no la voy a usar por que no se de donde viene 
		$array_sinmp = [];

		// SELECT productos
		// SET ORDER TO 1   && ALLTRIM(PRDID)
		// GO top
		
		// SELECT ordfab
		// SET ORDER TO 1   && ALLTRIM(ORDFIDREC)
		// SET FILTER TO (ordfab.ordfidrec >= '40000000' AND ordfab.ordfidrec <= '59999999') or;
		// 			  (ordfab.ordfidrec >= '70000000' AND ordfab.ordfidrec <= '99999999') 	
		// GO TOP
		// DO WHILE !EOF()
		// 	XX = ' '
		// 	XX = ALLTRIM(ordfab.ordfidrec)
		// 	SELECT PRODUCTOS
		// 	SEEK ALLTRIM(ordfab.ORDFSIDPRD)
		// 	IF FOUND()
		// 		SELECT ordfab
		// 		REPLACE ordfidrec  WITH PRODUCTOS.PRDID
		// 		REPLACE PRDTXTAMP  WITH PRODUCTOS.PRDTXTAMP
		// 		REPLACE ORDFSIDPRD WITH XX
		// 		IF (XX >= '40000000' AND XX <= '59999999') OR ;
		// 			  (XX >= '70000000' AND XX <= '99999999')
		// 			  REPLACE pt WITH 'X'
		// 		   ENDIF
		// 	ENDIF
		// 	SELECT ordfab
		// 	SKIP
		// ENDDO	


		for($i=0; $i<count($array_orfab); $i++){
			if(($array_orfab[$i]['ordfidrec'] >= '40000000' && $array_orfab[$i]['ordfidrec'] <= '59999999') || ($array_orfab[$i]['ordfidrec'] >= '70000000' && $array_orfab[$i]['ordfidrec'] <= '99999999')){
				for($z=0; $z<count($array_prd); $z++){
					$xx = $array_orfab[$i]['ordfidrec'];
					if($array_orfab[$i]['OrdfsIdPrd'] == $array_prd[$z]['PrdId']){
						$array_orfab[$i]['ordfidrec'] = $array_prd[$i]['PrdId'];
						$array_orfab[$i]['PrdTxtAmp'] = $array_prd[$i]['PrdTxtAmp'];
						$array_orfab[$i]['OrdfsIdPrd'] = $xx;
						if(($xx >= '40000000' && $xx <= '59999999') 
						|| ($xx >= '70000000' && $xx <= '99999999')){
							$array_orfab[$i]['pt'] = 'X';
						}
					}
				}
			}
		}		



		// for($i=0; $i<count($array_orfab); $i++){
		// 	$encontro=0;
		// 	for($z=0; $z<count($array_prd); $z++){
		// 		if($array_orfab[$i]['OrdfsIdPrd'] ==  $array_prd[$z]['prdid']){
		// 			$array_orfab[$i]['ordfidrec'] = $array_prd[$z]['prdid'];
		// 			$array_orfab[$i]['PrdTxtAmp'] = $array_prd[$z]['PrdTxtAmp'];
		// 			$encontro++;

		// 		}
		// 	}
		// }

		


			// $XX = ' ';
			// $XX = trim(odbc_result($result, 'ordfidrec'));
		
			// // SELECT PRODUCTOS
			// $productosResult = odbc_exec($db, "SELECT * FROM PRODUCTOS WHERE PRDSIDPRD = '" . $XX . "'");
		
			// IF FOUND()
			// if (odbc_fetch_row($productosResult)) {
			// 	// SELECT ordfab
			// 	$ordfabResult = odbc_exec($db, "SELECT * FROM ordfab");
		
			// 	// REPLACE ordfidrec WITH PRODUCTOS.PRDID
			// 	odbc_exec($db, "UPDATE ordfab SET ordfidrec = '" . trim(odbc_result($productosResult, 'PRDID')) . "' WHERE ordfidrec = '" . $XX . "'");
		
			// 	// REPLACE PRDTXTAMP WITH PRODUCTOS.PRDTXTAMP
			// 	odbc_exec($db, "UPDATE ordfab SET PRDTXTAMP = '" . trim(odbc_result($productosResult, 'PRDTXTAMP')) . "' WHERE ordfidrec = '" . $XX . "'");
		
			// 	// REPLACE ORDFSIDPRD WITH XX
			// 	odbc_exec($db, "UPDATE ordfab SET ORDFSIDPRD = '" . $XX . "' WHERE ordfidrec = '" . $XX . "'");
		
			// 	// IF (XX >= '40000000' AND XX <= '59999999') OR (XX >= '70000000' AND XX <= '99999999')
			// 	if ((intval($XX) >= 40000000 && intval($XX) <= 59999999) || (intval($XX) >= 70000000 && intval($XX) <= 99999999)) {
			// 		// REPLACE pt WITH 'X'
			// 		odbc_exec($db, "UPDATE ordfab SET pt = 'X' WHERE ordfidrec = '" . $XX . "'");
			// 	}
			// }
		
			// // SELECT ordfab
			// $ordfabResult = odbc_exec($db, "SELECT * FROM ordfab");
		
			// // SKIP
			// odbc_fetch_row($ordfabResult);
		
		
		
		






		// SELECT STK30
		// SET FILTER TO
		// GO TOP
		// DO WHILE !EOF()
		// 	SELECT ordfab
		// 	SEEK ALLTRIM(STK30.STKENTNROL)+ALLTRIM(STK30.prdid)
		// 	IF !FOUND()
		// 		APPEND BLANK
		// 		REPLACE ORDFIDREC  WITH STK30.PRDID
		// 		REPLACE PRDTXTAMP  WITH STK30.PRDTXTAMP
		// 		REPLACE ORDFNROLOT WITH STK30.STKENTNROL
		// 		REPLACE TOTAL      WITH STK30.sum_exp1
		// 	ENDIF
		// 	SELECT STK30
		// 	SKIP
		// ENDDO


		// if($array_clasificacion != 0){
		// 	//updateo
		// 	$update_clasificacion= update_clasificacion($_POST['wcodigo'], 
		// 												$_POST['wpresenta'], 
		// 												$_POST['wproducto'], 
		// 												$_POST['wcentro'], 
		// 												$_POST['wlinea'], 
		// 												$_POST['wdroga'], 
		// 												$_POST['wmultiplo'], 
		// 												$_POST['wdivisor']);
														
		// }else{
		// 	//inserteo :)
		// 	$update_clasificacion= insert_clasificacion($_POST['wcodigo'], 
		// 												$_POST['wpresenta'], 
		// 												$_POST['wproducto'], 
		// 												$_POST['wcentro'], 
		// 												$_POST['wlinea'], 
		// 												$_POST['wdroga'], 
		// 												$_POST['wmultiplo'], 
		// 												$_POST['wdivisor']);			
		// }
		//agrupo arrays en uno solo
		$array_semis = array('array_producto' => $array_producto,
							'array_stk30' => $array_stk30,
							'array_orfab' => $array_orfab,
							'array_prueba' => $array_prueba,
							'nuevo_registro' => $nuevoRegistro
							// 'ventas_linea_chicos' => $array_venta_linea_3m_chicos,
							// 'ventas_solidos' => $array_venta_linea_3m_solidos,
							// 'ventas_inyectables' => $array_venta_linea_3m_inyectables,
							// 'ventas_suspensiones' => $array_venta_linea_3m_suspensiones,
							// 'linea' => $array_linea_3m,
							// 'semis' => $array_semi_mes,
							);
		echo json_encode($array_semis);	
	break;		
	//----------------------------------------------------------------------------------------
	case 'guarda_clasificacion':
		$array_clasificacion = busca_clasificacion($_POST['wcodigo']);
		if($array_clasificacion != 0){
			//updateo
			$update_clasificacion= update_clasificacion($_POST['wcodigo'], 
														$_POST['wpresenta'], 
														$_POST['wproducto'], 
														$_POST['wcentro'], 
														$_POST['wlinea'], 
														$_POST['wdroga'], 
														$_POST['wmultiplo'], 
														$_POST['wdivisor']);
														
		}else{
			//inserteo :)
			$update_clasificacion= insert_clasificacion($_POST['wcodigo'], 
														$_POST['wpresenta'], 
														$_POST['wproducto'], 
														$_POST['wcentro'], 
														$_POST['wlinea'], 
														$_POST['wdroga'], 
														$_POST['wmultiplo'], 
														$_POST['wdivisor']);			
		}
		echo json_encode($update_clasificacion);
	break;	
	//----------------------------------------------------------------------------------------
	case 'busca_clasificacion':
		$array_clasificacion = busca_clasificacion($_POST['codigo']);
		echo json_encode($array_clasificacion);
	break;	
	//----------------------------------------------------------------------------------------
	case 'bajada_datos_semi_vta_est':
		$array_linea_3m = estimados_corregidas_periodos_3m($_POST['version'], 'wlinea');

		//consulto las lineas con mas datos y las separo del resto de las lineas con menos datos pedido por andres mail 23/03/2023
		//ademas hago la separacion de las lineas con grandes volumenes separo
		if($_POST['tiempo'] == 'presente'){
			$array_venta_linea_3m = ventas_ultimos_3meses('wlinea', '1=1');
			$array_semi_mes = semi_mes('wlinea');
		}else{
			$array_venta_linea_3m = ventas_ultimos_3meses_pasado('wlinea', '1=1');
			$array_semi_mes = semi_mes_pasado('wlinea');
		}
		
		$array_venta_linea_3m_grandes = array();
		$array_venta_linea_3m_chicos = array();
		$array_venta_linea_3m_solidos = array();
		$array_venta_linea_3m_inyectables = array();
		$array_venta_linea_3m_suspensiones = array();
		

		//********** x linea */
		//aca agrego los datos de los promedios en un solo array el de ventas
		//agrego a ventas $array_venta_linea_3m un indice mas con el promedio de 3 meses de estimados x linea de produccion 
		//no lo hago en sql por que se tara :(
		for($i=0; $i<count($array_linea_3m); $i++){
			for($z=0; $z<count($array_venta_linea_3m); $z++){
				if($array_linea_3m[$i]['wlinea'] == $array_venta_linea_3m[$z]['wlinea']){
					//separo los grandes de los chicos por volumen ya los conozco por nombre
					switch($array_linea_3m[$i]['wlinea']){
					// 	//----------------------------------------------------------------------------------------
						case 'CAPSULAS NO BETA':
							array_push($array_venta_linea_3m_grandes, array('promedio_estimado' => $array_linea_3m[$i]['promedio_baj'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'wlinea' => $array_venta_linea_3m[$z]['wlinea']
																		));							
						break;	
						case 'COMPRIMIDOS NO BETA':
							array_push($array_venta_linea_3m_grandes, array('promedio_estimado' => $array_linea_3m[$i]['promedio_baj'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'wlinea' => $array_venta_linea_3m[$z]['wlinea']
																		));							
						break;
						case 'COMPRIMIDOS BETA':
							array_push($array_venta_linea_3m_grandes, array('promedio_estimado' => $array_linea_3m[$i]['promedio_baj'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'wlinea' => $array_venta_linea_3m[$z]['wlinea']																		
																		));							
						break;
						case 'OTROS':
							array_push($array_venta_linea_3m_chicos, array('promedio_estimado' => $array_linea_3m[$i]['promedio_baj'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'wlinea' => $array_venta_linea_3m[$z]['wlinea']
																		));							
						break;
						case 'AGUA Y SOLVENTE':
							array_push($array_venta_linea_3m_chicos, array('promedio_estimado' => $array_linea_3m[$i]['promedio_baj'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'wlinea' => $array_venta_linea_3m[$z]['wlinea']
																		));							
						break;
						case 'AMPOLLAS NO BETA':
							array_push($array_venta_linea_3m_chicos, array('promedio_estimado' => $array_linea_3m[$i]['promedio_baj'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'wlinea' => $array_venta_linea_3m[$z]['wlinea']
																		));							
						break;
						case 'BIOLOGICOS':
							array_push($array_venta_linea_3m_chicos, array('promedio_estimado' => $array_linea_3m[$i]['promedio_baj'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'wlinea' => $array_venta_linea_3m[$z]['wlinea']
																		));										
						break;
						case 'GRANDES VOLUMENES NO BETA':
							array_push($array_venta_linea_3m_chicos, array('promedio_estimado' => $array_linea_3m[$i]['promedio_baj'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'wlinea' => $array_venta_linea_3m[$z]['wlinea']
																		));										
						break;
						case 'LIOFILIZADO NO BETA':
							array_push($array_venta_linea_3m_chicos, array('promedio_estimado' => $array_linea_3m[$i]['promedio_baj'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'wlinea' => $array_venta_linea_3m[$z]['wlinea']
																		));																
						break;
						case 'VIAL POLVO BETA':
							array_push($array_venta_linea_3m_chicos, array('promedio_estimado' => $array_linea_3m[$i]['promedio_baj'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'wlinea' => $array_venta_linea_3m[$z]['wlinea']
																		));									
						break;
						case 'VIALES LIQUIDOS NO BETA':
							array_push($array_venta_linea_3m_chicos, array('promedio_estimado' => $array_linea_3m[$i]['promedio_baj'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'wlinea' => $array_venta_linea_3m[$z]['wlinea']
																		));								
						break;
						case 'VIALES POLVO NO BETA':
							array_push($array_venta_linea_3m_chicos, array('promedio_estimado' => $array_linea_3m[$i]['promedio_baj'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'wlinea' => $array_venta_linea_3m[$z]['wlinea']
																		));									
						break;
						case 'SUSPENSIONES BETA':
							array_push($array_venta_linea_3m_chicos, array('promedio_estimado' => $array_linea_3m[$i]['promedio_baj'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'wlinea' => $array_venta_linea_3m[$z]['wlinea']
																		));									
						break;	
						case 'SUSPENSIONES NO BETA':
							array_push($array_venta_linea_3m_chicos, array('promedio_estimado' => $array_linea_3m[$i]['promedio_baj'],
																			'promedio_uni3' => $array_venta_linea_3m[$z]['promedio_uni3'],
																			'wlinea' => $array_venta_linea_3m[$z]['wlinea']
																		));									
						break;																																																																								
					}					
				}
			}
		}

		//agrego a $array_ventas gran y chico volumen un indice mas con la produccion de semielaborados actual x linea de produccion 
		//**** grandes
		for($i=0; $i<count($array_semi_mes); $i++){
			for($z=0; $z<count($array_venta_linea_3m_grandes); $z++){
				if($array_semi_mes[$i]['wlinea'] == $array_venta_linea_3m_grandes[$z]['wlinea']){
					$array_venta_linea_3m_grandes[$z]['semi_tot'] = $array_semi_mes[$i]['corregidas_tot'];
				}
			}
		}	
		for($z=0; $z<count($array_venta_linea_3m_grandes); $z++){
			$noencontro = 0;
			for($i=0; $i<count($array_semi_mes); $i++){
				if($array_venta_linea_3m_grandes[$z]['wlinea'] == $array_semi_mes[$i]['wlinea']){
					$noencontro++;
				}
			}
			if($noencontro == 0){
				$array_venta_linea_3m_grandes[$z]['semi_tot'] = 0;
			}			
		}
		//**** chicos
		for($i=0; $i<count($array_semi_mes); $i++){
			for($z=0; $z<count($array_venta_linea_3m_chicos); $z++){
				if($array_semi_mes[$i]['wlinea'] == $array_venta_linea_3m_chicos[$z]['wlinea']){
					$array_venta_linea_3m_chicos[$z]['semi_tot'] = $array_semi_mes[$i]['corregidas_tot'];
				}
			}
		}	
		for($z=0; $z<count($array_venta_linea_3m_chicos); $z++){
			$noencontro = 0;
			for($i=0; $i<count($array_semi_mes); $i++){
				if($array_venta_linea_3m_chicos[$z]['wlinea'] == $array_semi_mes[$i]['wlinea']){
					$noencontro++;
				}
			}
			if($noencontro == 0){
				$array_venta_linea_3m_chicos[$z]['semi_tot'] = 0;
			}			
		}	

		//*** agupando y promediando lineas */	
		//*** Modificacion 26/04/2023 andres no quiere el promedio de los grupos Solidos, inyectables y suspensiones quiere la sumatoria */
		//*** sigo dejando la variable promedio pero es la suma*/ 
		//solidos son todos las lineas que estan dentro de "Grandes" asi que solo recorro, sumo y promedio no comparo
		$promedio_estimado_solidos = 0;
		$promedio_ventas_solidos = 0;
		$promedio_semis_solidos = 0;
		$contador = 0;
		for($z=0; $z<count($array_venta_linea_3m_grandes); $z++){
			$promedio_estimado_solidos = ($promedio_estimado_solidos + $array_venta_linea_3m_grandes[$z]['promedio_estimado']);
			$promedio_ventas_solidos = ($promedio_ventas_solidos + $array_venta_linea_3m_grandes[$z]['promedio_uni3']);
			$promedio_semis_solidos = ($promedio_semis_solidos + $array_venta_linea_3m_grandes[$z]['semi_tot']);
			$contador++;
		}		
		// array_push($array_venta_linea_3m_solidos, array('promedio_estimado' => round($promedio_estimado_solidos/$contador, 2),
		// 												'promedio_uni3' => round($promedio_ventas_solidos/$contador, 2),
		// 												'semi_tot' => round($promedio_semis_solidos/$contador, 2),
		// 												'wlinea' => 'Solidos'
		// 											));		
		array_push($array_venta_linea_3m_solidos, array('promedio_estimado' => round($promedio_estimado_solidos, 2),
														'promedio_uni3' => round($promedio_ventas_solidos, 2),
														'semi_tot' => round($promedio_semis_solidos, 2),
														'wlinea' => 'Solidos'
													));												
		//inyectables 
		$promedio_estimado_inyectables = 0;
		$promedio_ventas_inyectables = 0;
		$promedio_semis_inyectables = 0;
		$contador = 0;
		for($z=0; $z<count($array_venta_linea_3m_chicos); $z++){
			$contador++;
			switch($array_venta_linea_3m_chicos[$z]['wlinea']){
				case 'AGUA Y SOLVENTE':
					$promedio_estimado_inyectables = ($promedio_estimado_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_estimado']);
					$promedio_ventas_inyectables = ($promedio_ventas_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_uni3']);
					$promedio_semis_inyectables = ($promedio_semis_inyectables + $array_venta_linea_3m_chicos[$z]['semi_tot']);
				break;
				case 'AMPOLLAS NO BETA':
					$promedio_estimado_inyectables = ($promedio_estimado_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_estimado']);
					$promedio_ventas_inyectables = ($promedio_ventas_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_uni3']);
					$promedio_semis_inyectables = ($promedio_semis_inyectables + $array_venta_linea_3m_chicos[$z]['semi_tot']);						
				break;
				case 'BIOLOGICOS':
					$promedio_estimado_inyectables = ($promedio_estimado_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_estimado']);
					$promedio_ventas_inyectables = ($promedio_ventas_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_uni3']);
					$promedio_semis_inyectables = ($promedio_semis_inyectables + $array_venta_linea_3m_chicos[$z]['semi_tot']);							
				break;
				case 'GRANDES VOLUMENES NO BETA':
					$promedio_estimado_inyectables = ($promedio_estimado_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_estimado']);
					$promedio_ventas_inyectables = ($promedio_ventas_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_uni3']);
					$promedio_semis_inyectables = ($promedio_semis_inyectables + $array_venta_linea_3m_chicos[$z]['semi_tot']);							
				break;
				case 'LIOFILIZADO NO BETA':
					$promedio_estimado_inyectables = ($promedio_estimado_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_estimado']);
					$promedio_ventas_inyectables = ($promedio_ventas_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_uni3']);
					$promedio_semis_inyectables = ($promedio_semis_inyectables + $array_venta_linea_3m_chicos[$z]['semi_tot']);							
				break;
				case 'VIAL POLVO BETA':
					$promedio_estimado_inyectables = ($promedio_estimado_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_estimado']);
					$promedio_ventas_inyectables = ($promedio_ventas_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_uni3']);
					$promedio_semis_inyectables = ($promedio_semis_inyectables + $array_venta_linea_3m_chicos[$z]['semi_tot']);							
				break;
				case 'VIALES LIQUIDOS NO BETA':
					$promedio_estimado_inyectables = ($promedio_estimado_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_estimado']);
					$promedio_ventas_inyectables = ($promedio_ventas_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_uni3']);
					$promedio_semis_inyectables = ($promedio_semis_inyectables + $array_venta_linea_3m_chicos[$z]['semi_tot']);							
				break;
				case 'VIALES POLVO NO BETA':
					$promedio_estimado_inyectables = ($promedio_estimado_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_estimado']);
					$promedio_ventas_inyectables = ($promedio_ventas_inyectables + $array_venta_linea_3m_chicos[$z]['promedio_uni3']);
					$promedio_semis_inyectables = ($promedio_semis_inyectables + $array_venta_linea_3m_chicos[$z]['semi_tot']);							
				break;
			}			
		}	
		array_push($array_venta_linea_3m_inyectables, array('promedio_estimado' => round($promedio_estimado_inyectables, 2),
															'promedio_uni3' => round($promedio_ventas_inyectables, 2),
															'semi_tot' => round($promedio_semis_inyectables, 2),
															'wlinea' => 'Inyectables'
														));		

		//suspensiones 
		$promedio_estimado_suspensiones = 0;
		$promedio_ventas_suspensiones = 0;
		$promedio_semis_suspensiones = 0;
		$contador = 0;
		for($z=0; $z<count($array_venta_linea_3m_chicos); $z++){
			$contador++;
			switch($array_venta_linea_3m_chicos[$z]['wlinea']){
				case 'SUSPENSIONES NO BETA':
					$promedio_estimado_suspensiones = ($promedio_estimado_suspensiones + $array_venta_linea_3m_chicos[$z]['promedio_estimado']);
					$promedio_ventas_suspensiones = ($promedio_ventas_suspensiones + $array_venta_linea_3m_chicos[$z]['promedio_uni3']);
					$promedio_semis_suspensiones = ($promedio_semis_suspensiones + $array_venta_linea_3m_chicos[$z]['semi_tot']);							
				break;
				case 'SUSPENSIONES BETA':
					$promedio_estimado_suspensiones = ($promedio_estimado_suspensiones + $array_venta_linea_3m_chicos[$z]['promedio_estimado']);
					$promedio_ventas_suspensiones = ($promedio_ventas_suspensiones + $array_venta_linea_3m_chicos[$z]['promedio_uni3']);
					$promedio_semis_suspensiones = ($promedio_semis_suspensiones + $array_venta_linea_3m_chicos[$z]['semi_tot']);							
				break;
				// case 'OTROS':
				// 	$promedio_estimado_suspensiones = ($promedio_estimado_suspensiones + $array_venta_linea_3m_chicos[$z]['promedio_estimado']);
				// 	$promedio_ventas_suspensiones = ($promedio_ventas_suspensiones + $array_venta_linea_3m_chicos[$z]['promedio_uni3']);
				// 	$promedio_semis_suspensiones = ($promedio_semis_suspensiones + $array_venta_linea_3m_chicos[$z]['semi_tot']);							
				// break;				
			}			
		}	
		array_push($array_venta_linea_3m_suspensiones, array('promedio_estimado' => round($promedio_estimado_suspensiones, 2),
															'promedio_uni3' => round($promedio_ventas_suspensiones, 2),
															'semi_tot' => round($promedio_semis_suspensiones, 2),
															'wlinea' => 'Suspensiones'
														));															
											
		
		//agrupo arrays en uno solo
		$array_datos = array('ventas_linea' => $array_venta_linea_3m,
							'ventas_linea_grandes' => $array_venta_linea_3m_grandes,
							'ventas_linea_chicos' => $array_venta_linea_3m_chicos,
							'ventas_solidos' => $array_venta_linea_3m_solidos,
							'ventas_inyectables' => $array_venta_linea_3m_inyectables,
							'ventas_suspensiones' => $array_venta_linea_3m_suspensiones,
							'linea' => $array_linea_3m,
							'semis' => $array_semi_mes,
							);
		echo json_encode($array_datos);		
	break;	


	//----------------------------------------------------------------------------------------
	case 'estimados_corregidas_periodos':
		$array_centro = estimados_corregidas_periodos($_POST['version'], 'wcentro');
		$array_linea = estimados_corregidas_periodos($_POST['version'], 'wlinea');
		$array_producto = estimados_corregidas_periodos($_POST['version'], 'wproducto');
		$array_canal = estimados_corregidas_periodos($_POST['version'], 'wcanal');
		$array_venta6_centro = ventas_ultimos_6meses('wcentro');
		$array_venta6_linea = ventas_ultimos_6meses('wlinea');
		$array_venta6_producto = ventas_ultimos_6meses('facprdid');
		$array_venta6_canal = ventas_ultimos_6meses('gtpvtades');

		//agrego a ventas $array_venta6 un indice mas con el promedio de cpa y 1 indice haciendo el calculo de crecimiento estimado
		//no lo hago en sql por que se tara :(
		for($i=0; $i<count($array_centro); $i++){
			for($z=0; $z<count($array_venta6_centro); $z++){
				if($array_centro[$i]['wcentro'] == $array_venta6_centro[$z]['wcentro']){
					$crecimiento_estimado = round((($array_centro[$i]['promedio']/$array_venta6_centro[$z]['promedio_uni6'])-1)*100,2);
					$array_venta6_centro[$z]['crecimiento'] = $crecimiento_estimado;
					$array_venta6_centro[$z]['promedio_estimado'] = $array_centro[$i]['promedio'];
				}
			}
		}
		for($i=0; $i<count($array_linea); $i++){
			for($z=0; $z<count($array_venta6_linea); $z++){
				if($array_linea[$i]['wlinea'] == $array_venta6_linea[$z]['wlinea']){
					$crecimiento_estimado = round((($array_linea[$i]['promedio']/$array_venta6_linea[$z]['promedio_uni6'])-1)*100,2);
					$array_venta6_linea[$z]['crecimiento'] = $crecimiento_estimado;
					$array_venta6_linea[$z]['promedio_estimado'] = $array_linea[$i]['promedio'];
				}
			}
		}

		//producto al derecho y al revez por que puede que este estimado y no se haya vendido o que se haya vendido pero no estimado
		for($i=0; $i<count($array_producto); $i++){
			for($z=0; $z<count($array_venta6_producto); $z++){
				if($array_producto[$i]['wproducto'] == $array_venta6_producto[$z]['facprdid']){
					$crecimiento_estimado = round((($array_producto[$i]['promedio']/$array_venta6_producto[$z]['promedio_uni6'])-1)*100,2);
					$array_venta6_producto[$z]['crecimiento'] = $crecimiento_estimado;
					$array_venta6_producto[$z]['promedio_estimado'] = $array_producto[$i]['promedio'];
				}
			}
		}
		for($z=0; $z<count($array_venta6_producto); $z++){
			$noencontro = 0;
			for($i=0; $i<count($array_producto); $i++){
				if($array_venta6_producto[$z]['facprdid'] == $array_producto[$i]['wproducto']){
					$noencontro++;
				}
			}
			if($noencontro == 0){
				$crecimiento_estimado = 0;
				$array_venta6_producto[$z]['crecimiento'] = $crecimiento_estimado;
				$array_venta6_producto[$z]['promedio_estimado'] = 0;
			}			
		}		
		// //fin producto al derecho y al revez
		for($i=0; $i<count($array_canal); $i++){
			for($z=0; $z<count($array_venta6_canal); $z++){
				if($array_canal[$i]['wdescanal'] == $array_venta6_canal[$z]['gtpvtades']){
					$crecimiento_estimado = round((($array_canal[$i]['promedio']/$array_venta6_canal[$z]['promedio_uni6'])-1)*100,2);
					$array_venta6_canal[$z]['crecimiento'] = $crecimiento_estimado;
					$array_venta6_canal[$z]['promedio_estimado'] = $array_canal[$i]['promedio'];
				}
			}
		}				

		//agrupo arrays en uno solo
		$array_estimados = array('centro' => $array_centro,
								 'linea' => $array_linea,
								 'producto' => $array_producto,
								 'canal' => $array_canal,
								 'ventas_centro' => $array_venta6_centro,
								 'ventas_linea' => $array_venta6_linea,
								 'ventas_producto' => $array_venta6_producto,
								 'ventas_canal' => $array_venta6_canal
								);
		echo json_encode($array_estimados);		
	break;
	case 'ultima_ver_estimados':
		$array_version = ultima_ver_estimados();
		echo json_encode($array_version);		
	break;		
	case 'ultimo_recibo':
		$array_urec = ultimo_recibo();
		echo json_encode($array_urec);		
	break;			
	case 'recibos_id':
		$array_rec = select_recibos_id($_POST['id']);
		echo json_encode($array_rec);		
	break;		
	case 'cabecera_recibos':
		$array_cab_rec = select_cabecera_recibos($_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['vendedor']);
		echo json_encode($array_cab_rec);		
	break;			
	case 'consulta_test2':
		//consultar la tabla test2
		$array_test2 = consulta_test2($_POST['usuario'], $_POST['password']);
		echo json_encode($array_test2);		
	break;	
	case 'cierre_diario':
		$array_cierre_diario = cierre_diario($_POST['fecha']);
//		header('Content-Type: application/json');
		echo json_encode($array_cierre_diario);
	break;	
	case 'totales_estadisticas':
		
		$array_actual = totales_estadisticas($_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['fecha_hoy']);
		$array_pasado = totales_estadisticas($_POST['fecha_desde_pasada'], $_POST['fecha_hasta_pasada'], $_POST['fecha_hoy_pasada']);		
		$array_totales_estadisticas = array('actual' => $array_actual,
					   						'pasado' => $array_pasado);
		echo json_encode($array_totales_estadisticas);
	break;
	case 'ultima_fac_expo':
		$array_actual = ultima_fac_expo($_POST['fecha_desde'], $_POST['fecha_hasta']);
		$array_pasado = ultima_fac_expo($_POST['fecha_desde_pasada'], $_POST['fecha_hasta_pasada']);
		$array_ultima_fac_expo = array('actual' => $array_actual,
					   				   'pasado' => $array_pasado);		
		echo json_encode($array_ultima_fac_expo);
	break;		
	case 'premios_historicos_jefes':	
		function periodos_generales($fecha_desde_g, 
							$fecha_hasta_g,
						    $w_periodo_actual,
						    $meses){
			//preparo datos para hacer el where segun codigo de usuario puede ser un jefe o un admin=0
			//estableciendo parametros de fecha
			$fecha_actual = date('Y-m-d');
			$mywhere_periodo = '(';
			//tengo que consultar los periodos para atras por lo menos de 12 meses atras porque?
			//por que puedo tener vendedores que durante el ultimo año hayan dejado de vender y se quieran revisar por lo menos el historial
			//aunque en el periodo actual no tenga datos
			for($i=0; $i<12; $i++){
				//hago la recorrida hacia atras
				//calculo fecha temporal es la fecha actual menos $i cantidad de meses
				$fecha_temp = date('Y-m-d',strtotime($fecha_actual."- ".$i." month"));
				//calculo fechas desde y hasta en base a fecha_temp
				$fecha_desde = primer_dia_del_mes_x_fecha_amd($fecha_temp);
				$fecha_hasta = ultimo_dia_del_mes_x_fecha_amd($fecha_temp);	
				$mes = date('m',strtotime($fecha_actual."- ".$i." month"));
				$year = date('Y',strtotime($fecha_actual."- ".$i." month"));
				$w_periodo = $year.$mes;	
				//armo where de forma con los periodos
				if(12 == $i+1){
					//llegue al ultimo registro el trato para hacer la cadena cambia
					$mywhere_periodo.= 'w_periodo = '.$w_periodo.')';
				}else{
					$mywhere_periodo.= 'w_periodo = '.$w_periodo.' OR ';
				}			
			}
			//diferencio informacion entregada segun login  codigo de jefe o codigo 0 => admin
			if($_POST['codigo'] == 0){
				//consulto que! vendedores estan cargados para premios de ese jefe
				//si no hago esta consulta el totalizado por jefe me trae los datos de toooodos los vendedores de ese jefe
				//y el historico sobrepasa siempre al de los vendedores que reciben premios
				$mywhere_premios = '1=1';
				//verifica vendedores con premios
				$array_premios_jefe_vdor = premios_12meses_jefe_vdor($mywhere_premios, $mywhere_periodo);
				$new_mywhere_premios='(';
				for($z=0; $z<count($array_premios_jefe_vdor); $z++){
					//armo where de forma dinamica tipo w_codjefe = 7 AND w_pcodven = 83 OR w_codjefe = 7 AND w_pcodven = 88....
					if(count($array_premios_jefe_vdor) == $z+1){
						//llegue al ultimo registro el trato para hacer la cadena cambia
						$new_mywhere_premios.= 'w_pcodven = '.$array_premios_jefe_vdor[$z]['w_pcodven'].') AND '.$mywhere_periodo;
					}else{
						$new_mywhere_premios.= 'w_pcodven = '.$array_premios_jefe_vdor[$z]['w_pcodven'].' OR ';				
					}
				}	
			}else{
				//mismo concepto que toda la captura pero por jefe de ventas
				$mywhere_premios = 'w_codjefe = '.$_POST['codigo'];
				//verifica vendedores con premios
				$array_premios_jefe_vdor = premios_12meses_jefe_vdor($mywhere_premios, $mywhere_periodo);
				$new_mywhere_premios='(';
				for($z=0; $z<count($array_premios_jefe_vdor); $z++){
					//armo where de forma dinamica tipo w_codjefe = 7 AND w_pcodven = 83 OR w_codjefe = 7 AND w_pcodven = 88....
					if(count($array_premios_jefe_vdor) == $z+1){
						//llegue al ultimo registro el trato para hacer la cadena cambia
						$new_mywhere_premios.= 'w_pcodven = '.$array_premios_jefe_vdor[$z]['w_pcodven'].') AND '.$mywhere_periodo;
					}else{
						$new_mywhere_premios.= 'w_pcodven = '.$array_premios_jefe_vdor[$z]['w_pcodven'].' OR ';				
					}
				}	
			}			

			//Realizo las consultas de premios agrupando por jefes
			$array_premios_jefe = premios_periodo_jefe($w_periodo_actual, $mywhere_premios);
			//en un principio se presento un problema, querian ver los periodos anteriores aunque no tengan premios cargados...
			//esto es un problema por que yo recorro en base a los premios cargados, y en base a eso busco los valores correspondientes a ese periodo
			//para los jefes y vendedores pero... al no tener premios para periodos aun no cargados tengo que de alguna forma generarlos por lo menos vacios
			//eso es lo que hago con este proceso, tomo como referencia el ultimo periodo y lo replico la cantidad de meses restantes hasta llegar a los 12 meses
			//de antiguedad completando esos meses que no tienen premios con valores en cero en el array
			for($i=0; $i<count($array_premios_jefe); $i++){
				if(!is_array($array_premios_jefe)){
					$array_premios_jefe = [];
					$array_premios_jefe_temporal = premios_periodo_jefe(202012, $mywhere_premios);
					for($x=0; $x<count($array_premios_jefe_temporal); $x++){
						$array_premios_jefe[$x]['w_codjefe'] = $array_premios_jefe_temporal[$x]['w_codjefe'];
						$array_premios_jefe[$x]['w_nomjefe'] = $array_premios_jefe_temporal[$x]['w_nomjefe'];
						$array_premios_jefe[$x]['cli_p1'] = 0;
						$array_premios_jefe[$x]['cli_p2'] = 0;
						$array_premios_jefe[$x]['uni_p1'] = 0;
						$array_premios_jefe[$x]['uni_p2'] = 0;
						$array_premios_jefe[$x]['dire_p1'] = 0;
						$array_premios_jefe[$x]['dire_p2'] = 0;
						$array_premios_jefe[$x]['tra_p1'] = 0;
						$array_premios_jefe[$x]['tra_p2'] = 0;
						$array_premios_jefe[$x]['w_periodo'] = $w_periodo_actual;
					}
				}
			}

			//controlo que los jefes que trae $array_premios_jefe sean todos los jefes del ultimo año en caso de que no esten en $array_premios_jefe
			//pero si este en $array_premios_jefe_vdor pongo todo sus registros en cero
			//esto quiere decir que en algun momento del año ese jefe tuvo premios pero hoy ya no tiene en el periodo actual no tiene premios cargados
			for($i=0; $i<count($array_premios_jefe_vdor); $i++){
				$encontro = 0;
				for($x=0; $x<count($array_premios_jefe); $x++){
					if($array_premios_jefe_vdor[$i]['w_codjefe'] == $array_premios_jefe[$x]['w_codjefe']){
						$encontro++;
					}
				}			
				if($encontro == 0){
					//añado registro de jefe que tuvo premios en el año pero no esn este periodo todo en cero
					$numerador = count($array_premios_jefe);
					$array_premios_jefe[$numerador]['w_codjefe'] = $array_premios_jefe_vdor[$i]['w_codjefe'];
					$array_premios_jefe[$numerador]['w_nomjefe'] = $array_premios_jefe_vdor[$i]['w_nomjefe'];;
					$array_premios_jefe[$numerador]['cli_p1'] = 0;
					$array_premios_jefe[$numerador]['cli_p2'] = 0;
					$array_premios_jefe[$numerador]['uni_p1'] = 0;
					$array_premios_jefe[$numerador]['uni_p2'] = 0;
					$array_premios_jefe[$numerador]['dire_p1'] = 0;
					$array_premios_jefe[$numerador]['dire_p2'] = 0;
					$array_premios_jefe[$numerador]['tra_p1'] = 0;
					$array_premios_jefe[$numerador]['tra_p2'] = 0;
					$array_premios_jefe[$numerador]['w_periodo'] = $w_periodo_actual;		
				}
			}

			//Realizo las consultas de premios agrupando por vendedores
			$array_premios_vendedor = premios_periodo_vendedores($w_periodo_actual, $mywhere_premios);
			//en un principio se presento un problema, querian ver los periodos anteriores aunque no tengan premios cargados...
			//esto es un problema por que yo recorro en base a los premios cargados, y en base a eso busco los valores correspondientes a ese periodo
			//para los jefes y vendedores pero... al no tener premios para periodos aun no cargados tengo que de alguna forma generarlos por lo menos vacios
			//eso es lo que hago con este proceso, tomo como referencia el ultimo periodo y lo replico la cantidad de meses restantes hasta llegar a los 12 meses
			//de antiguedad completando esos meses que no tienen premios con valores en cero en el array
			for($i=0; $i<count($array_premios_vendedor); $i++){
				if(!is_array($array_premios_vendedor)){
					$array_premios_vendedor = [];
					$array_premios_vendedor_temporal = premios_periodo_vendedores(202012, $mywhere_premios);
					for($x=0; $x<count($array_premios_vendedor_temporal); $x++){
						$array_premios_vendedor[$x]['w_codjefe'] = $array_premios_vendedor_temporal[$x]['w_codjefe'];
						$array_premios_vendedor[$x]['w_nomjefe'] = $array_premios_vendedor_temporal[$x]['w_nomjefe'];
						$array_premios_vendedor[$x]['w_pcodven'] = $array_premios_vendedor_temporal[$x]['w_pcodven'];
						$array_premios_vendedor[$x]['w_pnomven'] = $array_premios_vendedor_temporal[$x]['w_pnomven'];
						$array_premios_vendedor[$x]['cli_p1'] = 0;
						$array_premios_vendedor[$x]['cli_p2'] = 0;
						$array_premios_vendedor[$x]['uni_p1'] = 0;
						$array_premios_vendedor[$x]['uni_p2'] = 0;
						$array_premios_vendedor[$x]['dire_p1'] = 0;
						$array_premios_vendedor[$x]['dire_p2'] = 0;
						$array_premios_vendedor[$x]['tra_p1'] = 0;
						$array_premios_vendedor[$x]['tra_p2'] = 0;
						$array_premios_vendedor[$x]['w_periodo'] = $w_periodo_actual;	
					} 
				}
			}	

			//controlo que los vendedores que trae $array_premios_vendedor sean todos los vendedores del ultimo año en caso de que no esten en $array_premios_vendedor...
			//pero si esten en $array_premios_jefe_vdor pongo todo sus registros en cero
			//esto quiere decir que en algun momento del año ese vendedor tuvo premios pero hoy ya no tiene... en el periodo actual no tiene premios cargados
			for($i=0; $i<count($array_premios_jefe_vdor); $i++){
				$encontro = 0;
				for($x=0; $x<count($array_premios_vendedor); $x++){
					if($array_premios_jefe_vdor[$i]['w_pcodven'] == $array_premios_vendedor[$x]['w_pcodven'] ){
						$encontro++;
					}
				}			
				if($encontro == 0){
					//añado registro de jefe que tuvo premios en el año pero no esn este periodo todo en cero
					$numerador = count($array_premios_vendedor);
					$array_premios_vendedor[$numerador]['w_codjefe'] = $array_premios_jefe_vdor[$i]['w_codjefe'];
					$array_premios_vendedor[$numerador]['w_nomjefe'] = $array_premios_jefe_vdor[$i]['w_nomjefe'];
					$array_premios_vendedor[$numerador]['w_pcodven'] = $array_premios_jefe_vdor[$i]['w_pcodven'];
					$array_premios_vendedor[$numerador]['w_pnomven'] = $array_premios_jefe_vdor[$i]['w_pnomven'];
					$array_premios_vendedor[$numerador]['cli_p1'] = 0;
					$array_premios_vendedor[$numerador]['cli_p2'] = 0;
					$array_premios_vendedor[$numerador]['uni_p1'] = 0;
					$array_premios_vendedor[$numerador]['uni_p2'] = 0;
					$array_premios_vendedor[$numerador]['dire_p1'] = 0;
					$array_premios_vendedor[$numerador]['dire_p2'] = 0;
					$array_premios_vendedor[$numerador]['tra_p1'] = 0;
					$array_premios_vendedor[$numerador]['tra_p2'] = 0;
					$array_premios_vendedor[$numerador]['w_periodo'] = $array_premios_jefe_vdor[$i]['w_periodo'];					
				}
			} 	

			//$array_datos_totales tiene todos los datos agrupados por vendedores inclusive los que no tienen premios cargados
			$array_datos_totales = premios_totales_jefe($fecha_desde_g, $fecha_hasta_g);
			$array_datos_totales_x_cli = premios_totales_jefe_x_cli($fecha_desde_g, $fecha_hasta_g);

			//lo que tengo que hacer con estos datos es un proceso de busqueda de vendedores en base a $array_premios_vendedores que son lo que tienen o tuvieron premios
			//hacer un grupo de totales por jefes y totales por vendedor del periodo actual
			//totales jefes con la tabla de premios por vendedore por que tengo que buscar jefe+vendedor
			for($i=0; $i<count($array_premios_vendedor); $i++){
				$cli = 0;
				$dire = 0;
				$tra = 0;
				$uni = 0;
				
				for($x=0; $x<count($array_datos_totales); $x++){
					//tengo que comparar jefe+vendedores
					//pero para eso tengo que saber que vendedores componen los distintos jefes  
					if(trim($array_premios_vendedor[$i]['w_pcodven']) == trim($array_datos_totales[$x]['campo_id_vdor'])){
						$campo_id_jefe = trim($array_premios_vendedor[$i]['w_codjefe']);


						$cli = $cli+$array_datos_totales[$x]['cli_total'];
						$dire = $dire+$array_datos_totales[$x]['importe_directa'];
						$tra = $tra+$array_datos_totales[$x]['importe_transfer'];
						$uni = $uni+$array_datos_totales[$x]['total_unidades'];
						$periodo = trim($array_premios_vendedor[$i]['w_periodo']);								
					}
					// if(trim($array_premios_vendedor[$i]['w_codjefe']) == trim($array_datos_totales[$x]['campo_id_jefe']) & trim($array_premios_vendedor[$i]['w_pcodven']) == trim($array_datos_totales[$x]['campo_id_vdor'])){
					//     $campo_id_jefe = trim($array_datos_totales[$x]['campo_id_jefe']);
					//     $cli = $cli+$array_datos_totales[$x]['cli_total'];
					//     $dire = $dire+$array_datos_totales[$x]['importe_directa'];
					//     $tra = $tra+$array_datos_totales[$x]['importe_transfer'];
					//     $uni = $uni+$array_datos_totales[$x]['total_unidades'];
					//     $periodo = trim($array_premios_vendedor[$i]['w_periodo']);								
					// }
				}
				//genero array con los totales temporales
				$array_datos_jefe_temp[$i]['campo_id_jefe'] = $campo_id_jefe;
				$array_datos_jefe_temp[$i]['cli'] = $cli;
				$array_datos_jefe_temp[$i]['dire'] = $dire;
				$array_datos_jefe_temp[$i]['tra'] = $tra;
				$array_datos_jefe_temp[$i]['uni'] = $uni;
				$array_datos_jefe_temp[$i]['periodo'] = $periodo;		

			}

			//me falta hacerle otro proceso de suma a los jefes de ventas
			//ahora que en el vector temporal tengo solo los vendedores involucrados con los jefes que tienen cargados premios hago la sumatoria por jefe
			//siempre que el periodo actual corresponda
			for($i=0; $i<count($array_premios_jefe); $i++){
				$cli = 0;
				$dire = 0;
				$tra = 0;
				$uni = 0;
				for($x=0; $x<count($array_datos_jefe_temp); $x++){
					//tengo que comparar jefe+vendedore
					if(trim($array_premios_jefe[$i]['w_codjefe']) == trim($array_datos_jefe_temp[$x]['campo_id_jefe']) & $w_periodo_actual == trim($array_datos_jefe_temp[$x]['periodo'])){
						$campo_id_jefe = trim($array_datos_jefe_temp[$x]['campo_id_jefe']);
						$cli = $cli+$array_datos_jefe_temp[$x]['cli'];
						$dire = $dire+$array_datos_jefe_temp[$x]['dire'];
						$tra = $tra+$array_datos_jefe_temp[$x]['tra'];
						$uni = $uni+$array_datos_jefe_temp[$x]['uni'];
						$periodo = trim($array_premios_jefe[$i]['w_periodo']);
					}
				}
				//genero array con los totales temporales
				$array_datos_jefe[$i]['campo_id_jefe'] = $campo_id_jefe;
				$array_datos_jefe[$i]['cli'] = $cli;
				$array_datos_jefe[$i]['dire'] = $dire;
				$array_datos_jefe[$i]['tra'] = $tra;
				$array_datos_jefe[$i]['uni'] = $uni;
				$array_datos_jefe[$i]['periodo'] = $periodo;		
			}		

			//totales vendedor correccion 29/12/2020
			for($i=0; $i<count($array_premios_vendedor); $i++){
				$cli = 0;
				$dire = 0;
				$tra = 0;
				$uni = 0;
				//segunda correccion para que junte 2 zonas 14/08/2021
//				if(trim($array_premios_vendedor[$i]['w_pcodven']) != 21 && trim($array_premios_vendedor[$i]['w_pcodven']) != 24){
				if(trim($array_premios_vendedor[$i]['w_pcodven']) != 21){
					for($x=0; $x<count($array_datos_totales); $x++){
						if(trim($array_premios_vendedor[$i]['w_pcodven']) == trim($array_datos_totales[$x]['campo_id_vdor'])){
							if(trim($array_datos_totales[$x]['campo_id_vdor']) == 2){
								//recolecta datos del vendedor 2
								$campo_id_vdor = trim($array_datos_totales[$x]['campo_id_vdor']);
								for($z=0; $z<count($array_datos_totales_x_cli); $z++){
									if($array_datos_totales_x_cli[$z]['campo_id_vdor'] == $campo_id_vdor){
										$cli = $cli+$array_datos_totales_x_cli[$z]['cli_total'];
									}
								}									
								$dire = $dire+$array_datos_totales[$x]['importe_directa']; 
								$tra = $tra+$array_datos_totales[$x]['importe_transfer'];
								$uni = $uni+$array_datos_totales[$x]['total_unidades'];
								$periodo = trim($array_premios_vendedor[$i]['w_periodo']);	
								//fin de recoleccion de datos de vendedor 2

								//recolecta datos del vendedor 21
								//subbucle para buscar la otra zona de anile
								for($xx=0; $xx<count($array_datos_totales); $xx++){
									if(trim($array_datos_totales[$xx]['campo_id_vdor']) == 21){
										$cli21 = 0;
										$dire21 = 0;
										$tra21 = 0;
										$uni21 = 0;                                            
										$campo_id_vdor = trim($array_datos_totales[$xx]['campo_id_vdor']);
										for($z=0; $z<count($array_datos_totales_x_cli); $z++){
											if($array_datos_totales_x_cli[$z]['campo_id_vdor'] == $campo_id_vdor){
												$cli21 = $cli21+$array_datos_totales_x_cli[$z]['cli_total'];
											}
										}
										$dire21 = $dire21+$array_datos_totales[$xx]['importe_directa']; 
										$tra21 = $tra21+$array_datos_totales[$xx]['importe_transfer'];
										$uni21 = $uni21+$array_datos_totales[$xx]['total_unidades'];
										$periodo = trim($array_premios_vendedor[$i]['w_periodo']);	
									}
								}
								//fin de recoleccion de datos del vendedor 21

								//sumo las 2 zonas
								$dire = $dire+$dire21; 
								$tra = $tra+$tra21;
								$uni = $uni+$uni21;    
								$cli = $cli+$cli21;    
 								
								// //subbucle para buscar la otra zona de anile
								// for($xx=0; $xx<count($array_datos_totales); $xx++){
								// 	if(trim($array_datos_totales[$xx]['campo_id_vdor']) == 21){
								// 		//verifico los clientes totales de las 2 zonas
								// 		for($z=0; $z<count($array_datos_totales_x_cli); $z++){
								// 			if($array_datos_totales_x_cli[$z]['campo_id_vdor'] == 2){
								// 				//subbucle para buscar la otra zona de anile
								// 				for($zz=0; $zz<count($array_datos_totales_x_cli); $zz++){
								// 					if($array_datos_totales_x_cli[$zz]['campo_id_vdor'] == 21){
								// 						$cli = $cli+$array_datos_totales_x_cli[$z]['cli_total']+$array_datos_totales_x_cli[$zz]['cli_total'];
								// 					}
								// 				}												
								// 			}
								// 		}
								// 		$campo_id_vdor = trim($array_datos_totales[$x]['campo_id_vdor']);
								// 		$dire = $dire+$array_datos_totales[$x]['importe_directa']+$array_datos_totales[$xx]['importe_directa']; 
								// 		$tra = $tra+$array_datos_totales[$x]['importe_transfer']+$array_datos_totales[$xx]['importe_transfer'];
								// 		$uni = $uni+$array_datos_totales[$x]['total_unidades']+$array_datos_totales[$xx]['total_unidades'];
								// 		$periodo = trim($array_premios_vendedor[$i]['w_periodo']);					
								// 	}
									
								// }
							}else{
								// //cambio realizado despues de que ramiro solo toma la zona 24 23/01/2023
								// if(trim($array_datos_totales[$x]['campo_id_vdor']) == 4){
								// 	//subbucle para buscar la otra zona de anile
								// 	for($xx=0; $xx<count($array_datos_totales); $xx++){

								// 		if(trim($array_datos_totales[$xx]['campo_id_vdor']) == 24){
								// 			$campo_id_vdor = trim($array_datos_totales[$x]['campo_id_vdor']);
								// 			//verifico los clientes totales de las 2 zonas
								// 			for($z=0; $z<count($array_datos_totales_x_cli); $z++){
								// 				if($array_datos_totales_x_cli[$z]['campo_id_vdor'] == 4){
								// 					//subbucle para buscar la otra zona de anile
								// 					for($zz=0; $zz<count($array_datos_totales_x_cli); $zz++){
								// 						if($array_datos_totales_x_cli[$zz]['campo_id_vdor'] == 24){
								// 							$cli = $cli+$array_datos_totales_x_cli[$z]['cli_total']+$array_datos_totales_x_cli[$zz]['cli_total'];
								// 						}
								// 					}												
								// 				}
								// 			}											
								// 			$dire = $dire+$array_datos_totales[$x]['importe_directa']+$array_datos_totales[$xx]['importe_directa']; 
								// 			$tra = $tra+$array_datos_totales[$x]['importe_transfer']+$array_datos_totales[$xx]['importe_transfer'];
								// 			$uni = $uni+$array_datos_totales[$x]['total_unidades']+$array_datos_totales[$xx]['total_unidades'];
								// 			$periodo = trim($array_premios_vendedor[$i]['w_periodo']);					
								// 		}

								// 	}
								// }else{	
									
									
									$campo_id_vdor = trim($array_datos_totales[$x]['campo_id_vdor']);

									//verifico los clientes totales de las 2 zonas
									for($z=0; $z<count($array_datos_totales_x_cli); $z++){
										if($array_datos_totales_x_cli[$z]['campo_id_vdor'] == $campo_id_vdor){
											$cli = $cli+$array_datos_totales_x_cli[$z]['cli_total'];
										}
									}									
//									$cli = $cli+$array_datos_totales[$x]['cli_total'];
									$dire = $dire+$array_datos_totales[$x]['importe_directa']; 
									$tra = $tra+$array_datos_totales[$x]['importe_transfer'];
									$uni = $uni+$array_datos_totales[$x]['total_unidades'];
									$periodo = trim($array_premios_vendedor[$i]['w_periodo']);		
								// }
							}						
						}
					}
					//genero array con los totales
					$array_datos_vendedor[$i]['campo_id_vdor'] = trim($array_premios_vendedor[$i]['w_pcodven']);
					$array_datos_vendedor[$i]['cli'] = $cli;
					$array_datos_vendedor[$i]['dire'] = $dire;
					$array_datos_vendedor[$i]['tra'] = $tra;
					$array_datos_vendedor[$i]['uni'] = $uni;
					$array_datos_vendedor[$i]['periodo'] = trim($array_premios_vendedor[$i]['w_periodo']);	
				}
			}	

			$array_jef = array('array_datos_jefe' => $array_datos_jefe,
		//							'array_datos_totales' => $array_datos_totales,
		//							'array_datos_jefe_temp' => $array_datos_jefe_temp,
								'array_premios_jefe' => $array_premios_jefe,
								'w_periodo_actual' => $w_periodo_actual,
								'mes' => $meses);	

			$array_ven = array('array_datos_vendedor' => $array_datos_vendedor,
								'array_premios_vendedor' => $array_premios_vendedor,
								'w_periodo_actual' => $w_periodo_actual,
								'mes' => $meses);

			$array_periodo = array('array_jefe' => $array_jef,
								   'array_vendedor' => $array_ven);
			return $array_periodo;	
		}

		function inicio_proceso(){
			$fecha_actual = date('Y-m-d');
			$fecha_desde_actual = primer_dia_del_mes_x_fecha_amd($fecha_actual);
			$fecha_desde = primer_dia_del_mes_x_fecha_amd($fecha_actual);
			$fecha_hasta = ultimo_dia_del_mes_x_fecha_amd($fecha_actual);
			$year_actual = date('Y');
			$mes_actual = date('m');
			$w_periodo_actual = $year_actual.$mes_actual;
			$meses = meses_numero_a_letra_dos_digitos(date('m'));
			////recorro 12 meses
			for($i=0; $i<12; $i++){
				//hago la recorrida hacia atras
				//calculo fecha temporal es la fecha actual menos $i cantidad de meses
				$fecha_temp = date('Y-m-d',strtotime($fecha_desde_actual."- ".$i." month"));
				//calculo fechas desde y hasta en base a fecha_temp
				$fecha_desde = primer_dia_del_mes_x_fecha_amd($fecha_temp);
				$fecha_hasta = ultimo_dia_del_mes_x_fecha_amd($fecha_temp);	
				$mes = date('m',strtotime($fecha_desde_actual."- ".$i." month"));
				$year = date('Y',strtotime($fecha_desde_actual."- ".$i." month"));
				$w_periodo = $year.$mes;
				$label_meses[$i] = meses_numero_a_letra_dos_digitos(date('m',strtotime($fecha_desde_actual."- ".$i." month")));			
				$meses = meses_numero_a_letra_dos_digitos(date('m',strtotime($fecha_desde_actual."- ".$i." month")));
				$array_periodo_gral['array_mes'.$i] = periodos_generales( $fecha_desde, $fecha_hasta, $w_periodo, $meses);
			}
			$label_meses = array_reverse($label_meses);
			$array_periodo_gral['label_meses'] = $label_meses;
			return $array_periodo_gral;
		}
		$array_periodo_gral = inicio_proceso();		
		echo json_encode($array_periodo_gral);			
	break;		
		
		
		
	
		
		
		
	case 'premios_jefe_actual':
		$tiempo_inicio = microtime(true);		
		//fecha actual
		$fecha_actual = date('Y-m-d');

		//preparo datos para hacer el where segun codigo de usuario puede ser un jefe o un admin=0
		$mywhere_periodo = '(';
		//tengo que consultar los periodos para atras por lo menos de 12 meses atras porque?
		//por que puedo tener vendedores que durante el ultimo año hayan dejado de vender y se quieran revisar por lo menos el historial
		//aunque en el periodo actual no tenga datos
		for($i=0; $i<12; $i++){
			//hago la recorrida hacia atras
			//calculo fecha temporal es la fecha actual menos $i cantidad de meses
			$fecha_temp = date('Y-m-d',strtotime($fecha_actual."- ".$i." month"));
			//calculo fechas desde y hasta en base a fecha_temp
			$fecha_desde = primer_dia_del_mes_x_fecha_amd($fecha_temp);
			$fecha_hasta = ultimo_dia_del_mes_x_fecha_amd($fecha_temp);	
			$mes = date('m',strtotime($fecha_actual."- ".$i." month"));
			$year = date('Y',strtotime($fecha_actual."- ".$i." month"));
			$w_periodo = $year.$mes;	
			
			//armo where de forma con los periodos
			if(12 == $i+1){
				//llegue al ultimo registro el trato para hacer la cadena cambia
				$mywhere_periodo.= 'w_periodo = '.$w_periodo.')';
			}else{
				$mywhere_periodo.= 'w_periodo = '.$w_periodo.' OR ';
			}			
		}
		
		//estableciendo parametros de fecha
		$fecha_desde = primer_dia_del_mes_x_fecha_amd($fecha_actual);
		$fecha_hasta = ultimo_dia_del_mes_x_fecha_amd($fecha_actual);
		$year_actual = date('Y');
		$mes_actual = date('m');
		$w_periodo_actual = $year_actual.$mes_actual;
		
		if($_POST['codigo'] == 0){
			//consulto que! vendedores estan cargados para premios de ese jefe
			//si no hago esta consulta el totalizado por jefe me trae los datos de toooodos los vendedores de ese jefe
			//y el historico sobrepasa siempre al de los vendedores que reciben premios
			$mywhere_premios = '1=1';
			//verifica vendedores con premios
			$array_premios_jefe_vdor = premios_12meses_jefe_vdor($mywhere_premios, $mywhere_periodo);
			$new_mywhere_premios='(';
			for($z=0; $z<count($array_premios_jefe_vdor); $z++){
				//armo where de forma dinamica tipo w_codjefe = 7 AND w_pcodven = 83 OR w_codjefe = 7 AND w_pcodven = 88....
				if(count($array_premios_jefe_vdor) == $z+1){
					//llegue al ultimo registro el trato para hacer la cadena cambia
					$new_mywhere_premios.= 'w_pcodven = '.$array_premios_jefe_vdor[$z]['w_pcodven'].') AND '.$mywhere_periodo;
				}else{
					$new_mywhere_premios.= 'w_pcodven = '.$array_premios_jefe_vdor[$z]['w_pcodven'].' OR ';				
				}
			}	
		}else{
			//mismo concepto que toda la captura pero por jefe de ventas
			$mywhere_premios = 'w_codjefe = '.$_POST['codigo'];
			//verifica vendedores con premios
			$array_premios_jefe_vdor = premios_12meses_jefe_vdor($mywhere_premios, $mywhere_periodo);
			$new_mywhere_premios='(';
			for($z=0; $z<count($array_premios_jefe_vdor); $z++){
				//armo where de forma dinamica tipo w_codjefe = 7 AND w_pcodven = 83 OR w_codjefe = 7 AND w_pcodven = 88....
				if(count($array_premios_jefe_vdor) == $z+1){
					//llegue al ultimo registro el trato para hacer la cadena cambia
					$new_mywhere_premios.= 'w_pcodven = '.$array_premios_jefe_vdor[$z]['w_pcodven'].') AND '.$mywhere_periodo;
				}else{
					$new_mywhere_premios.= 'w_pcodven = '.$array_premios_jefe_vdor[$z]['w_pcodven'].' OR ';				
				}
			}	
		}			
		//medio tiempo
		$tiempo_fin = microtime(true);
		$mediotiempo = $tiempo_fin - $tiempo_inicio;
		$tiempo_inicio = microtime(true);
		
	
		//Realizo las consultas de premios agrupando por jefes
		$array_premios_jefe = premios_periodo_jefe($w_periodo_actual, $mywhere_premios);
		//controlo que los jefes que trae $array_premios_jefe sean todos los jefes del ultimo año en caso de que no esten en $array_premios_jefe
		//pero si este en $array_premios_jefe_vdor pongo todo sus registros en cero
	    //esto quiere decir que en algun momento del año ese jefe tuvo premios pero hoy ya no tiene en el periodo actual no tiene premios cargados
		for($i=0; $i<count($array_premios_jefe_vdor); $i++){
			$encontro = 0;
			for($x=0; $x<count($array_premios_jefe); $x++){
				if($array_premios_jefe_vdor[$i]['w_codjefe'] == $array_premios_jefe[$x]['w_codjefe']){
					$encontro++;
				}
			}			
			if($encontro == 0){
				//añado registro de jefe que tuvo premios en el año pero no esn este periodo todo en cero
				$numerador = count($array_premios_jefe);
				$array_premios_jefe[$numerador]['w_codjefe'] = $array_premios_jefe_vdor[$i]['w_codjefe'];
				$array_premios_jefe[$numerador]['w_nomjefe'] = $array_premios_jefe_vdor[$i]['w_nomjefe'];;
				$array_premios_jefe[$numerador]['cli_p1'] = 0;
				$array_premios_jefe[$numerador]['cli_p2'] = 0;
				$array_premios_jefe[$numerador]['uni_p1'] = 0;
				$array_premios_jefe[$numerador]['uni_p2'] = 0;
				$array_premios_jefe[$numerador]['dire_p1'] = 0;
				$array_premios_jefe[$numerador]['dire_p2'] = 0;
				$array_premios_jefe[$numerador]['tra_p1'] = 0;
				$array_premios_jefe[$numerador]['tra_p2'] = 0;
				$array_premios_jefe[$numerador]['w_periodo'] = 0;				
			}
		}
	
		//Realizo las consultas de premios agrupando por vendedores
		$array_premios_vendedor = premios_periodo_vendedores($w_periodo_actual, $mywhere_premios);	
		//controlo que los vendedores que trae $array_premios_vendedor sean todos los vendedores del ultimo año en caso de que no esten en $array_premios_vendedor...
		//pero si esten en $array_premios_jefe_vdor pongo todo sus registros en cero
	    //esto quiere decir que en algun momento del año ese vendedor tuvo premios pero hoy ya no tiene... en el periodo actual no tiene premios cargados
		for($i=0; $i<count($array_premios_jefe_vdor); $i++){
			$encontro = 0;
			for($x=0; $x<count($array_premios_vendedor); $x++){
				if($array_premios_jefe_vdor[$i]['w_pcodven'] == $array_premios_vendedor[$x]['w_pcodven'] ){
					$encontro++;
				}
			}			
			if($encontro == 0){
				//añado registro de jefe que tuvo premios en el año pero no esn este periodo todo en cero
				$numerador = count($array_premios_vendedor);
				$array_premios_vendedor[$numerador]['w_codjefe'] = $array_premios_jefe_vdor[$i]['w_codjefe'];
				$array_premios_vendedor[$numerador]['w_nomjefe'] = $array_premios_jefe_vdor[$i]['w_nomjefe'];
				$array_premios_vendedor[$numerador]['w_pcodven'] = $array_premios_jefe_vdor[$i]['w_pcodven'];
				$array_premios_vendedor[$numerador]['w_pnomven'] = $array_premios_jefe_vdor[$i]['w_pnomven'];
				$array_premios_vendedor[$numerador]['cli_p1'] = 0;
				$array_premios_vendedor[$numerador]['cli_p2'] = 0;
				$array_premios_vendedor[$numerador]['uni_p1'] = 0;
				$array_premios_vendedor[$numerador]['uni_p2'] = 0;
				$array_premios_vendedor[$numerador]['dire_p1'] = 0;
				$array_premios_vendedor[$numerador]['dire_p2'] = 0;
				$array_premios_vendedor[$numerador]['tra_p1'] = 0;
				$array_premios_vendedor[$numerador]['tra_p2'] = 0;
				$array_premios_vendedor[$numerador]['w_periodo'] = 0;				
			}
		}	
	
	
	
//	echo '<pre>';
//print_r($array_premios_vendedor);
//echo '<pre>';	
//	
//		echo '<pre>';
//print_r($array_premios_jefe);
//echo '<pre>';	
	
	
	
		//$array_datos_totales tiene todos los datos agrupados por vendedores inclusive los que no tienen premios cargados
		$array_datos_totales = premios_totales_jefe($fecha_desde, $fecha_hasta);
		$array_datos_totales_x_cli = premios_totales_jefe_x_cli($fecha_desde, $fecha_hasta);
			
		//lo que tengo que hacer con estos datos es un proceso de busqueda de vendedores en base a $array_premios_vendedores que son lo que tienen o tuvieron premios
		//hacer un grupo de totales por jefes y totales por vendedor del periodo actual
		//totales jefes con la tabla de premios por vendedore por que tengo que buscar jefe+vendedor
		for($i=0; $i<count($array_premios_vendedor); $i++){
			$cli = 0;
			$dire = 0;
			$tra = 0;
			$uni = 0;
			for($x=0; $x<count($array_datos_totales); $x++){
					//tengo que comparar jefe+vendedore
				if(trim($array_premios_vendedor[$i]['w_codjefe']) == trim($array_datos_totales[$x]['campo_id_jefe']) & trim($array_premios_vendedor[$i]['w_pcodven']) == trim($array_datos_totales[$x]['campo_id_vdor'])){
					$campo_id_jefe = trim($array_datos_totales[$x]['campo_id_jefe']);
					$cli = $cli+$array_datos_totales[$x]['cli_total'];
					$dire = $dire+$array_datos_totales[$x]['importe_directa'];
					$tra = $tra+$array_datos_totales[$x]['importe_transfer'];
					$uni = $uni+$array_datos_totales[$x]['total_unidades'];
					$periodo = trim($array_premios_vendedor[$i]['w_periodo']);
				}					

			}
			//genero array con los totales temporales
			$array_datos_jefe_temp[$i]['campo_id_jefe'] = $campo_id_jefe;
			$array_datos_jefe_temp[$i]['cli'] = $cli;
			$array_datos_jefe_temp[$i]['dire'] = $dire;
			$array_datos_jefe_temp[$i]['tra'] = $tra;
			$array_datos_jefe_temp[$i]['uni'] = $uni;
			$array_datos_jefe_temp[$i]['periodo'] = $periodo;		
		}
		//me falta hacerle otro proceso de suma a los jefes de ventas
		//ahora que en el vector temporal tengo solo los vendedores involucrados con los jefes que tienen cargados premios hago la sumatoria por jefe
		//siempre que el periodo actual corresponda
		for($i=0; $i<count($array_premios_jefe); $i++){
			$cli = 0;
			$dire = 0;
			$tra = 0;
			$uni = 0;
			for($x=0; $x<count($array_datos_jefe_temp); $x++){
				//tengo que comparar jefe+vendedore
				if(trim($array_premios_jefe[$i]['w_codjefe']) == trim($array_datos_jefe_temp[$x]['campo_id_jefe']) & $w_periodo_actual == trim($array_datos_jefe_temp[$x]['periodo'])){
					$campo_id_jefe = trim($array_datos_jefe_temp[$x]['campo_id_jefe']);
					$cli = $cli+$array_datos_jefe_temp[$x]['cli'];
					$dire = $dire+$array_datos_jefe_temp[$x]['dire'];
					$tra = $tra+$array_datos_jefe_temp[$x]['tra'];
					$uni = $uni+$array_datos_jefe_temp[$x]['uni'];
					$periodo = trim($array_premios_jefe[$i]['w_periodo']);
				}
			}
			//genero array con los totales temporales
			$array_datos_jefe[$i]['campo_id_jefe'] = $campo_id_jefe;
			$array_datos_jefe[$i]['cli'] = $cli;
			$array_datos_jefe[$i]['dire'] = $dire;
			$array_datos_jefe[$i]['tra'] = $tra;
			$array_datos_jefe[$i]['uni'] = $uni;
			$array_datos_jefe[$i]['periodo'] = $periodo;		
		}		
		
	
		//totales vendedor correccion 29/12/2020
		for($i=0; $i<count($array_premios_vendedor); $i++){
			$cli = 0;
			$dire = 0;
			$tra = 0;
			$uni = 0;
			//segunda correccion para que junte 2 zonas 14/08/2021
//			if(trim($array_premios_vendedor[$i]['w_pcodven']) != 21 && trim($array_premios_vendedor[$i]['w_pcodven']) != 24){
			if(trim($array_premios_vendedor[$i]['w_pcodven']) != 21){
				for($x=0; $x<count($array_datos_totales); $x++){
					if(trim($array_premios_vendedor[$i]['w_pcodven']) == trim($array_datos_totales[$x]['campo_id_vdor'])){
						if(trim($array_datos_totales[$x]['campo_id_vdor']) == 2){
							//subbucle para buscar la otra zona de anile
							for($xx=0; $xx<count($array_datos_totales); $xx++){
								if(trim($array_datos_totales[$xx]['campo_id_vdor']) == 21){
									//verifico los clientes totales de las 2 zonas
									for($z=0; $z<count($array_datos_totales_x_cli); $z++){
										if($array_datos_totales_x_cli[$z]['campo_id_vdor'] == 2){
											//subbucle para buscar la otra zona de anile
											for($zz=0; $zz<count($array_datos_totales_x_cli); $zz++){
												if($array_datos_totales_x_cli[$zz]['campo_id_vdor'] == 21){
													$cli = $cli+$array_datos_totales_x_cli[$z]['cli_total']+$array_datos_totales_x_cli[$zz]['cli_total'];
												}
											}												
										}
									}
									$campo_id_vdor = trim($array_datos_totales[$x]['campo_id_vdor']);
									$dire = $dire+$array_datos_totales[$x]['importe_directa']+$array_datos_totales[$xx]['importe_directa']; 
									$tra = $tra+$array_datos_totales[$x]['importe_transfer']+$array_datos_totales[$xx]['importe_transfer'];
									$uni = $uni+$array_datos_totales[$x]['total_unidades']+$array_datos_totales[$xx]['total_unidades'];
									$periodo = trim($array_premios_vendedor[$i]['w_periodo']);					
								}

							}
						}else{
							//cambio realizado despues de que ramiro solo toma la zona 24 23/01/2023
							if(trim($array_datos_totales[$x]['campo_id_vdor']) == 4){
								//subbucle para buscar la otra zona de anile
								for($xx=0; $xx<count($array_datos_totales); $xx++){

									if(trim($array_datos_totales[$xx]['campo_id_vdor']) == 24){
										$campo_id_vdor = trim($array_datos_totales[$x]['campo_id_vdor']);
										//verifico los clientes totales de las 2 zonas
										for($z=0; $z<count($array_datos_totales_x_cli); $z++){
											if($array_datos_totales_x_cli[$z]['campo_id_vdor'] == 4){
												//subbucle para buscar la otra zona de anile
												for($zz=0; $zz<count($array_datos_totales_x_cli); $zz++){
													if($array_datos_totales_x_cli[$zz]['campo_id_vdor'] == 24){
														$cli = $cli+$array_datos_totales_x_cli[$z]['cli_total']+$array_datos_totales_x_cli[$zz]['cli_total'];
													}
												}												
											}
										}											
										$dire = $dire+$array_datos_totales[$x]['importe_directa']+$array_datos_totales[$xx]['importe_directa']; 
										$tra = $tra+$array_datos_totales[$x]['importe_transfer']+$array_datos_totales[$xx]['importe_transfer'];
										$uni = $uni+$array_datos_totales[$x]['total_unidades']+$array_datos_totales[$xx]['total_unidades'];
										$periodo = trim($array_premios_vendedor[$i]['w_periodo']);					
									}

								}
							}else{								
								$campo_id_vdor = trim($array_datos_totales[$x]['campo_id_vdor']);
								//verifico los clientes totales de las 2 zonas
								for($z=0; $z<count($array_datos_totales_x_cli); $z++){
									if($array_datos_totales_x_cli[$z]['campo_id_vdor'] == $campo_id_vdor){
										$cli = $cli+$array_datos_totales_x_cli[$z]['cli_total'];
									}
								}									
//									$cli = $cli+$array_datos_totales[$x]['cli_total'];
								$dire = $dire+$array_datos_totales[$x]['importe_directa']; 
								$tra = $tra+$array_datos_totales[$x]['importe_transfer'];
								$uni = $uni+$array_datos_totales[$x]['total_unidades'];
								$periodo = trim($array_premios_vendedor[$i]['w_periodo']);		
							}
						}						
					}
				}
				//genero array con los totales
				$array_datos_vendedor[$i]['campo_id_vdor'] = trim($array_premios_vendedor[$i]['w_pcodven']);
				$array_datos_vendedor[$i]['cli'] = $cli;
				$array_datos_vendedor[$i]['dire'] = $dire;
				$array_datos_vendedor[$i]['tra'] = $tra;
				$array_datos_vendedor[$i]['uni'] = $uni;
				$array_datos_vendedor[$i]['periodo'] = trim($array_premios_vendedor[$i]['w_periodo']);	
			}
		}			
		
		
		
		
		$array_jef = array('array_datos_jefe' => $array_datos_jefe,
//							'codigo' => $_POST['codigo'],
//							'array_premios_jefe_vdor' => $array_premios_jefe_vdor,
							'array_premios_jefe' => $array_premios_jefe,
							'w_periodo_actual' => $w_periodo_actual,
							'mes' => $meses);	
		
		$array_jefe['array_mes0'] = $array_jef;
		
		$array_ven = array('array_datos_vendedor' => $array_datos_vendedor,
							'array_premios_vendedor' => $array_premios_vendedor,
						    'w_periodo_actual' => $w_periodo_actual,
							'mes' => $meses);
		

		$array_vendedor['array_mes0'] = $array_ven;	
		
		$array_tot = array('array_datos_totales' => $array_datos_totales);
		$array_totales['array_mes0'] = $array_tot;
		
		// este paso genera un array tipo array_mes0, array_mes1... array_mes12
		// donde array_mes0 es el ultimo mes procesado el mas actual y array_mes12 es el primer mes procesado el mas viejo un año atras
		//		Array
		//		(
		//			[array_mes0] => Array
		//				(
		//					[array_datos_jefe] => Array
		//						(
		//							[0] => Array
		//								(
		//									[cli_total] => 12
		//									[campo_id_jefe] => 2 
		$tiempo_fin = microtime(true);
		echo json_encode(array('array_jefe' => $array_jefe,
							   'array_vendedor' => $array_vendedor,
							   'array_totales' => $array_totales
							  ));	
	break;	

//	case 'premios_historicos_vendedor':
//		$fecha_actual = date('Y-m-d');
//		$mywhere_periodo = '(';
//		//tengo que consultar los periodos para atras por lo menos de 12 meses atras porque?
//		//por que puedo tener vendedores que durante el ultimo año hayan dejado de vender y se quieran revisar por lo menos el historial
//		//aunque en el periodo actual no tenga datos
//		for($i=0; $i<12; $i++){
//			//hago la recorrida hacia atras
//			//calculo fecha temporal es la fecha actual menos $i cantidad de meses
//			$fecha_temp = date('Y-m-d',strtotime($fecha_actual."- ".$i." month"));
//			//calculo fechas desde y hasta en base a fecha_temp
//			$fecha_desde = primer_dia_del_mes_x_fecha_amd($fecha_temp);
//			$fecha_hasta = ultimo_dia_del_mes_x_fecha_amd($fecha_temp);	
//			$mes = date('m',strtotime($fecha_actual."- ".$i." month"));
//			$year = date('Y',strtotime($fecha_actual."- ".$i." month"));
//			$w_periodo = $year.$mes;	
//			
//			//armo where de forma con los periodos
//			if(12 == $i+1){
//				//llegue al ultimo registro el trato para hacer la cadena cambia
//				$mywhere_periodo.= 'w_periodo = '.$w_periodo.')';
//			}else{
//				$mywhere_periodo.= 'w_periodo = '.$w_periodo.' OR ';
//			}			
//		}
//
//		//inicializo los vectores que tendran los datos de 12 periodos de Jefes y Vendedores
//		$array_vendedor = array();
//		//recorro 12 meses
//		for($i=0; $i<12; $i++){
//			//hago la recorrida hacia atras
//			//calculo fecha temporal es la fecha actual menos $i cantidad de meses
//			$fecha_temp = date('Y-m-d',strtotime($fecha_actual."- ".$i." month"));
//			//calculo fechas desde y hasta en base a fecha_temp
//			$fecha_desde = primer_dia_del_mes_x_fecha_amd($fecha_temp);
//			$fecha_hasta = ultimo_dia_del_mes_x_fecha_amd($fecha_temp);	
//			$mes = date('m',strtotime($fecha_actual."- ".$i." month"));
//			$year = date('Y',strtotime($fecha_actual."- ".$i." month"));
//			$w_periodo = $year.$mes;
//			$label_meses[$i] = meses_numero_a_letra_dos_digitos(date('m',strtotime($fecha_actual."- ".$i." month")));			
//			$meses = meses_numero_a_letra_dos_digitos(date('m',strtotime($fecha_actual."- ".$i." month")));
//			
//			//Genero Consulta Where de forma dinamica tanto para la tabla estad como para premios
//			//preparo datos para hacer el where segun codigo de jefe clickeado			
//			//mismo concepto que toda la captura pero por jefe de ventas
//			$mywhere_premios = 'w_codjefe = '.$_POST['jefe'];
//			//verifica vendedores con premios
//			$array_premios_jefe_vdor = premios_12meses_jefe_vdor($mywhere_premios, $mywhere_periodo);
//			
////			echo '<pre>';
////			print_r($array_premios_jefe_vdor);
////			echo '<pre>';			
//			
//			$new_mywhere='(';
//			$new_mywhere_transfer='(';
//			$new_mywhere_premios='(';
//			for($z=0; $z<count($array_premios_jefe_vdor); $z++){
//				//armo where de forma dinamica tipo w_codjefe = 7 AND w_pcodven = 83 OR w_codjefe = 7 AND w_pcodven = 88....
//				if(count($array_premios_jefe_vdor) == $z+1){
//					//llegue al ultimo registro el trato para hacer la cadena cambia
//					$new_mywhere_premios.= 'w_pcodven = '.$array_premios_jefe_vdor[$z]['w_pcodven'].') AND w_periodo = '.$w_periodo;
//					$new_mywhere.= 'facvdorid = '.$array_premios_jefe_vdor[$z]['w_pcodven'].') AND FacFch BETWEEN \''.$fecha_desde.'\' AND \''.$fecha_hasta.'\'';
//					$new_mywhere_transfer.= 'pedvdorid = '.$array_premios_jefe_vdor[$z]['w_pcodven'].') AND FacFch BETWEEN \''.$fecha_desde.'\' AND \''.$fecha_hasta.'\'';
//				}else{
//					$new_mywhere_premios.= 'w_pcodven = '.$array_premios_jefe_vdor[$z]['w_pcodven'].' OR ';				
//					$new_mywhere.= 'facvdorid = '.$array_premios_jefe_vdor[$z]['w_pcodven'].' OR ';
//					$new_mywhere_transfer.= 'pedvdorid = '.$array_premios_jefe_vdor[$z]['w_pcodven'].' OR ';
//				}
//			}	
//			
//			$array_premios_vendedor = premios_historico_jefe($new_mywhere_premios);
//			$array_datos_vendedor = totales_historico_jefe($new_mywhere, $new_mywhere_transfer, $_POST['jefe']);
//			
//			$array_vdor = array('array_datos_vendedor' => $array_datos_vendedor,
//								'array_premios_vendedor' => $array_premios_vendedor,
//								'mes' => $meses,
//							 );	
//			$array_vendedor['array_mes'.$i] = $array_vdor;
//		}	
//		$label_meses = array_reverse($label_meses);
//		echo json_encode(array('array_vendedor' => $array_vendedor,
//							   'label_meses' => $label_meses
//							  ));			
//	break;			

//	case 'premios_historicos_jefes':
//		$fecha_actual = date('Y-m-d');
//		$mywhere_periodo = '(';
//		//tengo que consultar los periodos para atras por lo menos de 12 meses atras porque?
//		//por que puedo tener vendedores que durante el ultimo año hayan dejado de vender y se quieran revisar por lo menos el historial
//		//aunque en el periodo actual no tenga datos
//		for($i=0; $i<12; $i++){
//			//hago la recorrida hacia atras
//			//calculo fecha temporal es la fecha actual menos $i cantidad de meses
//			$fecha_temp = date('Y-m-d',strtotime($fecha_actual."- ".$i." month"));
//			//calculo fechas desde y hasta en base a fecha_temp
//			$fecha_desde = primer_dia_del_mes_x_fecha_amd($fecha_temp);
//			$fecha_hasta = ultimo_dia_del_mes_x_fecha_amd($fecha_temp);	
//			$mes = date('m',strtotime($fecha_actual."- ".$i." month"));
//			$year = date('Y',strtotime($fecha_actual."- ".$i." month"));
//			$w_periodo = $year.$mes;	
//			
//			//armo where de forma con los periodos
//			if(12 == $i+1){
//				//llegue al ultimo registro el trato para hacer la cadena cambia
//				$mywhere_periodo.= 'w_periodo = '.$w_periodo.')';
//			}else{
//				$mywhere_periodo.= 'w_periodo = '.$w_periodo.' OR ';
//			}			
//		}
//
//		//inicializo los vectores que tendran los datos de 12 periodos de Jefes y Vendedores
//		$array_jefe = array();
//		//recorro 12 meses
//		for($i=0; $i<12; $i++){
//			//hago la recorrida hacia atras
//			//calculo fecha temporal es la fecha actual menos $i cantidad de meses
//			$fecha_temp = date('Y-m-d',strtotime($fecha_actual."- ".$i." month"));
//			//calculo fechas desde y hasta en base a fecha_temp
//			$fecha_desde = primer_dia_del_mes_x_fecha_amd($fecha_temp);
//			$fecha_hasta = ultimo_dia_del_mes_x_fecha_amd($fecha_temp);	
//			$mes = date('m',strtotime($fecha_actual."- ".$i." month"));
//			$year = date('Y',strtotime($fecha_actual."- ".$i." month"));
//			$w_periodo = $year.$mes;
//			$label_meses[$i] = meses_numero_a_letra_dos_digitos(date('m',strtotime($fecha_actual."- ".$i." month")));			
//			$meses = meses_numero_a_letra_dos_digitos(date('m',strtotime($fecha_actual."- ".$i." month")));
//			
//			//Genero Consulta Where de forma dinamica tanto para la tabla estad como para premios
//			//preparo datos para hacer el where segun codigo de jefe clickeado			
//			//mismo concepto que toda la captura pero por jefe de ventas
//			$mywhere_premios = '1=1';
////			$mywhere_premios = 'w_codjefe = '.$_POST['jefe'];
//			//verifica vendedores con premios
//			$array_premios_jefe_vdor = premios_12meses_jefe_vdor($mywhere_premios, $mywhere_periodo);
//			
////			echo '<pre>';
////			print_r($array_premios_jefe_vdor);
////			echo '<pre>';			
//			
//			$new_mywhere='(';
//			$new_mywhere_transfer='(';
//			$new_mywhere_premios='(';
//			for($z=0; $z<count($array_premios_jefe_vdor); $z++){
//				//armo where de forma dinamica tipo w_codjefe = 7 AND w_pcodven = 83 OR w_codjefe = 7 AND w_pcodven = 88....
//				if(count($array_premios_jefe_vdor) == $z+1){
//					//llegue al ultimo registro el trato para hacer la cadena cambia
//					$new_mywhere_premios.= 'w_pcodven = '.$array_premios_jefe_vdor[$z]['w_pcodven'].') AND w_periodo = '.$w_periodo;
//					$new_mywhere.= 'facvdorid = '.$array_premios_jefe_vdor[$z]['w_pcodven'].') AND FacFch BETWEEN \''.$fecha_desde.'\' AND \''.$fecha_hasta.'\'';
//					$new_mywhere_transfer.= 'pedvdorid = '.$array_premios_jefe_vdor[$z]['w_pcodven'].') AND FacFch BETWEEN \''.$fecha_desde.'\' AND \''.$fecha_hasta.'\'';
//				}else{
//					$new_mywhere_premios.= 'w_pcodven = '.$array_premios_jefe_vdor[$z]['w_pcodven'].' OR ';				
//					$new_mywhere.= 'facvdorid = '.$array_premios_jefe_vdor[$z]['w_pcodven'].' OR ';
//					$new_mywhere_transfer.= 'pedvdorid = '.$array_premios_jefe_vdor[$z]['w_pcodven'].' OR ';
//				}
//			}	
//			
//			
//			$array_premios_jefe = premios_historico_jefe($new_mywhere_premios);
//			
//			
//			
//			
//			
//			
//			$array_datos_totales = totales_historico_jefe($new_mywhere, $new_mywhere_transfer, $_POST['jefe']);
//			
//			
//			
//			
//			
//
//					
//			
//			
//			
//			
//			
//			
//			
//			
//			
//			
//			
//			
//			
//			
//			
//			
//			
//			
//			$array_jef = array('array_datos_jefe' => $array_datos_jefe,
//								'array_premios_jefe' => $array_premios_jefe,
//								'mes' => $meses,
//							 );	
//			$array_jefe['array_mes'.$i] = $array_jef;
//		}	
//		$label_meses = array_reverse($label_meses);
////		echo '<pre>';
////		print_r(array('array_jefe' => $array_jefe,
////									   'label_meses' => $label_meses
////									  ));
////		echo '<pre>';	
//		echo json_encode(array('array_jefe' => $array_jefe,
//							   'label_meses' => $label_meses
//							  ));			
//	break;	
		
		
	case 'guarda_visita':
		
		//fecha y hora actual
		$date = new DateTime();
		$fecha_actual = $date->format('Y-m-d H:i:s');	
		//obtiene navegador
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$navegador = getBrowser($user_agent);
		//obtiene ip
		$new_ip=get_client_ip();
		//obtiene la ubicacion
		$ubicacion = str_pad(ip_info($new_ip, "address"),25);
		guarda_visita($_POST['usuario'], $fecha_actual, $navegador, $new_ip, $ubicacion);
	break;		
	case 'clientes_sin_ventas_expo_porc':
		$array_clientes_expo = clientes_totales_expo($_POST['codigo'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		$array_clientes_expo_no_ventas = clientes_totales_expo_sin_ventas($_POST['codigo'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		$array_color = colores();
		//proceso los datos para sacar porcentaje
//		for($i=0; $i<count($array_clientes_expo); $i++){
//			for($x=0; $x<count($array_clientes_expo_no_ventas); $x++){
//				if($array_clientes_expo[$i]['CliDptoRed'] == $array_clientes_expo_no_ventas[$x]['CliDptoRed']){
//					$nombreexpo = $array_clientes_expo[$i]['CliDptoRed'];
//					//filtro nombres raros que aparecen en expo
////					if($nombreexpo != 'BUENOS AIRES' || $nombreexpo != 'CAPITAL FEDERAL' || $nombreexpo != 'COMERCIO EXTERIOR' || $nombreexpo != 'PAIS EXTRANJERO'){
//						$tot_no_venta = $array_clientes_expo_no_ventas[$x]['total'];
//						$tot = $array_clientes_expo[$i]['total'];
//						$tot_venta = $tot-$tot_no_venta;
//						$array_expo[$i]['CliDptoRed'] = $nombreexpo;
//						$array_expo[$i]['porc_venta'] = round($tot_venta*100/$tot, 2);
//						$array_expo[$i]['porc_noventa'] = round($tot_no_venta*100/$tot, 2);
////						$array_expo[$i]['porc_noventa_color'] = $array_color[$i]['color'];
////					}
//				}
//			}
//		}
		echo json_encode($array_clientes_expo_no_ventas);
	break;		
	case 'clientes_sin_ventas_provincia_porc':
		$array_clientes_provincia = clientes_totales_provincia($_POST['codigo'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		$array_clientes_provincia_no_ventas = clientes_totales_provincia_sin_ventas($_POST['codigo'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		$array_color = colores();
		//proceso los datos para sacar porcentaje
//		for($i=0; $i<count($array_clientes_provincia); $i++){
//			for($x=0; $x<count($array_clientes_provincia_no_ventas); $x++){
//				if($array_clientes_provincia[$i]['CliDptoRed'] == $array_clientes_provincia_no_ventas[$x]['CliDptoRed']){
//					$nombreprovincia = $array_clientes_provincia[$i]['CliDptoRed'];
//					for($z=0; $z<count($array_color); $z++){
//						if($array_color[$z]['descripcion'] == $nombreprovincia){
//							$tot_no_venta = $array_clientes_provincia_no_ventas[$x]['total'];
//							$tot = $array_clientes_provincia[$i]['total'];
//							$tot_venta = $tot-$tot_no_venta;
//							$array_provincia[$i]['CliDptoRed'] = $nombreprovincia;
//							$array_provincia[$i]['porc_venta'] = round($tot_venta*100/$tot, 2);
//							$array_provincia[$i]['porc_noventa'] = round($tot_no_venta*100/$tot, 2);
//							$array_provincia[$i]['porc_noventa_color'] = $array_color[$z]['color'];
//						}
//					}
//				}
//			}
//		}
//		echo json_encode($array_provincia);
		echo json_encode($array_clientes_provincia);
	break;			
	case 'clientes_sin_ventas_porc':
		$array_clientes_totales = clientes_totales($_POST['codigo'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		$array_clientes_totales_no_ventas = clientes_totales_sin_ventas($_POST['codigo'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
			
		//proceso los datos para sacar porcentaje
//		$tot_no_venta = $array_clientes_totales_no_ventas[0]['no_venta'];
//		$tot = $array_clientes_totales[0]['total'];
//		$tot_venta = $tot-$tot_no_venta;
////		$array_clientes[0]['venta'] = $tot_venta;
//		$array_clientes[0]['porc_venta'] = round($tot_venta*100/$tot, 2);
//		$array_clientes[0]['porc_noventa'] = round($tot_no_venta*100/$tot, 2);
//		$array_color = colores();
//		for($i=0; $i<count($array_color); $i++){
//			if($array_color[$i]['descripcion'] == 'NO_VENTA'){
//				$array_clientes[0]['porc_noventa_color'] = $array_color[$i]['color'];
//			}
//			if($array_color[$i]['descripcion'] == 'VENTA'){
//				$array_clientes[0]['porc_venta_color'] = $array_color[$i]['color'];
//			}
//		}
//		echo json_encode($array_clientes);
//		echo json_encode($array_clientes_totales);
	break;	

	case 'clientes_sin_ventas_jefes':
//		$array_clientes_sin = clientes_sin_ventas($_POST['codigo'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
//		$array_clientes_con = clientes_con_ventas($_POST['codigo'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		//modificacion 26/08/2020
		switch($_POST['periodo']){
			case '3m':
				$array_clientes_sin = clientes_sin_ventas_3m($_POST['codigo']);
				$array_clientes_con = clientes_con_ventas_3m($_POST['codigo']);				
			break;	
			case '6m':
				$array_clientes_sin = clientes_sin_ventas_6m($_POST['codigo']);
				$array_clientes_con = clientes_con_ventas_6m($_POST['codigo']);				
			break;	
			case '12m':
				$array_clientes_sin = clientes_sin_ventas_12m($_POST['codigo']);
				$array_clientes_con = clientes_con_ventas_12m($_POST['codigo']);				
			break;					
		}		
		$array_colores = colores();
		
		//cuenta clientes no facturados por jefe
		$array_jefcod_sin = group_by('JefNom', $array_clientes_sin);
		$a = 0;
		foreach($array_jefcod_sin as $clave => $valor) {
			$total = count($valor);
			$contador = 0;
			if($total != 0){
					$contador++;
			}
			if($contador != 0){
				$array_clientes_sin_jefe[$a]['JefNom'] = $clave;
				$array_clientes_sin_jefe[$a]['total'] = $total;
				$a++;
			}
		}
		//cuenta clientes facturados por jefe
		$array_jefcod_con = group_by('JefNom', $array_clientes_con);
		$a = 0;
		foreach($array_jefcod_con as $clave => $valor) {
			$total = count($valor);
			$contador = 0;
			if($total != 0){
				$contador++;
			}
			if($contador != 0){
				$array_clientes_con_jefe[$a]['JefNom'] = $clave;
				$array_clientes_con_jefe[$a]['total'] = $total;
				$a++;
			}

		}
		
		//saco porcentaje jefe
		for($i=0; $i<count($array_clientes_sin_jefe); $i++){
			$encontro = 0;
			for($x=0; $x<count($array_clientes_con_jefe); $x++){
				if($array_clientes_sin_jefe[$i]['JefNom'] == $array_clientes_con_jefe[$x]['JefNom']){
					$encontro++;
					$tot_no_venta = $array_clientes_sin_jefe[$i]['total'];
					$tot_si_venta = $array_clientes_con_jefe[$x]['total'];
				}
			}
			if($encontro != 0){
				//saca porcentaje de ventas totales
				$tot_venta = $tot_si_venta+$tot_no_venta;
				$porc_no_venta = round($tot_no_venta*100/$tot_venta, 2);
				$porc_si_venta = round($tot_si_venta*100/$tot_venta, 2);
				$array_clientes_jefe_porc[$i]['nombre'] = $array_clientes_sin_jefe[$i]['JefNom'];
				$array_clientes_jefe_porc[$i]['total_no_venta'] = $porc_no_venta;
				$array_clientes_jefe_porc[$i]['total_si_venta'] = $porc_si_venta;
				$array_clientes_jefe_porc[$i]['color'] = $array_colores[$i]['color'];		
			}else{
				//saca porcentaje de ventas totales
				$porc_no_venta = 100;
				$porc_si_venta = 0;
				$array_clientes_jefe_porc[$i]['nombre'] = $array_clientes_sin_jefe[$i]['JefNom'];
				$array_clientes_jefe_porc[$i]['total_no_venta'] = $porc_no_venta;
				$array_clientes_jefe_porc[$i]['total_si_venta'] = $porc_si_venta;
				$array_clientes_jefe_porc[$i]['color'] = $array_colores[$i]['color'];			
			}
		}		

		
		
		
		//cuenta clientes sin facturacion por jefes por vendedores haciendo un doble group_by function()
		$a = 0;
		foreach($array_jefcod_sin as $clave_jefe => $valor_jefe) {
			//vuelvo a agrupar dentro de provincias por jefes
			$array_jefe_vend_sin = group_by('VdorTxt', $valor_jefe);
//			$array_prov_jefe_sin = group_by('JefNom', $valor_jefe);
			//vuelvo a hacer el foreach ahora sobre el agrupado de provincias+jefes
			foreach($array_jefe_vend_sin as $clave_vend => $valor_vend) {
//				$total_jefe = count($valor_vend);
				$total_vend = count($valor_vend);
				$contador = 0;
				if($total_vend != 0){
					$contador++;
				}
				if($contador != 0){
					$array_clientes_sin_jefe_vendedor[$a]['VdorTxt'] = $clave_vend;
					$array_clientes_sin_jefe_vendedor[$a]['nombre'] = $clave_jefe;
					$array_clientes_sin_jefe_vendedor[$a]['total'] = $total_vend;
					$a++;
				}
			}
		}		
		//cuenta clientes con facturacion por jefes por vendedores haciendo un doble group_by function()
		$a = 0;
		foreach($array_jefcod_con as $clave_jefe => $valor_jefe) {
			//vuelvo a agrupar dentro de provincias por jefes
			$array_jefe_vend_con = group_by('VdorTxt', $valor_jefe);
//			$array_prov_jefe_sin = group_by('JefNom', $valor_jefe);
			//vuelvo a hacer el foreach ahora sobre el agrupado de provincias+jefes
			foreach($array_jefe_vend_con as $clave_vend => $valor_vend) {
//				$total_jefe = count($valor_vend);
				$total_vend = count($valor_vend);
				$contador = 0;
				if($total_vend != 0){
					$contador++;
				}
				if($contador != 0){
					$array_clientes_con_jefe_vendedor[$a]['VdorTxt'] = $clave_vend;
					$array_clientes_con_jefe_vendedor[$a]['nombre'] = $clave_jefe;
					$array_clientes_con_jefe_vendedor[$a]['total'] = $total_vend;
					$a++;
				}
			}
		}		
		
		//saco porcentaje jefe+vendedor
//		for($i=0; $i<count($array_clientes_sin_provincia_jefe); $i++){
		for($i=0; $i<count($array_clientes_sin_jefe_vendedor); $i++){
			$encontro = 0;
			for($x=0; $x<count($array_clientes_con_jefe_vendedor); $x++){
				if($array_clientes_sin_jefe_vendedor[$i]['nombre'] == $array_clientes_con_jefe_vendedor[$x]['nombre'] 
				   && $array_clientes_sin_jefe_vendedor[$i]['VdorTxt'] == $array_clientes_con_jefe_vendedor[$x]['VdorTxt']){
					$encontro++;
					$tot_no_venta = $array_clientes_sin_jefe_vendedor[$i]['total'];
					$tot_si_venta = $array_clientes_con_jefe_vendedor[$x]['total'];
				}
			}
			if($encontro != 0){
				$tot_venta = $tot_si_venta+$tot_no_venta;
				$porc_no_venta = round($tot_no_venta*100/$tot_venta, 2);
				$porc_si_venta = round($tot_si_venta*100/$tot_venta, 2);				
				$array_clientes_jefe_vend_porc[$i]['nombre'] = $array_clientes_sin_jefe_vendedor[$i]['nombre'];
				$array_clientes_jefe_vend_porc[$i]['VdorTxt'] = $array_clientes_sin_jefe_vendedor[$i]['VdorTxt'];
				$array_clientes_jefe_vend_porc[$i]['tot_no_venta'] = $porc_no_venta;
				$array_clientes_jefe_vend_porc[$i]['tot_si_venta'] = $porc_si_venta;	
				$encontro_color=0;
				for($z=0; $z<count($array_colores); $z++){
					if($array_colores[$z]['descripcion'] == $array_clientes_sin_jefe_vendedor[$i]['nombre']){
						$encontro_color++;
						$color = $array_colores[$z]['color'];
					}
				}	
				if($encontro_color != 0){
					$array_clientes_jefe_vend_porc[$i]['color'] = $color;
				}else{
					$array_clientes_jefe_vend_porc[$i]['color'] = $array_colores[3]['color'];
				}
			}else{
				//si no encuentro un cliente sin ventas entre los clientes con ventas
				//significa que no le vendi por que la consulta de clientes SIN ventas
				//ya tiene el filtro de que en ese periodo ese cliente contra la base de datos de clientes gral
				//no tuvo ventas
				//saca porcentaje de ventas totales
				$tot_no_venta = $array_clientes_sin_jefe_vendedor[$i]['total'];
				$tot_si_venta = 0;
				$porc_no_venta = 100;
				$porc_si_venta = 0;
				$array_clientes_jefe_vend_porc[$i]['nombre'] = $array_clientes_sin_jefe_vendedor[$i]['nombre'];
				$array_clientes_jefe_vend_porc[$i]['VdorTxt'] = $array_clientes_sin_jefe_vendedor[$i]['VdorTxt'];
				$array_clientes_jefe_vend_porc[$i]['tot_no_venta'] = $porc_no_venta;
				$array_clientes_jefe_vend_porc[$i]['tot_si_venta'] = $porc_si_venta;	
				$encontro_color=0;
				for($z=0; $z<count($array_colores); $z++){
					if($array_colores[$z]['descripcion'] == $array_clientes_sin_jefe_vendedor[$i]['nombre']){
						$encontro_color++;
						$color = $array_colores[$z]['color'];
					}
				}	
				if($encontro_color != 0){
					$array_clientes_jefe_vend_porc[$i]['color'] = $color;
				}else{
					$array_clientes_jefe_vend_porc[$i]['color'] = $array_colores[3]['color'];
				}		
			}
		}		
		
		//cuenta clientes sin facturacion por provincia por jefes haciendo un doble group_by function()
		$array_provincia_sin = group_by('CliDptoRed', $array_clientes_sin);
		$a = 0;
		foreach($array_provincia_sin as $clave_prov => $valor_prov) {
			//vuelvo a agrupar dentro de provincias por jefes
			$array_prov_jefe_sin = group_by('JefNom', $valor_prov);
			//vuelvo a hacer el foreach ahora sobre el agrupado de provincias+jefes
			foreach($array_prov_jefe_sin as $clave_jefe => $valor_jefe) {
				//vuelvo a agrupar dentro de provincias x jefe para sacar a los vendedores
				$array_prov_jefe_vend_sin = group_by('VdorTxt', $valor_jefe);
				foreach($array_prov_jefe_vend_sin as $clave_vend => $valor_vend) {
					$total_vend = count($valor_vend);
					$contador = 0;
					if($total_vend != 0){
						$contador++;
					}
					if($contador != 0){
						$array_clientes_sin_provincia_jefe_vend[$a]['VdorTxt'] = $clave_vend;
						$array_clientes_sin_provincia_jefe_vend[$a]['JefNom'] = $clave_jefe;
						$array_clientes_sin_provincia_jefe_vend[$a]['nombre'] = $clave_prov;
						$array_clientes_sin_provincia_jefe_vend[$a]['total'] = $total_vend;
						$a++;
					}
				}
			}
		}

		//cuenta clientes con facturacion por provincia por jefes haciendo un doble group_by function()
		$array_provincia_con = group_by('CliDptoRed', $array_clientes_con);
		$a = 0;
		foreach($array_provincia_con as $clave_prov => $valor_prov) {
			//vuelvo a agrupar dentro de provincias por jefes
			$array_prov_jefe_con = group_by('JefNom', $valor_prov);
			//vuelvo a hacer el foreach ahora sobre el agrupado de provincias+jefes
			foreach($array_prov_jefe_con as $clave_jefe => $valor_jefe) {
				//vuelvo a agrupar dentro de provincias x jefe para sacar a los vendedores
				$array_prov_jefe_vend_con = group_by('VdorTxt', $valor_jefe);
				foreach($array_prov_jefe_vend_con as $clave_vend => $valor_vend) {
					$total_vend = count($valor_vend);
					$contador = 0;
					if($total_vend != 0){
						$contador++;
					}
					if($contador != 0){
						$array_clientes_con_provincia_jefe_vend[$a]['VdorTxt'] = $clave_vend;
						$array_clientes_con_provincia_jefe_vend[$a]['JefNom'] = $clave_jefe;
						$array_clientes_con_provincia_jefe_vend[$a]['nombre'] = $clave_prov;
						$array_clientes_con_provincia_jefe_vend[$a]['total'] = $total_vend;
						$a++;
					}
				}
			}
		}		
		//saco porcentaje provincia+jefe
		for($i=0; $i<count($array_clientes_sin_provincia_jefe_vend); $i++){
			$encontro = 0;
			for($x=0; $x<count($array_clientes_con_provincia_jefe_vend); $x++){
				if($array_clientes_sin_provincia_jefe_vend[$i]['nombre'] == $array_clientes_con_provincia_jefe_vend[$x]['nombre'] 
				   && $array_clientes_sin_provincia_jefe_vend[$i]['JefNom'] == $array_clientes_con_provincia_jefe_vend[$x]['JefNom']
				   && $array_clientes_sin_provincia_jefe_vend[$i]['VdorTxt'] == $array_clientes_con_provincia_jefe_vend[$x]['VdorTxt']){
					$encontro++;
					$tot_no_venta = $array_clientes_sin_provincia_jefe_vend[$i]['total'];
					$tot_si_venta = $array_clientes_con_provincia_jefe_vend[$x]['total'];
				}
			}
			if($encontro != 0){
				$tot_venta = $tot_si_venta+$tot_no_venta;
				$porc_no_venta = round($tot_no_venta*100/$tot_venta, 2);
				$array_clientes_prov_jefe_vend_porc[$i]['nombre'] = $array_clientes_sin_provincia_jefe_vend[$i]['nombre'];
				$array_clientes_prov_jefe_vend_porc[$i]['JefNom'] = $array_clientes_sin_provincia_jefe_vend[$i]['JefNom'];
				$array_clientes_prov_jefe_vend_porc[$i]['VdorTxt'] = $array_clientes_sin_provincia_jefe_vend[$i]['VdorTxt'];
				$array_clientes_prov_jefe_vend_porc[$i]['total'] = $porc_no_venta;
				$array_clientes_prov_jefe_vend_porc[$i]['tot_no_venta'] = $tot_no_venta;
				$array_clientes_prov_jefe_vend_porc[$i]['tot_si_venta'] = $tot_si_venta;	
				$array_clientes_prov_jefe_vend_porc[$i]['tot_venta'] = $tot_si_venta+$tot_no_venta;	
				$encontro_color=0;
				for($z=0; $z<count($array_colores); $z++){
					if($array_colores[$z]['descripcion'] == $array_clientes_sin_provincia_jefe_vend[$i]['nombre']){
						$encontro_color++;
						$color = $array_colores[$z]['color'];
					}
				}	
				if($encontro_color != 0){
					$array_clientes_prov_jefe_vend_porc[$i]['color'] = $color;
				}else{
					$array_clientes_prov_jefe_vend_porc[$i]['color'] = $array_colores[3]['color'];
				}
			}else{
				//si no encuentro un cliente sin ventas entre los clientes con ventas
				//significa que no le vendi por que la consulta de clientes SIN ventas
				//ya tiene el filtro de que en ese periodo ese cliente contra la base de datos de clientes gral
				//no tuvo ventas
				//saca porcentaje de ventas totales
				$tot_no_venta = $array_clientes_sin_provincia_jefe_vend[$i]['total'];
				$tot_si_venta = 0;
				$porc_no_venta = 100;
				$array_clientes_prov_jefe_vend_porc[$i]['nombre'] = $array_clientes_sin_provincia_jefe_vend[$i]['nombre'];
				$array_clientes_prov_jefe_vend_porc[$i]['JefNom'] = $array_clientes_sin_provincia_jefe_vend[$i]['JefNom'];
				$array_clientes_prov_jefe_vend_porc[$i]['VdorTxt'] = $array_clientes_sin_provincia_jefe_vend[$i]['VdorTxt'];
				$array_clientes_prov_jefe_vend_porc[$i]['total'] = $porc_no_venta;
				$array_clientes_prov_jefe_vend_porc[$i]['tot_no_venta'] = $tot_no_venta;
				$array_clientes_prov_jefe_vend_porc[$i]['tot_si_venta'] = $tot_si_venta;	
				$array_clientes_prov_jefe_vend_porc[$i]['tot_venta'] = $tot_si_venta+$tot_no_venta;	
				$encontro_color=0;
				for($z=0; $z<count($array_colores); $z++){
					if($array_colores[$z]['descripcion'] == $array_clientes_sin_provincia_jefe_vend[$i]['nombre']){
						$encontro_color++;
						$color = $array_colores[$z]['color'];
					}
				}	
				if($encontro_color != 0){
					$array_clientes_prov_jefe_vend_porc[$i]['color'] = $color;
				}else{
					$array_clientes_prov_jefe_vend_porc[$i]['color'] = $array_colores[3]['color'];
				}		
			}
		}		

		//cuenta clientes sin facturacion por provincia por jefes haciendo un doble group_by function()
		$array_provincia_sin = group_by('CliDptoRed', $array_clientes_sin);
		$a = 0;
		foreach($array_provincia_sin as $clave_prov => $valor_prov) {
			//vuelvo a agrupar dentro de provincias por jefes
			$array_prov_jefe_sin = group_by('JefNom', $valor_prov);
			//vuelvo a hacer el foreach ahora sobre el agrupado de provincias+jefes
			foreach($array_prov_jefe_sin as $clave_jefe => $valor_jefe) {
				$total_jefe = count($valor_jefe);
				$contador = 0;
				if($total_jefe != 0){
					$contador++;
				}
				if($contador != 0){
					$array_clientes_sin_provincia_jefe[$a]['JefNom'] = $clave_jefe;
					$array_clientes_sin_provincia_jefe[$a]['nombre'] = $clave_prov;
					$array_clientes_sin_provincia_jefe[$a]['total'] = $total_jefe;
					$a++;
				}
			}
		}

		//cuenta clientes con facturacion por provincia por jefes haciendo un doble group_by function()
		$array_provincia_con = group_by('CliDptoRed', $array_clientes_con);
		$a = 0;
		foreach($array_provincia_con as $clave_prov => $valor_prov) {
			//vuelvo a agrupar dentro de provincias por jefes
			$array_prov_jefe_con = group_by('JefNom', $valor_prov);
			//vuelvo a hacer el foreach ahora sobre el agrupado de provincias+jefes
			foreach($array_prov_jefe_con as $clave_jefe => $valor_jefe) {
				$total_jefe = count($valor_jefe);
				$contador = 0;
				if($total_jefe != 0){
					$contador++;
				}
				if($contador != 0){
					$array_clientes_con_provincia_jefe[$a]['JefNom'] = $clave_jefe;
					$array_clientes_con_provincia_jefe[$a]['nombre'] = $clave_prov;
					$array_clientes_con_provincia_jefe[$a]['total'] = $total_jefe;
					$a++;
				}
			}
		}
		
		//saco porcentaje provincia+jefe
		for($i=0; $i<count($array_clientes_sin_provincia_jefe); $i++){
			$encontro = 0;
			for($x=0; $x<count($array_clientes_con_provincia_jefe); $x++){
				if($array_clientes_sin_provincia_jefe[$i]['nombre'] == $array_clientes_con_provincia_jefe[$x]['nombre'] 
				   && $array_clientes_sin_provincia_jefe[$i]['JefNom'] == $array_clientes_con_provincia_jefe[$x]['JefNom']){
					$encontro++;
					$tot_no_venta = $array_clientes_sin_provincia_jefe[$i]['total'];
					$tot_si_venta = $array_clientes_con_provincia_jefe[$x]['total'];
				}
			}
			if($encontro != 0){
				$tot_venta = $tot_si_venta+$tot_no_venta;
				$porc_no_venta = round($tot_no_venta*100/$tot_venta, 2);
				$array_clientes_prov_jefe_porc[$i]['nombre'] = $array_clientes_sin_provincia_jefe[$i]['nombre'];
				$array_clientes_prov_jefe_porc[$i]['JefNom'] = $array_clientes_sin_provincia_jefe[$i]['JefNom'];
				$array_clientes_prov_jefe_porc[$i]['total'] = $porc_no_venta;
				$array_clientes_prov_jefe_porc[$i]['tot_no_venta'] = $tot_no_venta;
				$array_clientes_prov_jefe_porc[$i]['tot_si_venta'] = $tot_si_venta;	
				$array_clientes_prov_jefe_porc[$i]['tot_venta'] = $tot_si_venta+$tot_no_venta;	
				$encontro_color=0;
				for($z=0; $z<count($array_colores); $z++){
					if($array_colores[$z]['descripcion'] == $array_clientes_sin_provincia_jefe[$i]['nombre']){
						$encontro_color++;
						$color = $array_colores[$z]['color'];
					}
				}	
				if($encontro_color != 0){
					$array_clientes_prov_jefe_porc[$i]['color'] = $color;
				}else{
					$array_clientes_prov_jefe_porc[$i]['color'] = $array_colores[3]['color'];
				}
			}else{
				//si no encuentro un cliente sin ventas entre los clientes con ventas
				//significa que no le vendi por que la consulta de clientes SIN ventas
				//ya tiene el filtro de que en ese periodo ese cliente contra la base de datos de clientes gral
				//no tuvo ventas
				//saca porcentaje de ventas totales
				$tot_no_venta = $array_clientes_sin_provincia_jefe[$i]['total'];
				$tot_si_venta = 0;
				$porc_no_venta = 100;
				$array_clientes_prov_jefe_porc[$i]['nombre'] = $array_clientes_sin_provincia_jefe[$i]['nombre'];
				$array_clientes_prov_jefe_porc[$i]['JefNom'] = $array_clientes_sin_provincia_jefe[$i]['JefNom'];
				$array_clientes_prov_jefe_porc[$i]['total'] = $porc_no_venta;
				$array_clientes_prov_jefe_porc[$i]['tot_no_venta'] = $tot_no_venta;
				$array_clientes_prov_jefe_porc[$i]['tot_si_venta'] = $tot_si_venta;	
				$array_clientes_prov_jefe_porc[$i]['tot_venta'] = $tot_si_venta+$tot_no_venta;	
				$encontro_color=0;
				for($z=0; $z<count($array_colores); $z++){
					if($array_colores[$z]['descripcion'] == $array_clientes_sin_provincia_jefe[$i]['nombre']){
						$encontro_color++;
						$color = $array_colores[$z]['color'];
					}
				}	
				if($encontro_color != 0){
					$array_clientes_prov_jefe_porc[$i]['color'] = $color;
				}else{
					$array_clientes_prov_jefe_porc[$i]['color'] = $array_colores[3]['color'];
				}		
			}
		}
		

		
		//ordena array
		$array_clientes_prov_jefe_porc = ordena_array_multi_x_campo($array_clientes_prov_jefe_porc, 'total', 'desc');
		
		echo json_encode(array('array_cli_jefe_porc' => $array_clientes_jefe_porc,
//							   'array_clientes_con_jefe' => $array_clientes_con_jefe,
//							   'array_clientes_sin_jefe' => $array_clientes_sin_jefe,
							   'array_cli_prov_jefe_porc' => $array_clientes_prov_jefe_porc,
							   'array_clientes_jefe_vend_porc' => $array_clientes_jefe_vend_porc,
							   'array_clientes_prov_jefe_vend_porc' => $array_clientes_prov_jefe_vend_porc
							  ));	
	break;				
	case 'clientes_sin_ventas':
//		$array_clientes_sin = 0;
		//modificacion 26/08/2020
		switch($_POST['periodo']){
			case '3m':
				$array_clientes_sin = clientes_sin_ventas_3m($_POST['codigo']);
				$array_clientes_con = clientes_con_ventas_3m($_POST['codigo']);				
			break;	
			case '6m':
				$array_clientes_sin = clientes_sin_ventas_6m($_POST['codigo']);
				$array_clientes_con = clientes_con_ventas_6m($_POST['codigo']);				
			break;	
			case '12m':
				$array_clientes_sin = clientes_sin_ventas_12m($_POST['codigo']);
				$array_clientes_con = clientes_con_ventas_12m($_POST['codigo']);				
			break;					
		}
		
		$array_colores = colores();
		//saca porcentaje de ventas totales
		$tot_no_venta = count($array_clientes_sin);
		$tot_si_venta = count($array_clientes_con);
		$tot_venta = $tot_si_venta+$tot_no_venta;
		$porc_si_venta_total = round($tot_si_venta*100/$tot_venta, 2);
		$porc_no_venta_total = round($tot_no_venta*100/$tot_venta, 2);		
		
		//cuenta clientes sin facturacion por provincia
		$array_provincia_sin = group_by('CliDptoRed', $array_clientes_sin);
		$a = 0;
		foreach($array_provincia_sin as $clave => $valor) {
		//	echo "{$clave} => {$valor} ";
			$total = count($valor);
			$contador = 0;
			if($total != 0){
				if($valor[0]['CliPaisId'] == 1){
					if($valor[0]['CliDptoRed'] != 'INDIA'){
						$contador++;
					}
				}
			}
			if($contador != 0){
				$array_clientes_sin_provincia[$a]['nombre'] = $clave;
				$array_clientes_sin_provincia[$a]['total'] = $total;
				$a++;
			}
		}
		//cuenta clientes con facturacion por provincia
		$array_provincia_con = group_by('CliDptoRed', $array_clientes_con);
		$a = 0;
		foreach($array_provincia_con as $clave => $valor) {
			$total = count($valor);
			$contador = 0;
			if($total != 0){
				if($valor[0]['CliPaisId'] == 1){
					if($valor[0]['CliDptoRed'] != 'INDIA'){
						$contador++;
					}
				}
			}
			if($contador != 0){
				$array_clientes_con_provincia[$a]['nombre'] = $clave;
				$array_clientes_con_provincia[$a]['total'] = $total;
				$a++;
			}

		}

		//saco porcentaje por provincia
		for($i=0; $i<count($array_clientes_sin_provincia); $i++){
			$encontro = 0;
			for($x=0; $x<count($array_clientes_con_provincia); $x++){
				if($array_clientes_sin_provincia[$i]['nombre'] == $array_clientes_con_provincia[$x]['nombre']){
					$encontro++;
					//saca porcentaje de ventas totales
					$tot_no_venta = $array_clientes_sin_provincia[$i]['total'];
					$tot_si_venta = $array_clientes_con_provincia[$x]['total'];
				}
				if($encontro != 0){
					//saca porcentaje de ventas totales
					$tot_venta = $tot_si_venta+$tot_no_venta;
		//			$porc_si_venta = round($tot_si_venta*100/$tot_venta, 2);
					$porc_no_venta = round($tot_no_venta*100/$tot_venta, 2);
					for($z=0; $z<count($array_colores); $z++){
						if($array_colores[$z]['descripcion'] == $array_clientes_sin_provincia[$i]['nombre']){
							$array_clientes_provincia_porc[$i]['nombre'] = $array_clientes_sin_provincia[$i]['nombre'];
							$array_clientes_provincia_porc[$i]['total'] = $porc_no_venta;
							$array_clientes_provincia_porc[$i]['color'] = $array_colores[$z]['color'];
							$array_clientes_provincia_porc[$i]['tot_no_venta'] = $tot_no_venta;
							$array_clientes_provincia_porc[$i]['tot_si_venta'] = $tot_si_venta;	
							$array_clientes_provincia_porc[$i]['tot_venta'] = $tot_si_venta+$tot_no_venta;							
						}
					}	
				}else{
					//no tuvo ventas
					//saca porcentaje de ventas totales
					$porc_no_venta = 100;
					$tot_no_venta = $array_clientes_sin_provincia[$i]['total'];
					$tot_si_venta = 0;
					for($z=0; $z<count($array_colores); $z++){
						if($array_colores[$z]['descripcion'] == $array_clientes_sin_provincia[$i]['nombre']){
							$array_clientes_provincia_porc[$i]['nombre'] = $array_clientes_sin_provincia[$i]['nombre'];
							$array_clientes_provincia_porc[$i]['total'] = $porc_no_venta;
							$array_clientes_provincia_porc[$i]['color'] = $array_colores[$z]['color'];
							$array_clientes_provincia_porc[$i]['tot_no_venta'] = $tot_no_venta;
							$array_clientes_provincia_porc[$i]['tot_si_venta'] = $tot_si_venta;	
							$array_clientes_provincia_porc[$i]['tot_venta'] = $tot_si_venta+$tot_no_venta;									
						}
					}		
				}				
			}
		}
			
		
		//cuenta expo sin facturacion
		$a = 0;
		foreach($array_provincia_sin as $clave => $valor) {

			// $array[3] se actualizará con cada valor de $array...
		//	echo '<br>';
		//	echo "{$clave} => {$valor} ";
		//	echo '<br>';
			$total = count($valor);
		//	echo '<br>';
			$contador = 0;
			if($total != 0){
				if($valor[0]['CliPaisId'] != 1){
					if($valor[0]['CliDptoRed'] != 'COMERCIO EXTERIOR'){
						if($valor[0]['CliDptoRed'] != 'PAIS EXTRANJERO'){
							$contador++;
						}
					}
				}
			}
			if($contador != 0){
				$array_clientes_sin_expo[$a]['nombre'] = $clave;
				$array_clientes_sin_expo[$a]['total'] = $total;
				$a++;
			}
		}
		//cuenta expo con facturacion
		$a = 0;
		foreach($array_provincia_con as $clave => $valor) {
			$total = count($valor);
			$contador = 0;
			if($total != 0){
				if($valor[0]['CliPaisId'] != 1){
					if($valor[0]['CliDptoRed'] != 'COMERCIO EXTERIOR'){
						if($valor[0]['CliDptoRed'] != 'PAIS EXTRANJERO'){
							$contador++;
						}
					}
				}
			}
			if($contador != 0){
				$array_clientes_con_expo[$a]['nombre'] = $clave;
				$array_clientes_con_expo[$a]['total'] = $total;
				$a++;
			}

		}

		//saco porcentaje expo
		for($i=0; $i<count($array_clientes_sin_expo); $i++){
			$encontro = 0;
			for($x=0; $x<count($array_clientes_con_expo); $x++){
				if($array_clientes_sin_expo[$i]['nombre'] == $array_clientes_con_expo[$x]['nombre']){
					$encontro++;
					$tot_no_venta = $array_clientes_sin_expo[$i]['total'];
					$tot_si_venta = $array_clientes_con_expo[$x]['total'];
				}
			}
			if($encontro != 0){
				//saca porcentaje de ventas totales
				$tot_venta = $tot_si_venta+$tot_no_venta;
				$porc_no_venta = round($tot_no_venta*100/$tot_venta, 2);
				$array_clientes_expo_porc[$i]['nombre'] = $array_clientes_sin_expo[$i]['nombre'];
				$array_clientes_expo_porc[$i]['total'] = $porc_no_venta;
				$array_clientes_expo_porc[$i]['color'] = $array_colores[3]['color'];	
				$array_clientes_expo_porc[$i]['tot_no_venta'] = $tot_no_venta;
				$array_clientes_expo_porc[$i]['tot_si_venta'] = $tot_si_venta;	
				$array_clientes_expo_porc[$i]['tot_venta'] = $tot_si_venta+$tot_no_venta;	
			}else{
				//saca porcentaje de ventas totales
				$tot_no_venta = $array_clientes_sin_expo[$i]['total'];
				$tot_si_venta = 0;
				$porc_no_venta = 100;
				$array_clientes_expo_porc[$i]['nombre'] = $array_clientes_sin_expo[$i]['nombre'];
				$array_clientes_expo_porc[$i]['total'] = $porc_no_venta;
				$array_clientes_expo_porc[$i]['color'] = $array_colores[3]['color'];	
				$array_clientes_expo_porc[$i]['tot_no_venta'] = $tot_no_venta;
				$array_clientes_expo_porc[$i]['tot_si_venta'] = $tot_si_venta;	
				$array_clientes_expo_porc[$i]['tot_venta'] = $tot_si_venta+$tot_no_venta;					
			}
		}

		//ordena array
		$array_clientes_provincia_porc = ordena_array_multi_x_campo($array_clientes_provincia_porc, 'total', 'desc');	
		echo json_encode(array('array_clientes_sin' => $array_clientes_sin,
							   'array_clientes_con' => $array_clientes_con,
							   'porc_si_venta' => $porc_si_venta_total,
							   'porc_no_venta' => $porc_no_venta_total,
							   'array_clientes_provincia_porc' => $array_clientes_provincia_porc,
							   'array_clientes_expo_porc' => $array_clientes_expo_porc,
//							   'array_clientes_sin_provincia' => $array_clientes_sin_provincia,
							   'tiempo' =>  $_POST['fecha_desde']
							  ));	
	break;			
	case 'clientes_listado':
		$array_clientes = clientes_listado($_POST['codigo']);
		echo json_encode($array_clientes);
	break;		
	case 'stock_falta_canal':
		$array_stock_faltas_canal = stock_falta_canal($_POST['fecha_desde'], $_POST['fecha_hasta']);
		echo json_encode($array_stock_faltas_canal);
	break;
	case 'stock_dias_de_falta_producto':
		$array_stock_faltas_producto = stock_dias_de_falta_producto($_POST['codigo'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		echo json_encode($array_stock_faltas_producto);
	break;
	case 'stock_dias_de_falta':
		$array_stock_faltas = stock_dias_de_falta($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		echo json_encode($array_stock_faltas);
//		echo json_encode($_POST);
	break;		
	case 'ordena_array_multi_x_campo_canales':
		//cambia el orden del periodo1 
		//*************************** DATOS CANALES ************************
		//captura CANALES del periodo
		$agrupado = $_POST['origen']['grupo_canales'];
		$periodo1_canales = $_POST['origen']['periodo1_canales'];
		$periodo2_canales = $_POST['origen']['periodo2_canales'];
		$periodo3_canales = $_POST['origen']['periodo3_canales'];
		$periodomes_canales = $_POST['origen']['periodomes_canales'];
		switch($_POST['myorden']){
			case 'total_importe':
				//compara periodo1 con el agrupado 
				$periodo1_canales = compara_grupo_periodo1($periodo1_canales, $agrupado);	
				//lo ordena
				$periodo1_canales = ordena_array_multi_x_campo($periodo1_canales, 'totalimp', 'desc');					
			break;
			case 'total_unidades':
				//compara periodo1 con el agrupado 
				$periodo1_canales = compara_grupo_periodo1($periodo1_canales, $agrupado);	
				//lo ordena
				$periodo1_canales = ordena_array_multi_x_campo($periodo1_canales, 'totaluni', 'desc');					
			break;	
		}
		
		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		$periodo2_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodo2_canales);
		$periodo3_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodo3_canales);
		$periodomes_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodomes_canales);
		// array de Subcanales
		$canales = array('grupo_canales' => $agrupado,
							'periodo1_canales' => $periodo1_canales,
							'periodo2_canales' => $periodo2_canales,
							'periodo3_canales' => $periodo3_canales,
						    'periodomes_canales' => $periodomes_canales);
		//*************************** FIN DATOS CANALES ************************		
		echo json_encode($canales);
	break;	
		
	case 'ordena_array_multi_x_campo_subcanales':
		//cambia el orden del periodo1 
		//*************************** DATOS SUBCANALES ************************
		//captura SUBCANALES del periodo
		$agrupado = $_POST['origen']['grupo_subcanales'];
		$periodo1_subcanales = $_POST['origen']['periodo1_subcanales'];
		$periodo2_subcanales = $_POST['origen']['periodo2_subcanales'];
		$periodo3_subcanales = $_POST['origen']['periodo3_subcanales'];
		$periodomes_subcanales = $_POST['origen']['periodomes_subcanales'];
		switch($_POST['myorden']){
			case 'total_importe':
				//compara periodo1 con el agrupado 
				$periodo1_subcanales = compara_grupo_periodo1($periodo1_subcanales, $agrupado);	
				//lo ordena
				$periodo1_subcanales = ordena_array_multi_x_campo($periodo1_subcanales, 'totalimp', 'desc');					
			break;
			case 'total_unidades':
				//compara periodo1 con el agrupado 
				$periodo1_subcanales = compara_grupo_periodo1($periodo1_subcanales, $agrupado);	
				//lo ordena
				$periodo1_subcanales = ordena_array_multi_x_campo($periodo1_subcanales, 'totaluni', 'desc');					
			break;	
		}
		
		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		$periodo2_subcanales = ordena_array_segun_myarray1_x_campo($periodo1_subcanales, $periodo2_subcanales);
		$periodo3_subcanales = ordena_array_segun_myarray1_x_campo($periodo1_subcanales, $periodo3_subcanales);
		$periodomes_subcanales = ordena_array_segun_myarray1_x_campo($periodo1_subcanales, $periodomes_subcanales);

		// array de Subcanales
		$subcanales = array('grupo_subcanales' => $agrupado,
							'periodo1_subcanales' => $periodo1_subcanales,
							'periodo2_subcanales' => $periodo2_subcanales,
							'periodo3_subcanales' => $periodo3_subcanales,
							'periodomes_subcanales' => $periodomes_subcanales);
		//*************************** FIN DATOS SUBCANALES ************************	
		echo json_encode($subcanales);
	break;	
	case 'ordena_array_multi_x_campo_jefes':
		//cambia el orden del periodo1 
		//*************************** DATOS JEFES ************************
		$agrupado = $_POST['grupo_jefes'];
		switch($_POST['myorden']){
			case 'total_importe':
				//recopila transfer JEFES
				$periodo1_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');
				if($_POST['comparativa1_desde'] != 0){
					$periodo2_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');
					$periodo2_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo2_jefes);
				}else{
					$periodo2_jefes = 0;
				}
				if($_POST['comparativa2_desde'] != 0){
					$periodo3_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');
					$periodo3_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo3_jefes);
				}else{
					$periodo3_jefes = 0;
				}
				if($_POST['comparativames_desde'] != 0){
					$periodomes_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');				
					$periodomes_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodomes_jefes);
				}else{
					$periodomes_jefes = 0;
				}	

				//compara periodo1 con el agrupado 
				$periodo1_jefes = compara_grupo_periodo1($periodo1_jefes, $_POST['grupo_jefes']);			
				
				//lo ordena
				if($_POST['mywhere'] == 'FacTpoVtaC = 0'){
					//lo ordena por importe transfer
					$periodo1_jefes = ordena_array_multi_x_campo($periodo1_jefes, 'totalimp_tr', 'desc');							
				}else{
					//lo ordena por importe directa
					$periodo1_jefes = ordena_array_multi_x_campo($periodo1_jefes, 'totalimp', 'desc');							
				}
			break;
			case 'total_unidades':
				//tiene transfer para ordenar
				$periodo1_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_unidades', 'tpovtajefe');
				// $periodo2_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_unidades', 'tpovtajefe');
				// $periodo3_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_unidades', 'tpovtajefe');
				// $periodomes_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_unidades', 'tpovtajefe');				

				if($_POST['comparativa1_desde'] != 0){
					$periodo2_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_unidades', 'tpovtajefe');
					$periodo2_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo2_jefes);
				}else{
					$periodo2_jefes = 0;
				}
				if($_POST['comparativa2_desde'] != 0){
					$periodo3_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_unidades', 'tpovtajefe');
					$periodo3_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo3_jefes);
				}else{
					$periodo3_jefes = 0;
				}
				if($_POST['comparativames_desde'] != 0){
					$periodomes_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_unidades', 'tpovtajefe');				
					$periodomes_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodomes_jefes);
				}else{
					$periodomes_jefes = 0;
				}	

				//compara periodo1 con el agrupado 
				$periodo1_jefes = compara_grupo_periodo1($periodo1_jefes, $_POST['grupo_jefes']);
				
				if($_POST['mywhere'] == 'FacTpoVtaC = 0'){					
					//lo ordena por unidades transfer
					$periodo1_jefes = ordena_array_multi_x_campo($periodo1_jefes, 'totaluni_tr', 'desc');	
				}else{
					//lo ordena por unidades directa
					$periodo1_jefes = ordena_array_multi_x_campo($periodo1_jefes, 'totaluni', 'desc');							
				}
			break;	
			default:
				//no tiene transfer para ordenar
				$periodo1_jefes = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'jefnom', $_POST['myorden'], 'tpovtajefe');
				// $periodo2_jefes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'jefnom', $_POST['myorden'], 'tpovtajefe');
				// $periodo3_jefes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'jefnom', $_POST['myorden'], 'tpovtajefe');
				// $periodomes_jefes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'jefnom', $_POST['myorden'], 'tpovtajefe');
				//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1 solo si tiene comparativa
				if($_POST['comparativa1_desde'] != 0){
					$periodo2_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'jefnom', $_POST['myorden'], 'tpovtajefe');
					$periodo2_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo2_jefes);
				}else{
					$periodo2_jefes = 0;
				}
				if($_POST['comparativa2_desde'] != 0){
					$periodo3_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'jefnom', $_POST['myorden'], 'tpovtajefe');
					$periodo3_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo3_jefes);
				}else{
					$periodo3_jefes = 0;
				}
				if($_POST['comparativames_desde'] != 0){
					$periodomes_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'jefnom', $_POST['myorden'], 'tpovtajefe');				
					$periodomes_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodomes_jefes);
				}else{
					$periodomes_jefes = 0;
				}

				//compara periodo1 con el agrupado 
				$periodo1_jefes = compara_grupo_periodo1($periodo1_jefes, $_POST['grupo_jefes']);		

				//lo ordena
				if($_POST['myorden'] == 'data_para_barra'){
					$periodo1_jefes = ordena_array_multi_x_campo($periodo1_jefes, 'totalimp', 'desc');							
				}else{
					$periodo1_jefes = ordena_array_multi_x_campo($periodo1_jefes, 'totaluni', 'desc');						
				}

		}			

		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		// $periodo2_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo2_jefes);
		// $periodo3_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo3_jefes);
		// $periodomes_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodomes_jefes);

		// array de Jefes
		$jefes = array('grupo_jefes' => $agrupado,
							// 'myorden' => $_POST['myorden'],
							// 'mywhere' => $_POST['mywhere'],
							// 'mywhere_transfer' => $_POST['mywhere_transfer'],
							// 'fecha_desde' => $_POST['fecha_desde'],
							// 'fecha_hasta' =>  $_POST['fecha_hasta'],
							// 'informacion_fecha' => $_POST['informacion_fecha'],
			
							'periodo1_jefes' => $periodo1_jefes,
							'periodo2_jefes' => $periodo2_jefes,
							'periodo3_jefes' => $periodo3_jefes,			
							'periodomes_jefes' => $periodomes_jefes
						);		
		echo json_encode($jefes);
		//*************************** FIN DATOS JEFES **********************		
	
	break;
	case 'ordena_array_multi_x_campo_vendedores':
		//cambia el orden del periodo1 
		//*************************** DATOS VENDEDORES ************************
		//captura VENDEDORES del periodo seleccionado
		switch($_POST['myorden']){
			case 'total_importe':
				//tiene transfer para ordenar
				$periodo1_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_importe', 'facvdorid');
				if($_POST['comparativa1_desde'] != 0){
					$periodo2_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_importe', 'facvdorid');
					$periodo2_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo2_vendedores);
				}else{
					$periodo2_vendedores = 0;
				}
				if($_POST['comparativa2_desde'] != 0){
					$periodo3_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_importe', 'facvdorid');
					$periodo3_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo3_vendedores);
				}else{
					$periodo3_vendedores = 0;
				}
				if($_POST['comparativames_desde'] != 0){
					$periodomes_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_importe', 'facvdorid');				
					$periodomes_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodomes_vendedores);
				}else{
					$periodomes_vendedores = 0;
				}		

				//compara periodo1 con el agrupado 
				$periodo1_vendedores = compara_grupo_periodo1($periodo1_vendedores, $_POST['grupo_vendedores']);

				if($_POST['mywhere'] == 'FacTpoVtaC = 0'){
					//lo ordena por importe transfer
					$periodo1_vendedores = ordena_array_multi_x_campo($periodo1_vendedores, 'totalimp_tr', 'desc');							
				}else{
					//lo ordena por importe directa
					$periodo1_vendedores = ordena_array_multi_x_campo($periodo1_vendedores, 'totalimp', 'desc');							
				}
			break;
			case 'total_unidades':
				//tiene transfer para ordenar
				$periodo1_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_unidades', 'facvdorid');
				if($_POST['comparativa1_desde'] != 0){
					$periodo2_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_unidades', 'facvdorid');
					$periodo2_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo2_vendedores);
				}else{
					$periodo2_vendedores = 0;
				}
				if($_POST['comparativa2_desde'] != 0){
					$periodo3_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_unidades', 'facvdorid');
					$periodo3_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo3_vendedores);
				}else{
					$periodo3_vendedores = 0;
				}
				if($_POST['comparativames_desde'] != 0){
					$periodomes_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_unidades', 'facvdorid');				
					$periodomes_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodomes_vendedores);
				}else{
					$periodomes_vendedores = 0;
				}	

				//compara periodo1 con el agrupado 
				$periodo1_vendedores = compara_grupo_periodo1($periodo1_vendedores, $_POST['grupo_vendedores']);	
				
				if($_POST['mywhere'] == 'FacTpoVtaC = 0'){
					//lo ordena por importe transfer
					$periodo1_vendedores = ordena_array_multi_x_campo($periodo1_vendedores, 'totaluni_tr', 'desc');							
				}else{
					//lo ordena por importe directa
					$periodo1_vendedores = ordena_array_multi_x_campo($periodo1_vendedores, 'totaluni', 'desc');							
				}				
				
			break;	
			default:
				//no tiene transfer para ordenar
				$periodo1_vendedores = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'vdortxt', $_POST['myorden'], 'facvdorid');
				if($_POST['comparativa1_desde'] != 0){
					$periodo2_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'vdortxt', $_POST['myorden'], 'facvdorid');
					$periodo2_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo2_vendedores);
				}else{
					$periodo2_vendedores = 0;
				}
				if($_POST['comparativa2_desde'] != 0){
					$periodo3_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'vdortxt', $_POST['myorden'], 'facvdorid');
					$periodo3_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo3_vendedores);
				}else{
					$periodo3_vendedores = 0;
				}
				if($_POST['comparativames_desde'] != 0){
					$periodomes_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'vdortxt', $_POST['myorden'], 'facvdorid');				
					$periodomes_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodomes_vendedores);
				}else{
					$periodomes_vendedores = 0;
				}

				//compara periodo1 con el agrupado 
				$periodo1_vendedores = compara_grupo_periodo1($periodo1_vendedores, $_POST['grupo_vendedores']);			
				//lo ordena
				if($_POST['myorden'] == 'data_para_barra'){
					$periodo1_vendedores = ordena_array_multi_x_campo($periodo1_vendedores, 'totalimp', 'desc');							
				}else{
					$periodo1_vendedores = ordena_array_multi_x_campo($periodo1_vendedores, 'totaluni', 'desc');						
				}					
		}			

		// array de vendedores
		$vendedores = array('grupo_vendedores' => $_POST['grupo_vendedores'],
							'periodo1_vendedores' => $periodo1_vendedores,
							'periodo2_vendedores' => $periodo2_vendedores,
							'periodo3_vendedores' => $periodo3_vendedores,			
							'periodomes_vendedores' => $periodomes_vendedores);			

		echo json_encode($vendedores);	
		//*************************** FIN DATOS VENDEDORES **********************			
	break;
	case 'ordena_array_multi_x_campo_productos':
		//cambia el orden del periodo1 
		//*************************** DATOS PRODUCTOS ************************
		//captura PRODUCTOS del periodo seleccionado
		switch($_POST['myorden']){
			case 'total_importe':
				//tiene transfer para ordenar
				$periodo1_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_importe', 'facprdid');
				if($_POST['comparativa1_desde'] != 0){
					$periodo2_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_importe', 'facprdid');
					$periodo2_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo2_productos);
				}else{
					$periodo2_productos = 0;
				}
				if($_POST['comparativa2_desde'] != 0){
					$periodo3_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_importe', 'facprdid');
					$periodo3_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo3_productos);
				}else{
					$periodo3_productos = 0;
				}
				if($_POST['comparativames_desde'] != 0){
					$periodomes_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_importe', 'facprdid');				
					$periodomes_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodomes_productos);
				}else{
					$periodomes_productos = 0;
				}
				//compara periodo1 con el agrupado 
				$periodo1_productos = compara_grupo_periodo1($periodo1_productos, $_POST['grupo_productos']);
				
				if($_POST['mywhere'] == 'FacTpoVtaC = 0'){
					//lo ordena por importe transfer
					$periodo1_productos = ordena_array_multi_x_campo($periodo1_productos, 'totalimp_tr', 'desc');							
				}else{
					//lo ordena por importe directa
					$periodo1_productos = ordena_array_multi_x_campo($periodo1_productos, 'totalimp', 'desc');							
				}
			break;
			case 'total_unidades':
				//tiene transfer para ordenar
				$periodo1_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_unidades', 'facprdid');
				if($_POST['comparativa1_desde'] != 0){
					$periodo2_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_unidades', 'facprdid');
					$periodo2_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo2_productos);
				}else{
					$periodo2_productos = 0;
				}
				if($_POST['comparativa2_desde'] != 0){
					$periodo3_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_unidades', 'facprdid');
					$periodo3_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo3_productos);
				}else{
					$periodo3_productos = 0;
				}
				if($_POST['comparativames_desde'] != 0){
					$periodomes_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_unidades', 'facprdid');				
					$periodomes_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodomes_productos);
				}else{
					$periodomes_productos = 0;
				}
				//compara periodo1 con el agrupado 
				$periodo1_productos = compara_grupo_periodo1($periodo1_productos, $_POST['grupo_productos']);	
				
				if($_POST['mywhere'] == 'FacTpoVtaC = 0'){
					//lo ordena por importe transfer
					$periodo1_productos = ordena_array_multi_x_campo($periodo1_productos, 'totaluni_tr', 'desc');							
				}else{
					//lo ordena por importe directa
					$periodo1_productos = ordena_array_multi_x_campo($periodo1_productos, 'totaluni', 'desc');							
				}				
			break;	
			default:
				//no tiene transfer para ordenar
				$periodo1_productos = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'prdtxt', $_POST['myorden'], 'facprdid');
				if($_POST['comparativa1_desde'] != 0){
					$periodo2_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'prdtxt',  $_POST['myorden'], 'facprdid');
					$periodo2_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo2_productos);
				}else{
					$periodo2_productos = 0;
				}
				if($_POST['comparativa2_desde'] != 0){
					$periodo3_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'prdtxt',  $_POST['myorden'], 'facprdid');
					$periodo3_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo3_productos);
				}else{
					$periodo3_productos = 0;
				}
				if($_POST['comparativames_desde'] != 0){
					$periodomes_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'prdtxt',  $_POST['myorden'], 'facprdid');				
					$periodomes_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodomes_productos);
				}else{
					$periodomes_productos = 0;
				}
				//compara periodo1 con el agrupado 
				$periodo1_productos = compara_grupo_periodo1($periodo1_productos, $_POST['grupo_productos']);			
				//lo ordena
				if($_POST['myorden'] == 'data_para_barra'){
					$periodo1_productos = ordena_array_multi_x_campo($periodo1_productos, 'totalimp', 'desc');							
				}else{
					$periodo1_productos = ordena_array_multi_x_campo($periodo1_productos, 'totaluni', 'desc');						
				}					
		}			

		// array de productos
		$productos = array('grupo_productos' => $_POST['grupo_productos'],
							'periodo1_productos' => $periodo1_productos,
							'periodo2_productos' => $periodo2_productos,
							'periodo3_productos' => $periodo3_productos,			
							'periodomes_productos' => $periodomes_productos);		

		echo json_encode($productos);	
	break;
	case 'ordena_array_multi_x_campo_clientes':
		//cambia el orden del periodo1 
		//*************************** DATOS CLIENTES ************************
		//captura CLIENTES del periodo seleccionado
		switch($_POST['myorden']){
			case 'total_importe':
				//tiene transfer para ordenar
				$periodo1_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_importe', 'faccliid');
				if($_POST['comparativa1_desde'] != 0){
					$periodo2_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_importe', 'faccliid');
					$periodo2_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo2_clientes);
				}else{
					$periodo2_clientes = 0;
				}
				if($_POST['comparativa2_desde'] != 0){
					$periodo3_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_importe', 'faccliid');
					$periodo3_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo3_clientes);
				}else{
					$periodo3_clientes = 0;
				}
				if($_POST['comparativames_desde'] != 0){
					$periodomes_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_importe', 'faccliid');				
					$periodomes_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodomes_clientes);
				}else{
					$periodomes_clientes = 0;
				}
			break;


			// case 'total_importe':
			// 	//recopila transfer JEFES
			// 	$periodo1_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');
			// 	if($_POST['comparativa1_desde'] != 0){
			// 		$periodo2_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');
			// 		$periodo2_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo2_jefes);
			// 	}else{
			// 		$periodo2_jefes = 0;
			// 	}
			// 	if($_POST['comparativa2_desde'] != 0){
			// 		$periodo3_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');
			// 		$periodo3_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo3_jefes);
			// 	}else{
			// 		$periodo3_jefes = 0;
			// 	}
			// 	if($_POST['comparativames_desde'] != 0){
			// 		$periodomes_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');				
			// 		$periodomes_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodomes_jefes);
			// 	}else{
			// 		$periodomes_jefes = 0;
			// 	}	

			// 	//compara periodo1 con el agrupado 
			// 	$periodo1_jefes = compara_grupo_periodo1($periodo1_jefes, $_POST['grupo_jefes']);			
				
			// 	//lo ordena
			// 	if($_POST['mywhere'] == 'FacTpoVtaC = 0'){
			// 		//lo ordena por importe transfer
			// 		$periodo1_jefes = ordena_array_multi_x_campo($periodo1_jefes, 'totalimp_tr', 'desc');							
			// 	}else{
			// 		//lo ordena por importe directa
			// 		$periodo1_jefes = ordena_array_multi_x_campo($periodo1_jefes, 'totalimp', 'desc');							
			// 	}
			// break;




			case 'total_unidades':
				//tiene transfer para ordenar
				$periodo1_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_unidades', 'faccliid');
				if($_POST['comparativa1_desde'] != 0){
					$periodo2_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_unidades', 'faccliid');
					$periodo2_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo2_clientes);
				}else{
					$periodo2_clientes = 0;
				}
				if($_POST['comparativa2_desde'] != 0){
					$periodo3_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_unidades', 'faccliid');
					$periodo3_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo3_clientes);
				}else{
					$periodo3_clientes = 0;
				}
				if($_POST['comparativames_desde'] != 0){
					$periodomes_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_unidades', 'faccliid');				
					$periodomes_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodomes_clientes);
				}else{
					$periodomes_clientes = 0;
				}					
			break;	
			default:
				//no tiene transfer para ordenar
				$periodo1_clientes = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'clinomred', $_POST['myorden'], 'faccliid');
				if($_POST['comparativa1_desde'] != 0){
					$periodo2_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'clinomred',  $_POST['myorden'], 'faccliid');
					$periodo2_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo2_clientes);
				}else{
					$periodo2_clientes = 0;
				}
				if($_POST['comparativa2_desde'] != 0){
					$periodo3_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'clinomred',  $_POST['myorden'], 'faccliid');
					$periodo3_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo3_clientes);
				}else{
					$periodo3_clientes = 0;
				}
				if($_POST['comparativames_desde'] != 0){
					$periodomes_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'clinomred',  $_POST['myorden'], 'faccliid');			
					$periodomes_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodomes_clientes);
				}else{
					$periodomes_clientes = 0;
				}						
		}			

		// array de clientes
		$clientes = array('grupo_clientes' => $_POST['grupo_clientes'],
							'periodo1_clientes' => $periodo1_clientes,
							'periodo2_clientes' => $periodo2_clientes,
							'periodo3_clientes' => $periodo3_clientes,			
							'periodomes_clientes' => $periodomes_clientes);		
	
		echo json_encode($clientes);
	break;	
	case 'ordena_array_multi_x_campo_centro':
		//*************************** ARMA DATOS CENTRO PARA GRAFICOS ************************
		//datos que vienen de funcion reordenamiento 
		$grupo_centro = $_POST['grupo_centro'];
		$array_detalle_periodo1_centro_importe = $_POST['array_detalle_periodo1_centro_importe'];
		$array_detalle_periodo2_centro_importe = $_POST['$array_detalle_periodo2_centro_importe'];
		$array_detalle_periodo3_centro_importe = $_POST['array_detalle_periodo3_centro_importe'];
		$array_detalle_intermensual_centro_importe = $_POST['array_detalle_intermensual_centro_importe'];
		$comparativa1_desde = $_POST['comparativa1_desde'];
		$comparativa2_desde = $_POST['comparativa2_desde'];
		$comparativames_desde = $_POST['comparativames_desde'];
		$myorden = $_POST['myorden']; 

		//arma_datos_canvas es una funcion que hace trabaja desde funciones.php
		$array_resultado = arma_datos_canvas($array_detalle_periodo1_centro_importe, 
											  $array_detalle_periodo2_centro_importe, 
											  $array_detalle_periodo3_centro_importe, 
											  $array_detalle_intermensual_centro_importe,
											  $comparativa1_desde, 
											  $comparativa2_desde, 
											  $comparativames_desde, 
											  $grupo_centro,
											  'wcentro');
		
		$periodo1_centro = $array_resultado['periodo1_resultado'];
		$periodo2_centro = $array_resultado['periodo2_resultado'];
		$periodo3_centro = $array_resultado['periodo3_resultado'];
		$periodomes_centro = $array_resultado['periodomes_resultado'];
		
		//compara periodo1 con el agrupado 
		$periodo1_centro = compara_grupo_periodo1($periodo1_centro, $grupo_centro);
		
		//lo ordena
		$periodo1_centro = ordena_array_multi_x_campo($periodo1_centro, $_POST['myorden'], 'desc');
		
		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		$periodo2_centro = ordena_array_segun_myarray1_x_campo($periodo1_centro, $periodo2_centro);
		$periodo3_centro = ordena_array_segun_myarray1_x_campo($periodo1_centro, $periodo3_centro);
		$periodomes_centro = ordena_array_segun_myarray1_x_campo($periodo1_centro, $periodomes_centro);

		// array de Centros
		$centro = array('grupo_centro' => $grupo_centro,
//							'array_detalle_periodo1_centro_importe' => $array_detalle_periodo1_centro_importe,
							'periodo1_centro' => $periodo1_centro,
							'periodo2_centro' => $periodo2_centro,
							'periodo3_centro' => $periodo3_centro,
							'periodomes_centro' => $periodomes_centro
						   );
		echo json_encode($centro);		 
		//*************************** FIN DATOS CENTRO ************************			
	break;	
	case 'ordena_array_multi_x_campo_linea':
		//*************************** CAPTURA DATOS LINEA ************************
		
		//datos que vienen de funcion reordenamiento 
		$grupo_linea = $_POST['grupo_linea'];
		$array_detalle_periodo1_linea_importe = $_POST['array_detalle_periodo1_linea_importe'];
		$array_detalle_periodo2_linea_importe = $_POST['$array_detalle_periodo2_linea_importe'];
		$array_detalle_periodo3_linea_importe = $_POST['array_detalle_periodo3_linea_importe'];
		$array_detalle_intermensual_linea_importe = $_POST['array_detalle_intermensual_linea_importe'];
		$comparativa1_desde = $_POST['comparativa1_desde'];
		$comparativa2_desde = $_POST['comparativa2_desde'];
		$comparativames_desde = $_POST['comparativames_desde'];
		$myorden = $_POST['myorden'];
		
		//arma_datos_canvas es una funcion que hace trabajo desde funciones.php
		$array_resultado = arma_datos_canvas($array_detalle_periodo1_linea_importe, 
											  $array_detalle_periodo2_linea_importe, 
											  $array_detalle_periodo3_linea_importe, 
											  $array_detalle_intermensual_linea_importe,
											  $comparativa1_desde, 
											  $comparativa2_desde, 
											  $comparativames_desde, 										 
											  $grupo_linea,
											  'wlinea');
		
		$periodo1_linea = $array_resultado['periodo1_resultado'];
		$periodo2_linea = $array_resultado['periodo2_resultado'];
		$periodo3_linea = $array_resultado['periodo3_resultado'];
		$periodomes_linea = $array_resultado['periodomes_resultado'];		

		//compara periodo1 con el agrupado 
		$periodo1_linea = compara_grupo_periodo1($periodo1_linea, $grupo_linea);

		//lo ordena 
		$periodo1_linea = ordena_array_multi_x_campo($periodo1_linea, $_POST['myorden'], 'desc');

		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		$periodo2_linea = ordena_array_segun_myarray1_x_campo($periodo1_linea, $periodo2_linea);
		$periodo3_linea = ordena_array_segun_myarray1_x_campo($periodo1_linea, $periodo3_linea);
		$periodomes_linea = ordena_array_segun_myarray1_x_campo($periodo1_linea, $periodomes_linea);

		// array de Subcanales
		$linea = array('grupo_linea' => $grupo_linea,
							'periodo1_linea' => $periodo1_linea,
							'periodo2_linea' => $periodo2_linea,
							'periodo3_linea' => $periodo3_linea,
							'periodomes_linea' => $periodomes_linea
						   );
		echo json_encode($linea);	

		//*************************** FIN DATOS LINEA ************************	
	break;		
	case 'ordena_array_multi_x_campo_producto_c':
	
		//*************************** CAPTURA DATOS PRODUCTO ************************
		$grupo_producto = $_POST['grupo_producto'];
		$array_detalle_periodo1_producto_importe = $_POST['array_detalle_periodo1_producto_importe'];
		$array_detalle_periodo2_producto_importe = $_POST['$array_detalle_periodo2_producto_importe'];
		$array_detalle_periodo3_producto_importe = $_POST['array_detalle_periodo3_producto_importe'];
		$array_detalle_intermensual_producto_importe = $_POST['array_detalle_intermensual_producto_importe'];
		$comparativa1_desde = $_POST['comparativa1_desde'];
		$comparativa2_desde = $_POST['comparativa2_desde'];
		$comparativames_desde = $_POST['comparativames_desde'];
		$myorden = $_POST['myorden'];
		
		//arma_datos_canvas es una funcion que hace trabajo desde funciones.php
		$array_resultado = arma_datos_canvas($array_detalle_periodo1_producto_importe, 
											  $array_detalle_periodo2_producto_importe, 
											  $array_detalle_periodo3_producto_importe, 
											  $array_detalle_intermensual_producto_importe,
											  $comparativa1_desde, 
											  $comparativa2_desde, 
											  $comparativames_desde, 										 
											  $grupo_producto,
											  'wproducto'); //wproducto agrupa por producto como quiere andres si se quiere modificar por codigo modificar por codigo
		
		$periodo1_producto = $array_resultado['periodo1_resultado'];
		$periodo2_producto = $array_resultado['periodo2_resultado'];
		$periodo3_producto = $array_resultado['periodo3_resultado'];
		$periodomes_producto = $array_resultado['periodomes_resultado'];		

		//compara periodo1 con el agrupado 
		$periodo1_producto = compara_grupo_periodo1($periodo1_producto, $grupo_producto);

		//lo ordena
		$periodo1_producto = ordena_array_multi_x_campo($periodo1_producto, $_POST['myorden'], 'desc');

		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		$periodo2_producto = ordena_array_segun_myarray1_x_campo($periodo1_producto, $periodo2_producto);
		$periodo3_producto = ordena_array_segun_myarray1_x_campo($periodo1_producto, $periodo3_producto);
		$periodomes_producto = ordena_array_segun_myarray1_x_campo($periodo1_producto, $periodomes_producto);

		// array de Subcanales
		$producto = array('grupo_producto' => $grupo_producto,
							'periodo1_producto' => $periodo1_producto,
							'periodo2_producto' => $periodo2_producto,
							'periodo3_producto' => $periodo3_producto,
							'periodomes_producto' => $periodomes_producto
						   );
		echo json_encode($producto);
		//*************************** FIN DATOS PRODUCTO ************************			
	break;	
	case 'ordena_array_multi_x_campo_centrocanal':
		//*************************** ARMA DATOS CENTRO PARA GRAFICOS ************************
		//datos que vienen de funcion reordenamiento 
		
		$array_detalle_periodo1_centrocanal_importe = $_POST['array_detalle_periodo1_centrocanal_importe'];
		$array_detalle_periodo2_centrocanal_importe = $_POST['array_detalle_periodo2_centrocanal_importe'];
		$array_detalle_periodo3_centrocanal_importe = $_POST['array_detalle_periodo3_centrocanal_importe'];
		$array_detalle_intermensual_centrocanal_importe = $_POST['array_detalle_intermensual_centrocanal_importe'];
		$myorden = $_POST['myorden']; 

		//agrupo nombres de centrocanal de todos los periodos consultados 		
//		$grupo_centrocanal = agrupa_nombres2($array_detalle_periodo1_centrocanal_importe, 
//										$array_detalle_periodo2_centrocanal_importe, 
//										$array_detalle_periodo3_centrocanal_importe, 
//										$array_detalle_intermensual_centrocanal_importe, 
//										$_POST['comparativa1_desde'], 
//										$_POST['comparativa2_desde'], 
//										$_POST['comparativames_desde'], 
//										'wcentro');		
		
		$periodo1_centrocanal = $array_detalle_periodo1_centrocanal_importe;
		$periodo2_centrocanal = $array_detalle_periodo2_centrocanal_importe;
		$periodo3_centrocanal = $array_detalle_periodo3_centrocanal_importe;
		$periodomes_centrocanal = $array_detalle_intermensual_centrocanal_importe;

		//lo ordena
		
		
		//tengo que revisar los 2 periodos al derecho y al revez
		//comparando canal/centro de uno contra otro ya que no solo pueden tener diferente cantidad de registros sino que pueden tener
		//la misma cantidad de registros pero diferentes entre si
		if($_POST['fecha_comparativa1_desde'] != 0){
			$resultado = normaliza_periodos_cpa($periodo1_centrocanal, $periodo2_centrocanal);
			$periodo1_centrocanal = $resultado['array1'];
			$periodo2_centrocanal = $resultado['array2'];
		}
		if($_POST['fecha_comparativa2_desde'] != 0){
			$resultado = normaliza_periodos_cpa($periodo1_centrocanal, $periodo3_centrocanal);
			$periodo1_centrocanal = $resultado['array1'];
			$periodo3_centrocanal = $resultado['array2'];
			$resultado = normaliza_periodos_cpa($periodo2_centrocanal, $periodo3_centrocanal);
			$periodo2_centrocanal = $resultado['array1'];
			$periodo3_centrocanal = $resultado['array2'];			
		}
		if($_POST['fecha_comparativames_desde'] != 0){
			$resultado = normaliza_periodos_cpa($periodo1_centrocanal, $periodomes_centrocanal);
			$periodo1_centrocanal = $resultado['array1'];
			$periodomes_centrocanal = $resultado['array2'];
		}
		//reordeno ahora que los tengo bien completitos
		
		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		$periodo1_centrocanal = ordena_array_multi_x_campo($periodo1_centrocanal, $_POST['myorden'], 'desc');
		$periodo2_centrocanal = ordena_array_segun_myarray1_x_campo_cpa($periodo1_centrocanal, $periodo2_centrocanal);
		$periodo3_centrocanal = ordena_array_segun_myarray1_x_campo_cpa($periodo1_centrocanal, $periodo3_centrocanal);
		$periodomes_centrocanal = ordena_array_segun_myarray1_x_campo_cpa($periodo1_centrocanal, $periodomes_centrocanal);		
		
		// array de Centros
		$centrocanal = array(
							'grupo_centrocanal' => $grupo_centrocanal,
							'periodo1_centrocanal' => $periodo1_centrocanal,
							'periodo2_centrocanal' => $periodo2_centrocanal,
							'periodo3_centrocanal' => $periodo3_centrocanal,
							'periodomes_centrocanal' => $periodomes_centrocanal
//			'mes'=>$_POST['fecha_comparativa2_desde'],
//			'$array_detalle_periodo1_centrocanal_importe' => $array_detalle_periodo1_centrocanal_importe,
//			'$array_detalle_periodo2_centrocanal_importe' => $array_detalle_periodo2_centrocanal_importe,
//			'$array_detalle_periodo3_centrocanal_importe' => $array_detalle_periodo3_centrocanal_importe
						   );
		echo json_encode($centrocanal);		 
		//*************************** FIN DATOS CENTRO ************************			
	break;		
		
		
		
		
		
		
		
		
		
		
	case 'totales_seleccion_fecha_desde_fecha_hasta_cpa':

		//consulta los totales de facturacion del periodo ingresado
		$array_detalle_periodo1 = det_vta_seleccion_sin_impuestos('1=1', $_POST['fecha_desde'], $_POST['fecha_hasta']);
		if($array_detalle_periodo1 != 0){	
			$array_totales_periodo1_importe[0]['total'] = intval($array_detalle_periodo1[0]['TotalAximpo']);
		}		
		//consulta los totales por centro, linea, producto y centrocanal
		
// //funcion que totaliza las unidades corregidas
// function saca_totales_facturacion_unidades_corregidas_nf($miarray){ 
// 	$array_totales = [];
// 	$sumc = 0;
// 	for($z=0; $z<count($miarray); $z++){
// 			$sumc = $sumc+$miarray[$z]['corregidas'];
// 	}
// 	$array_totales[0]['total'] = $sumc;
// 	$array_totales[0]['totalv'] = 0;
// 	$array_totales[0]['totalb'] = 0;
// 	return($array_totales);
// }
//proceso de baja y reproceso especial para la parte de produccion con unidades corregidas centro y linea de produccion
// $array_detalle_periodo1 = det_vta_seleccion_sin_impuestos('1=1', $_POST['fecha_desde'], $_POST['fecha_hasta']);
// $array_totales_periodo1_importe = saca_totales_facturacion_nf($array_detalle_periodo1);




		$array_detalle_periodo1_centro_importe = det_vta_seleccion_sin_impuestos_produccion('wcentro', $_POST['fecha_desde'], $_POST['fecha_hasta']);
		$array_totales_periodo1_centro_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo1_centro_importe);
		$array_detalle_periodo1_linea_importe = det_vta_seleccion_sin_impuestos_produccion('wlinea', $_POST['fecha_desde'], $_POST['fecha_hasta']);
		$array_totales_periodo1_linea_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo1_linea_importe);	
		$array_detalle_periodo1_producto_importe = det_vta_seleccion_sin_impuestos_produccion('wproducto', $_POST['fecha_desde'], $_POST['fecha_hasta']);
		$array_totales_periodo1_producto_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo1_producto_importe);	


		/**01/08/2024 aca esta el error */
		// $array_detalle_periodo1_centrocanal_importe = det_vta_seleccion_sin_impuestos_produccion_centro_canal($_POST['fecha_desde'], $_POST['fecha_hasta']);
		// $array_totales_periodo1_centrocanal_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo1_centrocanal_importe);

		$array_totales_periodo1_unidades = $array_totales_periodo1_producto_unidades;


		//consulta los detalles por centro, linea, producto y centrocanal
		//informacion dia a dia facturado para armar el array_1er_periodo
		//data_estad_todo_corregidas en (funciones.php)
		$data_vta_todo = data_estad_todo_corregidas('1=1', $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha']);
		


		//separo los datos para: Grafico de facturacion
		//data_para_barra
		//data_para_barra_unidades
		//data_para_datatable
		//data_para_datatable_unidades
		// for($i=0; $i<count($data_vta_todo); $i++){
		// 	$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
		// 	$data_para_barra_unidades[$i] = $data_vta_todo[$i]['corregidas'];
		// 	$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
		// 	$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
		// 	$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
		// 	$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['corregidas'];
		// }
		
		//arma arrays con datos para armar graficos y datatables del periodo actual y en caso de que tengan las comparativa1 y comparativa2
		// array_1er_periodo es el vector de facturacion con los datos para armar el grafico y la tabla detras del grafico







// 		$array_1er_periodo = array(
// 									// 'data_para_barra' => $data_para_barra,
// 									// 'data_para_barra_unidades' => $data_para_barra_unidades,
// 									// 'data_para_datatable' => $data_para_datatable,
// 									// 'data_para_datatable_unidades' => $data_para_datatable_unidades,
// 									'array_totales_periodo1_importe' => $array_totales_periodo1_importe,
// 									'array_totales_periodo1_unidades' => $array_totales_periodo1_unidades,

// //								    'data_para_barra_transfer' => $data_para_barra_transfer,
// //									'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
// //									'data_para_datatable_transfer' => $data_para_datatable_transfer,
// //									'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
// //									'array_totales_periodo1_importe_transfer' => $array_totales_periodo1_importe_transfer,

// 									'array_detalle_periodo1' => $array_detalle_periodo1,
// 								    // 'array_detalle_periodo1_centrocanal_importe' => $array_detalle_periodo1_centrocanal_importe,
// 									// 'array_totales_periodo1_centrocanal_unidades' => $array_totales_periodo1_centrocanal_unidades,	
// 								    'array_detalle_periodo1_centro_importe' => $array_detalle_periodo1_centro_importe,
// 									'array_totales_periodo1_centro_unidades' => $array_totales_periodo1_centro_unidades,
// 								    'array_detalle_periodo1_linea_importe' => $array_detalle_periodo1_linea_importe,
// 								    'array_totales_periodo1_linea_unidades' => $array_totales_periodo1_linea_unidades,
// 								    'array_detalle_periodo1_producto_importe' => $array_detalle_periodo1_producto_importe,
// 								    'array_totales_periodo1_producto_unidades' => $array_totales_periodo1_producto_unidades,
// 								    'data_vta_todo' => $data_vta_todo
// 								  ); 

// 		//verifica si tiene comparativa1
// 		if($_POST['comparativa1_desde'] != 0){
// 			//consulta el detalle de venta del periodo fecha ingresada
// 			$array_detalle_periodo2 = det_vta_seleccion_sin_impuestos('1=1', $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
// 			$array_totales_periodo2_importe = saca_totales_facturacion_nf($array_detalle_periodo2);
// 			$array_detalle_periodo2_centro_importe = det_vta_seleccion_sin_impuestos_produccion('wcentro', $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
// 			$array_totales_periodo2_centro_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo2_centro_importe);
// 			$array_detalle_periodo2_linea_importe = det_vta_seleccion_sin_impuestos_produccion('wlinea', $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
// 			$array_totales_periodo2_linea_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo2_linea_importe);	
// 			$array_detalle_periodo2_producto_importe = det_vta_seleccion_sin_impuestos_produccion('wproducto', $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
// 			$array_totales_periodo2_producto_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo2_producto_importe);	
// 			$array_detalle_periodo2_centrocanal_importe = det_vta_seleccion_sin_impuestos_produccion_centro_canal($_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
// 			$array_totales_periodo2_centrocanal_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo2_centrocanal_importe);			
// 			$array_totales_periodo2_unidades = $array_totales_periodo2_centro_unidades;	
			
// 			//informacion dia a dia facturado para armar el array_1er_periodo
// 			$data_vta_todo = data_estad_todo_corregidas('1=1', $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha']);
// 			for($i=0; $i<count($data_vta_todo); $i++){
// 				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
// 				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['corregidas'];
// 				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
// 				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
// 				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
// 				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['corregidas'];
// 			}			
			
// 			// array_detalle_periodo2 es el vector de facturacion de la primer comparativa con los datos para armar el grafico y la tabla detras del grafico
// 			$array_2do_periodo = array('data_para_barra' => $data_para_barra,
// 										'data_para_barra_unidades' => $data_para_barra_unidades,
// 										'data_para_datatable' => $data_para_datatable,
// 										'data_para_datatable_unidades' => $data_para_datatable_unidades,
// 										'array_totales_periodo2_importe' => $array_totales_periodo2_importe,
// 										'array_totales_periodo2_unidades' => $array_totales_periodo2_unidades,
// 										'array_detalle_periodo2' => $array_detalle_periodo2,
// 										'array_detalle_periodo2_centrocanal_importe' => $array_detalle_periodo2_centrocanal_importe,
// 										'array_totales_periodo2_centrocanal_unidades' => $array_totales_periodo2_centrocanal_unidades,								   
// 										'array_detalle_periodo2_centro_importe' => $array_detalle_periodo2_centro_importe,
// 										'array_totales_periodo2_centro_unidades' => $array_totales_periodo2_centro_unidades,
// 										'array_detalle_periodo2_linea_importe' => $array_detalle_periodo2_linea_importe,
// 										'array_totales_periodo2_linea_unidades' => $array_totales_periodo2_linea_unidades,
// 										'array_detalle_periodo2_producto_importe' => $array_detalle_periodo2_producto_importe,
// 										'array_totales_periodo2_producto_unidades' => $array_totales_periodo2_producto_unidades,
// 										'data_vta_todo' => $data_vta_todo
// 									  ); 
// 		}else{
// 			$array_2do_periodo = 0; 
// 			$array_detalle_periodo2 = 0;
// 			$array_totales_periodo2_importe = 0;
// 			$array_totales_periodo2_unidades = 0;	
// 		}

// 		//verifica si tiene comparativa2
// 		if($_POST['comparativa2_desde'] != 0){
			
// 			//consulta el detalle de venta del periodo fecha ingresada
// 			$array_detalle_periodo3 = det_vta_seleccion_sin_impuestos('1=1', $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
// 			$array_totales_periodo3_importe = saca_totales_facturacion_nf($array_detalle_periodo3);
// 			$array_detalle_periodo3_centro_importe = det_vta_seleccion_sin_impuestos_produccion('wcentro', $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
// 			$array_totales_periodo3_centro_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo3_centro_importe);
// 			$array_detalle_periodo3_linea_importe = det_vta_seleccion_sin_impuestos_produccion('wlinea', $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
// 			$array_totales_periodo3_linea_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo3_linea_importe);	
// 			$array_detalle_periodo3_producto_importe = det_vta_seleccion_sin_impuestos_produccion('wproducto', $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
// 			$array_totales_periodo3_producto_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo3_producto_importe);	
// 			$array_detalle_periodo3_centrocanal_importe = det_vta_seleccion_sin_impuestos_produccion_centro_canal($_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
// 			$array_totales_periodo3_centrocanal_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo3_centrocanal_importe);			
// 			$array_totales_periodo3_unidades = $array_totales_periodo3_centro_unidades;				
			
// 			//informacion dia a dia facturado para armar el array_1er_periodo
// 			$data_vta_todo = data_estad_todo_corregidas('1=1', $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha']);
// 			for($i=0; $i<count($data_vta_todo); $i++){
// 				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
// 				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['corregidas'];
// 				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
// 				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
// 				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
// 				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['corregidas'];
// 			}			
			
// 			// array_detalle_periodo3 es el vector de facturacion de la segunda comparativa con los datos para armar el grafico y la tabla detras del grafico
// 			$array_3er_periodo = array('data_para_barra' => $data_para_barra,
// 										'data_para_barra_unidades' => $data_para_barra_unidades,
// 										'data_para_datatable' => $data_para_datatable,
// 										'data_para_datatable_unidades' => $data_para_datatable_unidades,
// 										'array_totales_periodo3_importe' => $array_totales_periodo3_importe,
// 										'array_totales_periodo3_unidades' => $array_totales_periodo3_unidades,
// 										'array_detalle_periodo3' => $array_detalle_periodo3,
// 										'array_detalle_periodo3_centrocanal_importe' => $array_detalle_periodo3_centrocanal_importe,
// 										'array_totales_periodo3_centrocanal_unidades' => $array_totales_periodo3_centrocanal_unidades,								   
// 										'array_detalle_periodo3_centro_importe' => $array_detalle_periodo3_centro_importe,
// 										'array_totales_periodo3_centro_unidades' => $array_totales_periodo3_centro_unidades,
// 										'array_detalle_periodo3_linea_importe' => $array_detalle_periodo3_linea_importe,
// 										'array_totales_periodo3_linea_unidades' => $array_totales_periodo3_linea_unidades,
// 										'array_detalle_periodo3_producto_importe' => $array_detalle_periodo3_producto_importe,
// 										'array_totales_periodo3_producto_unidades' => $array_totales_periodo3_producto_unidades,
// 										'data_vta_todo' => $data_vta_todo
// 									  ); 			
// 		}else{
// 			$array_3er_periodo = 0;
// 			$array_detalle_periodo3 = 0;
// 			$array_totales_periodo3_importe = 0;
// 			$array_totales_periodo3_unidades = 0;			
// 		}	
// 		//verifica si tiene comparativa intermensual
// 		if($_POST['comparativames_desde'] != 0){

// 			//consulta el detalle de venta del fecha intermensual ingresada
// 			$array_detalle_intermensual = det_vta_seleccion_sin_impuestos('1=1', $_POST['comparativames_desde'], $_POST['comparativames_hasta']);
// 			$array_totales_intermensual_importe = saca_totales_facturacion_nf($array_detalle_intermensual);
// 			$array_detalle_intermensual_centro_importe = det_vta_seleccion_sin_impuestos_produccion('wcentro', $_POST['comparativames_desde'], $_POST['comparativames_hasta']);
// 			$array_totales_intermensual_centro_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_intermensual_centro_importe);
// 			$array_detalle_intermensual_linea_importe = det_vta_seleccion_sin_impuestos_produccion('wlinea', $_POST['comparativames_desde'], $_POST['comparativames_hasta']);
// 			$array_totales_intermensual_linea_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_intermensual_linea_importe);	
// 			$array_detalle_intermensual_producto_importe = det_vta_seleccion_sin_impuestos_produccion('wproducto', $_POST['comparativames_desde'], $_POST['comparativames_hasta']);
// 			$array_totales_intermensual_producto_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_intermensual_producto_importe);	
// 			$array_detalle_intermensual_centrocanal_importe = det_vta_seleccion_sin_impuestos_produccion_centro_canal($_POST['comparativames_desde'], $_POST['comparativames_hasta']);
// //			$array_detalle_intermensual_centrocanal_importe2 = det_vta_seleccion_sin_impuestos_produccion_centro_canal($_POST['fecha_desde'], $_POST['fecha_hasta']);
// 			$array_totales_intermensual_centrocanal_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_intermensual_centrocanal_importe);
// 			$array_totales_intermensual_unidades = $array_totales_intermensual_centro_unidades;	
			
// 			//informacion dia a dia facturado para armar el array_intermensual
// 			$data_vta_todo = data_estad_todo_corregidas('1=1', $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha']);
// 			for($i=0; $i<count($data_vta_todo); $i++){
// 				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
// 				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['corregidas'];
// 				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
// 				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
// 				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
// 				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['corregidas'];
// 			}			
// 			$array_intermensual = array('data_para_barra' => $data_para_barra,
// 										'data_para_barra_unidades' => $data_para_barra_unidades,
// 										'data_para_datatable' => $data_para_datatable,
// 										'data_para_datatable_unidades' => $data_para_datatable_unidades,
// 										'array_totales_intermensual_importe' => $array_totales_intermensual_importe,
// 										'array_totales_intermensual_unidades' => $array_totales_intermensual_unidades,
// 										'array_detalle_intermensual' => $array_detalle_intermensual,
// 										'array_detalle_intermensual_centrocanal_importe' => $array_detalle_intermensual_centrocanal_importe,
// 										'array_totales_intermensual_centrocanal_unidades' => $array_totales_intermensual_centrocanal_unidades,								   
// 										'array_detalle_intermensual_centro_importe' => $array_detalle_intermensual_centro_importe,
// 										'array_totales_intermensual_centro_unidades' => $array_totales_intermensual_centro_unidades,
// 										'array_detalle_intermensual_linea_importe' => $array_detalle_intermensual_linea_importe,
// 										'array_totales_intermensual_linea_unidades' => $array_totales_intermensual_linea_unidades,
// 										'array_detalle_intermensual_producto_importe' => $array_detalle_intermensual_producto_importe,
// 										'array_totales_intermensual_producto_unidades' => $array_totales_intermensual_producto_unidades
// 									  ); 			
// 		}else{
// 			$array_intermensual = 0;
// 		}		









		//********** sigo recopilando datos **************
		
		//agrupo nombres de los tres periodos consultados en caso de que tengan datos 

		// //agrupo nombres de centros de produccion de todos los periodos consultados 
		// $grupo_centro_nf = agrupa_nombre_nf($_POST['fecha_desde'], 
		// 									$_POST['fecha_hasta'],
		// 									$_POST['comparativa1_desde'],
		// 									$_POST['comparativa1_hasta'],
		// 									$_POST['comparativa2_desde'],
		// 									$_POST['comparativa2_hasta'],
		// 									$_POST['comparativames_desde'],
		// 									$_POST['comparativames_hasta'],
		// 									'wcentro',
		// 									'wcentro',
		// 									'1=1',
		// 									'1=1' 
		// 								);
		// for($i=0; $i<count($grupo_centro_nf); $i++){
		// 	$grupo_centro[$i] = rtrim($grupo_centro_nf[$i]['wcentro']); //quito espacios
		// }				

		
		// $centro = $grupo_centro;




		// //agrupo nombres de centros de produccion de todos los periodos consultados 		
		// $grupo_centro = agrupa_nombres2($array_detalle_periodo1_centro_importe, 
		// 								$array_detalle_periodo2_centro_importe, 
		// 								$array_detalle_periodo3_centro_importe, 
		// 								$array_detalle_intermensual_centro_importe, 
		// 								$_POST['comparativa1_desde'], 
		// 								$_POST['comparativa2_desde'], 
		// 								$_POST['comparativames_desde'], 
		// 								'wcentro');
		





		// $grupo_linea_nf = agrupa_nombre_nf($_POST['fecha_desde'], 
		// 									$_POST['fecha_hasta'],
		// 									$_POST['comparativa1_desde'],
		// 									$_POST['comparativa1_hasta'],
		// 									$_POST['comparativa2_desde'],
		// 									$_POST['comparativa2_hasta'],
		// 									$_POST['comparativames_desde'],
		// 									$_POST['comparativames_hasta'],
		// 									'wlinea',
		// 									'wlinea',
		// 									'1=1',
		// 									'1=1' 
		// 								);
		// for($i=0; $i<count($grupo_linea_nf); $i++){
		// 	$grupo_linea[$i] = rtrim($grupo_linea_nf[$i]['wlinea']); //quito espacios
		// }			







		// //agrupo nombres de lineas de produccion de todos los periodos consultados 		
		// $grupo_linea = agrupa_nombres2($array_detalle_periodo1_linea_importe, 
		// 								$array_detalle_periodo2_linea_importe, 
		// 								$array_detalle_periodo3_linea_importe, 
		// 								$array_detalle_intermensual_linea_importe, 
		// 								$_POST['comparativa1_desde'], 
		// 								$_POST['comparativa2_desde'], 
		// 								$_POST['comparativames_desde'], 
		// 								'wlinea');	





		// $grupo_producto_nf = agrupa_nombre_nf($_POST['fecha_desde'], 
		// 									$_POST['fecha_hasta'],
		// 									$_POST['comparativa1_desde'],
		// 									$_POST['comparativa1_hasta'],
		// 									$_POST['comparativa2_desde'],
		// 									$_POST['comparativa2_hasta'],
		// 									$_POST['comparativames_desde'],
		// 									$_POST['comparativames_hasta'],
		// 									'wproducto',
		// 									'wproducto',
		// 									'1=1',
		// 									'1=1' 
		// 	);
		// for($i=0; $i<count($grupo_producto_nf); $i++){
		// 	$grupo_producto[$i] = rtrim($grupo_producto_nf[$i]['wproducto']); //quito espacios
		// }






		// //agrupo nombres de productos de produccion de todos los periodos consultados 		
		// $grupo_producto = agrupa_nombres2($array_detalle_periodo1_producto_importe, 
		// 								$array_detalle_periodo2_producto_importe, 
		// 								$array_detalle_periodo3_producto_importe, 
		// 								$array_detalle_intermensual_producto_importe, 
		// 								$_POST['comparativa1_desde'], 
		// 								$_POST['comparativa2_desde'], 
		// 								$_POST['comparativames_desde'], 
		// 								'wproducto');	




		// //agrupo nombres de canal de todos los periodos consultados 	
		// $grupo_canal_nf = agrupa_nombre_nf($_POST['fecha_desde'], 
		// 									$_POST['fecha_hasta'],
		// 									$_POST['comparativa1_desde'],
		// 									$_POST['comparativa1_hasta'],
		// 									$_POST['comparativa2_desde'],
		// 									$_POST['comparativa2_hasta'],
		// 									$_POST['comparativames_desde'],
		// 									$_POST['comparativames_hasta'],
		// 									'gtpvtades',
		// 									'gtpvtades',
		// 									'1=1',
		// 									'1=1' 
		// 	);
		// for($i=0; $i<count($grupo_canal_nf); $i++){
		// 	$grupo_canal[$i] = rtrim($grupo_canal_nf[$i]['gtpvtades']); //quito espacios
		// }			




		// $grupo_canal = agrupa_nombres2($array_detalle_periodo1_centrocanal_importe, 
		// 								$array_detalle_periodo2_centrocanal_importe, 
		// 								$array_detalle_periodo3_centrocanal_importe, 
		// 								$array_detalle_intermensual_centrocanal_importe, 
		// 								$_POST['comparativa1_desde'], 
		// 								$_POST['comparativa2_desde'], 
		// 								$_POST['comparativames_desde'], 
		// 								'gtpvtades');		





		// //agrupo nombres de centrocanal de todos los periodos consultados 	
		// $grupo_centrocanal_nf = agrupa_nombre_nf($_POST['fecha_desde'], 
		// 									$_POST['fecha_hasta'],
		// 									$_POST['comparativa1_desde'],
		// 									$_POST['comparativa1_hasta'],
		// 									$_POST['comparativa2_desde'],
		// 									$_POST['comparativa2_hasta'],
		// 									$_POST['comparativames_desde'],
		// 									$_POST['comparativames_hasta'],
		// 									'gtpvtades',
		// 									'gtpvtades',
		// 									'1=1',
		// 									'1=1' 
		// 	);
		// for($i=0; $i<count($grupo_centrocanal_nf); $i++){
		// 	$grupo_centrocanal[$i] = rtrim($grupo_centrocanal_nf[$i]['gtpvtades']); //quito espacios
		// }	
		
		



		// $grupo_centrocanal = agrupa_nombres2($array_detalle_periodo1_centrocanal_importe, 
		// 								$array_detalle_periodo2_centrocanal_importe, 
		// 								$array_detalle_periodo3_centrocanal_importe, 
		// 								$array_detalle_intermensual_centrocanal_importe, 
		// 								$_POST['comparativa1_desde'], 
		// 								$_POST['comparativa2_desde'], 
		// 								$_POST['comparativames_desde'], 
		// 								'gtpvtades');
		
		// //agrupo nombres de centrocanal de todos los periodos consultados 		
		// $grupo_centrocanal_dt = agrupa_nombres2($array_detalle_periodo1_centrocanal_importe, 
		// 								$array_detalle_periodo2_centrocanal_importe, 
		// 								$array_detalle_periodo3_centrocanal_importe, 
		// 								$array_detalle_intermensual_centrocanal_importe, 
		// 								$_POST['comparativa1_desde'], 
		// 								$_POST['comparativa2_desde'], 
		// 								$_POST['comparativames_desde'], 
		// 								'wcentro');	
		

		//***********************************************************************************************************************************
		//***********************************************************************************************************************************
		//                                          recopila datos x grupo
		//***********************************************************************************************************************************
		//***********************************************************************************************************************************
		
		//*************************** ARMA DATOS CENTRO PARA GRAFICOS ************************


		








		// //arma_datos_canvas es una funcion que hace trabaja desde funciones.php
		// $array_resultado = arma_datos_canvas($array_detalle_periodo1_centro_importe, 
		// 									  $array_detalle_periodo2_centro_importe, 
		// 									  $array_detalle_periodo3_centro_importe, 
		// 									  $array_detalle_intermensual_centro_importe,
		// 									  $_POST['comparativa1_desde'], 
		// 									  $_POST['comparativa2_desde'], 
		// 									  $_POST['comparativames_desde'], 
		// 									  $grupo_centro,
		// 									  'wcentro');
		
		// $periodo1_centro = $array_resultado['periodo1_resultado'];
		// $periodo2_centro = $array_resultado['periodo2_resultado'];
		// $periodo3_centro = $array_resultado['periodo3_resultado'];
		// $periodomes_centro = $array_resultado['periodomes_resultado'];
		
		// //compara periodo1 con el agrupado 
		// $periodo1_centro = compara_grupo_periodo1($periodo1_centro, $grupo_centro);
		
		// //lo ordena
		// $periodo1_centro = ordena_array_multi_x_campo($periodo1_centro, 'totalimp', 'desc');
		
		// //ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		// $periodo2_centro = ordena_array_segun_myarray1_x_campo($periodo1_centro, $periodo2_centro);
		// $periodo3_centro = ordena_array_segun_myarray1_x_campo($periodo1_centro, $periodo3_centro);
		// $periodomes_centro = ordena_array_segun_myarray1_x_campo($periodo1_centro, $periodomes_centro);

		// // array de Centros
		// $centro = array('grupo_centro' => $grupo_centro,
		// 					'periodo1_centro' => $periodo1_centro,
		// 					'periodo2_centro' => $periodo2_centro,
		// 					'periodo3_centro' => $periodo3_centro,
		// 					'periodomes_centro' => $periodomes_centro
		// 				   );
		// //*************************** FIN DATOS CENTRO ************************	
		
// 		//*************************** CAPTURA DATOS LINEA ************************
// 		//arma_datos_canvas es una funcion que hace trabaja desde funciones.php
// 		$array_resultado = arma_datos_canvas($array_detalle_periodo1_linea_importe, 
// 											  $array_detalle_periodo2_linea_importe, 
// 											  $array_detalle_periodo3_linea_importe, 
// 											  $array_detalle_intermensual_linea_importe,
// 											  $_POST['comparativa1_desde'], 
// 											  $_POST['comparativa2_desde'], 
// 											  $_POST['comparativames_desde'], 											 
// 											  $grupo_linea,
// 											  'wlinea');
		
// 		$periodo1_linea = $array_resultado['periodo1_resultado'];
// 		$periodo2_linea = $array_resultado['periodo2_resultado'];
// 		$periodo3_linea = $array_resultado['periodo3_resultado'];
// 		$periodomes_linea = $array_resultado['periodomes_resultado'];		

// 		//compara periodo1 con el agrupado 
// 		$periodo1_linea = compara_grupo_periodo1($periodo1_linea, $grupo_linea);

// 		//lo ordena
// 		$periodo1_linea = ordena_array_multi_x_campo($periodo1_linea, 'totalimp', 'desc');

// 		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
// 		$periodo2_linea = ordena_array_segun_myarray1_x_campo($periodo1_linea, $periodo2_linea);
// 		$periodo3_linea = ordena_array_segun_myarray1_x_campo($periodo1_linea, $periodo3_linea);
// 		$periodomes_linea = ordena_array_segun_myarray1_x_campo($periodo1_linea, $periodomes_linea);

// 		// array de Subcanales
// 		$linea = array('grupo_linea' => $grupo_linea,
// 							'periodo1_linea' => $periodo1_linea,
// 							'periodo2_linea' => $periodo2_linea,
// 							'periodo3_linea' => $periodo3_linea,
// 							'periodomes_linea' => $periodomes_linea
// 						   );
// 		//*************************** FIN DATOS LINEA ************************	
		
// 		//*************************** CAPTURA DATOS PRODUCTO ************************
// 		//arma_datos_canvas es una funcion que hace trabaja desde funciones.php
// 		$array_resultado = arma_datos_canvas($array_detalle_periodo1_producto_importe, 
// 											  $array_detalle_periodo2_producto_importe, 
// 											  $array_detalle_periodo3_producto_importe, 
// 											  $array_detalle_intermensual_producto_importe,
// 											  $_POST['comparativa1_desde'], 
// 											  $_POST['comparativa2_desde'], 
// 											  $_POST['comparativames_desde'], 											 
// 											  $grupo_producto,
// 											  'wproducto'); //wproducto agrupa por producto como quiere andres si se quiere modificar por codigo modificar por codigo
		
// 		$periodo1_producto = $array_resultado['periodo1_resultado'];
// 		$periodo2_producto = $array_resultado['periodo2_resultado'];
// 		$periodo3_producto = $array_resultado['periodo3_resultado'];
// 		$periodomes_producto = $array_resultado['periodomes_resultado'];		

// 		//compara periodo1 con el agrupado 
// 		$periodo1_producto = compara_grupo_periodo1($periodo1_producto, $grupo_producto);

// 		//lo ordena
// 		$periodo1_producto = ordena_array_multi_x_campo($periodo1_producto, 'totalimp', 'desc');

// 		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
// 		$periodo2_producto = ordena_array_segun_myarray1_x_campo($periodo1_producto, $periodo2_producto);
// 		$periodo3_producto = ordena_array_segun_myarray1_x_campo($periodo1_producto, $periodo3_producto);
// 		$periodomes_producto = ordena_array_segun_myarray1_x_campo($periodo1_producto, $periodomes_producto);

// 		// array de Subcanales
// 		$producto = array('grupo_producto' => $grupo_producto,
// 							'periodo1_producto' => $periodo1_producto,
// 							'periodo2_producto' => $periodo2_producto,
// 							'periodo3_producto' => $periodo3_producto,
// 							'periodomes_producto' => $periodomes_producto
// 						   );

// 		//*************************** FIN DATOS PRODUCTO ************************	
// 		//*************************** ARMA DATOS CANAL PARA GRAFICOS ************************

// 		//arma_datos_canvas es una funcion que trabaja desde funciones.php
// 		$array_resultado = arma_datos_canvas_totaliza_campo($array_detalle_periodo1_centrocanal_importe, 
// 											  $array_detalle_periodo2_centrocanal_importe, 
// 											  $array_detalle_periodo3_centrocanal_importe, 
// 											  $array_detalle_intermensual_centrocanal_importe,
// 											  $_POST['comparativa1_desde'], 
// 											  $_POST['comparativa2_desde'], 
// 											  $_POST['comparativames_desde'], 
// 											  $grupo_canal,
// 											  'gtpvtades');
		
// 		$periodo1_canal = $array_resultado['periodo1_resultado'];
// 		$periodo2_canal = $array_resultado['periodo2_resultado'];
// 		$periodo3_canal = $array_resultado['periodo3_resultado'];
// 		$periodo3_canala = $array_resultado['periodo3_resultado'];
// 		$periodomes_canal = $array_resultado['periodomes_resultado'];
		
// 		//compara periodo1 con el agrupado 
// 		$periodo1_canal = compara_grupo_periodo1($periodo1_canal, $grupo_canal);
		
// 		//lo ordena
// 		$periodo1_canal = ordena_array_multi_x_campo($periodo1_canal, 'totalimp', 'desc');
		
// 		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
// 		$periodo2_canal = ordena_array_segun_myarray1_x_campo($periodo1_canal, $periodo2_canal);
// 		$periodo3_canal = ordena_array_segun_myarray1_x_campo($periodo1_canal, $periodo3_canal);
// 		$periodomes_canal = ordena_array_segun_myarray1_x_campo($periodo1_canal, $periodomes_canal);

// 		// array de Centros
// 		$canal = array('grupo_canal' => $grupo_canal,
// 							'periodo1_canal' => $periodo1_canal,
// 							'periodo2_canal' => $periodo2_canal,
// 							'periodo3_canal' => $periodo3_canal,
// 							'periodo3_canala' => $periodo3_canala,
// 							'periodomes_canal' => $periodomes_canal
// 						   );
// 		//*************************** FIN DATOS CANAL ************************		
// 		//*************************** FIN DATOS CENTRO ************************			
		
// 		//arma_datos_canvas es una funcion que trabaja desde funciones.php
// 		$array_resultado = arma_datos_canvas($array_detalle_periodo1_centrocanal_importe, 
// 											  $array_detalle_periodo2_centrocanal_importe, 
// 											  $array_detalle_periodo3_centrocanal_importe, 
// 											  $array_detalle_intermensual_centrocanal_importe,
// 											  $_POST['comparativa1_desde'], 
// 											  $_POST['comparativa2_desde'], 
// 											  $_POST['comparativames_desde'], 
// 											  $grupo_centrocanal,
// 											  'wcentro');
// //											  'gtpvtades');
		
// 		$periodo1_centrocanal = $array_resultado['periodo1_resultado'];
// 		$periodo2_centrocanal = $array_resultado['periodo2_resultado'];
// 		$periodo3_centrocanal = $array_resultado['periodo3_resultado'];
// 		$periodomes_centrocanal = $array_resultado['periodomes_resultado'];
		
// 		//compara periodo1 con el agrupado 
// 		$periodo1_centrocanal = compara_grupo_periodo1($periodo1_centrocanal, $grupo_centrocanal);
		
// 		//lo ordena
// 		$periodo1_centrocanal = ordena_array_multi_x_campo($periodo1_centrocanal, 'totalimp', 'desc');
		
// 		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
// 		$periodo2_centrocanal = ordena_array_segun_myarray1_x_campo($periodo1_centrocanal, $periodo2_centrocanal);
// 		$periodo3_centrocanal = ordena_array_segun_myarray1_x_campo($periodo1_centrocanal, $periodo3_centrocanal);
// 		$periodomes_centrocanal = ordena_array_segun_myarray1_x_campo($periodo1_centrocanal, $periodomes_centrocanal);
		
// 		// array de Centros
// 		$centrocanal = array('grupo_centrocanal' => $grupo_centrocanal,
// 							'periodo1_centrocanal' => $periodo1_centrocanal,
// 							'periodo2_centrocanal' => $periodo2_centrocanal,
// 							'periodo3_centrocanal' => $periodo3_centrocanal,
// 							'periodomes_centrocanal' => $periodomes_centrocanal,
// 							'array_resultado' => $array_resultado
// 						   );
// 		//*************************** FIN DATOS CENTRO-CANAL ************************					
// 		//*************************** ARMA DATOS CENTRO-CANAL PARA DATATABLE ************************
		
// 		$periodo1_centrocanal = $array_detalle_periodo1_centrocanal_importe;
// 		$periodo2_centrocanal = $array_detalle_periodo2_centrocanal_importe;
// 		$periodo3_centrocanal = $array_detalle_periodo3_centrocanal_importe;
// 		$periodomes_centrocanal = $array_detalle_intermensual_centrocanal_importe;

// 		//lo ordena
		
// 		//tengo que revisar los 2 periodos al derecho y al revez
// 		//comparando canal/centro de uno contra otro ya que no solo pueden tener diferente cantidad de registros sino que pueden tener
// 		//la misma cantidad de registros pero diferentes entre si
// 		if($_POST['comparativa1_desde'] != 0){
// 			$resultado = normaliza_periodos_cpa($periodo1_centrocanal, $periodo2_centrocanal);
// 			$periodo1_centrocanal = $resultado['array1'];
// 			$periodo2_centrocanal = $resultado['array2'];
// 		}
// 		if($_POST['comparativa2_desde'] != 0){
// 			$resultado = normaliza_periodos_cpa($periodo1_centrocanal, $periodo3_centrocanal);
// 			$periodo1_centrocanal = $resultado['array1'];
// 			$periodo3_centrocanal = $resultado['array2'];
// 			$resultado = normaliza_periodos_cpa($periodo2_centrocanal, $periodo3_centrocanal);
// 			$periodo2_centrocanal = $resultado['array1'];
// 			$periodo3_centrocanal = $resultado['array2'];			
// 		}
// 		if($_POST['comparativames_desde'] != 0){
// 			$resultado = normaliza_periodos_cpa($periodo1_centrocanal, $periodomes_centrocanal);
// 			$periodo1_centrocanal = $resultado['array1'];
// 			$periodomes_centrocanal = $resultado['array2'];
// 		}
// 		//reordeno ahora que los tengo bien completitos
		
// 		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
// 		$periodo1_centrocanal = ordena_array_multi_x_campo($periodo1_centrocanal, 'aximpo', 'desc');
// 		$periodo2_centrocanal = ordena_array_segun_myarray1_x_campo_cpa($periodo1_centrocanal, $periodo2_centrocanal);
// 		$periodo3_centrocanal = ordena_array_segun_myarray1_x_campo_cpa($periodo1_centrocanal, $periodo3_centrocanal);
// 		$periodomes_centrocanal = ordena_array_segun_myarray1_x_campo_cpa($periodo1_centrocanal, $periodomes_centrocanal);		
		
// 		// array de Centros
// 		$centrocanal_dt = array(
// 							'grupo_centrocanal_dt' => $grupo_centrocanal_dt,
// 							'periodo1_centrocanal' => $periodo1_centrocanal,
// 							'periodo2_centrocanal' => $periodo2_centrocanal,
// 							'periodo3_centrocanal' => $periodo3_centrocanal,
// 							'periodomes_centrocanal' => $periodomes_centrocanal
// 						   );
 
// //		//*************************** FIN DATOS CENTRO-CANAL ************************			

		if($array_detalle_periodo1 == 0 && $array_detalle_periodo1_transfer == 0){
			echo json_encode(0); 
		}else{
			//saca los totales de los jefes en con funcion.php que totaliza el detalle capturado desde mysql
//			$array_totales = saca_totales_jefes($array_jefes_detalle);
			header('Content-Type: application/json');
			echo json_encode(array('array_1er_periodo' => $array_1er_periodo,
								//    'centro' => $centro,
								//    'linea' => $linea,
								//    'producto' => $producto,
								//    'canal' => $canal,
								//    'centrocanal' => $centrocanal,
								//    'centrocanal_dt' => $centrocanal_dt,
								   'array_2do_periodo' => $array_2do_periodo,
								   'array_3er_periodo' => $array_3er_periodo,
								   'array_intermensual' => $array_intermensual

//								   'array_totales_periodo2_importe' => $array_totales_periodo2_importe,
//								   'array_totales_periodo2_unidades' => $array_totales_periodo2_unidades,								   
			));			
		}		
	break;	
	
	




// 	case 'totales_seleccion_fecha_desde_fecha_hasta_cpa':
// 		//proceso de baja y reproceso especial para la parte de produccion con unidades corregidas centro y linea de produccion
// 		$array_detalle_periodo1 = det_vta_seleccion_sin_impuestos('1=1', $_POST['fecha_desde'], $_POST['fecha_hasta']);
// 		$array_totales_periodo1_importe = saca_totales_facturacion_nf($array_detalle_periodo1);
// 		$array_detalle_periodo1_centro_importe = det_vta_seleccion_sin_impuestos_produccion('wcentro', $_POST['fecha_desde'], $_POST['fecha_hasta']);
// 		$array_totales_periodo1_centro_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo1_centro_importe);
// 		$array_detalle_periodo1_linea_importe = det_vta_seleccion_sin_impuestos_produccion('wlinea', $_POST['fecha_desde'], $_POST['fecha_hasta']);
// 		$array_totales_periodo1_linea_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo1_linea_importe);	
// 		$array_detalle_periodo1_producto_importe = det_vta_seleccion_sin_impuestos_produccion('wproducto', $_POST['fecha_desde'], $_POST['fecha_hasta']);
// 		$array_totales_periodo1_producto_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo1_producto_importe);	

// 		$array_detalle_periodo1_centrocanal_importe = det_vta_seleccion_sin_impuestos_produccion_centro_canal($_POST['fecha_desde'], $_POST['fecha_hasta']);
// 		$array_totales_periodo1_centrocanal_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo1_centrocanal_importe);		
// 		$array_totales_periodo1_unidades = $array_totales_periodo1_producto_unidades;

// 		//informacion dia a dia facturado para armar el array_1er_periodo
// 		$data_vta_todo = data_estad_todo_corregidas('1=1', $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha']);
		
// 		//separo los datos para: Grafico de facturacion
// 		//data_para_barra
// 		//data_para_barra_unidades
// 		//data_para_datatable
// 		//data_para_datatable_unidades
// 		for($i=0; $i<count($data_vta_todo); $i++){
// 			$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
// 			$data_para_barra_unidades[$i] = $data_vta_todo[$i]['corregidas'];
// 			$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
// 			$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
// 			$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
// 			$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['corregidas'];
// 		}
		
// 		//arma arrays con datos para armar graficos y datatables del periodo actual y en caso de que tengan las comparativa1 y comparativa2
// 		// array_1er_periodo es el vector de facturacion con los datos para armar el grafico y la tabla detras del grafico
// 		$array_1er_periodo = array('data_para_barra' => $data_para_barra,
// 									'data_para_barra_unidades' => $data_para_barra_unidades,
// 									'data_para_datatable' => $data_para_datatable,
// 									'data_para_datatable_unidades' => $data_para_datatable_unidades,
// 									'array_totales_periodo1_importe' => $array_totales_periodo1_importe,
// 									'array_totales_periodo1_unidades' => $array_totales_periodo1_unidades,
// //								    'data_para_barra_transfer' => $data_para_barra_transfer,
// //									'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
// //									'data_para_datatable_transfer' => $data_para_datatable_transfer,
// //									'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
// //									'array_totales_periodo1_importe_transfer' => $array_totales_periodo1_importe_transfer,
// 									'array_detalle_periodo1' => $array_detalle_periodo1,
// 								    'array_detalle_periodo1_centrocanal_importe' => $array_detalle_periodo1_centrocanal_importe,
// 									'array_totales_periodo1_centrocanal_unidades' => $array_totales_periodo1_centrocanal_unidades,	
// 								    'array_detalle_periodo1_centro_importe' => $array_detalle_periodo1_centro_importe,
// 									'array_totales_periodo1_centro_unidades' => $array_totales_periodo1_centro_unidades,
// 								    'array_detalle_periodo1_linea_importe' => $array_detalle_periodo1_linea_importe,
// 								    'array_totales_periodo1_linea_unidades' => $array_totales_periodo1_linea_unidades,
// 								    'array_detalle_periodo1_producto_importe' => $array_detalle_periodo1_producto_importe,
// 								    'array_totales_periodo1_producto_unidades' => $array_totales_periodo1_producto_unidades,
// 								    'data_vta_todo' => $data_vta_todo
// 								  ); 

// 		//verifica si tiene comparativa1
// 		if($_POST['comparativa1_desde'] != 0){
// 			//consulta el detalle de venta del periodo fecha ingresada
// 			$array_detalle_periodo2 = det_vta_seleccion_sin_impuestos('1=1', $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
// 			$array_totales_periodo2_importe = saca_totales_facturacion_nf($array_detalle_periodo2);
// 			$array_detalle_periodo2_centro_importe = det_vta_seleccion_sin_impuestos_produccion('wcentro', $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
// 			$array_totales_periodo2_centro_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo2_centro_importe);
// 			$array_detalle_periodo2_linea_importe = det_vta_seleccion_sin_impuestos_produccion('wlinea', $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
// 			$array_totales_periodo2_linea_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo2_linea_importe);	
// 			$array_detalle_periodo2_producto_importe = det_vta_seleccion_sin_impuestos_produccion('wproducto', $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
// 			$array_totales_periodo2_producto_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo2_producto_importe);	
// 			$array_detalle_periodo2_centrocanal_importe = det_vta_seleccion_sin_impuestos_produccion_centro_canal($_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
// 			$array_totales_periodo2_centrocanal_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo2_centrocanal_importe);			
// 			$array_totales_periodo2_unidades = $array_totales_periodo2_centro_unidades;	
			
// 			//informacion dia a dia facturado para armar el array_1er_periodo
// 			$data_vta_todo = data_estad_todo_corregidas('1=1', $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha']);
// 			for($i=0; $i<count($data_vta_todo); $i++){
// 				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
// 				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['corregidas'];
// 				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
// 				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
// 				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
// 				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['corregidas'];
// 			}			
			
// 			// array_detalle_periodo2 es el vector de facturacion de la primer comparativa con los datos para armar el grafico y la tabla detras del grafico
// 			$array_2do_periodo = array('data_para_barra' => $data_para_barra,
// 										'data_para_barra_unidades' => $data_para_barra_unidades,
// 										'data_para_datatable' => $data_para_datatable,
// 										'data_para_datatable_unidades' => $data_para_datatable_unidades,
// 										'array_totales_periodo2_importe' => $array_totales_periodo2_importe,
// 										'array_totales_periodo2_unidades' => $array_totales_periodo2_unidades,
// 										'array_detalle_periodo2' => $array_detalle_periodo2,
// 										'array_detalle_periodo2_centrocanal_importe' => $array_detalle_periodo2_centrocanal_importe,
// 										'array_totales_periodo2_centrocanal_unidades' => $array_totales_periodo2_centrocanal_unidades,								   
// 										'array_detalle_periodo2_centro_importe' => $array_detalle_periodo2_centro_importe,
// 										'array_totales_periodo2_centro_unidades' => $array_totales_periodo2_centro_unidades,
// 										'array_detalle_periodo2_linea_importe' => $array_detalle_periodo2_linea_importe,
// 										'array_totales_periodo2_linea_unidades' => $array_totales_periodo2_linea_unidades,
// 										'array_detalle_periodo2_producto_importe' => $array_detalle_periodo2_producto_importe,
// 										'array_totales_periodo2_producto_unidades' => $array_totales_periodo2_producto_unidades,
// 										'data_vta_todo' => $data_vta_todo
// 									  ); 
// 		}else{
// 			$array_2do_periodo = 0; 
// 			$array_detalle_periodo2 = 0;
// 			$array_totales_periodo2_importe = 0;
// 			$array_totales_periodo2_unidades = 0;	
// 		}

// 		//verifica si tiene comparativa2
// 		if($_POST['comparativa2_desde'] != 0){
			
// 			//consulta el detalle de venta del periodo fecha ingresada
// 			$array_detalle_periodo3 = det_vta_seleccion_sin_impuestos('1=1', $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
// 			$array_totales_periodo3_importe = saca_totales_facturacion_nf($array_detalle_periodo3);
// 			$array_detalle_periodo3_centro_importe = det_vta_seleccion_sin_impuestos_produccion('wcentro', $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
// 			$array_totales_periodo3_centro_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo3_centro_importe);
// 			$array_detalle_periodo3_linea_importe = det_vta_seleccion_sin_impuestos_produccion('wlinea', $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
// 			$array_totales_periodo3_linea_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo3_linea_importe);	
// 			$array_detalle_periodo3_producto_importe = det_vta_seleccion_sin_impuestos_produccion('wproducto', $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
// 			$array_totales_periodo3_producto_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo3_producto_importe);	
// 			$array_detalle_periodo3_centrocanal_importe = det_vta_seleccion_sin_impuestos_produccion_centro_canal($_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
// 			$array_totales_periodo3_centrocanal_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_periodo3_centrocanal_importe);			
// 			$array_totales_periodo3_unidades = $array_totales_periodo3_centro_unidades;				
			
// 			//informacion dia a dia facturado para armar el array_1er_periodo
// 			$data_vta_todo = data_estad_todo_corregidas('1=1', $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha']);
// 			for($i=0; $i<count($data_vta_todo); $i++){
// 				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
// 				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['corregidas'];
// 				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
// 				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
// 				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
// 				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['corregidas'];
// 			}			
			
// 			// array_detalle_periodo3 es el vector de facturacion de la segunda comparativa con los datos para armar el grafico y la tabla detras del grafico
// 			$array_3er_periodo = array('data_para_barra' => $data_para_barra,
// 										'data_para_barra_unidades' => $data_para_barra_unidades,
// 										'data_para_datatable' => $data_para_datatable,
// 										'data_para_datatable_unidades' => $data_para_datatable_unidades,
// 										'array_totales_periodo3_importe' => $array_totales_periodo3_importe,
// 										'array_totales_periodo3_unidades' => $array_totales_periodo3_unidades,
// 										'array_detalle_periodo3' => $array_detalle_periodo3,
// 										'array_detalle_periodo3_centrocanal_importe' => $array_detalle_periodo3_centrocanal_importe,
// 										'array_totales_periodo3_centrocanal_unidades' => $array_totales_periodo3_centrocanal_unidades,								   
// 										'array_detalle_periodo3_centro_importe' => $array_detalle_periodo3_centro_importe,
// 										'array_totales_periodo3_centro_unidades' => $array_totales_periodo3_centro_unidades,
// 										'array_detalle_periodo3_linea_importe' => $array_detalle_periodo3_linea_importe,
// 										'array_totales_periodo3_linea_unidades' => $array_totales_periodo3_linea_unidades,
// 										'array_detalle_periodo3_producto_importe' => $array_detalle_periodo3_producto_importe,
// 										'array_totales_periodo3_producto_unidades' => $array_totales_periodo3_producto_unidades,
// 										'data_vta_todo' => $data_vta_todo
// 									  ); 			
// 		}else{
// 			$array_3er_periodo = 0;
// 			$array_detalle_periodo3 = 0;
// 			$array_totales_periodo3_importe = 0;
// 			$array_totales_periodo3_unidades = 0;			
// 		}	
// 		//verifica si tiene comparativa intermensual
// 		if($_POST['comparativames_desde'] != 0){

// 			//consulta el detalle de venta del fecha intermensual ingresada
// 			$array_detalle_intermensual = det_vta_seleccion_sin_impuestos('1=1', $_POST['comparativames_desde'], $_POST['comparativames_hasta']);
// 			$array_totales_intermensual_importe = saca_totales_facturacion_nf($array_detalle_intermensual);
// 			$array_detalle_intermensual_centro_importe = det_vta_seleccion_sin_impuestos_produccion('wcentro', $_POST['comparativames_desde'], $_POST['comparativames_hasta']);
// 			$array_totales_intermensual_centro_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_intermensual_centro_importe);
// 			$array_detalle_intermensual_linea_importe = det_vta_seleccion_sin_impuestos_produccion('wlinea', $_POST['comparativames_desde'], $_POST['comparativames_hasta']);
// 			$array_totales_intermensual_linea_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_intermensual_linea_importe);	
// 			$array_detalle_intermensual_producto_importe = det_vta_seleccion_sin_impuestos_produccion('wproducto', $_POST['comparativames_desde'], $_POST['comparativames_hasta']);
// 			$array_totales_intermensual_producto_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_intermensual_producto_importe);	
// 			$array_detalle_intermensual_centrocanal_importe = det_vta_seleccion_sin_impuestos_produccion_centro_canal($_POST['comparativames_desde'], $_POST['comparativames_hasta']);
// //			$array_detalle_intermensual_centrocanal_importe2 = det_vta_seleccion_sin_impuestos_produccion_centro_canal($_POST['fecha_desde'], $_POST['fecha_hasta']);
// 			$array_totales_intermensual_centrocanal_unidades = saca_totales_facturacion_unidades_corregidas_nf($array_detalle_intermensual_centrocanal_importe);
// 			$array_totales_intermensual_unidades = $array_totales_intermensual_centro_unidades;	
			
// 			//informacion dia a dia facturado para armar el array_intermensual
// 			$data_vta_todo = data_estad_todo_corregidas('1=1', $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha']);
// 			for($i=0; $i<count($data_vta_todo); $i++){
// 				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
// 				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['corregidas'];
// 				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
// 				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
// 				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
// 				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['corregidas'];
// 			}			
// 			$array_intermensual = array('data_para_barra' => $data_para_barra,
// 										'data_para_barra_unidades' => $data_para_barra_unidades,
// 										'data_para_datatable' => $data_para_datatable,
// 										'data_para_datatable_unidades' => $data_para_datatable_unidades,
// 										'array_totales_intermensual_importe' => $array_totales_intermensual_importe,
// 										'array_totales_intermensual_unidades' => $array_totales_intermensual_unidades,
// 										'array_detalle_intermensual' => $array_detalle_intermensual,
// 										'array_detalle_intermensual_centrocanal_importe' => $array_detalle_intermensual_centrocanal_importe,
// 										'array_totales_intermensual_centrocanal_unidades' => $array_totales_intermensual_centrocanal_unidades,								   
// 										'array_detalle_intermensual_centro_importe' => $array_detalle_intermensual_centro_importe,
// 										'array_totales_intermensual_centro_unidades' => $array_totales_intermensual_centro_unidades,
// 										'array_detalle_intermensual_linea_importe' => $array_detalle_intermensual_linea_importe,
// 										'array_totales_intermensual_linea_unidades' => $array_totales_intermensual_linea_unidades,
// 										'array_detalle_intermensual_producto_importe' => $array_detalle_intermensual_producto_importe,
// 										'array_totales_intermensual_producto_unidades' => $array_totales_intermensual_producto_unidades
// 									  ); 			
// 		}else{
// 			$array_intermensual = 0;
// 		}		

// 		//sigo recopilando datos
		

// 		//agrupo nombres de centros de produccion de todos los periodos consultados 		
// 		$grupo_centro = agrupa_nombres2($array_detalle_periodo1_centro_importe, 
// 										$array_detalle_periodo2_centro_importe, 
// 										$array_detalle_periodo3_centro_importe, 
// 										$array_detalle_intermensual_centro_importe, 
// 										$_POST['comparativa1_desde'], 
// 										$_POST['comparativa2_desde'], 
// 										$_POST['comparativames_desde'], 
// 										'wcentro');
		
// 		//agrupo nombres de lineas de produccion de todos los periodos consultados 		
// 		$grupo_linea = agrupa_nombres2($array_detalle_periodo1_linea_importe, 
// 										$array_detalle_periodo2_linea_importe, 
// 										$array_detalle_periodo3_linea_importe, 
// 										$array_detalle_intermensual_linea_importe, 
// 										$_POST['comparativa1_desde'], 
// 										$_POST['comparativa2_desde'], 
// 										$_POST['comparativames_desde'], 
// 										'wlinea');	
		
// 		//agrupo nombres de productos de produccion de todos los periodos consultados 		
// 		$grupo_producto = agrupa_nombres2($array_detalle_periodo1_producto_importe, 
// 										$array_detalle_periodo2_producto_importe, 
// 										$array_detalle_periodo3_producto_importe, 
// 										$array_detalle_intermensual_producto_importe, 
// 										$_POST['comparativa1_desde'], 
// 										$_POST['comparativa2_desde'], 
// 										$_POST['comparativames_desde'], 
// 										'wproducto');	
// 		//agrupo nombres de canal de todos los periodos consultados 		
// 		$grupo_canal = agrupa_nombres2($array_detalle_periodo1_centrocanal_importe, 
// 										$array_detalle_periodo2_centrocanal_importe, 
// 										$array_detalle_periodo3_centrocanal_importe, 
// 										$array_detalle_intermensual_centrocanal_importe, 
// 										$_POST['comparativa1_desde'], 
// 										$_POST['comparativa2_desde'], 
// 										$_POST['comparativames_desde'], 
// 										'gtpvtades');		
// 		//agrupo nombres de centrocanal de todos los periodos consultados 		
// 		$grupo_centrocanal = agrupa_nombres2($array_detalle_periodo1_centrocanal_importe, 
// 										$array_detalle_periodo2_centrocanal_importe, 
// 										$array_detalle_periodo3_centrocanal_importe, 
// 										$array_detalle_intermensual_centrocanal_importe, 
// 										$_POST['comparativa1_desde'], 
// 										$_POST['comparativa2_desde'], 
// 										$_POST['comparativames_desde'], 
// 										'gtpvtades');
		
// 		//agrupo nombres de centrocanal de todos los periodos consultados 		
// 		$grupo_centrocanal_dt = agrupa_nombres2($array_detalle_periodo1_centrocanal_importe, 
// 										$array_detalle_periodo2_centrocanal_importe, 
// 										$array_detalle_periodo3_centrocanal_importe, 
// 										$array_detalle_intermensual_centrocanal_importe, 
// 										$_POST['comparativa1_desde'], 
// 										$_POST['comparativa2_desde'], 
// 										$_POST['comparativames_desde'], 
// 										'wcentro');	
		

// 		//***********************************************************************************************************************************
// 		//***********************************************************************************************************************************
// 		//                                          recopila datos x grupo
// 		//***********************************************************************************************************************************
// 		//***********************************************************************************************************************************
		
// 		//*************************** ARMA DATOS CENTRO PARA GRAFICOS ************************

// 		//arma_datos_canvas es una funcion que hace trabaja desde funciones.php
// 		$array_resultado = arma_datos_canvas($array_detalle_periodo1_centro_importe, 
// 											  $array_detalle_periodo2_centro_importe, 
// 											  $array_detalle_periodo3_centro_importe, 
// 											  $array_detalle_intermensual_centro_importe,
// 											  $_POST['comparativa1_desde'], 
// 											  $_POST['comparativa2_desde'], 
// 											  $_POST['comparativames_desde'], 
// 											  $grupo_centro,
// 											  'wcentro');
		
// 		$periodo1_centro = $array_resultado['periodo1_resultado'];
// 		$periodo2_centro = $array_resultado['periodo2_resultado'];
// 		$periodo3_centro = $array_resultado['periodo3_resultado'];
// 		$periodomes_centro = $array_resultado['periodomes_resultado'];
		
// 		//compara periodo1 con el agrupado 
// 		$periodo1_centro = compara_grupo_periodo1($periodo1_centro, $grupo_centro);
		
// 		//lo ordena
// 		$periodo1_centro = ordena_array_multi_x_campo($periodo1_centro, 'totalimp', 'desc');
		
// 		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
// 		$periodo2_centro = ordena_array_segun_myarray1_x_campo($periodo1_centro, $periodo2_centro);
// 		$periodo3_centro = ordena_array_segun_myarray1_x_campo($periodo1_centro, $periodo3_centro);
// 		$periodomes_centro = ordena_array_segun_myarray1_x_campo($periodo1_centro, $periodomes_centro);

// 		// array de Centros
// 		$centro = array('grupo_centro' => $grupo_centro,
// 							'periodo1_centro' => $periodo1_centro,
// 							'periodo2_centro' => $periodo2_centro,
// 							'periodo3_centro' => $periodo3_centro,
// 							'periodomes_centro' => $periodomes_centro
// 						   );
// 		//*************************** FIN DATOS CENTRO ************************	
		
// 		//*************************** CAPTURA DATOS LINEA ************************
// 		//arma_datos_canvas es una funcion que hace trabaja desde funciones.php
// 		$array_resultado = arma_datos_canvas($array_detalle_periodo1_linea_importe, 
// 											  $array_detalle_periodo2_linea_importe, 
// 											  $array_detalle_periodo3_linea_importe, 
// 											  $array_detalle_intermensual_linea_importe,
// 											  $_POST['comparativa1_desde'], 
// 											  $_POST['comparativa2_desde'], 
// 											  $_POST['comparativames_desde'], 											 
// 											  $grupo_linea,
// 											  'wlinea');
		
// 		$periodo1_linea = $array_resultado['periodo1_resultado'];
// 		$periodo2_linea = $array_resultado['periodo2_resultado'];
// 		$periodo3_linea = $array_resultado['periodo3_resultado'];
// 		$periodomes_linea = $array_resultado['periodomes_resultado'];		

// 		//compara periodo1 con el agrupado 
// 		$periodo1_linea = compara_grupo_periodo1($periodo1_linea, $grupo_linea);

// 		//lo ordena
// 		$periodo1_linea = ordena_array_multi_x_campo($periodo1_linea, 'totalimp', 'desc');

// 		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
// 		$periodo2_linea = ordena_array_segun_myarray1_x_campo($periodo1_linea, $periodo2_linea);
// 		$periodo3_linea = ordena_array_segun_myarray1_x_campo($periodo1_linea, $periodo3_linea);
// 		$periodomes_linea = ordena_array_segun_myarray1_x_campo($periodo1_linea, $periodomes_linea);

// 		// array de Subcanales
// 		$linea = array('grupo_linea' => $grupo_linea,
// 							'periodo1_linea' => $periodo1_linea,
// 							'periodo2_linea' => $periodo2_linea,
// 							'periodo3_linea' => $periodo3_linea,
// 							'periodomes_linea' => $periodomes_linea
// 						   );
// 		//*************************** FIN DATOS LINEA ************************	
		
// 		//*************************** CAPTURA DATOS PRODUCTO ************************
// 		//arma_datos_canvas es una funcion que hace trabaja desde funciones.php
// 		$array_resultado = arma_datos_canvas($array_detalle_periodo1_producto_importe, 
// 											  $array_detalle_periodo2_producto_importe, 
// 											  $array_detalle_periodo3_producto_importe, 
// 											  $array_detalle_intermensual_producto_importe,
// 											  $_POST['comparativa1_desde'], 
// 											  $_POST['comparativa2_desde'], 
// 											  $_POST['comparativames_desde'], 											 
// 											  $grupo_producto,
// 											  'wproducto'); //wproducto agrupa por producto como quiere andres si se quiere modificar por codigo modificar por codigo
		
// 		$periodo1_producto = $array_resultado['periodo1_resultado'];
// 		$periodo2_producto = $array_resultado['periodo2_resultado'];
// 		$periodo3_producto = $array_resultado['periodo3_resultado'];
// 		$periodomes_producto = $array_resultado['periodomes_resultado'];		

// 		//compara periodo1 con el agrupado 
// 		$periodo1_producto = compara_grupo_periodo1($periodo1_producto, $grupo_producto);

// 		//lo ordena
// 		$periodo1_producto = ordena_array_multi_x_campo($periodo1_producto, 'totalimp', 'desc');

// 		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
// 		$periodo2_producto = ordena_array_segun_myarray1_x_campo($periodo1_producto, $periodo2_producto);
// 		$periodo3_producto = ordena_array_segun_myarray1_x_campo($periodo1_producto, $periodo3_producto);
// 		$periodomes_producto = ordena_array_segun_myarray1_x_campo($periodo1_producto, $periodomes_producto);

// 		// array de Subcanales
// 		$producto = array('grupo_producto' => $grupo_producto,
// 							'periodo1_producto' => $periodo1_producto,
// 							'periodo2_producto' => $periodo2_producto,
// 							'periodo3_producto' => $periodo3_producto,
// 							'periodomes_producto' => $periodomes_producto
// 						   );

// 		//*************************** FIN DATOS PRODUCTO ************************	
// 		//*************************** ARMA DATOS CANAL PARA GRAFICOS ************************

// 		//arma_datos_canvas es una funcion que trabaja desde funciones.php
// 		$array_resultado = arma_datos_canvas_totaliza_campo($array_detalle_periodo1_centrocanal_importe, 
// 											  $array_detalle_periodo2_centrocanal_importe, 
// 											  $array_detalle_periodo3_centrocanal_importe, 
// 											  $array_detalle_intermensual_centrocanal_importe,
// 											  $_POST['comparativa1_desde'], 
// 											  $_POST['comparativa2_desde'], 
// 											  $_POST['comparativames_desde'], 
// 											  $grupo_canal,
// 											  'gtpvtades');
		
// 		$periodo1_canal = $array_resultado['periodo1_resultado'];
// 		$periodo2_canal = $array_resultado['periodo2_resultado'];
// 		$periodo3_canal = $array_resultado['periodo3_resultado'];
// 		$periodo3_canala = $array_resultado['periodo3_resultado'];
// 		$periodomes_canal = $array_resultado['periodomes_resultado'];
		
// 		//compara periodo1 con el agrupado 
// 		$periodo1_canal = compara_grupo_periodo1($periodo1_canal, $grupo_canal);
		
// 		//lo ordena
// 		$periodo1_canal = ordena_array_multi_x_campo($periodo1_canal, 'totalimp', 'desc');
		
// 		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
// 		$periodo2_canal = ordena_array_segun_myarray1_x_campo($periodo1_canal, $periodo2_canal);
// 		$periodo3_canal = ordena_array_segun_myarray1_x_campo($periodo1_canal, $periodo3_canal);
// 		$periodomes_canal = ordena_array_segun_myarray1_x_campo($periodo1_canal, $periodomes_canal);

// 		// array de Centros
// 		$canal = array('grupo_canal' => $grupo_canal,
// 							'periodo1_canal' => $periodo1_canal,
// 							'periodo2_canal' => $periodo2_canal,
// 							'periodo3_canal' => $periodo3_canal,
// 							'periodo3_canala' => $periodo3_canala,
// 							'periodomes_canal' => $periodomes_canal
// 						   );
// 		//*************************** FIN DATOS CANAL ************************		
// 		//*************************** FIN DATOS CENTRO ************************			
		
// 		//arma_datos_canvas es una funcion que trabaja desde funciones.php
// 		$array_resultado = arma_datos_canvas($array_detalle_periodo1_centrocanal_importe, 
// 											  $array_detalle_periodo2_centrocanal_importe, 
// 											  $array_detalle_periodo3_centrocanal_importe, 
// 											  $array_detalle_intermensual_centrocanal_importe,
// 											  $_POST['comparativa1_desde'], 
// 											  $_POST['comparativa2_desde'], 
// 											  $_POST['comparativames_desde'], 
// 											  $grupo_centrocanal,
// 											  'wcentro');
// //											  'gtpvtades');
		
// 		$periodo1_centrocanal = $array_resultado['periodo1_resultado'];
// 		$periodo2_centrocanal = $array_resultado['periodo2_resultado'];
// 		$periodo3_centrocanal = $array_resultado['periodo3_resultado'];
// 		$periodomes_centrocanal = $array_resultado['periodomes_resultado'];
		
// 		//compara periodo1 con el agrupado 
// 		$periodo1_centrocanal = compara_grupo_periodo1($periodo1_centrocanal, $grupo_centrocanal);
		
// 		//lo ordena
// 		$periodo1_centrocanal = ordena_array_multi_x_campo($periodo1_centrocanal, 'totalimp', 'desc');
		
// 		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
// 		$periodo2_centrocanal = ordena_array_segun_myarray1_x_campo($periodo1_centrocanal, $periodo2_centrocanal);
// 		$periodo3_centrocanal = ordena_array_segun_myarray1_x_campo($periodo1_centrocanal, $periodo3_centrocanal);
// 		$periodomes_centrocanal = ordena_array_segun_myarray1_x_campo($periodo1_centrocanal, $periodomes_centrocanal);
		
// 		// array de Centros
// 		$centrocanal = array('grupo_centrocanal' => $grupo_centrocanal,
// 							'periodo1_centrocanal' => $periodo1_centrocanal,
// 							'periodo2_centrocanal' => $periodo2_centrocanal,
// 							'periodo3_centrocanal' => $periodo3_centrocanal,
// 							'periodomes_centrocanal' => $periodomes_centrocanal,
// 							'array_resultado' => $array_resultado
// 						   );
// 		//*************************** FIN DATOS CENTRO-CANAL ************************					
// 		//*************************** ARMA DATOS CENTRO-CANAL PARA DATATABLE ************************
		
// 		$periodo1_centrocanal = $array_detalle_periodo1_centrocanal_importe;
// 		$periodo2_centrocanal = $array_detalle_periodo2_centrocanal_importe;
// 		$periodo3_centrocanal = $array_detalle_periodo3_centrocanal_importe;
// 		$periodomes_centrocanal = $array_detalle_intermensual_centrocanal_importe;

// 		//lo ordena
		
// 		//tengo que revisar los 2 periodos al derecho y al revez
// 		//comparando canal/centro de uno contra otro ya que no solo pueden tener diferente cantidad de registros sino que pueden tener
// 		//la misma cantidad de registros pero diferentes entre si
// 		if($_POST['comparativa1_desde'] != 0){
// 			$resultado = normaliza_periodos_cpa($periodo1_centrocanal, $periodo2_centrocanal);
// 			$periodo1_centrocanal = $resultado['array1'];
// 			$periodo2_centrocanal = $resultado['array2'];
// 		}
// 		if($_POST['comparativa2_desde'] != 0){
// 			$resultado = normaliza_periodos_cpa($periodo1_centrocanal, $periodo3_centrocanal);
// 			$periodo1_centrocanal = $resultado['array1'];
// 			$periodo3_centrocanal = $resultado['array2'];
// 			$resultado = normaliza_periodos_cpa($periodo2_centrocanal, $periodo3_centrocanal);
// 			$periodo2_centrocanal = $resultado['array1'];
// 			$periodo3_centrocanal = $resultado['array2'];			
// 		}
// 		if($_POST['comparativames_desde'] != 0){
// 			$resultado = normaliza_periodos_cpa($periodo1_centrocanal, $periodomes_centrocanal);
// 			$periodo1_centrocanal = $resultado['array1'];
// 			$periodomes_centrocanal = $resultado['array2'];
// 		}
// 		//reordeno ahora que los tengo bien completitos
		
// 		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
// 		$periodo1_centrocanal = ordena_array_multi_x_campo($periodo1_centrocanal, 'aximpo', 'desc');
// 		$periodo2_centrocanal = ordena_array_segun_myarray1_x_campo_cpa($periodo1_centrocanal, $periodo2_centrocanal);
// 		$periodo3_centrocanal = ordena_array_segun_myarray1_x_campo_cpa($periodo1_centrocanal, $periodo3_centrocanal);
// 		$periodomes_centrocanal = ordena_array_segun_myarray1_x_campo_cpa($periodo1_centrocanal, $periodomes_centrocanal);		
		
// 		// array de Centros
// 		$centrocanal_dt = array(
// 							'grupo_centrocanal_dt' => $grupo_centrocanal_dt,
// 							'periodo1_centrocanal' => $periodo1_centrocanal,
// 							'periodo2_centrocanal' => $periodo2_centrocanal,
// 							'periodo3_centrocanal' => $periodo3_centrocanal,
// 							'periodomes_centrocanal' => $periodomes_centrocanal
// 						   );
 
// //		//*************************** FIN DATOS CENTRO-CANAL ************************			

// 		if($array_detalle_periodo1 == 0 && $array_detalle_periodo1_transfer == 0){
// 			echo json_encode(0); 
// 		}else{
// 			//saca los totales de los jefes en con funcion.php que totaliza el detalle capturado desde mysql
// //			$array_totales = saca_totales_jefes($array_jefes_detalle);
// 			header('Content-Type: application/json');
// 			echo json_encode(array('array_1er_periodo' => $array_1er_periodo,
// 								   'centro' => $centro,
// 								   'linea' => $linea,
// 								   'producto' => $producto,
// 								   'canal' => $canal,
// 								   'centrocanal' => $centrocanal,
// 								   'centrocanal_dt' => $centrocanal_dt,
// 								   'array_2do_periodo' => $array_2do_periodo,
// //								   'array_totales_periodo2_importe' => $array_totales_periodo2_importe,
// //								   'array_totales_periodo2_unidades' => $array_totales_periodo2_unidades,								   
// 								   'array_3er_periodo' => $array_3er_periodo,
// 								   'array_intermensual' => $array_intermensual));			
// 		}		
// 	break;	















	case 'totales_seleccion_fecha_desde_fecha_hasta22':	

		//consulta los totales del periodo ingresado
		$array_detalle_periodo1 = det_vta_seleccion_sin_impuestos($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		if($array_detalle_periodo1 != 0){	
			$array_totales_periodo1_importe[0]['total'] = intval($array_detalle_periodo1[0]['TotalAximpo']);
			$array_totales_periodo1_unidades[0]['total'] =  intval($array_detalle_periodo1[0]['TotalAxunid'])+intval($array_detalle_periodo1[0]['TotalAxunib']);
			$array_totales_periodo1_unidades[0]['totalv'] =  intval($array_detalle_periodo1[0]['TotalAxunid']);
			$array_totales_periodo1_unidades[0]['totalb'] =  intval($array_detalle_periodo1[0]['TotalAxunib']);

			// $array_totales_periodo1_importe = saca_totales_facturacion_nf($array_detalle_periodo1);
			// $array_totales_periodo1_unidades = saca_totales_facturacion_unidadesvyb_nf($array_detalle_periodo1);
		}
		
		//consulta los totales de pedidos transfer del periodo fecha ingresada
		$array_detalle_periodo1_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		if($array_detalle_periodo1_transfer != 0){
			$array_totales_periodo1_importe_transfer[0]['total'] = intval($array_detalle_periodo1_transfer[0]['TotalAximpo']);
			$array_totales_periodo1_unidades_transfer[0]['total'] = intval($array_detalle_periodo1_transfer[0]['TotalAxunid'])+intval($array_detalle_periodo1_transfer[0]['TotalAxunib']);
			$array_totales_periodo1_unidades_transfer[0]['totalv'] = intval($array_detalle_periodo1_transfer[0]['TotalAxunid']);
			$array_totales_periodo1_unidades_transfer[0]['totalb'] = intval($array_detalle_periodo1_transfer[0]['TotalAxunib']);
		}

		// //consulta el detalle de pedidos transfer del periodo fecha ingresada
		// $array_detalle_periodo1_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		// $array_totales_periodo1_importe_transfer = saca_totales_transfer_nf($array_detalle_periodo1_transfer);
		// $array_totales_periodo1_unidades_transfer = saca_totales_transfer_unidadesvyb_nf($array_detalle_periodo1_transfer);		


		//informacion dia a dia facturado para armar el array_1er_periodo
		$data_vta_todo = data_estad_todo($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha']);
		//separo los datos para:
		//data_para_barra
		//data_para_barra_unidades
		//data_para_datatable
		//data_para_datatable_unidades
		if($data_vta_todo != 0){
			for($i=0; $i<count($data_vta_todo); $i++){
				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['axunid'];
				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['axunid'];
			}
		}
		
		//informacion dia a dia transfer para armar el array_1er_periodo
		$data_trf_todo = data_transfer_todo($_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha']);
		if($data_trf_todo != 0){
			for($i=0; $i<count($data_trf_todo); $i++){
				$data_para_barra_transfer[$i] = $data_trf_todo[$i]['aximpo'];
				$data_para_barra_unidades_transfer[$i] = $data_trf_todo[$i]['axunid'];
				$data_para_datatable_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_transfer[$i]['totalx'] = $data_trf_todo[$i]['aximpo'];
				$data_para_datatable_unidades_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_unidades_transfer[$i]['totalx'] = $data_trf_todo[$i]['axunid'];
			}
		}
		
		//arma arrays con datos para armar graficos y datatables del periodo actual y en caso de que tengan las comparativa1 y comparativa2
		// array_1er_periodo es el vector de facturacion con los datos para armar el grafico y la tabla detras del grafico
		$array_1er_periodo = array('data_para_barra' => $data_para_barra,
									'data_para_barra_unidades' => $data_para_barra_unidades,
									'data_para_datatable' => $data_para_datatable,
									'data_para_datatable_unidades' => $data_para_datatable_unidades,
									'array_totales_periodo1_importe' => $array_totales_periodo1_importe,
									'array_totales_periodo1_unidades' => $array_totales_periodo1_unidades,
								    'data_para_barra_transfer' => $data_para_barra_transfer,
									'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
									'data_para_datatable_transfer' => $data_para_datatable_transfer,
									'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
									'array_totales_periodo1_importe_transfer' => $array_totales_periodo1_importe_transfer,
									'array_detalle_periodo1' => $array_detalle_periodo1,
									'array_totales_periodo1_unidades_transfer' => $array_totales_periodo1_unidades_transfer); 

		//verifica si tiene comparativa1
		if($_POST['comparativa1_desde'] != 0){

			//consulta el total de venta del periodo fecha ingresada
			$array_detalle_periodo2 = det_vta_seleccion_sin_impuestos($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
			if($array_detalle_periodo2 != 0){	
				$array_totales_periodo2_importe[0]['total'] = intval($array_detalle_periodo2[0]['TotalAximpo']);
				$array_totales_periodo2_unidades[0]['total'] =  intval($array_detalle_periodo2[0]['TotalAxunid'])+intval($array_detalle_periodo2[0]['TotalAxunib']);
				$array_totales_periodo2_unidades[0]['totalv'] =  intval($array_detalle_periodo2[0]['TotalAxunid']);
				$array_totales_periodo2_unidades[0]['totalb'] =  intval($array_detalle_periodo2[0]['TotalAxunib']);
			}
			
			//consulta el detalle de pedidos transfer del periodo fecha ingresada
			$array_detalle_periodo2_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
			if($array_detalle_periodo2_transfer != 0){
				$array_totales_periodo2_importe_transfer[0]['total'] = intval($array_detalle_periodo2_transfer[0]['TotalAximpo']);
				$array_totales_periodo2_unidades_transfer[0]['total'] = intval($array_detalle_periodo2_transfer[0]['TotalAxunid'])+intval($array_detalle_periodo2_transfer[0]['TotalAxunib']);
				$array_totales_periodo2_unidades_transfer[0]['totalv'] = intval($array_detalle_periodo2_transfer[0]['TotalAxunid']);
				$array_totales_periodo2_unidades_transfer[0]['totalb'] = intval($array_detalle_periodo2_transfer[0]['TotalAxunib']);
			}
		
			//informacion dia a dia facturado para armar el array_2do_periodo
			$data_vta_todo = data_estad_todo($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_vta_todo); $i++){
				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['axunid'];
				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['axunid'];
			}

			//informacion dia a dia transfer para armar el array_2do_periodo
			$data_trf_todo = data_transfer_todo($_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_trf_todo); $i++){
				$data_para_barra_transfer[$i] = $data_trf_todo[$i]['aximpo'];
				$data_para_barra_unidades_transfer[$i] = $data_trf_todo[$i]['axunid'];
				$data_para_datatable_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_transfer[$i]['totalx'] = $data_trf_todo[$i]['aximpo'];
				$data_para_datatable_unidades_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_unidades_transfer[$i]['totalx'] = $data_trf_todo[$i]['axunid'];
			}
						
			// array_detalle_periodo2 es el vector de facturacion de la primer comparativa con los datos para armar el grafico y la tabla detras del grafico
			$array_2do_periodo = array('data_para_barra' => $data_para_barra,
											'data_para_barra_unidades' => $data_para_barra_unidades,
											'data_para_datatable' => $data_para_datatable,
											'data_para_datatable_unidades' => $data_para_datatable_unidades,
											'array_totales_periodo2_importe' => $array_totales_periodo2_importe,
											'array_totales_periodo2_unidades' => $array_totales_periodo2_unidades,
									   		'data_para_barra_transfer' => $data_para_barra_transfer,
											'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
											'data_para_datatable_transfer' => $data_para_datatable_transfer,
											'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
											'array_totales_periodo2_importe_transfer' => $array_totales_periodo2_importe_transfer,
											'array_totales_periodo2_unidades_transfer' => $array_totales_periodo2_unidades_transfer);
		}else{
			$array_2do_periodo = 0;
			$array_detalle_periodo2 = 0;
			$array_totales_periodo2_importe = 0;
			$array_totales_periodo2_unidades = 0;
		}

		//verifica si tiene comparativa2
		if($_POST['comparativa2_desde'] != 0){

			//consulta los totales del periodo ingresado
			$array_detalle_periodo3 = det_vta_seleccion_sin_impuestos($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
			if($array_detalle_periodo3 != 0){	
				$array_totales_periodo3_importe[0]['total'] = intval($array_detalle_periodo3[0]['TotalAximpo']);
				$array_totales_periodo3_unidades[0]['total'] =  intval($array_detalle_periodo3[0]['TotalAxunid'])+intval($array_detalle_periodo3[0]['TotalAxunib']);
				$array_totales_periodo3_unidades[0]['totalv'] =  intval($array_detalle_periodo3[0]['TotalAxunid']);
				$array_totales_periodo3_unidades[0]['totalb'] =  intval($array_detalle_periodo3[0]['TotalAxunib']);
			}
			
			//consulta los totales de pedidos transfer del periodo fecha ingresada
			$array_detalle_periodo3_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
			if($array_detalle_periodo3_transfer != 0){
				$array_totales_periodo3_importe_transfer[0]['total'] = intval($array_detalle_periodo3_transfer[0]['TotalAximpo']);
				$array_totales_periodo3_unidades_transfer[0]['total'] = intval($array_detalle_periodo3_transfer[0]['TotalAxunid'])+intval($array_detalle_periodo3_transfer[0]['TotalAxunib']);
				$array_totales_periodo3_unidades_transfer[0]['totalv'] = intval($array_detalle_periodo3_transfer[0]['TotalAxunid']);
				$array_totales_periodo3_unidades_transfer[0]['totalb'] = intval($array_detalle_periodo3_transfer[0]['TotalAxunib']);
			}			
			
			//informacion dia a dia facturado para armar el array_3er_periodo
			$data_vta_todo = data_estad_todo($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_vta_todo); $i++){
				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['axunid'];
				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['axunid'];
			}			
			
			//informacion dia a dia transfer para armar el array_2do_periodo
			$data_trf_todo = data_transfer_todo($_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_trf_todo); $i++){
				$data_para_barra_transfer[$i] = $data_trf_todo[$i]['aximpo'];
				$data_para_barra_unidades_transfer[$i] = $data_trf_todo[$i]['axunid'];
				$data_para_datatable_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_transfer[$i]['totalx'] = $data_trf_todo[$i]['aximpo'];
				$data_para_datatable_unidades_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_unidades_transfer[$i]['totalx'] = $data_trf_todo[$i]['axunid'];
			}	
				
			// array_detalle_periodo3 es el vector de facturacion de la segunda comparativa con los datos para armar el grafico y la tabla detras del grafico
			$array_3er_periodo = array('data_para_barra' => $data_para_barra,
											'data_para_barra_unidades' => $data_para_barra_unidades,
											'data_para_datatable' => $data_para_datatable,
											'data_para_datatable_unidades' => $data_para_datatable_unidades,
											'array_totales_periodo3_importe' => $array_totales_periodo3_importe,
											'array_totales_periodo3_unidades' => $array_totales_periodo3_unidades,
									   		'data_para_barra_transfer' => $data_para_barra_transfer,
											'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
											'data_para_datatable_transfer' => $data_para_datatable_transfer,
											'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
											'array_totales_periodo3_importe_transfer' => $array_totales_periodo3_importe_transfer,
											'array_totales_periodo3_unidades_transfer' => $array_totales_periodo3_unidades_transfer);									   
		}else{
			$array_3er_periodo = 0;
			$array_detalle_periodo3 = 0;
			$array_totales_periodo3_importe = 0;
			$array_totales_periodo3_unidades = 0;
		}	
		//verifica si tiene comparativa intermensual
		if($_POST['comparativames_desde'] != 0){

			//consulta el total de venta del fecha intermensual ingresada
			$array_detalle_intermensual = det_vta_seleccion_sin_impuestos($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta']);
			if($array_detalle_intermensual != 0){	
				$array_totales_intermensual_importe[0]['total'] = intval($array_detalle_intermensual[0]['TotalAximpo']);
				$array_totales_intermensual_unidades[0]['total'] =  intval($array_detalle_intermensual[0]['TotalAxunid'])+intval($array_detalle_intermensual[0]['TotalAxunib']);
				$array_totales_intermensual_unidades[0]['totalv'] =  intval($array_detalle_intermensual[0]['TotalAxunid']);
				$array_totales_intermensual_unidades[0]['totalb'] =  intval($array_detalle_intermensual[0]['TotalAxunib']);

			}	

			//consulta el total de venta transfer del fecha intermensual ingresada		
			$array_detalle_intermensual_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta']);
			if($array_detalle_intermensual_transfer != 0){
				$array_totales_intermensual_importe_transfer[0]['total'] = intval($array_detalle_intermensual_transfer[0]['TotalAximpo']);
				$array_totales_intermensual_unidades_transfer[0]['total'] = intval($array_detalle_intermensual_transfer[0]['TotalAxunid'])+intval($array_detalle_intermensual_transfer[0]['TotalAxunib']);
				$array_totales_intermensual_unidades_transfer[0]['totalv'] = intval($array_detalle_intermensual_transfer[0]['TotalAxunid']);
				$array_totales_intermensual_unidades_transfer[0]['totalb'] = intval($array_detalle_intermensual_transfer[0]['TotalAxunib']);
			}
			
			//informacion dia a dia facturado para armar el array_intermensual
			$data_vta_todo = data_estad_todo($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_vta_todo); $i++){
				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['axunid'];
				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['axunid'];
			}
			
			//informacion dia a dia transfer para armar el array_intermensual
			$data_trf_todo = data_transfer_todo($_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_trf_todo); $i++){
				$data_para_barra_transfer[$i] = $data_trf_todo[$i]['aximpo'];
				$data_para_barra_unidades_transfer[$i] = $data_trf_todo[$i]['axunid'];
				$data_para_datatable_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_transfer[$i]['totalx'] = $data_trf_todo[$i]['aximpo'];
				$data_para_datatable_unidades_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_unidades_transfer[$i]['totalx'] = $data_trf_todo[$i]['axunid'];
			}
			
			$array_intermensual = array('data_para_barra' => $data_para_barra,
											'data_para_barra_unidades' => $data_para_barra_unidades,
											'data_para_datatable' => $data_para_datatable,
											'data_para_datatable_unidades' => $data_para_datatable_unidades,
											'array_totales_intermensual_importe' => $array_totales_intermensual_importe,
											'array_totales_intermensual_unidades' => $array_totales_intermensual_unidades,
									   		'data_para_barra_transfer' => $data_para_barra_transfer,
											'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
											'data_para_datatable_transfer' => $data_para_datatable_transfer,
											'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
											'array_totales_intermensual_importe_transfer' => $array_totales_intermensual_importe_transfer,
//											'array_detalle_intermensual' => $array_detalle_intermensual,
											'array_totales_intermensual_unidades_transfer' => $array_totales_intermensual_unidades_transfer);
		}else{
			$array_intermensual = 0;
		}		
		
		//sigo recopilando datos
		
		//agrupo nombres de subcanales canales jefes vendedores productos clientes de los tres periodos consultados en caso de que tengan datos 
		
		$grupo_subcanales_nf = agrupa_nombre_nf($_POST['fecha_desde'], 
											$_POST['fecha_hasta'],
											$_POST['comparativa1_desde'],
											$_POST['comparativa1_hasta'],
											$_POST['comparativa2_desde'],
											$_POST['comparativa2_hasta'],
											$_POST['comparativames_desde'],
											$_POST['comparativames_hasta'],
											'TpoVtaDsc',
											'TpoVtaDsc',
											$_POST['mywhere'],
											$_POST['mywhere_transfer'] 
										);
		for($i=0; $i<count($grupo_subcanales_nf); $i++){
			$grupo_subcanales[$i] = rtrim($grupo_subcanales_nf[$i]['TpoVtaDsc']); //quito espacios
		}		
		 
		
		//en subcanales agrego el subcanal TRANSFER de forma manual
		array_push($grupo_subcanales, "TRANSFER");

		$grupo_canales_nf = agrupa_nombre_nf($_POST['fecha_desde'], 
											$_POST['fecha_hasta'],
											$_POST['comparativa1_desde'],
											$_POST['comparativa1_hasta'],
											$_POST['comparativa2_desde'],
											$_POST['comparativa2_hasta'],
											$_POST['comparativames_desde'],
											$_POST['comparativames_hasta'],
											'GTpVtaDes',
											'GTpVtaDes',
											$_POST['mywhere'],
											$_POST['mywhere_transfer'] 
										);
		for($i=0; $i<count($grupo_canales_nf); $i++){
			$grupo_canales[$i] = rtrim($grupo_canales_nf[$i]['GTpVtaDes']); //quito espacios
		}

		// $grupo_canales = agrupa_nombres2($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $array_detalle_intermensual, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], $_POST['comparativames_desde'], 'GTpVtaDes');


		//		$grupo_jefes = agrupa_nombres2($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $array_detalle_intermensual, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], $_POST['comparativames_desde'], 'JefNom');
		// $grupo_jefes = agrupa_nombres21($array_detalle_periodo1, 
		// 									$array_detalle_periodo2, 
		// 									$array_detalle_periodo3, 
		// 									$array_detalle_intermensual, 
		// 									$array_detalle_periodo1_transfer, 
		// 									$array_detalle_periodo2_transfer, 
		// 									$array_detalle_periodo3_transfer, 
		// 									$array_detalle_intermensual_transfer, 											
		// 									$_POST['comparativa1_desde'], 
		// 									$_POST['comparativa2_desde'], 
		// 									$_POST['comparativames_desde'], 
		// 									'JefNom',
		// 									'JefNom');

		//tendria que verificar si selecciono el periodo 1 o el 2 o el 3 o intermes para juntar todo en una vista y agruparlo
		
		$grupo_jefes_nf = agrupa_nombre_nf($_POST['fecha_desde'], 
											$_POST['fecha_hasta'],
											$_POST['comparativa1_desde'],
											$_POST['comparativa1_hasta'],
											$_POST['comparativa2_desde'],
											$_POST['comparativa2_hasta'],
											$_POST['comparativames_desde'],
											$_POST['comparativames_hasta'],
											'JefNom',
											'JefNom',
											$_POST['mywhere'],
											$_POST['mywhere_transfer'] 
										);
		for($i=0; $i<count($grupo_jefes_nf); $i++){
			$grupo_jefes[$i] = rtrim($grupo_jefes_nf[$i]['JefNom']); //quito espacios
		}

		$grupo_vendedores_nf = agrupa_nombre_nf($_POST['fecha_desde'], 
											$_POST['fecha_hasta'],
											$_POST['comparativa1_desde'],
											$_POST['comparativa1_hasta'],
											$_POST['comparativa2_desde'],
											$_POST['comparativa2_hasta'],
											$_POST['comparativames_desde'],
											$_POST['comparativames_hasta'],
											'VdorTxt',
											'VdorTxt',
											$_POST['mywhere'],
											$_POST['mywhere_transfer']  
										);
		for($i=0; $i<count($grupo_vendedores_nf); $i++){
			$grupo_vendedores[$i] = rtrim($grupo_vendedores_nf[$i]['VdorTxt']); //quito espacios
		}		

		// $grupo_vendedores = agrupa_nombres21($array_detalle_periodo1, 
		// 									$array_detalle_periodo2, 
		// 									$array_detalle_periodo3, 
		// 									$array_detalle_intermensual, 
		// 									$array_detalle_periodo1_transfer, 
		// 									$array_detalle_periodo2_transfer, 
		// 									$array_detalle_periodo3_transfer, 
		// 									$array_detalle_intermensual_transfer, 											
		// 									$_POST['comparativa1_desde'], 
		// 									$_POST['comparativa2_desde'], 
		// 									$_POST['comparativames_desde'], 
		// 									'VdorTxt',
		// 									'VdorTxt');

		$grupo_productos_nf = agrupa_nombre_nf($_POST['fecha_desde'], 
											$_POST['fecha_hasta'],
											$_POST['comparativa1_desde'],
											$_POST['comparativa1_hasta'],
											$_POST['comparativa2_desde'],
											$_POST['comparativa2_hasta'],
											$_POST['comparativames_desde'],
											$_POST['comparativames_hasta'],
											'PrdTxt',
											'PrdTxt',
											$_POST['mywhere'],
											$_POST['mywhere_transfer']  
										);
		for($i=0; $i<count($grupo_productos_nf); $i++){
			$grupo_productos[$i] = rtrim($grupo_productos_nf[$i]['PrdTxt']); //quito espacios
		}

		// $grupo_productos = agrupa_nombres21($array_detalle_periodo1, 
		// 									$array_detalle_periodo2, 
		// 									$array_detalle_periodo3, 
		// 									$array_detalle_intermensual, 
		// 									$array_detalle_periodo1_transfer, 
		// 									$array_detalle_periodo2_transfer, 
		// 									$array_detalle_periodo3_transfer, 
		// 									$array_detalle_intermensual_transfer, 		 									
		// 									$_POST['comparativa1_desde'], 
		// 									$_POST['comparativa2_desde'], 
		// 									$_POST['comparativames_desde'], 
		// 									'PrdTxt',
		// 									'PrdTxt');		

		$grupo_clientes_nf = agrupa_nombre_nf($_POST['fecha_desde'], 
											$_POST['fecha_hasta'],
											$_POST['comparativa1_desde'],
											$_POST['comparativa1_hasta'],
											$_POST['comparativa2_desde'],
											$_POST['comparativa2_hasta'],
											$_POST['comparativames_desde'],
											$_POST['comparativames_hasta'],
											'CliNomRed',
											'CliNomRed',
											$_POST['mywhere'],
											$_POST['mywhere_transfer']  
										);
		for($i=0; $i<count($grupo_clientes_nf); $i++){
			$grupo_clientes[$i] = rtrim($grupo_clientes_nf[$i]['CliNomRed']); //quito espacios
		}		

		// $grupo_clientes = agrupa_nombres21($array_detalle_periodo1, 
		// 									$array_detalle_periodo2, 
		// 									$array_detalle_periodo3, 
		// 									$array_detalle_intermensual, 
		// 									$array_detalle_periodo1_transfer, 
		// 									$array_detalle_periodo2_transfer, 
		// 									$array_detalle_periodo3_transfer, 
		// 									$array_detalle_intermensual_transfer, 											
		// 									$_POST['comparativa1_desde'], 
		// 									$_POST['comparativa2_desde'], 
		// 									$_POST['comparativames_desde'], 
		// 									'CliNomRed',
		// 									'CliNomRed');		
		
		
		//***********************************************************************************************************************************
		//***********************************************************************************************************************************
		//                                      calcula los totales x grupo
		//***********************************************************************************************************************************
		//***********************************************************************************************************************************		

		//*************************** DATOS CANALES ************************
		//captura CANALES del periodo
		//if que verifica el grupo... pasa con clientes por ejemplo que solo tienen ventas x transfer entonces el grupo_canales en este caso no cuenta con el nombre del CANAL
		//entonces al verificar que no tiene nombre de CANAL definitivamente solo tiene venta por Transfer entonces obligo a la consulta a ir a ventas transfer en todas las demas consultas
		//jefes, vendedores, productos, clientes para evitar los graficos en blancos aunque 
		//si salen en blanco luego de pulsar el boton TR los datos salen... pero evitaremos ese paso cuando el cliente solo compro transfers ;)






		if(count($grupo_canales) < 1){
		//			$myw = substr($_POST['mywhere_transfer'], 0, 15);  // abcd $_POST['mywhere_transfer'] = 'PedTpoVtaC = 99';
			$_POST['mywhere_transfer'] = 'PedTpoVtaC = 99 AND '.$_POST['mywhere_transfer'];
		}
		if(substr($_POST['mywhere_transfer'], 0, 15) == 'PedTpoVtaC = 99'){
			array_push($grupo_canales, "ORIGINALES");

			// Recopilo CANALES datos para canvas
			$periodo1_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');
			
			//compara periodo1 con el agrupado 
			$periodo1_canales = compara_grupo_periodo1($periodo1_canales, $grupo_canales);

			//lo ordena
			$periodo1_canales = ordena_array_multi_x_campo($periodo1_canales, 'totalimp_tr', 'desc');			
			
			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1 solo si tiene comparativa
			if($_POST['comparativa1_desde'] != 0){ 
				$periodo2_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');
				$periodo2_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodo2_canales);
			}else{
				$periodo2_canales = 0;
			}
			if($_POST['comparativa2_desde'] != 0){
				$periodo3_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');
				$periodo3_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodo3_canales);
			}else{
				$periodo3_canales = 0;
			}
			if($_POST['comparativames_desde'] != 0){
				$periodomes_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');			
				$periodomes_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodomes_canales);
			}else{
				$periodomes_canales = 0;
			}

			// array de Subcanales
			$canales = array('grupo_canales' => $grupo_canales,
								'periodo1_canales' => $periodo1_canales,
								'periodo2_canales' => $periodo2_canales,
								'periodo3_canales' => $periodo3_canales,
								'periodomes_canales' => $periodomes_canales,
								'ped99' => 'Trasfer'
							);		
		}else{	
			// Recopilo CANALES datos para canvas
			$periodo1_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');
			// $periodo1_canales_A = $periodo1_canales;
			//compara periodo1 con el agrupado 
			$periodo1_canales = compara_grupo_periodo1($periodo1_canales, $grupo_canales);
			// $periodo1_canales_B = $periodo1_canales; // aca se rompia por que habia un nombre de campo/canal "" vacio lo filtro en funciones
			//lo ordena
			$periodo1_canales = ordena_array_multi_x_campo($periodo1_canales, 'totalimp', 'desc');
			// $periodo1_canales_C = $periodo1_canales;
			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1 solo si tiene comparativa
			if($_POST['comparativa1_desde'] != 0){
				$periodo2_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');
				$periodo2_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodo2_canales);
			}else{
				$periodo2_canales = 0;
			}
			if($_POST['comparativa2_desde'] != 0){
				$periodo3_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');
				$periodo3_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodo3_canales);
			}else{
				$periodo3_canales = 0;
			}
			if($_POST['comparativames_desde'] != 0){
				$periodomes_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');			
				$periodomes_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodomes_canales);
			}else{
				$periodomes_canales = 0;
			}			

			// array de Subcanales
			$canales = array('grupo_canales' => $grupo_canales,
								'periodo1_canales' => $periodo1_canales,
								'periodo2_canales' => $periodo2_canales,
								'periodo3_canales' => $periodo3_canales,
								'periodomes_canales' => $periodomes_canales
								// 'est_grupo_canales' => $_POST['mywhere_transfer'],
								// 'periodo1_canales_A' => $periodo1_canales_A,
								// 'periodo1_canales_B' => $periodo1_canales_B,
								// 'periodo1_canales_C' => $periodo1_canales_C
							);		
		}

		//*************************** FIN DATOS CANALES ************************

		//*************************** DATOS SUBCANALES ************************

		//captura SUBCANALES del periodo
		$periodo1_subcanales = datos_canvas_datatable_directa_transfer($_POST['mywhere'],  $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'TpoVtaDsc', 'data_para_barra', 'factpovtac');

		//compara periodo1 con el agrupado 
		$periodo1_subcanales = compara_grupo_periodo1($periodo1_subcanales, $grupo_subcanales);			

		//lo ordena
		$periodo1_subcanales = ordena_array_multi_x_campo($periodo1_subcanales, 'totalimp', 'desc');

		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1 solo si tiene comparativa
		if($_POST['comparativa1_desde'] != 0){
			$periodo2_subcanales = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'TpoVtaDsc', 'data_para_barra', 'factpovtac');
			$periodo2_subcanales = ordena_array_segun_myarray1_x_campo($periodo1_subcanales, $periodo2_subcanales);
		}else{
			$periodo2_subcanales = 0;
		}
		if($_POST['comparativa2_desde'] != 0){
			$periodo3_subcanales = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'TpoVtaDsc', 'data_para_barra', 'factpovtac');
			$periodo3_subcanales = ordena_array_segun_myarray1_x_campo($periodo1_subcanales, $periodo3_subcanales);
		}else{
			$periodo3_subcanales = 0;
		}
		if($_POST['comparativames_desde'] != 0){
			$periodomes_subcanales = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'TpoVtaDsc', 'data_para_barra', 'factpovtac');
			$periodomes_subcanales = ordena_array_segun_myarray1_x_campo($periodo1_subcanales, $periodomes_subcanales);
		}else{
			$periodomes_subcanales = 0;
		}

		// array de Subcanales
		$subcanales = array('grupo_subcanales' => $grupo_subcanales,
							'periodo1_subcanales' => $periodo1_subcanales,
							'periodo2_subcanales' => $periodo2_subcanales,
							'periodo3_subcanales' => $periodo3_subcanales,
							'periodomes_subcanales' => $periodomes_subcanales);

		//*************************** FIN DATOS SUBCANALES ************************

		//*************************** DATOS JEFES ************************

		//captura JEFES del periodo
		if(substr($_POST['mywhere_transfer'], 0, 15) == 'PedTpoVtaC = 99'){
			
			//recopila transfer JEFES
			$periodo1_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');
			
			//compara periodo1 con el agrupado 
			$periodo1_jefes = compara_grupo_periodo1($periodo1_jefes, $grupo_jefes);			
			
			//lo ordena
			$periodo1_jefes = ordena_array_multi_x_campo($periodo1_jefes, 'totalimp_tr', 'desc');					

			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1 solo si tiene comparativa
			if($_POST['comparativa1_desde'] != 0){
				$periodo2_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');
				$periodo2_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo2_jefes);
			}else{
				$periodo2_jefes = 0;
			}
			if($_POST['comparativa2_desde'] != 0){
				$periodo3_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');
				$periodo3_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo3_jefes);
			}else{
				$periodo3_jefes = 0;
			}
			if($_POST['comparativames_desde'] != 0){
				$periodomes_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');				
				$periodomes_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodomes_jefes);
			}else{
				$periodomes_jefes = 0;
			}			

			// array de Jefes
			$jefes = array('grupo_jefes' => $grupo_jefes,
								'periodo1_jefes' => $periodo1_jefes,
								'periodo2_jefes' => $periodo2_jefes,
								'periodo3_jefes' => $periodo3_jefes,
								'periodomes_jefes' => $periodomes_jefes, 
									);	
			
			
		}else{	
			//recopila datos JEFES
			$periodo1_jefes = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'JefNom', 'data_para_barra', 'tpovtajefe');

			//compara periodo1 con el agrupado 
			$periodo1_jefes = compara_grupo_periodo1($periodo1_jefes, $grupo_jefes);

			//lo ordena
			$periodo1_jefes = ordena_array_multi_x_campo($periodo1_jefes, 'totalimp', 'desc');		

			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1 solo si tiene comparativa
			if($_POST['comparativa1_desde'] != 0){
				$periodo2_jefes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'JefNom', 'data_para_barra', 'tpovtajefe');
				$periodo2_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo2_jefes);
			}else{
				$periodo2_jefes = 0;
			}
			if($_POST['comparativa2_desde'] != 0){
				$periodo3_jefes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'JefNom', 'data_para_barra', 'tpovtajefe');
				$periodo3_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo3_jefes);
			}else{
				$periodo3_jefes = 0;
			}
			if($_POST['comparativames_desde'] != 0){
				$periodomes_jefes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'JefNom', 'data_para_barra', 'tpovtajefe');		
				$periodomes_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodomes_jefes);
			}else{
				$periodomes_jefes = 0;
			}				

			// array de Jefes
			$jefes = array('grupo_jefes' => $grupo_jefes,
								'grupo_jefes_nf' => $grupo_jefes_nf,
								'preuba' => $prueba,
								'periodo1_jefes' => $periodo1_jefes,
								'periodo2_jefes' => $periodo2_jefes,
								'periodo3_jefes' => $periodo3_jefes,
								'periodomes_jefes' => $periodomes_jefes);
		}
		//*************************** FIN DATOS JEFES **********************	
		
		//*************************** DATOS VENDEDORES ************************
		//captura vendedores del periodo
		if(substr($_POST['mywhere_transfer'], 0, 15) == 'PedTpoVtaC = 99'){
			
			//recopila VENDEDORES transfer 
			$periodo1_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_importe', 'facvdorid');
			
			//compara periodo1 con el agrupado 
			$periodo1_vendedores = compara_grupo_periodo1($periodo1_vendedores, $grupo_vendedores);			
			
			//lo ordena
			$periodo1_vendedores = ordena_array_multi_x_campo($periodo1_vendedores, 'totalimp_tr', 'desc');					
			
			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1 solo si tiene comparativa
			if($_POST['comparativa1_desde'] != 0){
				$periodo2_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_importe', 'facvdorid');
				$periodo2_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo2_vendedores);
			}else{
				$periodo2_vendedores = 0;
			}
			if($_POST['comparativa2_desde'] != 0){
				$periodo3_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_importe', 'facvdorid');
				$periodo3_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo3_vendedores);
			}else{
				$periodo3_vendedores = 0;
			}
			if($_POST['comparativames_desde'] != 0){
				$periodomes_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_importe', 'facvdorid');				
				$periodomes_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodomes_vendedores);
			}else{
				$periodomes_vendedores = 0;
			}

			// array de vendedores
			$vendedores = array('grupo_vendedores' => $grupo_vendedores,
								'periodo1_vendedores' => $periodo1_vendedores,
								'periodo2_vendedores' => $periodo2_vendedores,
								'periodo3_vendedores' => $periodo3_vendedores,
								'periodomes_vendedores' => $periodomes_vendedores);	
		}else{
			$periodo1_vendedores = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'data_para_barra', 'facvdorid');

			//compara periodo1 con el agrupado 
			$periodo1_vendedores = compara_grupo_periodo1($periodo1_vendedores, $grupo_vendedores);		

			//lo ordena
			$periodo1_vendedores = ordena_array_multi_x_campo($periodo1_vendedores, 'totalimp', 'desc');		

			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1 solo si tiene comparativa
			if($_POST['comparativa1_desde'] != 0){
				$periodo2_vendedores = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'data_para_barra', 'facvdorid');
				$periodo2_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo2_vendedores);
			}else{
				$periodo2_vendedores = 0;
			}
			if($_POST['comparativa2_desde'] != 0){
				$periodo3_vendedores = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'data_para_barra', 'facvdorid');
				$periodo3_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo3_vendedores);
			}else{
				$periodo3_vendedores = 0;
			}
			if($_POST['comparativames_desde'] != 0){
				$periodomes_vendedores = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'data_para_barra', 'facvdorid');
				$periodomes_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodomes_vendedores);
			}else{
				$periodomes_vendedores = 0;
			}

			// array de vendedores
			$vendedores = array('grupo_vendedores' => $grupo_vendedores,
								'periodo1_vendedores' => $periodo1_vendedores,
								'periodo2_vendedores' => $periodo2_vendedores,
								'periodo3_vendedores' => $periodo3_vendedores,
								'periodomes_vendedores' => $periodomes_vendedores);
		}
		//*************************** FIN DATOS VENDEDORES ************************

		//*************************** DATOS PRODUCTOS ************************
		//captura productos del periodo
		if(substr($_POST['mywhere_transfer'], 0, 15) == 'PedTpoVtaC = 99'){
			
			//opcion subcanal = transfer subcanal.php
			$periodo1_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_importe', 'facprdid');
			
			//compara periodo1 con el agrupado 
			$periodo1_productos = compara_grupo_periodo1($periodo1_productos, $grupo_productos);			
			
			//lo ordena
			$periodo1_productos = ordena_array_multi_x_campo($periodo1_productos, 'totalimp_tr', 'desc');					

			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1 solo si tiene comparativa
			if($_POST['comparativa1_desde'] != 0){
				$periodo2_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_importe', 'facprdid');
				$periodo2_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo2_productos);
			}else{
				$periodo2_productos = 0;
			}
			if($_POST['comparativa2_desde'] != 0){
				$periodo3_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_importe', 'facprdid');
				$periodo3_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo3_productos);
			}else{
				$periodo3_productos = 0;
			}
			if($_POST['comparativames_desde'] != 0){
				$periodomes_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_importe', 'facprdid');				
				$periodomes_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodomes_productos);
			}else{
				$periodomes_productos = 0;
			}

			// array de productos
			$productos = array('grupo_productos' => $grupo_productos,
								'periodo1_productos' => $periodo1_productos,
								'periodo2_productos' => $periodo2_productos,
								'periodo3_productos' => $periodo3_productos,
								'periodomes_productos' => $periodomes_productos);	
		}else{		
			$periodo1_productos = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra', 'facprdid');

			//compara periodo1 con el agrupado 
			$periodo1_productos = compara_grupo_periodo1($periodo1_productos, $grupo_productos);					

			//lo ordena
			$periodo1_productos = ordena_array_multi_x_campo($periodo1_productos, 'totalimp', 'desc');		

			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1 solo si tiene comparativa
			if($_POST['comparativa1_desde'] != 0){
				$periodo2_productos = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra', 'facprdid');
				$periodo2_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo2_productos);
			}else{
				$periodo2_productos = 0;
			}
			if($_POST['comparativa2_desde'] != 0){
				$periodo3_productos = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra', 'facprdid');
				$periodo3_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo3_productos);
			}else{
				$periodo3_productos = 0;
			}
			if($_POST['comparativames_desde'] != 0){
				$periodomes_productos = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra', 'facprdid');
				$periodomes_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodomes_productos);
			}else{
				$periodomes_productos = 0;
			}

			// array de productos
			$productos = array('grupo_productos' => $grupo_productos,
								'periodo1_productos' => $periodo1_productos,
								'periodo2_productos' => $periodo2_productos,
								'periodo3_productos' => $periodo3_productos,
								'periodomes_productos' => $periodomes_productos);
		}
		//*************************** FIN DATOS PRODUCTOS ************************

		//*************************** DATOS CLIENTES ************************
		//captura clientes del periodo
		if(substr($_POST['mywhere_transfer'], 0, 15) == 'PedTpoVtaC = 99'){
			
			//recopila CLIENTES transfer 
			$periodo1_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_importe', 'faccliid');
			
			//compara periodo1 con el agrupado 
			$periodo1_clientes = compara_grupo_periodo1($periodo1_clientes, $grupo_clientes);			
			
			//lo ordena
			$periodo1_clientes = ordena_array_multi_x_campo($periodo1_clientes, 'totalimp_tr', 'desc');					
			
			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1 solo si tiene comparativa
			if($_POST['comparativa1_desde'] != 0){
				$periodo2_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_importe', 'faccliid');
				$periodo2_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo2_clientes);
			}else{
				$periodo2_clientes = 0;
			}
			if($_POST['comparativa2_desde'] != 0){
				$periodo3_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_importe', 'faccliid');
				$periodo3_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo3_clientes);
			}else{
				$periodo3_clientes = 0;
			}
			if($_POST['comparativames_desde'] != 0){
				$periodomes_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_importe', 'faccliid');
				$periodomes_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodomes_clientes);
			}else{
				$periodomes_clientes = 0;
			}

			// array de productos
			$clientes = array('grupo_clientes' => $grupo_clientes,
								'periodo1_clientes' => $periodo1_clientes,
								'periodo2_clientes' => $periodo2_clientes,
								'periodo3_clientes' => $periodo3_clientes,
								'periodomes_clientes' => $periodomes_clientes);	
		}else{			
			$periodo1_clientes = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'CliNomRed', 'data_para_barra', 'faccliid');

			//compara periodo1 con el agrupado 
			$periodo1_clientes = compara_grupo_periodo1($periodo1_clientes, $grupo_clientes);			

			//lo ordena
			$periodo1_clientes = ordena_array_multi_x_campo($periodo1_clientes, 'totalimp', 'desc');			

			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1 solo si tiene comparativa
			if($_POST['comparativa1_desde'] != 0){
				$periodo2_clientes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'CliNomRed', 'data_para_barra', 'faccliid');
				$periodo2_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo2_clientes);
			}else{
				$periodo2_clientes = 0;
			}
			if($_POST['comparativa2_desde'] != 0){
				$periodo3_clientes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'CliNomRed', 'data_para_barra', 'faccliid');
				$periodo3_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo3_clientes);
			}else{
				$periodo3_clientes = 0;
			}
			if($_POST['comparativames_desde'] != 0){
				$periodomes_clientes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'CliNomRed', 'data_para_barra', 'faccliid');
				$periodomes_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodomes_clientes);
			}else{
				$periodomes_clientes = 0;
			}			

			// array de clientes
			$clientes = array('grupo_clientes' => $grupo_clientes,
								'periodo1_clientes' => $periodo1_clientes,
								'periodo2_clientes' => $periodo2_clientes,
								'periodo3_clientes' => $periodo3_clientes,
								'periodomes_clientes' => $periodomes_clientes);
		}
		//*************************** FIN DATOS CLIENTES ************************			

			//saca los totales de los jefes en con funcion.php que totaliza el detalle capturado desde mysql
//			$array_totales = saca_totales_jefes($array_jefes_detalle);
			header('Content-Type: application/json');
			//echo json_encode($array_jefes_detalle);
			echo json_encode(array('array_1er_periodo' => $array_1er_periodo,
									'subcanales' => $subcanales,
									'canales' => $canales,
									'jefes' => $jefes,
									'vendedores' => $vendedores,
									'productos' => $productos,
									'clientes' => $clientes,
									'array_2do_periodo' => $array_2do_periodo,
									'array_3er_periodo' => $array_3er_periodo,
									'array_intermensual' => $array_intermensual
								));			
		
	break;	





























		
	case 'totales_seleccion_fecha_desde_fecha_hasta2':	
		$array_detalle_periodo1 = det_vta_seleccion_sin_impuestos($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		$array_totales_periodo1_importe = saca_totales_facturacion_nf($array_detalle_periodo1);
		$array_totales_periodo1_unidades = saca_totales_facturacion_unidadesvyb_nf($array_detalle_periodo1);
		
		//consulta el detalle de pedidos transfer del periodo fecha ingresada
		$array_detalle_periodo1_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		$array_totales_periodo1_importe_transfer = saca_totales_transfer_nf($array_detalle_periodo1_transfer);
		$array_totales_periodo1_unidades_transfer = saca_totales_transfer_unidadesvyb_nf($array_detalle_periodo1_transfer);		

		//informacion dia a dia facturado para armar el array_1er_periodo
		$data_vta_todo = data_estad_todo($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha']);
		//separo los datos para:
		//data_para_barra
		//data_para_barra_unidades
		//data_para_datatable
		//data_para_datatable_unidades
		for($i=0; $i<count($data_vta_todo); $i++){
			$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
			$data_para_barra_unidades[$i] = $data_vta_todo[$i]['axunid'];
			$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
			$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
			$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
			$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['axunid'];
		}
		
		//informacion dia a dia transfer para armar el array_1er_periodo
		$data_trf_todo = data_transfer_todo($_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha']);
		for($i=0; $i<count($data_trf_todo); $i++){
			$data_para_barra_transfer[$i] = $data_trf_todo[$i]['aximpo'];
			$data_para_barra_unidades_transfer[$i] = $data_trf_todo[$i]['axunid'];
			$data_para_datatable_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
			$data_para_datatable_transfer[$i]['totalx'] = $data_trf_todo[$i]['aximpo'];
			$data_para_datatable_unidades_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
			$data_para_datatable_unidades_transfer[$i]['totalx'] = $data_trf_todo[$i]['axunid'];
		}
		
		//arma arrays con datos para armar graficos y datatables del periodo actual y en caso de que tengan las comparativa1 y comparativa2
		// array_1er_periodo es el vector de facturacion con los datos para armar el grafico y la tabla detras del grafico
		$array_1er_periodo = array('data_para_barra' => $data_para_barra,
									'data_para_barra_unidades' => $data_para_barra_unidades,
									'data_para_datatable' => $data_para_datatable,
									'data_para_datatable_unidades' => $data_para_datatable_unidades,
									'array_totales_periodo1_importe' => $array_totales_periodo1_importe,
									'array_totales_periodo1_unidades' => $array_totales_periodo1_unidades,
								    'data_para_barra_transfer' => $data_para_barra_transfer,
									'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
									'data_para_datatable_transfer' => $data_para_datatable_transfer,
									'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
									'array_totales_periodo1_importe_transfer' => $array_totales_periodo1_importe_transfer,
//									'array_detalle_periodo1' => $array_detalle_periodo1,
									'array_totales_periodo1_unidades_transfer' => $array_totales_periodo1_unidades_transfer); 
//		$tiempo_fin = microtime(true);
//		echo "Tiempo proceso periodo 1: " . ($tiempo_fin - $tiempo_inicio);
//		echo '<br>';
//		$tiempo_inicio = microtime(true);
		//verifica si tiene comparativa1
		if($_POST['comparativa1_desde'] != 0){

			//consulta el detalle de venta del periodo fecha ingresada
			$array_detalle_periodo2 = det_vta_seleccion_sin_impuestos($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
			$array_totales_periodo2_importe = saca_totales_facturacion_nf($array_detalle_periodo2);
			$array_totales_periodo2_unidades = saca_totales_facturacion_unidadesvyb_nf($array_detalle_periodo2);
			
			//consulta el detalle de pedidos transfer del periodo fecha ingresada
			$array_detalle_periodo2_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
			$array_totales_periodo2_importe_transfer = saca_totales_transfer_nf($array_detalle_periodo2_transfer);
			$array_totales_periodo2_unidades_transfer = saca_totales_transfer_unidadesvyb_nf($array_detalle_periodo2_transfer);		
			
			//informacion dia a dia facturado para armar el array_2do_periodo
			$data_vta_todo = data_estad_todo($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_vta_todo); $i++){
				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['axunid'];
				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['axunid'];
			}
			
			//informacion dia a dia transfer para armar el array_2do_periodo
			$data_trf_todo = data_transfer_todo($_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_trf_todo); $i++){
				$data_para_barra_transfer[$i] = $data_trf_todo[$i]['aximpo'];
				$data_para_barra_unidades_transfer[$i] = $data_trf_todo[$i]['axunid'];
				$data_para_datatable_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_transfer[$i]['totalx'] = $data_trf_todo[$i]['aximpo'];
				$data_para_datatable_unidades_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_unidades_transfer[$i]['totalx'] = $data_trf_todo[$i]['axunid'];
			}
			
			// array_detalle_periodo2 es el vector de facturacion de la primer comparativa con los datos para armar el grafico y la tabla detras del grafico
			$array_2do_periodo = array('data_para_barra' => $data_para_barra,
											'data_para_barra_unidades' => $data_para_barra_unidades,
											'data_para_datatable' => $data_para_datatable,
											'data_para_datatable_unidades' => $data_para_datatable_unidades,
											'array_totales_periodo2_importe' => $array_totales_periodo2_importe,
											'array_totales_periodo2_unidades' => $array_totales_periodo2_unidades,
									   		'data_para_barra_transfer' => $data_para_barra_transfer,
											'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
											'data_para_datatable_transfer' => $data_para_datatable_transfer,
											'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
											'array_totales_periodo2_importe_transfer' => $array_totales_periodo2_importe_transfer,
											'array_totales_periodo2_unidades_transfer' => $array_totales_periodo2_unidades_transfer);
		}else{
			$array_2do_periodo = 0;
			$array_detalle_periodo2 = 0;
			$array_totales_periodo2_importe = 0;
			$array_totales_periodo2_unidades = 0;	
		}
//		$tiempo_fin = microtime(true);
//		echo "Tiempo proceso periodo 2: " . ($tiempo_fin - $tiempo_inicio);
//		echo '<br>';
//		$tiempo_inicio = microtime(true);

		//verifica si tiene comparativa2
		if($_POST['comparativa2_desde'] != 0){
			
			//consulta el detalle de venta del periodo fecha ingresada
			$array_detalle_periodo3 = det_vta_seleccion_sin_impuestos($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
			$array_totales_periodo3_importe = saca_totales_facturacion_nf($array_detalle_periodo3);
			$array_totales_periodo3_unidades = saca_totales_facturacion_unidadesvyb_nf($array_detalle_periodo3);
			
			//consulta el detalle de pedidos transfer del periodo fecha ingresada
			$array_detalle_periodo3_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
			$array_totales_periodo3_importe_transfer = saca_totales_transfer_nf($array_detalle_periodo3_transfer);
			$array_totales_periodo3_unidades_transfer = saca_totales_transfer_unidadesvyb_nf($array_detalle_periodo3_transfer);	
			
			//informacion dia a dia facturado para armar el array_3er_periodo
			$data_vta_todo = data_estad_todo($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_vta_todo); $i++){
				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['axunid'];
				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['axunid'];
			}			
			
			//informacion dia a dia transfer para armar el array_2do_periodo
			$data_trf_todo = data_transfer_todo($_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_trf_todo); $i++){
				$data_para_barra_transfer[$i] = $data_trf_todo[$i]['aximpo'];
				$data_para_barra_unidades_transfer[$i] = $data_trf_todo[$i]['axunid'];
				$data_para_datatable_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_transfer[$i]['totalx'] = $data_trf_todo[$i]['aximpo'];
				$data_para_datatable_unidades_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_unidades_transfer[$i]['totalx'] = $data_trf_todo[$i]['axunid'];
			}			
			
			// array_detalle_periodo3 es el vector de facturacion de la segunda comparativa con los datos para armar el grafico y la tabla detras del grafico
			$array_3er_periodo = array('data_para_barra' => $data_para_barra,
											'data_para_barra_unidades' => $data_para_barra_unidades,
											'data_para_datatable' => $data_para_datatable,
											'data_para_datatable_unidades' => $data_para_datatable_unidades,
											'array_totales_periodo3_importe' => $array_totales_periodo3_importe,
											'array_totales_periodo3_unidades' => $array_totales_periodo3_unidades,
									   		'data_para_barra_transfer' => $data_para_barra_transfer,
											'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
											'data_para_datatable_transfer' => $data_para_datatable_transfer,
											'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
											'array_totales_periodo3_importe_transfer' => $array_totales_periodo3_importe_transfer,
											'array_totales_periodo3_unidades_transfer' => $array_totales_periodo3_unidades_transfer);									   
		}else{
			$array_3er_periodo = 0;
			$array_detalle_periodo3 = 0;
			$array_totales_periodo3_importe = 0;
			$array_totales_periodo3_unidades = 0;			
		}	
		//verifica si tiene comparativa intermensual
		if($_POST['comparativames_desde'] != 0){

			//consulta el detalle de venta del fecha intermensual ingresada
			$array_detalle_intermensual = det_vta_seleccion_sin_impuestos($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta']);
			$array_totales_intermensual_importe = saca_totales_facturacion_nf($array_detalle_intermensual);
			$array_totales_intermensual_unidades = saca_totales_facturacion_unidadesvyb_nf($array_detalle_intermensual);
			
			//consulta el detalle de pedidos transfer fecha ingresada intermensual
			$array_detalle_intermensual_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta']);
			$array_totales_intermensual_importe_transfer = saca_totales_transfer_nf($array_detalle_intermensual_transfer);
			$array_totales_intermensual_unidades_transfer = saca_totales_transfer_unidadesvyb_nf($array_detalle_intermensual_transfer);		
			
			//informacion dia a dia facturado para armar el array_intermensual
			$data_vta_todo = data_estad_todo($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_vta_todo); $i++){
				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['axunid'];
				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['axunid'];
			}
			
			//informacion dia a dia transfer para armar el array_intermensual
			$data_trf_todo = data_transfer_todo($_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_trf_todo); $i++){
				$data_para_barra_transfer[$i] = $data_trf_todo[$i]['aximpo'];
				$data_para_barra_unidades_transfer[$i] = $data_trf_todo[$i]['axunid'];
				$data_para_datatable_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_transfer[$i]['totalx'] = $data_trf_todo[$i]['aximpo'];
				$data_para_datatable_unidades_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_unidades_transfer[$i]['totalx'] = $data_trf_todo[$i]['axunid'];
			}
			
			$array_intermensual = array('data_para_barra' => $data_para_barra,
											'data_para_barra_unidades' => $data_para_barra_unidades,
											'data_para_datatable' => $data_para_datatable,
											'data_para_datatable_unidades' => $data_para_datatable_unidades,
											'array_totales_intermensual_importe' => $array_totales_intermensual_importe,
											'array_totales_intermensual_unidades' => $array_totales_intermensual_unidades,
									   		'data_para_barra_transfer' => $data_para_barra_transfer,
											'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
											'data_para_datatable_transfer' => $data_para_datatable_transfer,
											'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
											'array_totales_intermensual_importe_transfer' => $array_totales_intermensual_importe_transfer,
//											'array_detalle_intermensual' => $array_detalle_intermensual,
											'array_totales_intermensual_unidades_transfer' => $array_totales_intermensual_unidades_transfer);
		}else{
			$array_intermensual = 0;
		}		
		
		
//		$tiempo_fin = microtime(true);
//		echo "Tiempo proceso periodo 3: " . ($tiempo_fin - $tiempo_inicio);
//		echo '<br>';
//		$tiempo_inicio = microtime(true);

		//sigo recopilando datos
		
		//agrupo nombres de subcanales jefes vendedores productos clientes de los tres periodos consultados en caso de que tengan datos 
		$grupo_subcanales = agrupa_nombres2($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $array_detalle_intermensual, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], $_POST['comparativames_desde'], 'TpoVtaDsc');
		//en subcanales agrego el subcanal TRANSFER de forma manual
		array_push($grupo_subcanales, "TRANSFER");
		$grupo_canales = agrupa_nombres2($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $array_detalle_intermensual, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], $_POST['comparativames_desde'], 'GTpVtaDes');
//		$grupo_jefes = agrupa_nombres2($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $array_detalle_intermensual, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], $_POST['comparativames_desde'], 'JefNom');
		$grupo_jefes = agrupa_nombres21($array_detalle_periodo1, 
											$array_detalle_periodo2, 
											$array_detalle_periodo3, 
											$array_detalle_intermensual, 
											$array_detalle_periodo1_transfer, 
											$array_detalle_periodo2_transfer, 
											$array_detalle_periodo3_transfer, 
											$array_detalle_intermensual_transfer, 											
											$_POST['comparativa1_desde'], 
											$_POST['comparativa2_desde'], 
											$_POST['comparativames_desde'], 
											'JefNom',
											'JefNom');
		$grupo_vendedores = agrupa_nombres21($array_detalle_periodo1, 
											$array_detalle_periodo2, 
											$array_detalle_periodo3, 
											$array_detalle_intermensual, 
											$array_detalle_periodo1_transfer, 
											$array_detalle_periodo2_transfer, 
											$array_detalle_periodo3_transfer, 
											$array_detalle_intermensual_transfer, 											
											$_POST['comparativa1_desde'], 
											$_POST['comparativa2_desde'], 
											$_POST['comparativames_desde'], 
											'VdorTxt',
											'VdorTxt');
		$grupo_productos = agrupa_nombres21($array_detalle_periodo1, 
											$array_detalle_periodo2, 
											$array_detalle_periodo3, 
											$array_detalle_intermensual, 
											$array_detalle_periodo1_transfer, 
											$array_detalle_periodo2_transfer, 
											$array_detalle_periodo3_transfer, 
											$array_detalle_intermensual_transfer, 											
											$_POST['comparativa1_desde'], 
											$_POST['comparativa2_desde'], 
											$_POST['comparativames_desde'], 
											'PrdTxt',
											'PrdTxt');		

		$grupo_clientes = agrupa_nombres21($array_detalle_periodo1, 
											$array_detalle_periodo2, 
											$array_detalle_periodo3, 
											$array_detalle_intermensual, 
											$array_detalle_periodo1_transfer, 
											$array_detalle_periodo2_transfer, 
											$array_detalle_periodo3_transfer, 
											$array_detalle_intermensual_transfer, 											
											$_POST['comparativa1_desde'], 
											$_POST['comparativa2_desde'], 
											$_POST['comparativames_desde'], 
											'CliNomRed',
											'CliNomRed');		
		//		$grupo_productos = agrupa_nombres2($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $array_detalle_intermensual, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], $_POST['comparativames_desde'], 'PrdTxt');
		
//		$grupo_clientes = agrupa_nombres2($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $array_detalle_intermensual, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], $_POST['comparativames_desde'], 'CliNomRed');
		
		
		//***********************************************************************************************************************************
		//***********************************************************************************************************************************
		//                                      calcula los totales x grupo
		//***********************************************************************************************************************************
		//***********************************************************************************************************************************
		
		//*************************** DATOS CANALES ************************
		//captura CANALES del periodo
		//if que verifica el grupo... pasa con clientes por ejemplo que solo tienen ventas x transfer entonces el grupo_canales en este caso no cuenta con el nombre del CANAL
		//entonces al verificar que no tiene nombre de CANAL definitivamente solo tiene venta por Transfer entonces obligo a la consulta a ir a ventas transfer en todas las demas consultas
		//jefes, vendedores, productos, clientes para evitar los graficos en blancos aunque 
		//si salen en blanco luego de pulsar el boton TR los datos salen... pero evitaremos ese paso cuando el cliente solo compro transfers ;)
		if(count($grupo_canales) < 1){
//			$myw = substr($_POST['mywhere_transfer'], 0, 15);  // abcd $_POST['mywhere_transfer'] = 'PedTpoVtaC = 99';
			$_POST['mywhere_transfer'] = 'PedTpoVtaC = 99 AND '.$_POST['mywhere_transfer'];
		}
		if(substr($_POST['mywhere_transfer'], 0, 15) == 'PedTpoVtaC = 99'){
			array_push($grupo_canales, "ORIGINALES");
			$periodo1_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');
			$periodo2_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');
			$periodo3_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');
			$periodomes_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');			

			//compara periodo1 con el agrupado 
			$periodo1_canales = compara_grupo_periodo1($periodo1_canales, $grupo_canales);

			//lo ordena
			$periodo1_canales = ordena_array_multi_x_campo($periodo1_canales, 'totalimp_tr', 'desc');

			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
			$periodo2_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodo2_canales);
			$periodo3_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodo3_canales);
			$periodomes_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodomes_canales);

			// array de Subcanales
			$canales = array('grupo_canales' => $grupo_canales,
								'periodo1_canales' => $periodo1_canales,
								'periodo2_canales' => $periodo2_canales,
								'periodo3_canales' => $periodo3_canales,
								'periodomes_canales' => $periodomes_canales);		
		}else{	

			$periodo1_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');
			$periodo2_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');
			$periodo3_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');
			$periodomes_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');			

			//compara periodo1 con el agrupado 
			$periodo1_canales = compara_grupo_periodo1($periodo1_canales, $grupo_canales);

			//lo ordena
			$periodo1_canales = ordena_array_multi_x_campo($periodo1_canales, 'totalimp', 'desc');

			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
			$periodo2_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodo2_canales);
			$periodo3_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodo3_canales);
			$periodomes_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodomes_canales);

			// array de Subcanales
			$canales = array('grupo_canales' => $grupo_canales,
								'periodo1_canales' => $periodo1_canales,
								'periodo2_canales' => $periodo2_canales,
								'periodo3_canales' => $periodo3_canales,
								'periodomes_canales' => $periodomes_canales,
							    'est_grupo_canales' => $_POST['mywhere_transfer']
							);			
		}

		//*************************** FIN DATOS CANALES ************************

		//$tiempo_fin = microtime(true);
		//echo "Tiempo empleado procesa canales: " . ($tiempo_fin - $tiempo_inicio);
		//echo '<br>';
		//$tiempo_inicio = microtime(true);


		//		
		//*************************** DATOS SUBCANALES ************************
		//captura SUBCANALES del periodo

		$periodo1_subcanales = datos_canvas_datatable_directa_transfer($_POST['mywhere'],  $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'TpoVtaDsc', 'data_para_barra', 'factpovtac');
		$periodo2_subcanales = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'TpoVtaDsc', 'data_para_barra', 'factpovtac');
		$periodo3_subcanales = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'TpoVtaDsc', 'data_para_barra', 'factpovtac');
		$periodomes_subcanales = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'TpoVtaDsc', 'data_para_barra', 'factpovtac');

		//compara periodo1 con el agrupado 
		$periodo1_subcanales = compara_grupo_periodo1($periodo1_subcanales, $grupo_subcanales);			

		//lo ordena
		$periodo1_subcanales = ordena_array_multi_x_campo($periodo1_subcanales, 'totalimp', 'desc');

		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		$periodo2_subcanales = ordena_array_segun_myarray1_x_campo($periodo1_subcanales, $periodo2_subcanales);
		$periodo3_subcanales = ordena_array_segun_myarray1_x_campo($periodo1_subcanales, $periodo3_subcanales);
		$periodomes_subcanales = ordena_array_segun_myarray1_x_campo($periodo1_subcanales, $periodomes_subcanales);

		// array de Subcanales
		$subcanales = array('grupo_subcanales' => $grupo_subcanales,
							'periodo1_subcanales' => $periodo1_subcanales,
							'periodo2_subcanales' => $periodo2_subcanales,
							'periodo3_subcanales' => $periodo3_subcanales,
							'periodomes_subcanales' => $periodomes_subcanales);

		//*************************** FIN DATOS SUBCANALES ************************

		//$tiempo_fin = microtime(true);
		//echo "Tiempo empleado procesa sub-canales: " . ($tiempo_fin - $tiempo_inicio);
		//echo '<br>';
		//$tiempo_inicio = microtime(true);

		//*************************** DATOS JEFES ************************
		//captura JEFES del periodo
		if(substr($_POST['mywhere_transfer'], 0, 15) == 'PedTpoVtaC = 99'){
			
			//opcion subcanal = transfer subcanal.php
			$periodo1_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');
			$periodo2_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');
			$periodo3_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');
			$periodomes_jefes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'jefnom', 'total_importe', 'tpovtajefe');				
			
			//compara periodo1 con el agrupado 
			$periodo1_jefes = compara_grupo_periodo1($periodo1_jefes, $grupo_jefes);			
			
			//lo ordena
			$periodo1_jefes = ordena_array_multi_x_campo($periodo1_jefes, 'totalimp_tr', 'desc');					
			
			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
			$periodo2_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo2_jefes);
			$periodo3_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo3_jefes);
			$periodomes_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodomes_jefes);
			
			// array de Jefes
			$jefes = array('grupo_jefes' => $grupo_jefes,
								'periodo1_jefes' => $periodo1_jefes,
								'periodo2_jefes' => $periodo2_jefes,
								'periodo3_jefes' => $periodo3_jefes,
								'periodomes_jefes' => $periodomes_jefes);	
			
			
		}else{	
			$periodo1_jefes = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'JefNom', 'data_para_barra', 'tpovtajefe');
			$periodo2_jefes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'JefNom', 'data_para_barra', 'tpovtajefe');
			$periodo3_jefes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'JefNom', 'data_para_barra', 'tpovtajefe');
			$periodomes_jefes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'JefNom', 'data_para_barra', 'tpovtajefe');		

			//compara periodo1 con el agrupado 
			$periodo1_jefes = compara_grupo_periodo1($periodo1_jefes, $grupo_jefes);

			//lo ordena
			$periodo1_jefes = ordena_array_multi_x_campo($periodo1_jefes, 'totalimp', 'desc');		

			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
			$periodo2_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo2_jefes);
			$periodo3_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo3_jefes);
			$periodomes_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodomes_jefes);

			// array de Jefes
			$jefes = array('grupo_jefes' => $grupo_jefes,
								'periodo1_jefes' => $periodo1_jefes,
								'periodo2_jefes' => $periodo2_jefes,
								'periodo3_jefes' => $periodo3_jefes,
								'periodomes_jefes' => $periodomes_jefes);
		}
		//*************************** FIN DATOS JEFES **********************	
		
		//$tiempo_fin = microtime(true);
		//echo "Tiempo empleado procesa jefes: " . ($tiempo_fin - $tiempo_inicio);
		//echo '<br>';
		//$tiempo_inicio = microtime(true);

		//*************************** DATOS VENDEDORES ************************
		//captura vendedores del periodo
		if(substr($_POST['mywhere_transfer'], 0, 15) == 'PedTpoVtaC = 99'){
			
			//opcion subcanal = transfer subcanal.php
			$periodo1_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_importe', 'facvdorid');
			$periodo2_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_importe', 'facvdorid');
			$periodo3_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_importe', 'facvdorid');
			$periodomes_vendedores = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'total_importe', 'facvdorid');				
			
			//compara periodo1 con el agrupado 
			$periodo1_vendedores = compara_grupo_periodo1($periodo1_vendedores, $grupo_vendedores);			
			
			//lo ordena
			$periodo1_vendedores = ordena_array_multi_x_campo($periodo1_vendedores, 'totalimp_tr', 'desc');					
			
			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
			$periodo2_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo2_vendedores);
			$periodo3_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo3_vendedores);
			$periodomes_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodomes_vendedores);
			
			// array de vendedores
			$vendedores = array('grupo_vendedores' => $grupo_vendedores,
								'periodo1_vendedores' => $periodo1_vendedores,
								'periodo2_vendedores' => $periodo2_vendedores,
								'periodo3_vendedores' => $periodo3_vendedores,
								'periodomes_vendedores' => $periodomes_vendedores);	
		}else{
			$periodo1_vendedores = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'data_para_barra', 'facvdorid');
			$periodo2_vendedores = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'data_para_barra', 'facvdorid');
			$periodo3_vendedores = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'data_para_barra', 'facvdorid');
			$periodomes_vendedores = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'vdortxt', 'data_para_barra', 'facvdorid');

			//compara periodo1 con el agrupado 
			$periodo1_vendedores = compara_grupo_periodo1($periodo1_vendedores, $grupo_vendedores);		

			//lo ordena
			$periodo1_vendedores = ordena_array_multi_x_campo($periodo1_vendedores, 'totalimp', 'desc');		

			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
			$periodo2_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo2_vendedores);
			$periodo3_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo3_vendedores);
			$periodomes_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodomes_vendedores);

			// array de vendedores
			$vendedores = array('grupo_vendedores' => $grupo_vendedores,
								'periodo1_vendedores' => $periodo1_vendedores,
								'periodo2_vendedores' => $periodo2_vendedores,
								'periodo3_vendedores' => $periodo3_vendedores,
								'periodomes_vendedores' => $periodomes_vendedores);
		}
		//*************************** FIN DATOS VENDEDORES ************************

		//$tiempo_fin = microtime(true);
		//echo "Tiempo empleado procesa vendedores: " . ($tiempo_fin - $tiempo_inicio);
		//echo '<br>';
		//$tiempo_inicio = microtime(true);

		//*************************** DATOS PRODUCTOS ************************
		//captura productos del periodo
		if(substr($_POST['mywhere_transfer'], 0, 15) == 'PedTpoVtaC = 99'){
			
			//opcion subcanal = transfer subcanal.php
			$periodo1_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_importe', 'facprdid');
			$periodo2_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_importe', 'facprdid');
			$periodo3_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_importe', 'facprdid');
			$periodomes_productos = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'prdtxt', 'total_importe', 'facprdid');				
			
			//compara periodo1 con el agrupado 
			$periodo1_productos = compara_grupo_periodo1($periodo1_productos, $grupo_productos);			
			
			//lo ordena
			$periodo1_productos = ordena_array_multi_x_campo($periodo1_productos, 'totalimp_tr', 'desc');					
			
			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
			$periodo2_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo2_productos);
			$periodo3_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo3_productos);
			$periodomes_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodomes_productos);
			
			// array de productos
			$productos = array('grupo_productos' => $grupo_productos,
								'periodo1_productos' => $periodo1_productos,
								'periodo2_productos' => $periodo2_productos,
								'periodo3_productos' => $periodo3_productos,
								'periodomes_productos' => $periodomes_productos);	
		}else{		
			$periodo1_productos = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra', 'facprdid');
			$periodo2_productos = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra', 'facprdid');
			$periodo3_productos = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra', 'facprdid');
			$periodomes_productos = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra', 'facprdid');

			//compara periodo1 con el agrupado 
			$periodo1_productos = compara_grupo_periodo1($periodo1_productos, $grupo_productos);					

			//lo ordena
			$periodo1_productos = ordena_array_multi_x_campo($periodo1_productos, 'totalimp', 'desc');		

			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
			$periodo2_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo2_productos);
			$periodo3_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo3_productos);
			$periodomes_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodomes_productos);

			// array de productos
			$productos = array('grupo_productos' => $grupo_productos,
								'periodo1_productos' => $periodo1_productos,
								'periodo2_productos' => $periodo2_productos,
								'periodo3_productos' => $periodo3_productos,
								'periodomes_productos' => $periodomes_productos);
		}
		//*************************** FIN DATOS PRODUCTOS ************************

		//$tiempo_fin = microtime(true);
		//echo "Tiempo empleado procesa productos: " . ($tiempo_fin - $tiempo_inicio);
		//echo '<br>';
		//$tiempo_inicio = microtime(true);

		//*************************** DATOS CLIENTES ************************
		//captura clientes del periodo
		if(substr($_POST['mywhere_transfer'], 0, 15) == 'PedTpoVtaC = 99'){
			
			//opcion subcanal = transfer subcanal.php
				$periodo1_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_importe', 'faccliid');
				$periodo2_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_importe', 'faccliid');
				$periodo3_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_importe', 'faccliid');
				$periodomes_clientes = datos_canvas_datatable_directa_transfer($_POST['mywhere'], $_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'clinomred', 'total_importe', 'faccliid');
			
			//compara periodo1 con el agrupado 
			$periodo1_clientes = compara_grupo_periodo1($periodo1_clientes, $grupo_clientes);			
//			
//			//lo ordena
			$periodo1_clientes = ordena_array_multi_x_campo($periodo1_clientes, 'totalimp_tr', 'desc');					
			
			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
			$periodo2_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo2_clientes);
			$periodo3_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo3_clientes);
			$periodomes_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodomes_clientes);
			
			// array de productos
			$clientes = array('grupo_clientes' => $grupo_clientes,
								'periodo1_clientes' => $periodo1_clientes,
								'periodo2_clientes' => $periodo2_clientes,
								'periodo3_clientes' => $periodo3_clientes,
								'periodomes_clientes' => $periodomes_clientes);	
		}else{			
			$periodo1_clientes = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'CliNomRed', 'data_para_barra', 'faccliid');
			$periodo2_clientes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'CliNomRed', 'data_para_barra', 'faccliid');
			$periodo3_clientes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'CliNomRed', 'data_para_barra', 'faccliid');
			$periodomes_clientes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'CliNomRed', 'data_para_barra', 'faccliid');

			//compara periodo1 con el agrupado 
			$periodo1_clientes = compara_grupo_periodo1($periodo1_clientes, $grupo_clientes);			

			//lo ordena
			$periodo1_clientes = ordena_array_multi_x_campo($periodo1_clientes, 'totalimp', 'desc');			

			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
			$periodo2_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo2_clientes);
			$periodo3_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo3_clientes);
			$periodomes_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodomes_clientes);

			// array de clientes
			$clientes = array('grupo_clientes' => $grupo_clientes,
								'periodo1_clientes' => $periodo1_clientes,
								'periodo2_clientes' => $periodo2_clientes,
								'periodo3_clientes' => $periodo3_clientes,
								'periodomes_clientes' => $periodomes_clientes);
		}
		//*************************** FIN DATOS CLIENTES ************************	

		//$tiempo_fin = microtime(true);
		//echo "Tiempo empleado procesa clientes: " . ($tiempo_fin - $tiempo_inicio);
		//echo '<br>';
		//$tiempo_inicio = microtime(true);

		if($array_detalle_periodo1 == 0 && $array_detalle_periodo1_transfer == 0){
			echo json_encode(0); 
		}else{
			//saca los totales de los jefes en con funcion.php que totaliza el detalle capturado desde mysql
//			$array_totales = saca_totales_jefes($array_jefes_detalle);
			header('Content-Type: application/json');
			//echo json_encode($array_jefes_detalle);
			echo json_encode(array('array_1er_periodo' => $array_1er_periodo,
//								   'periodo1_subcanales' => $periodo1_subcanales,
//								   'periodo1_subcanales2' => $periodo1_subcanales2,
//								   'periodo1_subcanales_ord_uni' => $periodo1_subcanales_ord_uni,
//								   'periodo2_subcanales' => $periodo2_subcanales,
//								   'periodo3_subcanales' => $periodo3_subcanales,
//								   'grupo_subcanales' => $grupo_subcanales,

								   'subcanales' => $subcanales,
								   'canales' => $canales,
								   'jefes' => $jefes,
								   'vendedores' => $vendedores,
								   'productos' => $productos,
								   'clientes' => $clientes,

//								   'prueba' => $array_detalle_periodo1_transfer,
//								   'data_para_barra' => $data_para_barra,
//								   'data_para_barra_unidades' => $data_para_barra_unidades,
//								   'data_para_datatable' => $data_para_datatable,
//								   'data_para_datatable_unidades' => $data_para_datatable_unidades,
//								   'array_totales_periodo1_importe' => $array_totales_periodo1_importe,
//								   'array_totales_periodo1_unidades' => $array_totales_periodo1_unidades,

								   'array_2do_periodo' => $array_2do_periodo,

//								   'array_totales_periodo2_importe' => $array_totales_periodo2_importe,
//								   'array_totales_periodo2_unidades' => $array_totales_periodo2_unidades,								   

								   'array_3er_periodo' => $array_3er_periodo,
								   'array_intermensual' => $array_intermensual
								));			
		}		
	break;		
		
		
		
	case 'totales_seleccion_fecha_desde_fecha_hasta':	
		$array_detalle_periodo1 = det_vta_seleccion_sin_impuestos($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		$array_totales_periodo1_importe = saca_totales_facturacion_nf($array_detalle_periodo1);
		$array_totales_periodo1_unidades = saca_totales_facturacion_unidadesvyb_nf($array_detalle_periodo1);
		
		//consulta el detalle de pedidos transfer del periodo fecha ingresada
		$array_detalle_periodo1_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		$array_totales_periodo1_importe_transfer = saca_totales_transfer_nf($array_detalle_periodo1_transfer);
		$array_totales_periodo1_unidades_transfer = saca_totales_transfer_unidadesvyb_nf($array_detalle_periodo1_transfer);		

		//informacion dia a dia facturado para armar el array_1er_periodo
		$data_vta_todo = data_estad_todo($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha']);
		//separo los datos para:
		//data_para_barra
		//data_para_barra_unidades
		//data_para_datatable
		//data_para_datatable_unidades
		for($i=0; $i<count($data_vta_todo); $i++){
			$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
			$data_para_barra_unidades[$i] = $data_vta_todo[$i]['axunid'];
			$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
			$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
			$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
			$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['axunid'];
		}
		
		//informacion dia a dia transfer para armar el array_1er_periodo
		$data_trf_todo = data_transfer_todo($_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha']);
		for($i=0; $i<count($data_trf_todo); $i++){
			$data_para_barra_transfer[$i] = $data_trf_todo[$i]['aximpo'];
			$data_para_barra_unidades_transfer[$i] = $data_trf_todo[$i]['axunid'];
			$data_para_datatable_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
			$data_para_datatable_transfer[$i]['totalx'] = $data_trf_todo[$i]['aximpo'];
			$data_para_datatable_unidades_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
			$data_para_datatable_unidades_transfer[$i]['totalx'] = $data_trf_todo[$i]['axunid'];
		}
		
		//arma arrays con datos para armar graficos y datatables del periodo actual y en caso de que tengan las comparativa1 y comparativa2
		// array_1er_periodo es el vector de facturacion con los datos para armar el grafico y la tabla detras del grafico
		$array_1er_periodo = array('data_para_barra' => $data_para_barra,
									'data_para_barra_unidades' => $data_para_barra_unidades,
									'data_para_datatable' => $data_para_datatable,
									'data_para_datatable_unidades' => $data_para_datatable_unidades,
									'array_totales_periodo1_importe' => $array_totales_periodo1_importe,
									'array_totales_periodo1_unidades' => $array_totales_periodo1_unidades,
								    'data_para_barra_transfer' => $data_para_barra_transfer,
									'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
									'data_para_datatable_transfer' => $data_para_datatable_transfer,
									'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
									'array_totales_periodo1_importe_transfer' => $array_totales_periodo1_importe_transfer,
									'array_totales_periodo1_unidades_transfer' => $array_totales_periodo1_unidades_transfer,
									'array_detalle_periodo1_transfer' => $array_detalle_periodo1_transfer,
									'array_detalle_periodo1' => $array_detalle_periodo1);
//		$tiempo_fin = microtime(true);
//		echo "Tiempo proceso periodo 1: " . ($tiempo_fin - $tiempo_inicio);
//		echo '<br>';
//		$tiempo_inicio = microtime(true);
		//verifica si tiene comparativa1
		if($_POST['comparativa1_desde'] != 0){

			//consulta el detalle de venta del periodo fecha ingresada
			$array_detalle_periodo2 = det_vta_seleccion_sin_impuestos($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
			$array_totales_periodo2_importe = saca_totales_facturacion_nf($array_detalle_periodo2);
			$array_totales_periodo2_unidades = saca_totales_facturacion_unidadesvyb_nf($array_detalle_periodo2);
			
			//consulta el detalle de pedidos transfer del periodo fecha ingresada
			$array_detalle_periodo2_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
			$array_totales_periodo2_importe_transfer = saca_totales_transfer_nf($array_detalle_periodo2_transfer);
			$array_totales_periodo2_unidades_transfer = saca_totales_transfer_unidadesvyb_nf($array_detalle_periodo2_transfer);		
			
			//informacion dia a dia facturado para armar el array_2do_periodo
			$data_vta_todo = data_estad_todo($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_vta_todo); $i++){
				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['axunid'];
				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['axunid'];
			}
			
			//informacion dia a dia transfer para armar el array_2do_periodo
			$data_trf_todo = data_transfer_todo($_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_trf_todo); $i++){
				$data_para_barra_transfer[$i] = $data_trf_todo[$i]['aximpo'];
				$data_para_barra_unidades_transfer[$i] = $data_trf_todo[$i]['axunid'];
				$data_para_datatable_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_transfer[$i]['totalx'] = $data_trf_todo[$i]['aximpo'];
				$data_para_datatable_unidades_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_unidades_transfer[$i]['totalx'] = $data_trf_todo[$i]['axunid'];
			}
			
			// array_detalle_periodo2 es el vector de facturacion de la primer comparativa con los datos para armar el grafico y la tabla detras del grafico
			$array_2do_periodo = array('data_para_barra' => $data_para_barra,
											'data_para_barra_unidades' => $data_para_barra_unidades,
											'data_para_datatable' => $data_para_datatable,
											'data_para_datatable_unidades' => $data_para_datatable_unidades,
											'array_totales_periodo2_importe' => $array_totales_periodo2_importe,
											'array_totales_periodo2_unidades' => $array_totales_periodo2_unidades,
									   		'data_para_barra_transfer' => $data_para_barra_transfer,
											'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
											'data_para_datatable_transfer' => $data_para_datatable_transfer,
											'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
											'array_totales_periodo2_importe_transfer' => $array_totales_periodo2_importe_transfer,
											'array_totales_periodo2_unidades_transfer' => $array_totales_periodo2_unidades_transfer);
		}else{
			$array_2do_periodo = 0;
			$array_detalle_periodo2 = 0;
			$array_totales_periodo2_importe = 0;
			$array_totales_periodo2_unidades = 0;	
		}
//		$tiempo_fin = microtime(true);
//		echo "Tiempo proceso periodo 2: " . ($tiempo_fin - $tiempo_inicio);
//		echo '<br>';
//		$tiempo_inicio = microtime(true);

		//verifica si tiene comparativa2
		if($_POST['comparativa2_desde'] != 0){
			
			//consulta el detalle de venta del periodo fecha ingresada
			$array_detalle_periodo3 = det_vta_seleccion_sin_impuestos($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
			$array_totales_periodo3_importe = saca_totales_facturacion_nf($array_detalle_periodo3);
			$array_totales_periodo3_unidades = saca_totales_facturacion_unidadesvyb_nf($array_detalle_periodo3);
			
			//consulta el detalle de pedidos transfer del periodo fecha ingresada
			$array_detalle_periodo3_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
			$array_totales_periodo3_importe_transfer = saca_totales_transfer_nf($array_detalle_periodo3_transfer);
			$array_totales_periodo3_unidades_transfer = saca_totales_transfer_unidadesvyb_nf($array_detalle_periodo3_transfer);	
			
			//informacion dia a dia facturado para armar el array_3er_periodo
			$data_vta_todo = data_estad_todo($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_vta_todo); $i++){
				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['axunid'];
				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['axunid'];
			}			
			
			//informacion dia a dia transfer para armar el array_2do_periodo
			$data_trf_todo = data_transfer_todo($_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_trf_todo); $i++){
				$data_para_barra_transfer[$i] = $data_trf_todo[$i]['aximpo'];
				$data_para_barra_unidades_transfer[$i] = $data_trf_todo[$i]['axunid'];
				$data_para_datatable_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_transfer[$i]['totalx'] = $data_trf_todo[$i]['aximpo'];
				$data_para_datatable_unidades_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_unidades_transfer[$i]['totalx'] = $data_trf_todo[$i]['axunid'];
			}			
			
			// array_detalle_periodo3 es el vector de facturacion de la segunda comparativa con los datos para armar el grafico y la tabla detras del grafico
			$array_3er_periodo = array('data_para_barra' => $data_para_barra,
											'data_para_barra_unidades' => $data_para_barra_unidades,
											'data_para_datatable' => $data_para_datatable,
											'data_para_datatable_unidades' => $data_para_datatable_unidades,
											'array_totales_periodo3_importe' => $array_totales_periodo3_importe,
											'array_totales_periodo3_unidades' => $array_totales_periodo3_unidades,
									   		'data_para_barra_transfer' => $data_para_barra_transfer,
											'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
											'data_para_datatable_transfer' => $data_para_datatable_transfer,
											'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
											'array_totales_periodo3_importe_transfer' => $array_totales_periodo3_importe_transfer,
											'array_totales_periodo3_unidades_transfer' => $array_totales_periodo3_unidades_transfer);									   
		}else{
			$array_3er_periodo = 0;
			$array_detalle_periodo3 = 0;
			$array_totales_periodo3_importe = 0;
			$array_totales_periodo3_unidades = 0;			
		}		
//		$tiempo_fin = microtime(true);
//		echo "Tiempo proceso periodo 3: " . ($tiempo_fin - $tiempo_inicio);
//		echo '<br>';
//		$tiempo_inicio = microtime(true);

		//sigo recopilando datos
		
		//agrupo nombres de subcanales jefes vendedores productos clientes de los tres periodos consultados en caso de que tengan datos 
		$grupo_subcanales = agrupa_nombres($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], 'TpoVtaDsc');
		$grupo_canales = agrupa_nombres($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], 'GTpVtaDes');
		$grupo_jefes = agrupa_nombres($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], 'JefNom');
		$grupo_vendedores = agrupa_nombres($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], 'VdorTxt');
		$grupo_productos = agrupa_nombres($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], 'PrdTxt');
		$grupo_clientes = agrupa_nombres($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], 'CliNomRed');
		
		
		//***********************************************************************************************************************************
		//***********************************************************************************************************************************
		//si el subcanal seleccionado es 99 - Transfer, modifico la tematica de las barras ya que transfer no tiene canal ni subcanal de venta
		//***********************************************************************************************************************************
		//***********************************************************************************************************************************
		
		//*************************** DATOS CANALES ************************
		//captura CANALES del periodo
		if($_POST['mywhere_transfer'] == 99 && $_POST['mywhere'] == 0){
			$periodo1_canales = 0;
			$periodo2_canales = 0;
			$periodo3_canales = 0;
			// array de Subcanales
			$canales = array('grupo_canales' => $grupo_canales,
								'periodo1_canales' => $periodo1_canales,
								'periodo2_canales' => $periodo2_canales,
								'periodo3_canales' => $periodo3_canales);			
		}else{
			$periodo1_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');
			$periodo2_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');
			$periodo3_canales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'GTpVtaDes', 'data_para_barra', 'tpovtagrup');
			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
			$periodo2_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodo2_canales);
			$periodo3_canales = ordena_array_segun_myarray1_x_campo($periodo1_canales, $periodo3_canales);
			// array de Subcanales
			$canales = array('grupo_canales' => $grupo_canales,
								'periodo1_canales' => $periodo1_canales,
								'periodo2_canales' => $periodo2_canales,
								'periodo3_canales' => $periodo3_canales);			
		}

		//*************************** FIN DATOS CANALES ************************

//$tiempo_fin = microtime(true);
//echo "Tiempo empleado procesa canales: " . ($tiempo_fin - $tiempo_inicio);
//echo '<br>';
//$tiempo_inicio = microtime(true);


//		
		//*************************** DATOS SUBCANALES ************************
		//captura SUBCANALES del periodo
		if($_POST['mywhere_transfer'] == 99 && $_POST['mywhere'] == 0){
			$periodo1_subcanales = 0;
			$periodo2_subcanales = 0;
			$periodo3_subcanales = 0;

			// array de Subcanales
			$subcanales = array('grupo_subcanales' => $grupo_subcanales,
								'periodo1_subcanales' => $periodo1_subcanales,
								'periodo2_subcanales' => $periodo2_subcanales,
								'periodo3_subcanales' => $periodo3_subcanales);		
		}else{		
			$periodo1_subcanales = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'TpoVtaDsc', 'data_para_barra', 'factpovtac');
			$periodo2_subcanales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'TpoVtaDsc', 'data_para_barra', 'factpovtac');
			$periodo3_subcanales = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'TpoVtaDsc', 'data_para_barra', 'factpovtac');

			//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
			$periodo2_subcanales = ordena_array_segun_myarray1_x_campo($periodo1_subcanales, $periodo2_subcanales);
			$periodo3_subcanales = ordena_array_segun_myarray1_x_campo($periodo1_subcanales, $periodo3_subcanales);

			// array de Subcanales
			$subcanales = array('grupo_subcanales' => $grupo_subcanales,
								'periodo1_subcanales' => $periodo1_subcanales,
								'periodo2_subcanales' => $periodo2_subcanales,
								'periodo3_subcanales' => $periodo3_subcanales);
		}
		//*************************** FIN DATOS SUBCANALES ************************

//$tiempo_fin = microtime(true);
//echo "Tiempo empleado procesa sub-canales: " . ($tiempo_fin - $tiempo_inicio);
//echo '<br>';
//$tiempo_inicio = microtime(true);

		//*************************** DATOS JEFES ************************
		//captura JEFES del periodo
		$periodo1_jefes = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'JefNom', 'data_para_barra', 'tpovtajefe');
		$periodo2_jefes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'JefNom', 'data_para_barra', 'tpovtajefe');
		$periodo3_jefes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'JefNom', 'data_para_barra', 'tpovtajefe');
		
		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		$periodo2_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo2_jefes);
		$periodo3_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo3_jefes);

		// array de Jefes
		$jefes = array('grupo_jefes' => $grupo_jefes,
							'periodo1_jefes' => $periodo1_jefes,
							'periodo2_jefes' => $periodo2_jefes,
							'periodo3_jefes' => $periodo3_jefes);
		//*************************** FIN DATOS JEFES **********************	
//$tiempo_fin = microtime(true);
//echo "Tiempo empleado procesa jefes: " . ($tiempo_fin - $tiempo_inicio);
//echo '<br>';
//$tiempo_inicio = microtime(true);



//		
		//*************************** DATOS VENDEDORES ************************
		//captura vendedores del periodo
		$periodo1_vendedores = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'VdorTxt', 'data_para_barra', 'facvdorid');
		$periodo2_vendedores = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'VdorTxt', 'data_para_barra', 'facvdorid');
		$periodo3_vendedores = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'VdorTxt', 'data_para_barra', 'facvdorid');

		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		$periodo2_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo2_vendedores);
		$periodo3_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo3_vendedores);

		// array de vendedores
		$vendedores = array('grupo_vendedores' => $grupo_vendedores,
							'periodo1_vendedores' => $periodo1_vendedores,
							'periodo2_vendedores' => $periodo2_vendedores,
							'periodo3_vendedores' => $periodo3_vendedores);
		//*************************** FIN DATOS VENDEDORES ************************

//$tiempo_fin = microtime(true);
//echo "Tiempo empleado procesa vendedores: " . ($tiempo_fin - $tiempo_inicio);
//echo '<br>';
//$tiempo_inicio = microtime(true);

		//*************************** DATOS PRODUCTOS ************************
		//captura productos del periodo
		$periodo1_productos = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra', 'facprdid');
		$periodo2_productos = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra', 'facprdid');
		$periodo3_productos = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra', 'facprdid');
		
		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		$periodo2_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo2_productos);
		$periodo3_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo3_productos);

		// array de productos
		$productos = array('grupo_productos' => $grupo_productos,
							'periodo1_productos' => $periodo1_productos,
							'periodo2_productos' => $periodo2_productos,
							'periodo3_productos' => $periodo3_productos);
		//*************************** FIN DATOS PRODUCTOS ************************

//$tiempo_fin = microtime(true);
//echo "Tiempo empleado procesa productos: " . ($tiempo_fin - $tiempo_inicio);
//echo '<br>';
//$tiempo_inicio = microtime(true);

		//*************************** DATOS CLIENTES ************************
		//captura clientes del periodo
		$periodo1_clientes = datos_canvas_datatable($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'CliNomRed', 'data_para_barra', 'faccliid');
		$periodo2_clientes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'CliNomRed', 'data_para_barra', 'faccliid');
		$periodo3_clientes = datos_canvas_datatable($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'CliNomRed', 'data_para_barra', 'faccliid');

		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		$periodo2_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo2_clientes);
		$periodo3_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo3_clientes);
		
		// array de clientes
		$clientes = array('grupo_clientes' => $grupo_clientes,
							'periodo1_clientes' => $periodo1_clientes,
							'periodo2_clientes' => $periodo2_clientes,
							'periodo3_clientes' => $periodo3_clientes);
		//*************************** FIN DATOS CLIENTES ************************	

//$tiempo_fin = microtime(true);
//echo "Tiempo empleado procesa clientes: " . ($tiempo_fin - $tiempo_inicio);
//echo '<br>';
//$tiempo_inicio = microtime(true);

		if($array_detalle_periodo1 == 0 && $array_detalle_periodo1_transfer == 0){
			echo json_encode(0); 
		}else{
			//saca los totales de los jefes en con funcion.php que totaliza el detalle capturado desde mysql
//			$array_totales = saca_totales_jefes($array_jefes_detalle);
			header('Content-Type: application/json');
			//echo json_encode($array_jefes_detalle);
			echo json_encode(array('array_1er_periodo' => $array_1er_periodo,
//								   'periodo1_subcanales' => $periodo1_subcanales,
//								   'periodo1_subcanales2' => $periodo1_subcanales2,
//								   'periodo1_subcanales_ord_uni' => $periodo1_subcanales_ord_uni,
//								   'periodo2_subcanales' => $periodo2_subcanales,
//								   'periodo3_subcanales' => $periodo3_subcanales,
//								   'grupo_subcanales' => $grupo_subcanales,
								   'subcanales' => $subcanales,
								   'canales' => $canales,
								   'jefes' => $jefes,
								   'vendedores' => $vendedores,
								   'productos' => $productos,
								   'clientes' => $clientes,
//								   'prueba' => $array_detalle_periodo1_transfer,
//								   'data_para_barra' => $data_para_barra,
//								   'data_para_barra_unidades' => $data_para_barra_unidades,
//								   'data_para_datatable' => $data_para_datatable,
//								   'data_para_datatable_unidades' => $data_para_datatable_unidades,
//								   'array_totales_periodo1_importe' => $array_totales_periodo1_importe,
//								   'array_totales_periodo1_unidades' => $array_totales_periodo1_unidades,
								   'array_2do_periodo' => $array_2do_periodo,
//								   'array_totales_periodo2_importe' => $array_totales_periodo2_importe,
//								   'array_totales_periodo2_unidades' => $array_totales_periodo2_unidades,								   
								   'array_3er_periodo' => $array_3er_periodo,
//								   'array_totales_periodo3_importe' => $array_totales_periodo3_importe,
//								   'array_totales_periodo3_unidades' => $array_totales_periodo3_unidades
								  ));			
		}		
	break;
	case 'totales_seleccion_fecha_desde_fecha_hasta_transfer':	
		//***********************************************************************************************************************************************************
		//en este ajax lo que hago es exclusivo para transfer, como la consulta es solo a transfer y no puedo dejar sin datos el periodo1 de facturacion en vez de 
		//llenarla con ceros y hacer if en el archivo canvas.js invierto los roles en este caso solamente todo lo que es transfer lo saco como periodo1 como si fuera
		//venta normal y lo de transfer lo pongo en cero... de esta forma me evito un monto de quilombo en mis funciones 
		//esta funcion ajax solo la aplico a subcanales.php cuando se selecciona tranasfer => 99
		//***********************************************************************************************************************************************************
		
		$array_detalle_periodo1 = det_trf_seleccion_nf($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		$array_totales_periodo1_importe = saca_totales_transfer_nf($array_detalle_periodo1);
		$array_totales_periodo1_unidades = saca_totales_transfer_unidadesvyb_nf($array_detalle_periodo1);
		
		//consulta el detalle de pedidos transfer del periodo fecha ingresada
		$array_detalle_periodo1_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		$array_totales_periodo1_importe_transfer = saca_totales_transfer_nf($array_detalle_periodo1_transfer);
		$array_totales_periodo1_unidades_transfer = saca_totales_transfer_unidadesvyb_nf($array_detalle_periodo1_transfer);		

		//informacion dia a dia facturado para armar el array_1er_periodo
		$data_vta_todo = data_transfer_todo($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha']);
		//separo los datos para:
		//data_para_barra
		//data_para_barra_unidades
		//data_para_datatable
		//data_para_datatable_unidades
		for($i=0; $i<count($data_vta_todo); $i++){
			$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
			$data_para_barra_unidades[$i] = $data_vta_todo[$i]['axunid'];
			$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
			$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
			$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
			$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['axunid'];
		}
		
		//informacion dia a dia transfer para armar el array_1er_periodo
		$data_trf_todo = data_transfer_todo($_POST['mywhere_transfer'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha']);
		for($i=0; $i<count($data_trf_todo); $i++){
			$data_para_barra_transfer[$i] = $data_trf_todo[$i]['aximpo'];
			$data_para_barra_unidades_transfer[$i] = $data_trf_todo[$i]['axunid'];
			$data_para_datatable_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
			$data_para_datatable_transfer[$i]['totalx'] = $data_trf_todo[$i]['aximpo'];
			$data_para_datatable_unidades_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
			$data_para_datatable_unidades_transfer[$i]['totalx'] = $data_trf_todo[$i]['axunid'];
		}
		
		//arma arrays con datos para armar graficos y datatables del periodo actual  y en caso de que tengan las comparativa1 y comparativa2
		// array_1er_periodo es el vector de facturacion con los datos para armar el grafico y la tabla detras del grafico
		$array_1er_periodo = array('data_para_barra' => $data_para_barra,
									'data_para_barra_unidades' => $data_para_barra_unidades,
									'data_para_datatable' => $data_para_datatable,
									'data_para_datatable_unidades' => $data_para_datatable_unidades,
									'array_totales_periodo1_importe' => $array_totales_periodo1_importe,
									'array_totales_periodo1_unidades' => $array_totales_periodo1_unidades,
								    'data_para_barra_transfer' => $data_para_barra_transfer,
									'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
									'data_para_datatable_transfer' => $data_para_datatable_transfer,
									'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
									'array_totales_periodo1_importe_transfer' => $array_totales_periodo1_importe_transfer,
									'array_totales_periodo1_unidades_transfer' => $array_totales_periodo1_unidades_transfer);
//		$tiempo_fin = microtime(true);
//		echo "Tiempo proceso periodo 1: " . ($tiempo_fin - $tiempo_inicio);
//		echo '<br>';
//		$tiempo_inicio = microtime(true);
		//verifica si tiene comparativa1
		if($_POST['comparativa1_desde'] != 0){

			//consulta el detalle de venta del periodo fecha ingresada
			$array_detalle_periodo2 = det_trf_seleccion_nf($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
			$array_totales_periodo2_importe = saca_totales_transfer_nf($array_detalle_periodo2);
			$array_totales_periodo2_unidades = saca_totales_transfer_unidadesvyb_nf($array_detalle_periodo2);
			
			//consulta el detalle de pedidos transfer del periodo fecha ingresada
			$array_detalle_periodo2_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta']);
			$array_totales_periodo2_importe_transfer = saca_totales_transfer_nf($array_detalle_periodo2_transfer);
			$array_totales_periodo2_unidades_transfer = saca_totales_transfer_unidadesvyb_nf($array_detalle_periodo2_transfer);		
			
			//informacion dia a dia facturado para armar el array_2do_periodo
			$data_vta_todo = data_transfer_todo($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_vta_todo); $i++){
				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['axunid'];
				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['axunid'];
			}
			
			//informacion dia a dia transfer para armar el array_2do_periodo
			$data_trf_todo = data_transfer_todo($_POST['mywhere_transfer'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_trf_todo); $i++){
				$data_para_barra_transfer[$i] = $data_trf_todo[$i]['aximpo'];
				$data_para_barra_unidades_transfer[$i] = $data_trf_todo[$i]['axunid'];
				$data_para_datatable_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_transfer[$i]['totalx'] = $data_trf_todo[$i]['aximpo'];
				$data_para_datatable_unidades_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_unidades_transfer[$i]['totalx'] = $data_trf_todo[$i]['axunid'];
			}
			
			// array_detalle_periodo2 es el vector de facturacion de la primer comparativa con los datos para armar el grafico y la tabla detras del grafico
			$array_2do_periodo = array('data_para_barra' => $data_para_barra,
											'data_para_barra_unidades' => $data_para_barra_unidades,
											'data_para_datatable' => $data_para_datatable,
											'data_para_datatable_unidades' => $data_para_datatable_unidades,
											'array_totales_periodo2_importe' => $array_totales_periodo2_importe,
											'array_totales_periodo2_unidades' => $array_totales_periodo2_unidades,
									   		'data_para_barra_transfer' => $data_para_barra_transfer,
											'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
											'data_para_datatable_transfer' => $data_para_datatable_transfer,
											'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
											'array_totales_periodo2_importe_transfer' => $array_totales_periodo2_importe_transfer,
											'array_totales_periodo2_unidades_transfer' => $array_totales_periodo2_unidades_transfer);
		}else{
			$array_2do_periodo = 0;
			$array_detalle_periodo2 = 0;
			$array_totales_periodo2_importe = 0;
			$array_totales_periodo2_unidades = 0;	
		}

		//verifica si tiene comparativa2
		if($_POST['comparativa2_desde'] != 0){
			
			//consulta el detalle de venta del periodo fecha ingresada
			$array_detalle_periodo3 = det_trf_seleccion_nf($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
			$array_totales_periodo3_importe = saca_totales_transfer_nf($array_detalle_periodo3);
			$array_totales_periodo3_unidades = saca_totales_transfer_unidadesvyb_nf($array_detalle_periodo3);
			
			//consulta el detalle de pedidos transfer del periodo fecha ingresada
			$array_detalle_periodo3_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta']);
			$array_totales_periodo3_importe_transfer = saca_totales_transfer_nf($array_detalle_periodo3_transfer);
			$array_totales_periodo3_unidades_transfer = saca_totales_transfer_unidadesvyb_nf($array_detalle_periodo3_transfer);	
			
			//informacion dia a dia facturado para armar el array_3er_periodo
			$data_vta_todo = data_transfer_todo($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_vta_todo); $i++){
				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['axunid'];
				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['axunid'];
			}			
			
			//informacion dia a dia transfer para armar el array_2do_periodo
			$data_trf_todo = data_transfer_todo($_POST['mywhere_transfer'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_trf_todo); $i++){
				$data_para_barra_transfer[$i] = $data_trf_todo[$i]['aximpo'];
				$data_para_barra_unidades_transfer[$i] = $data_trf_todo[$i]['axunid'];
				$data_para_datatable_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_transfer[$i]['totalx'] = $data_trf_todo[$i]['aximpo'];
				$data_para_datatable_unidades_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_unidades_transfer[$i]['totalx'] = $data_trf_todo[$i]['axunid'];
			}			
			
			// array_detalle_periodo3 es el vector de facturacion de la segunda comparativa con los datos para armar el grafico y la tabla detras del grafico
			$array_3er_periodo = array('data_para_barra' => $data_para_barra,
											'data_para_barra_unidades' => $data_para_barra_unidades,
											'data_para_datatable' => $data_para_datatable,
											'data_para_datatable_unidades' => $data_para_datatable_unidades,
											'array_totales_periodo3_importe' => $array_totales_periodo3_importe,
											'array_totales_periodo3_unidades' => $array_totales_periodo3_unidades,
									   		'data_para_barra_transfer' => $data_para_barra_transfer,
											'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
											'data_para_datatable_transfer' => $data_para_datatable_transfer,
											'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
											'array_totales_periodo3_importe_transfer' => $array_totales_periodo3_importe_transfer,
											'array_totales_periodo3_unidades_transfer' => $array_totales_periodo3_unidades_transfer);									   
		}else{
			$array_3er_periodo = 0;
			$array_detalle_periodo3 = 0;
			$array_totales_periodo3_importe = 0;
			$array_totales_periodo3_unidades = 0;			
		}		
		//verifica si tiene comparativa intermensual
		if($_POST['comparativames_desde'] != 0){
		
			//consulta el detalle de venta del fecha intermensual ingresada
			$array_detalle_intermensual = det_trf_seleccion_nf($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta']);
			$array_totales_intermensual_importe = saca_totales_transfer_nf($array_detalle_intermensual);
			$array_totales_intermensual_unidades = saca_totales_transfer_unidadesvyb_nf($array_detalle_intermensual);
			
			//consulta el detalle de pedidos transfer fecha ingresada intermensual
			$array_detalle_intermensual_transfer = det_trf_seleccion_nf($_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta']);
			$array_totales_intermensual_importe_transfer = saca_totales_transfer_nf($array_detalle_intermensual_transfer);
			$array_totales_intermensual_unidades_transfer = saca_totales_transfer_unidadesvyb_nf($array_detalle_intermensual_transfer);		
		
			//informacion dia a dia facturado para armar el array_intermensual
			$data_vta_todo = data_transfer_todo($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_vta_todo); $i++){
				$data_para_barra[$i] = $data_vta_todo[$i]['aximpo'];
				$data_para_barra_unidades[$i] = $data_vta_todo[$i]['axunid'];
				$data_para_datatable[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable[$i]['totalx'] = $data_vta_todo[$i]['aximpo'];
				$data_para_datatable_unidades[$i]['fecha'] = $data_vta_todo[$i]['fecha'];
				$data_para_datatable_unidades[$i]['totalx'] = $data_vta_todo[$i]['axunid'];
			}
			
			//informacion dia a dia transfer para armar el array_intermensual
			$data_trf_todo = data_transfer_todo($_POST['mywhere_transfer'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha']);
			for($i=0; $i<count($data_trf_todo); $i++){
				$data_para_barra_transfer[$i] = $data_trf_todo[$i]['aximpo'];
				$data_para_barra_unidades_transfer[$i] = $data_trf_todo[$i]['axunid'];
				$data_para_datatable_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_transfer[$i]['totalx'] = $data_trf_todo[$i]['aximpo'];
				$data_para_datatable_unidades_transfer[$i]['fecha'] = $data_trf_todo[$i]['fecha'];
				$data_para_datatable_unidades_transfer[$i]['totalx'] = $data_trf_todo[$i]['axunid'];
			}

			$array_intermensual = array('data_para_barra' => $data_para_barra,
											'data_para_barra_unidades' => $data_para_barra_unidades,
											'data_para_datatable' => $data_para_datatable,
											'data_para_datatable_unidades' => $data_para_datatable_unidades,
											'array_totales_intermensual_importe' => $array_totales_intermensual_importe,
											'array_totales_intermensual_unidades' => $array_totales_intermensual_unidades,
									   		'data_para_barra_transfer' => $data_para_barra_transfer,
											'data_para_barra_unidades_transfer' => $data_para_barra_unidades_transfer,
											'data_para_datatable_transfer' => $data_para_datatable_transfer,
											'data_para_datatable_unidades_transfer' => $data_para_datatable_unidades_transfer,
											'array_totales_intermensual_importe_transfer' => $array_totales_intermensual_importe_transfer,
//											'array_detalle_intermensual' => $array_detalle_intermensual,
											'array_totales_intermensual_unidades_transfer' => $array_totales_intermensual_unidades_transfer);
		}else{
			$array_intermensual = 0;
		}
		
		
		//sigo recopilando datos
		
		//agrupo nombres de subcanales jefes vendedores productos clientes de los tres periodos consultados en caso de que tengan datos 
//		$grupo_jefes = agrupa_nombres($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], 'JefNom');
//		$grupo_vendedores = agrupa_nombres($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], 'VdorTxt');
//		$grupo_productos = agrupa_nombres($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], 'PrdTxt');
//		$grupo_clientes = agrupa_nombres($array_detalle_periodo1, $array_detalle_periodo2, $array_detalle_periodo3, $_POST['comparativa1_desde'], $_POST['comparativa2_desde'], 'CliNomRed');
		$grupo_jefes = agrupa_nombres21($array_detalle_periodo1, 
											$array_detalle_periodo2, 
											$array_detalle_periodo3, 
											$array_detalle_intermensual, 
											$array_detalle_periodo1_transfer, 
											$array_detalle_periodo2_transfer, 
											$array_detalle_periodo3_transfer, 
											$array_detalle_intermensual_transfer, 											
											$_POST['comparativa1_desde'], 
											$_POST['comparativa2_desde'], 
											$_POST['comparativames_desde'], 
											'JefNom',
											'JefNom');
		$grupo_vendedores = agrupa_nombres21($array_detalle_periodo1, 
											$array_detalle_periodo2, 
											$array_detalle_periodo3, 
											$array_detalle_intermensual, 
											$array_detalle_periodo1_transfer, 
											$array_detalle_periodo2_transfer, 
											$array_detalle_periodo3_transfer, 
											$array_detalle_intermensual_transfer, 											
											$_POST['comparativa1_desde'], 
											$_POST['comparativa2_desde'], 
											$_POST['comparativames_desde'], 
											'VdorTxt',
											'VdorTxt');
		$grupo_productos = agrupa_nombres21($array_detalle_periodo1, 
											$array_detalle_periodo2, 
											$array_detalle_periodo3, 
											$array_detalle_intermensual, 
											$array_detalle_periodo1_transfer, 
											$array_detalle_periodo2_transfer, 
											$array_detalle_periodo3_transfer, 
											$array_detalle_intermensual_transfer, 											
											$_POST['comparativa1_desde'], 
											$_POST['comparativa2_desde'], 
											$_POST['comparativames_desde'], 
											'PrdTxt',
											'PrdTxt');		

		$grupo_clientes = agrupa_nombres21($array_detalle_periodo1, 
											$array_detalle_periodo2, 
											$array_detalle_periodo3, 
											$array_detalle_intermensual, 
											$array_detalle_periodo1_transfer, 
											$array_detalle_periodo2_transfer, 
											$array_detalle_periodo3_transfer, 
											$array_detalle_intermensual_transfer, 											
											$_POST['comparativa1_desde'], 
											$_POST['comparativa2_desde'], 
											$_POST['comparativames_desde'], 
											'CliNomRed',
											'CliNomRed');			
		
		//***********************************************************************************************************************************
		//***********************************************************************************************************************************
		//si el subcanal seleccionado es 99 - Transfer, modifico la tematica de las barras ya que transfer no tiene canal ni subcanal de venta
		//***********************************************************************************************************************************
		//***********************************************************************************************************************************
		
		//*************************** DATOS JEFES ************************
		//captura JEFES del periodo
		$periodo1_jefes = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'JefNom', 'data_para_barra');
		$periodo2_jefes = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'JefNom', 'data_para_barra');
		$periodo3_jefes = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'JefNom', 'data_para_barra');
		$periodomes_jefes = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'JefNom', 'data_para_barra');
		
		//compara periodo1 con el agrupado 
		$periodo1_jefes = compara_grupo_periodo1($periodo1_jefes, $grupo_jefes);

		//lo ordena
		$periodo1_jefes = ordena_array_multi_x_campo($periodo1_jefes, 'totalimp', 'desc');			
		
		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		$periodo2_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo2_jefes);
		$periodo3_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodo3_jefes);
		$periodomes_jefes = ordena_array_segun_myarray1_x_campo($periodo1_jefes, $periodomes_jefes);		

		// array de Jefes
		$jefes = array('grupo_jefes' => $grupo_jefes, 
							'periodo1_jefes' => $periodo1_jefes,
							'periodo2_jefes' => $periodo2_jefes,
							'periodo3_jefes' => $periodo3_jefes,
							'periodomes_jefes' => $periodomes_jefes);
		//*************************** FIN DATOS JEFES **********************	
		

		//*************************** DATOS VENDEDORES ************************
		//captura vendedores del periodo
		$periodo1_vendedores = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'VdorTxt', 'data_para_barra');
		$periodo2_vendedores = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'VdorTxt', 'data_para_barra');
		$periodo3_vendedores = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'VdorTxt', 'data_para_barra');
		$periodomes_vendedores = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'VdorTxt', 'data_para_barra');

		//compara periodo1 con el agrupado 
		$periodo1_vendedores = compara_grupo_periodo1($periodo1_vendedores, $grupo_vendedores);

		//lo ordena
		$periodo1_vendedores = ordena_array_multi_x_campo($periodo1_vendedores, 'totalimp', 'desc');			
		
		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		$periodo2_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo2_vendedores);
		$periodo3_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodo3_vendedores);
		$periodomes_vendedores = ordena_array_segun_myarray1_x_campo($periodo1_vendedores, $periodomes_vendedores);
 
		// array de vendedores
		$vendedores = array('grupo_vendedores' => $grupo_vendedores,
							'periodo1_vendedores' => $periodo1_vendedores,
							'periodo2_vendedores' => $periodo2_vendedores,
							'periodo3_vendedores' => $periodo3_vendedores,
							'periodomes_vendedores' => $periodomes_vendedores
						   );
		//*************************** FIN DATOS VENDEDORES ************************

		//*************************** DATOS PRODUCTOS ************************
		//captura productos del periodo
		$periodo1_productos = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra');
		$periodo2_productos = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra');
		$periodo3_productos = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra');
		$periodomes_productos = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra');

		//compara periodo1 con el agrupado 
		$periodo1_productos = compara_grupo_periodo1($periodo1_productos, $grupo_productos);

		//lo ordena
		$periodo1_productos = ordena_array_multi_x_campo($periodo1_productos, 'totalimp', 'desc');		
		
		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		$periodo2_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo2_productos);
		$periodo3_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodo3_productos);
		$periodomes_productos = ordena_array_segun_myarray1_x_campo($periodo1_productos, $periodomes_productos);

		// array de productos
		$productos = array('grupo_productos' => $grupo_productos,
							'periodo1_productos' => $periodo1_productos,
							'periodo2_productos' => $periodo2_productos,
							'periodo3_productos' => $periodo3_productos,
							'periodomes_productos' => $periodomes_productos,
						  );
		//*************************** FIN DATOS PRODUCTOS ************************

		//*************************** DATOS CLIENTES ************************
		//captura clientes del periodo
		$periodo1_clientes = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['informacion_fecha'], 'CliNomRed', 'data_para_barra');
		$periodo2_clientes = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['comparativa1_desde'], $_POST['comparativa1_hasta'], $_POST['informacion_fecha'], 'CliNomRed', 'data_para_barra');
		$periodo3_clientes = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['comparativa2_desde'], $_POST['comparativa2_hasta'], $_POST['informacion_fecha'], 'CliNomRed', 'data_para_barra');
		$periodomes_clientes = datos_canvas_datatable_transfer($_POST['mywhere'], $_POST['comparativames_desde'], $_POST['comparativames_hasta'], $_POST['informacion_fecha'], 'PrdTxt', 'data_para_barra');
		
		//compara periodo1 con el agrupado 
		$periodo1_clientes = compara_grupo_periodo1($periodo1_clientes, $grupo_clientes);

		//lo ordena
		$periodo1_clientes = ordena_array_multi_x_campo($periodo1_clientes, 'totalimp', 'desc');			
		
		//ordena periodo2 y periodo3 con el ordenamiento por 'campo' del periodo1
		$periodo2_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo2_clientes);
		$periodo3_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodo3_clientes);
		$periodomes_clientes = ordena_array_segun_myarray1_x_campo($periodo1_clientes, $periodomes_clientes);
		
		// array de clientes
		$clientes = array('grupo_clientes' => $grupo_clientes,
							'periodo1_clientes' => $periodo1_clientes,
							'periodo2_clientes' => $periodo2_clientes,
							'periodo3_clientes' => $periodo3_clientes,
							'periodomes_clientes' => $periodomes_clientes
						 );
		//*************************** FIN DATOS CLIENTES ************************	

		if($array_detalle_periodo1 == 0 && $array_detalle_periodo1_transfer == 0){
			echo json_encode(0); 
		}else{
			header('Content-Type: application/json');
			echo json_encode(array('array_1er_periodo' => $array_1er_periodo,
								   'subcanales' => $subcanales,
								   'canales' => $canales,
								   'jefes' => $jefes,
								   'vendedores' => $vendedores,
								   'productos' => $productos,
								   'clientes' => $clientes,
								   'array_2do_periodo' => $array_2do_periodo,
								   'array_3er_periodo' => $array_3er_periodo,
								   'array_intermensual' => $array_intermensual
								  ));			
		}		
	break;		
		
	//----------------------------------------------------------------------------------------
	case 'total_venta_un_dia_con_seleccion_fecha':
		$hoyman = fecha_param_mas_x($_POST['fecha'], 1);
		$desde = $hoyman['hoy'];
		$hasta = $hoyman['manana'];
		$rol = $_POST['rol_usuario'];
		$codigo = $_POST['codigo_usuario']; 
		
		$array_detalle = total_dia($desde, $hasta, $rol, $codigo);
		$array_color = colores(); 
		if($array_detalle == 0){ 
			echo json_encode($array_detalle); 
		}else{
//			echo json_encode($array_detalle); 
			//saca los totales de todo en funciones.php que totaliza el detalle capturado desde sql
			//tambien saca los porcentajes por eso paso como parametro  $array_totales_importe[0]['total'] en algunos casos
			//tambien recolecta los colores los colores son repetidos iguales en todos los graficos
			$array_totales_importe = saca_totales_facturacion_nf($array_detalle); //ok
			$array_totales_unidades = saca_totales_facturacion_unidadesvyb_nf($array_detalle); //ok
			
			//cuando hay solo una nota de credito este total es cero 0 por eso en la funcion si hay notas de credito traigo los valores en cero esto solo pasa
			//cuando se inicia el dia con notas de credito y no hay facturacion aun
			$array_totales_canales_imp = saca_totales_canales_imp($array_detalle, $array_totales_importe[0]['total'], $array_color); //ver
			$array_totales_canales_uni = saca_totales_canales_uni($array_detalle, $array_totales_unidades[0]['total'], $array_color); //ver
			$array_totyporc_subcanales_imp_uni = saca_totporc_subcanales_imp_uni($array_detalle, $array_totales_importe[0]['total'], $array_totales_unidades[0]['total'], $array_color); 
			$array_totales_jefes = saca_totales_jefes($array_detalle);
			$array_totales_subcanales = saca_totales_subcanales($array_detalle);
			$array_totales_vendedores = saca_totales_vendedores($array_detalle);
			$array_totales_productos_imp = saca_totales_productos_imp($array_detalle);
			$array_totales_productos_uni = saca_totales_productos_uni($array_detalle);
			$array_totales_clientes = saca_totales_clientes($array_detalle);
			
			//ordena array
			$array_totales_productos_imp = ordena_unacolumna_array_desc($array_totales_productos_imp, 'total');
			$array_totales_productos_uni = ordena_unacolumna_array_desc($array_totales_productos_uni, 'total');
			$array_totyporc_subcanales_imp_uni = ordena_array_multi_x_campo($array_totyporc_subcanales_imp_uni, 'totalimp', 'desc');

//			header('Content-Type: application/json');
			echo json_encode(array('array_totales_canales_imp' => $array_totales_canales_imp,
								   'array_totales_canales_uni' => $array_totales_canales_uni,
								   'array_totales_subcanales' => $array_totales_subcanales,
								   'hoyman' => $hoyman,
								   
								   'array_totyporc_subcanales_imp_uni' => $array_totyporc_subcanales_imp_uni,
//								   'array_totales_subcanales_imp' => $array_totales_subcanales_imp,
//								   'array_totales_subcanales_uni' => $array_totales_subcanales_uni,
								   'hoy' => $hoyman['hoy'],
								   'manana' => $hoyman['manana'],
								   'array_totales_jefes' => $array_totales_jefes,
								   'array_totales_vendedores' => $array_totales_vendedores,
								   'array_totales_productos_imp' => $array_totales_productos_imp,
								   'array_totales_productos_uni' => $array_totales_productos_uni,
								   'array_totales_clientes' => $array_totales_clientes,
								   'array_totales_importe' => $array_totales_importe,
								   'array_totales_unidades' => $array_totales_unidades,
								   'array_detalle' => $array_detalle ));
		}		
	break;			
//	case 'total_venta_un_dia_con_seleccion_fecha':
//		$hoyman = fecha_param_mas_x($_POST['fecha'], 1);
//		$desde = $hoyman['hoy'];
//		$hasta = $hoyman['manana'];
//		$rol = $_POST['rol_usuario'];
//		$codigo = $_POST['codigo_usuario'];
//		
//		$array_detalle = total_dia($desde, $hasta, $rol, $codigo);
//		$array_color = colores(); 
//		if($array_detalle == 0){
//			echo json_encode($array_detalle); 
//		}else{
//			//saca los totales de todo en funciones.php que totaliza el detalle capturado desde mysql
//			//tambien saca los porcentajes por eso paso como parametro  $array_totales_importe[0]['total'] en algunos casos
//			//tambien recolecta los colores los colores son repetidos iguales en todos los graficos
//			$array_totales_importe = saca_totales_facturacion_nf($array_detalle);
//			$array_totales_unidades = saca_totales_facturacion_unidadesvyb_nf($array_detalle);
//			$array_totales_canales_imp = saca_totales_canales_imp($array_detalle, $array_totales_importe[0]['total'], $array_color); 
//			$array_totales_canales_uni = saca_totales_canales_uni($array_detalle, $array_totales_unidades[0]['total'], $array_color);
//			$array_totyporc_subcanales_imp_uni = saca_totporc_subcanales_imp_uni($array_detalle, $array_totales_importe[0]['total'], $array_totales_unidades[0]['total'], $array_color); 
//			$array_totales_jefes = saca_totales_jefes($array_detalle);
//			$array_totales_subcanales = saca_totales_subcanales($array_detalle);
//			$array_totales_vendedores = saca_totales_vendedores($array_detalle);
//			$array_totales_productos_imp = saca_totales_productos_imp($array_detalle);
//			$array_totales_productos_uni = saca_totales_productos_uni($array_detalle);
//			$array_totales_clientes = saca_totales_clientes($array_detalle);
//			
//			//ordena array
//			$array_totales_productos_imp = ordena_unacolumna_array_desc($array_totales_productos_imp, 'total');
//			$array_totales_productos_uni = ordena_unacolumna_array_desc($array_totales_productos_uni, 'total');
//			$array_totyporc_subcanales_imp_uni = ordena_array_multi_x_campo($array_totyporc_subcanales_imp_uni, 'totalimp', 'desc');
//
////			header('Content-Type: application/json');
//			echo json_encode(array('array_totales_canales_imp' => $array_totales_canales_imp,
//								   'array_totales_canales_uni' => $array_totales_canales_uni,
//								   'array_totales_subcanales' => $array_totales_subcanales,
//								   
//								   'array_totyporc_subcanales_imp_uni' => $array_totyporc_subcanales_imp_uni,
////								   'array_totales_subcanales_imp' => $array_totales_subcanales_imp,
////								   'array_totales_subcanales_uni' => $array_totales_subcanales_uni,
//								   'hoy' => $hoyman['hoy'],
//								   'manana' => $hoyman['manana'],
//								   'array_totales_jefes' => $array_totales_jefes,
//								   'array_totales_vendedores' => $array_totales_vendedores,
//								   'array_totales_productos_imp' => $array_totales_productos_imp,
//								   'array_totales_productos_uni' => $array_totales_productos_uni,
//								   'array_totales_clientes' => $array_totales_clientes,
//								   'array_totales_importe' => $array_totales_importe,
//								   'array_totales_unidades' => $array_totales_unidades,
//								   'array_detalle' => $array_detalle ));
//		}		
//	break;	
		
	//----------------------------------------------------------------------------------------
	case 'total_venta_un_dia':
		$hoyman = fecha_mas_x(1);
		$desde = $hoyman['hoy'];
		$hasta = $hoyman['manana'];
		$rol = $_POST['rol_usuario'];
		$codigo = $_POST['codigo_usuario'];
		
		$array_detalle = total_dia($desde, $hasta, $rol, $codigo);
//		$array_detalle = 0;
//		$array_detalle = total_dia('2020-05-13', '2020-05-13'); //`prueba
//		$array_detalle = saco_impuestos($array_detalle);
		$array_color = colores(); 
//		echo '<pre>';
//		print_r($array_color);
//		echo '<pre>';
		if($array_detalle == 0){
//			echo json_encode($desde.' '.$hasta); 
			echo json_encode($array_detalle); 
		}else{
			//saca los totales de todo en funciones.php que totaliza el detalle capturado desde mysql
			//tambien saca los porcentajes por eso paso como parametro  $array_totales_importe[0]['total'] en algunos casos
			//tambien recolecta los colores los colores son repetidos iguales en todos los graficos
			$array_totales_importe = saca_totales_facturacion_nf($array_detalle);
			$array_totales_unidades = saca_totales_facturacion_unidadesvyb_nf($array_detalle);
			$array_totales_canales_imp = saca_totales_canales_imp($array_detalle, $array_totales_importe[0]['total'], $array_color); 
			$array_totales_canales_uni = saca_totales_canales_uni($array_detalle, $array_totales_unidades[0]['total'], $array_color);
//			$array_totales_subcanales_imp = saca_totales_subcanales_imp($array_detalle, $array_totales_importe[0]['total'], $array_color); 
			$array_totyporc_subcanales_imp_uni = saca_totporc_subcanales_imp_uni($array_detalle, $array_totales_importe[0]['total'], $array_totales_unidades[0]['total'], $array_color); 

//			$array_totales_subcanales_uni = saca_totales_subcanales_uni($array_detalle, $array_totales_unidades[0]['total'], $array_color); 
			$array_totales_jefes = saca_totales_jefes($array_detalle);
			$array_totales_subcanales = saca_totales_subcanales($array_detalle);
			$array_totales_vendedores = saca_totales_vendedores($array_detalle);
			$array_totales_productos_imp = saca_totales_productos_imp($array_detalle);
			$array_totales_productos_uni = saca_totales_productos_uni($array_detalle);
			$array_totales_clientes = saca_totales_clientes($array_detalle);

			

			
			//ordena array
			$array_totales_productos_imp = ordena_unacolumna_array_desc($array_totales_productos_imp, 'total');
			$array_totales_productos_uni = ordena_unacolumna_array_desc($array_totales_productos_uni, 'total');
//			$array_totales_subcanales_imp2 = ordena_unacolumna_array_desc($array_totales_subcanales_imp, 'total');

//			$array_totales_subcanales_imp = ordena_array_multi_x_campo($array_totales_subcanales_imp, 'total', 'desc');
			$array_totyporc_subcanales_imp_uni = ordena_array_multi_x_campo($array_totyporc_subcanales_imp_uni, 'totalimp', 'desc');

//			header('Content-Type: application/json');
			echo json_encode(array('array_totales_canales_imp' => $array_totales_canales_imp,
								   'array_totales_canales_uni' => $array_totales_canales_uni,
								   'array_totales_subcanales' => $array_totales_subcanales,
								   
								   'array_totyporc_subcanales_imp_uni' => $array_totyporc_subcanales_imp_uni,
//								   'array_totales_subcanales_imp' => $array_totales_subcanales_imp,
//								   'array_totales_subcanales_uni' => $array_totales_subcanales_uni,
//								   'hoy' => $hoyman['hoy'],
//								   'manana' => $hoyman['manana'],
								   'array_totales_jefes' => $array_totales_jefes,
								   'array_totales_vendedores' => $array_totales_vendedores,
								   'array_totales_productos_imp' => $array_totales_productos_imp,
								   'array_totales_productos_uni' => $array_totales_productos_uni,
								   'array_totales_clientes' => $array_totales_clientes,
								   'array_totales_importe' => $array_totales_importe,
								   'array_totales_unidades' => $array_totales_unidades,
								   'array_detalle' => $array_detalle ));
		}		
	break;		
	//----------------------------------------------------------------------------------------
	case 'los_jefes':
		$array_jefes = lista_jefes_rapida($_POST['codigo']);
		echo json_encode($array_jefes);
	break;	
	//----------------------------------------------------------------------------------------
	case 'los_vendedores':
		$array_vendedores = lista_vendedores_rapida($_POST['codigo']);
		echo json_encode($array_vendedores);
	break;	
	//----------------------------------------------------------------------------------------
	case 'los_canales':
		$array_canales = lista_canales_rapida($_POST['codigo']);
		echo json_encode($array_canales);
	break;
	//----------------------------------------------------------------------------------------
	case 'los_subcanales':
		$array_subcanales = lista_subcanales_rapida($_POST['codigo']);
		echo json_encode($array_subcanales);
	break;
	//----------------------------------------------------------------------------------------
	case 'los_productos':
		//creo temporal de productos en la primera consulta del dia y despues solo pregunto si la fecha coincide sino tiene que recorrer todo estad y se tarda Mod. 14/01/2021
		$fecha_hoy = date('Y-m-d');
		$array_producto = lista_productos_super_rapida($_POST['codigo']);
		// if($array_producto != 0){
		// 	if($array_producto[0]['fecha'] < $fecha_hoy){
		// 		//captura de estad y guarda en tmp_producto para hacer la super rapida
		// 		$array_producto_estad = lista_productos_rapida($_POST['codigo']);
		// 		if($array_producto_estad != 0){
		// 			//trunco tabla tmp_producto
		// 			truncate_tmp_producto();
		// 			//insert en tmp_producto			
		// 			for($z=0; $z<count($array_producto_estad); $z++){
		// 				insert_tmp_producto($array_producto_estad[$z]['FacPrdId'], $array_producto_estad[$z]['PrdTxt'], $array_producto_estad[$z]['tpovtajefe'], $fecha_hoy);
		// 			}
		// 			$array_producto = $array_producto_estad;
		// 		}
		// 	}	
		// }else{
		// 	//captura de estad y guarda en tmp_producto para hacer la super rapida
		// 	$array_producto_estad = lista_productos_rapida($_POST['codigo']);
		// 	if($array_producto_estad != 0){
		// 		//insert en tmp_producto			
		// 		for($z=0; $z<count($array_producto_estad); $z++){
		// 			insert_tmp_producto($array_producto_estad[$z]['FacPrdId'], $array_producto_estad[$z]['PrdTxt'], $array_producto_estad[$z]['tpovtajefe'], $fecha_hoy);
		// 		}
		// 		$array_producto = $array_producto_estad;
		// 	}			 
		// }
		echo json_encode($array_producto);
	break;
	//----------------------------------------------------------------------------------------
	case 'ok_marca_recibo':
		$array_recibo_fin = update_marca_recibo_ok($_POST['recibo_nro'], $_POST['estado']);
		echo json_encode($array_recibo_fin);
	break;		
	//----------------------------------------------------------------------------------------
	case 'edita_marca_recibo':
		$array_recibo_fin = update_marca_recibo($_POST['recibo_nro'], $_POST['observaciones'], $_POST['estado']);
		echo json_encode($array_recibo_fin);
	break;		
	//----------------------------------------------------------------------------------------
	case 'select_detalle_recibo':
		$array_recibo_det = select_detalle_recibo($_POST['recibo_nro']);
		echo json_encode($array_recibo_det);
	break;	
	//----------------------------------------------------------------------------------------
	case 'select_detalle_pagos':
		$array_recibo_pag = select_detalle_pagos($_POST['recibo_nro']);
		echo json_encode($array_recibo_pag);
	break;			
	//----------------------------------------------------------------------------------------
	case 'guarda_detalle_pagos':
		$array_rec_pag = guarda_detalle_pagos($_POST['recibo_nro'], 
											   $_POST['tipo_pago'],
											   $_POST['banco'],
											   $_POST['numero'],
											   $_POST['fecha_pago'],
											   $_POST['importe'],
											   $_POST['tpo']
											  );
		echo json_encode($array_rec_pag);
	break;				
	//----------------------------------------------------------------------------------------
	case 'guarda_detalle_recibo':
		$array_rec_det = guarda_detalle_recibo($_POST['recibo_nro'], 
											   $_POST['tipo_comprobante'],
											   $_POST['tpo'],
											   $_POST['serie'],
											   $_POST['numero'],
											   $_POST['fecha_comprobante'],
											   $_POST['importe']
											  );
		echo json_encode($array_rec_det);
	break;
	//----------------------------------------------------------------------------------------
	case 'elimina_detalle_recibo':
		$array_rec_del = delete_detalle_recibo( $_POST['id_det']);
		echo json_encode($array_rec_del);
	break;	
	//----------------------------------------------------------------------------------------
	case 'elimina_detalle_pagos':
		$array_rec_del = delete_detalle_pagos( $_POST['id_det']);
		echo json_encode($array_rec_del);
	break;	
	//----------------------------------------------------------------------------------------
	case 'elimina_recibo':
		$array_rec_del = delete_recibo( $_POST['id_det']);
		echo json_encode($array_rec_del);
	break;			
	//----------------------------------------------------------------------------------------
	case 'edita_detalle_recibo':
		$array_rec_det = update_detalle_recibo( $_POST['id_det'],
											   $_POST['fecha_comprobante'],
											   $_POST['importe'],
											   $_POST['numero']
											  );
		echo json_encode($array_rec_det);
	break;
	//----------------------------------------------------------------------------------------
	case 'edita_detalle_pagos':
		$array_rec_det = update_detalle_pagos( $_POST['id_det'],
											   $_POST['banco'],
											   $_POST['numero'],
											   $_POST['fecha'],
											   $_POST['importe']
											  );
		echo json_encode($array_rec_det);
	break;		
	//----------------------------------------------------------------------------------------
	case 'guarda_cabecera_recibo':
		$array_rec_cab = guarda_cabecera_recibo($_POST['vendedor_nom'],  $_POST['vendedor_cod'], $_POST['cliente_nom'], $_POST['cliente_cod']);
		echo json_encode($array_rec_cab);
	break;		
	//----------------------------------------------------------------------------------------
	case 'factura_numero':
		$array_fac_nro = factura_numero($_POST['factura']);
		echo json_encode($array_fac_nro);
	break;			
	//----------------------------------------------------------------------------------------
	case 'factura_clientes':
		$array_fac_cli = factura_clientes($_POST['cliente'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		echo json_encode($array_fac_cli);
	break;			
	//----------------------------------------------------------------------------------------
	case 'los_clientes':
		$array_clientes = lista_clientes_rapida($_POST['codigo'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		echo json_encode($array_clientes);
	break;		
	//----------------------------------------------------------------------------------------
	case 'el_stock':
		$array_stock = stock();
		echo json_encode($array_stock);
	break;		
	//----------------------------------------------------------------------------------------
	case 'los_dias_stock_a_fecha':
		$array_stock = dias_stock_a_fecha();
		echo json_encode($array_stock);
	break;			
	//----------------------------------------------------------------------------------------	
	case 'el_stock_a_fecha':
		$array_stock_a_fecha = stock_a_fecha($_POST['fecha']);
		echo json_encode($array_stock_a_fecha);
	break;		
	//----------------------------------------------------------------------------------------
	case 'lista_canales_rapida':
		$array_canales = lista_canales_rapida();
//		$_SESSION['usuario_temporal'] = $_POST['usuario_temporal'];
//		$_SESSION['password_temporal'] = $_POST['password_temporal'];
		echo json_encode($array_canales);
	break;	
	case 'verifico_temporales':
//		$_SESSION['usuario_temporal'] = $_POST['usuario_temporal'];
//		$_SESSION['password_temporal'] = $_POST['password_temporal'];
		echo json_encode($_SESSION);
	break;	
	case 'genera_session_de_recuperacion':
		unset($_SESSION['nombre']);
		unset($_SESSION['usuario']);
		unset($_SESSION['rol']);
		$_SESSION['usuario_temporal'] = $_POST['usuario_temporal'];
		$_SESSION['password_temporal'] = $_POST['password_temporal'];
		echo json_encode('ok');
	break;	
	case 'update_usuario':
		$array_update = update_usuario($_POST['usuario'], $_POST['password'], $_POST['perdio']);
		echo json_encode($array_update);
	break;	
	case 'encripta_md5':
		$nuevopass = md5($_POST['password']);
		echo json_encode($nuevopass);
	break;	
	case 'genera_pass':
		$pass_temporal = genera_pass();
		echo json_encode($pass_temporal);
	break;	
	case 'envia_mail_sendinblue':
		$html = cuerpo_mensaje($_POST['cuerpo']);
		$id_sendinblue = envia_mail_sendinblue($_POST['nombre'], $_POST['mail'], $html, $_POST['asunto']);
		echo json_encode($id_sendinblue);
	break;		
	case 'verifica_usuario':
		$usuario_ok = verifica_usuario($_POST['usuario']);
		echo json_encode($usuario_ok);
	break;		
	case 'jefe_sus_datos':
		$array_jefe_sus_datos = jefe_sus_datos($_POST['descripcion']);
		echo json_encode($array_jefe_sus_datos);
	break;			
	case 'vendedor_codigo':
		$array_vendedor = vendedor_codigo($_POST['descripcion']);
		echo json_encode($array_vendedor);
	break;			
	case 'vendedor_nombre':
		$array_vendedor = vendedor_nombre($_POST['codigo']);
		echo json_encode($array_vendedor);
	break;			
	case 'jefe_codigo':
		$array_jefe = jefe_codigo($_POST['descripcion']);
		echo json_encode($array_jefe);
	break;			
	case 'jefe_nombre':
		$array_jefe = jefe_nombre($_POST['codigo']);
		echo json_encode($array_jefe);
	break;			
	case 'subcanal_codigo':
		$array_subcanal = subcanal_codigo($_POST['descripcion']);
		echo json_encode($array_subcanal);
	break;			
	case 'subcanal_nombre':
		$array_subcanal = subcanal_nombre($_POST['codigo']);
		echo json_encode($array_subcanal);
	break;		
	case 'canal_codigo':
		$array_canal = canal_codigo($_POST['descripcion']);
		echo json_encode($array_canal);
	break;			
	case 'canal_nombre':
		$array_canal = canal_nombre($_POST['codigo']);
		echo json_encode($array_canal);
	break;			
	case 'cliente_nombre':
		$array_cliente = cliente_nombre($_POST['codigo'], $_POST['clitpo']);
		echo json_encode($array_cliente);
	break;	
	case 'cliente_codigo':
		$array_cliente = cliente_codigo($_POST['descripcion'], $_POST['clitpo']);
		echo json_encode($array_cliente);
	break;		
	case 'producto_nombre':
		$array_producto = producto_nombre($_POST['codigo']);
		echo json_encode($array_producto);
	break;			
	case 'producto_codigo':
		$array_producto = producto_codigo($_POST['descripcion']);
		echo json_encode($array_producto);
	break;	
	case 'usuario_activo':
		$array_usuario_activo = $_SESSION['usuario'];
		echo json_encode($array_usuario_activo);
	break;	
	case 'rol_usuario':
		$array_rol_usuario = rol_usu($_POST['usuario']);
		echo json_encode($array_rol_usuario);
	break;			
	case 'elimina_campos_array':
		//este proceso elimina todas las columnas => campos MENOS el campo que le envio por parametro en $_POST['campo']
		$new_array = elimina_campos_array($_POST['miarray'], $_POST['campo']);
		echo json_encode($new_array);
	break;			
		
	case 'valida_usuario':

		$array_validado = valida_usuario($_POST['usuario'], $_POST['clave']);

		
//    [0] => Array
//        (
//            [id] => 1
//            [usuario] => bandino
//            [clave] => 21232f297a57a5a743894a0e4a801fc3
//            [mail] => bandino@richet.com
//            [id_rol] => 0
//            [habilitado] => 
//        )		
		
		echo json_encode($array_validado);
	break;		
	case 'busca_des_id':
		$array_busqueda = busca_des_id($_POST['donde_busca'], $_POST['que_busca']);
		echo json_encode($array_busqueda);
	break;
	case 'ordena_array_desc':
		$array_ordenado = ordena_unacolumna_array_desc($_POST['array_a_ordenar'], $_POST['campo_a_ordenar']);
		
		echo json_encode($array_ordenado);
	break;
	case 'ordena_periodo_desc':
		$array_ordenado = ordena_periodo_array_desc($_POST['array_a_ordenar'], $_POST['campo_a_ordenar']);
		echo json_encode($array_ordenado);
	break;
	case 'retorna_array_ordenado_segun_periodo1':
		$array_ordenado = retorna_array_ordenado_segun_periodo1($_POST['periodo1'], $_POST['que_ordena'], $_POST['campo_a_ordenar'], $_POST['tipo_de_venta']);
		echo json_encode($array_ordenado);
	break;
		
		
	case 'ordena_segundo_array':
		$array2_ordenado = ordena_2_array_iguales($_POST['array_1'], $_POST['array_2']);
		echo json_encode($array2_ordenado);
	break;
		
		
	case 'ordena_array_asc':
		$array_ordenado = ordena_unacolumna_array($_POST['array_a_ordenar'], $_POST['campo_a_ordenar']);
		echo json_encode($array_ordenado);
	break;	
//	case 'porcentaje_jefes_total_periodo':
//		$array_porcentaje_jefes = porcentaje_jefe_sobre_total_periodo($_POST['codigo_jefe'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
//		echo json_encode($array_porcentaje_jefes);	
//	break;
//	case 'porcentaje_canales_total_periodo': 
//		$array_porcentaje_canales = porcentaje_canal_sobre_total_periodo($_POST['codigo_canal'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
//		echo json_encode($array_porcentaje_canales);	
//	break;
//	case 'porcentaje_subcanales_total_periodo': 
//		$array_porcentaje_subcanales = porcentaje_subcanal_sobre_total_periodo($_POST['codigo_subcanal'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
//		echo json_encode($array_porcentaje_subcanales);	
//	break;
//	case 'porcentaje_vendedores_total_periodo': 
//		$array_porcentaje_vendedores = porcentaje_vendedor_sobre_total_periodo($_POST['codigo_vendedor'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
//		echo json_encode($array_porcentaje_vendedores);	
//	break;
//	case 'porcentaje_productos_total_periodo': 
//		$array_porcentaje_productos = porcentaje_producto_sobre_total_periodo($_POST['codigo_producto'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
//		echo json_encode($array_porcentaje_productos);	
//	break;		
//	case 'porcentaje_clientes_total_periodo': 
//		$array_porcentaje_clientes = porcentaje_cliente_sobre_total_periodo($_POST['codigo_cliente'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['tipo_seleccion']);
//		echo json_encode($array_porcentaje_clientes);	
//	break;		
//	case 'porcentaje_transfer_total_periodo': 
//		$array_porcentaje_transfer = porcentaje_transfer_sobre_total_periodo($_POST['fecha_desde'], $_POST['fecha_hasta']);
//		echo json_encode($array_porcentaje_transfer);	
//	break;		
		
	case 'lista_jefes':
		$array_lista_jefes = lista_jefes($_POST['desde'], $_POST['hasta']);
		echo json_encode($array_lista_jefes);	
	break;
	case 'lista_canales':
		$array_lista_canales = lista_canales($_POST['desde'], $_POST['hasta']);
		echo json_encode($array_lista_canales);	
	break;	
	case 'lista_subcanales':
		$array_lista_subcanales = lista_subcanales($_POST['desde'], $_POST['hasta']);
		echo json_encode($array_lista_subcanales);	
	break;	
	case 'lista_vendedores':
		$array_lista_vendedores = lista_vendedores($_POST['desde'], $_POST['hasta']);
		echo json_encode($array_lista_vendedores);	
	break;
	case 'lista_productos':
		$array_lista_productos = lista_productos($_POST['desde'], $_POST['hasta']);
		echo json_encode($array_lista_productos);	
	break;
	case 'lista_clientes':
		$array_lista_clientes = lista_clientes($_POST['desde'], $_POST['hasta']);
		echo json_encode($array_lista_clientes);	
	break;		
	case 'select_ano_transfer':
		$array_anos_transfer = select_ano_transfer();
		echo json_encode($array_anos_transfer);	
	break;			
		
	case 'select_ano':
		$array_anos = select_ano();
		echo json_encode($array_anos);	
	break;	
	case 'select_ano_comparativa':
		$array_anos = select_ano_comparativa();
		echo json_encode($array_anos);	
	break;			
//	case 'detalle_venta_mes':
//		//$array_detalle_mes = jefes_detalles_codigo_desde_hasta_del_mes($_POST['codigo_jefe'], $_POST['desde'], $_POST['hasta']);
//		//divido el mes en 3 por que la funcion saco_impuestos no me deja procesar todos los registros
//		//venta_detalle_desde_hasta_del_mes($desde, $hasta)
//		$array_detalle_mes = venta_detalle_desde_hasta_del_mes($_POST['anio'].'-'.$_POST['mes'].'-01', $_POST['anio'].'-'.$_POST['mes'].'-20');
//		$array_detalle_mes = saco_impuestos($array_detalle_mes);
//		echo json_encode($array_detalle_mes);	
//		//echo json_encode($array_detalle_mes);	
//		
//	break;		
//	case 'total_x_jefe_del_mes':
//		//$array_jefes = jefes_totales_codigo_desde_hasta_del_mes($_POST['codigo_jefe'], $_POST['desde'], $_POST['hasta']);
//		$array_jefes = jefes_detalles_codigo_desde_hasta_del_mes($_POST['codigo_jefe'], $_POST['desde'], $_POST['hasta']);
//		$array_jefes = saco_impuestos($array_jefes);
//		$array_jefes_totales = saca_totales_jefes($array_jefes);
//		echo json_encode($array_jefes_totales);
//	break;
	case 'total_x_jefe_del_no_mes':
		$array_jefes = jefes_totales_codigo_desde_hasta_del_no_mes($_POST['codigo_jefe'], $_POST['desde'], $_POST['hasta']);
		echo json_encode($array_jefes);
	break;		
/*	case 'total_x_jefe_del_mes_a_mes':
		//$array_jefes = jefes_totales_codigo_desde_hasta_del_mes($_POST['codigo_jefe'], $_POST['desde'], $_POST['hasta']);
		$array_jefes = jefes_detalles_codigo_desde_hasta_del_mes_sin_impuestos($_POST['codigo_jefe'], $_POST['desde'], $_POST['hasta']);
		$array_jefes = saco_impuestos($array_jefes);
		$array_jefes_totales = saca_totales_jefes($array_jefes);
		echo json_encode($array_jefes_totales);
	break;*/	
		
//	case 'detalle_jefe_mes':
//		$array_jefes = jefes_detalle_codigo_por_mes($_POST['codigo_jefe'], $_POST['mes']);
//		$array_jefes = saco_impuestos($array_jefes);
//		echo json_encode($array_jefes);
//	break;	
	
		
		
	//richet.online	
//	case 'totales_jefe_fecha_desde_fecha_hasta':
//		//consulta el detalle de venta por importe por jefe en un periodo de tiempo
//		$array_jefes_detalle = jefes_detalle_codigo_por_mes($_POST['codigo_jefe'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
//		$array_jefes_detalle = saco_impuestos($array_jefes_detalle);
//		if($array_jefes_detalle == 0){
//			echo json_encode(0); 
//		}else{
//			//saca los totales de los jefes en con funcion.php que totaliza el detalle capturado desde mysql
//			$array_jefes_totales = saca_totales_jefes($array_jefes_detalle);
//			header('Content-Type: application/json');
//			//echo json_encode($array_jefes_detalle);
//			echo json_encode(array('array_jefes_totales' => $array_jefes_totales, 
//						'array_jefes_detalle' => $array_jefes_detalle));
//		}
//	break;
	case 'subconsulta_jefe_fecha_desde_fecha_hasta': 
		//consulta el detalle de venta por importe por  jefes en un periodo de tiempo
		$array_jefes_detalle = subconsulta_detalle_codigo_por_mes($_POST['subconsulta_jefe'],
																				$_POST['subconsulta_canal'],
																				$_POST['subconsulta_subcanal'],
																	   			$_POST['subconsulta_vendedor'],	
																	   			$_POST['subconsulta_producto'],	
																	   			$_POST['subconsulta_cliente'],	
																	   			$_POST['subconsulta_clitpo'],	
																				$_POST['fecha_desde'], 
																				$_POST['fecha_hasta']);
		$array_jefes_detalle = saco_impuestos($array_jefes_detalle);
		if($array_jefes_detalle == 0){
			echo json_encode(0); 
		}else{
			//saca los totales de los jefes en funcion.php que totaliza el detalle capturado desde mysql
			$array_jefes_totales = saca_totales_jefes($array_jefes_detalle);
			header('Content-Type: application/json');
			echo json_encode(array('array_jefes_totales' => $array_jefes_totales, 
						'array_jefes_detalle' => $array_jefes_detalle));
		}
	break;		
//	case 'totales_canal_fecha_desde_fecha_hasta': 
//		//consulta el detalle de venta por importe por jefe en un periodo de tiempo
//		$array_canales_detalle = canales_detalle_codigo_por_mes($_POST['codigo_canal'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
//		$array_canales_detalle = saco_impuestos($array_canales_detalle);
//		if($array_canales_detalle == 0){
//			echo json_encode(0); 
//		}else{
//			//saca los totales de los canales en funcion.php que totaliza el detalle capturado desde mysql
//			$array_canales_totales = saca_totales_canales($array_canales_detalle);
//			header('Content-Type: application/json');
//			echo json_encode(array('array_canales_totales' => $array_canales_totales, 
//						'array_canales_detalle' => $array_canales_detalle));
//		}
//	break;
	case 'subconsulta_canal_fecha_desde_fecha_hasta': 
		//consulta el detalle de venta por importe por subcanal en un periodo de tiempo
		$array_canales_detalle = subconsulta_detalle_codigo_por_mes($_POST['subconsulta_jefe'],
																	$_POST['subconsulta_canal'],
																	$_POST['subconsulta_subcanal'],
																	$_POST['subconsulta_vendedor'],	
																	$_POST['subconsulta_producto'],	
																	$_POST['subconsulta_cliente'],	
																	$_POST['subconsulta_clitpo'],	
																	$_POST['fecha_desde'], 
																	$_POST['fecha_hasta']);
//		echo '<pre>';
//		print_r($array_canales_detalle);
//		echo '<pre>';
		$array_canales_detalle = saco_impuestos($array_canales_detalle);
		if($array_canales_detalle == 0){
			echo json_encode(0); 
		}else{
			//saca los totales de los canales en funcion.php que totaliza el detalle capturado desde mysql
			$array_canales_totales = saca_totales_canales($array_canales_detalle);
			header('Content-Type: application/json');
			echo json_encode(array('array_canales_totales' => $array_canales_totales, 
						'array_canales_detalle' => $array_canales_detalle));
		}
	break;		
//	case 'totales_subcanal_fecha_desde_fecha_hasta': 
//		//consulta el detalle de venta por importe por subcanal en un periodo de tiempo
//		$array_subcanales_detalle = subcanales_detalle_codigo_por_mes($_POST['codigo_subcanal'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
//		$array_subcanales_detalle = saco_impuestos($array_subcanales_detalle);
//		if($array_subcanales_detalle == 0){
//			echo json_encode(0); 
//		}else{
//			//saca los totales de los subcanales en funcion.php que totaliza el detalle capturado desde mysql
//			$array_subcanales_totales = saca_totales_subcanales($array_subcanales_detalle);
//			header('Content-Type: application/json');
//			echo json_encode(array('array_subcanales_totales' => $array_subcanales_totales, 
//						'array_subcanales_detalle' => $array_subcanales_detalle));
//		}
//	break;
	case 'subconsulta_subcanal_fecha_desde_fecha_hasta': 
		//consulta el detalle de venta por importe por subcanal en un periodo de tiempo
		$array_subcanales_detalle = subconsulta_detalle_codigo_por_mes($_POST['subconsulta_jefe'],
																				$_POST['subconsulta_canal'],
																				$_POST['subconsulta_subcanal'],
																	   			$_POST['subconsulta_vendedor'],
																	   			$_POST['subconsulta_producto'],
																	   			$_POST['subconsulta_cliente'],
																	   			$_POST['subconsulta_clitpo'],
																				$_POST['fecha_desde'], 
																				$_POST['fecha_hasta']);
		$array_subcanales_detalle = saco_impuestos($array_subcanales_detalle);
		if($array_subcanales_detalle == 0){
			echo json_encode(0); 
		}else{
			//saca los totales de los subcanales en funcion.php que totaliza el detalle capturado desde mysql
			$array_subcanales_totales = saca_totales_subcanales($array_subcanales_detalle);
			header('Content-Type: application/json');
			echo json_encode(array('array_subcanales_totales' => $array_subcanales_totales, 
						'array_subcanales_detalle' => $array_subcanales_detalle));
		}
	break;			
//	case 'totales_vendedor_fecha_desde_fecha_hasta': 
//		//consulta el detalle de venta por importe por vendedor en un periodo de tiempo
//		$array_vendedores_detalle = vendedores_detalle_codigo_por_mes($_POST['codigo_vendedor'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
//		$array_vendedores_detalle = saco_impuestos($array_vendedores_detalle);
//		if($array_vendedores_detalle == 0){
//			echo json_encode(0); 
//		}else{
//			//saca los totales de los vendedores en funcion.php que totaliza el detalle capturado desde mysql
//			$array_vendedores_totales = saca_totales_vendedores($array_vendedores_detalle);
//			header('Content-Type: application/json');
//			echo json_encode(array('array_vendedores_totales' => $array_vendedores_totales, 
//						'array_vendedores_detalle' => $array_vendedores_detalle));
//		}
//	break;
	case 'subconsulta_vendedor_fecha_desde_fecha_hasta': 
		$array_vendedores_detalle = subconsulta_detalle_codigo_por_mes($_POST['subconsulta_jefe'],
																		$_POST['subconsulta_canal'],
																		$_POST['subconsulta_subcanal'],
																		$_POST['subconsulta_vendedor'],
																	   	$_POST['subconsulta_producto'],	
																	   	$_POST['subconsulta_cliente'],																	   
																	   	$_POST['subconsulta_clitpo'],																	   
																		$_POST['fecha_desde'], 
																		$_POST['fecha_hasta']);
//		echo '<pre>';
//		echo $array_vendedores_detalle;
//		echo '<pre>';
		
		$array_vendedores_detalle = saco_impuestos($array_vendedores_detalle);
		if($array_vendedores_detalle == 0){
			echo json_encode(0); 
		}else{
			//saca los totales de los vendedores en funcion.php que totaliza el detalle capturado desde mysql
			$array_vendedores_totales = saca_totales_vendedores($array_vendedores_detalle);
			header('Content-Type: application/json');
			echo json_encode(array('array_vendedores_totales' => $array_vendedores_totales, 
						'array_vendedores_detalle' => $array_vendedores_detalle));
		}
	break;			
//	case 'totales_producto_fecha_desde_fecha_hasta': 
//		//consulta el detalle de venta por importe por producto en un periodo de tiempo
//		$array_productos_detalle = productos_detalle_codigo_por_mes($_POST['codigo_producto'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
//		$array_productos_detalle = saco_impuestos($array_productos_detalle);
//		if($array_productos_detalle == 0){
//			echo json_encode(0); 
//		}else{
//			//saca los totales de los subcanales en funcion.php que totaliza el detalle capturado desde mysql
//			$array_productos_totales = saca_totales_productos($array_productos_detalle);
//			header('Content-Type: application/json');
//			echo json_encode(array('array_productos_totales' => $array_productos_totales, 
//						'array_productos_detalle' => $array_productos_detalle));
//		}
//	break;
	case 'subconsulta_producto_fecha_desde_fecha_hasta': 
		//consulta el detalle de venta por importe por  productos en un periodo de tiempo
		$array_productos_detalle = subconsulta_detalle_codigo_por_mes($_POST['subconsulta_jefe'],
																				$_POST['subconsulta_canal'],
																				$_POST['subconsulta_subcanal'],
																	   			$_POST['subconsulta_vendedor'],	
																	   			$_POST['subconsulta_producto'],	
																	   			$_POST['subconsulta_cliente'],	
																	   			$_POST['subconsulta_clitpo'],	
																				$_POST['fecha_desde'], 
																				$_POST['fecha_hasta']);
		$array_productos_detalle = saco_impuestos($array_productos_detalle);
		if($array_productos_detalle == 0){
			echo json_encode(0); 
		}else{
			//saca los totales de los productos en funcion.php que totaliza el detalle capturado desde mysql
			$array_productos_totales = saca_totales_productos($array_productos_detalle);
			header('Content-Type: application/json');
			echo json_encode(array('array_productos_totales' => $array_productos_totales, 
						'array_productos_detalle' => $array_productos_detalle));
		}
	break;		
//	case 'totales_cliente_fecha_desde_fecha_hasta': 
//		//consulta el detalle de venta por importe por cliente en un periodo de tiempo
//		$array_clientes_detalle = clientes_detalle_codigo_por_mes($_POST['codigo_cliente'], $_POST['fecha_desde'], $_POST['fecha_hasta'], $_POST['tipo_seleccion']);
//		$array_clientes_detalle = saco_impuestos($array_clientes_detalle);
//		if($array_clientes_detalle == 0){
//			echo json_encode(0); 
//		}else{
//			//saca los totales de los subcanales en funcion.php que totaliza el detalle capturado desde mysql
//			$array_clientes_totales = saca_totales_clientes($array_clientes_detalle);
//			header('Content-Type: application/json');
//			echo json_encode(array('array_clientes_totales' => $array_clientes_totales, 
//						'array_clientes_detalle' => $array_clientes_detalle));
//		}
//	break;	
	case 'subconsulta_cliente_fecha_desde_fecha_hasta': 
		//consulta el detalle de venta por importe por clientes en un periodo de tiempo
		$array_clientes_detalle = subconsulta_detalle_codigo_por_mes($_POST['subconsulta_jefe'],
																				$_POST['subconsulta_canal'],
																				$_POST['subconsulta_subcanal'],
																	   			$_POST['subconsulta_vendedor'],	
																	   			$_POST['subconsulta_producto'],	
																	   			$_POST['subconsulta_cliente'],	
																	   			$_POST['subconsulta_clitpo'],	
																				$_POST['fecha_desde'], 
																				$_POST['fecha_hasta']);
//		echo '<pre>';
//		echo $array_clientes_detalle;
//		echo '<pre>';
		
		
		$array_clientes_detalle = saco_impuestos($array_clientes_detalle);
		if($array_clientes_detalle == 0){
			echo json_encode(0); 
		}else{
			//saca los totales de los clientes en funcion.php que totaliza el detalle capturado desde mysql
			$array_clientes_totales = saca_totales_clientes($array_clientes_detalle);
			header('Content-Type: application/json');
			echo json_encode(array('array_clientes_totales' => $array_clientes_totales, 
						'array_clientes_detalle' => $array_clientes_detalle));
		}
	break;	
//	case 'totales_transfer_fecha_desde_fecha_hasta': 
//		//consulta el detalle de venta por importe por transfer en un periodo de tiempo fecha desde hasta
//		$array_transfer_detalle = transfer_detalle_codigo_por_mes($_POST['fecha_desde'], $_POST['fecha_hasta']);
//		$array_transfer_detalle = saco_impuestos_transfer($array_transfer_detalle);
//		if($array_transfer_detalle == 0){
//			echo json_encode(0); 
//		}else{
//			//saca los totales de los transfer en funcion.php que totaliza el detalle capturado desde mysql por importe
//			$array_transfer_totales = saca_totales_transfer($array_transfer_detalle);
//			//saca los totales de unidades de los transfer en funcion.php que totaliza el detalle capturado desde mysql por importe
//			$array_transfer_totales_unidades = saca_totales_transfer_unidades($array_transfer_detalle);			
//			header('Content-Type: application/json');
//			echo json_encode(array('array_transfer_totales' => $array_transfer_totales,
//								   'array_transfer_totales_unidades' => $array_transfer_totales_unidades,
//								   'array_transfer_detalle' => $array_transfer_detalle));
//		}
//	break;

//	case 'subconsulta_transfer_fecha_desde_fecha_hasta': 
//		//consulta el detalle de venta por importe por subcanal en un periodo de tiempo
//		$array_transfer_detalle = subconsulta_transfer_detalle_codigo_por_mes($_POST['subconsulta_jefe'],
//																	   			$_POST['subconsulta_vendedor'],
//																	   			$_POST['subconsulta_producto'],
//																	   			$_POST['subconsulta_cliente'],
//																	   			$_POST['subconsulta_clitpo'],
//																				$_POST['fecha_desde'], 
//																				$_POST['fecha_hasta']);
////		echo '<pre>';
////		echo $array_transfer_detalle;
////		echo '<pre>';
//
//		$array_transfer_detalle = saco_impuestos_transfer($array_transfer_detalle);
//		if($array_transfer_detalle == 0){
//			echo json_encode(0); 
//		}else{
//			//saca los totales de los subcanales en funcion.php que totaliza el detalle capturado desde mysql
//			$array_transfer_totales = saca_totales_transfer($array_transfer_detalle);
//			header('Content-Type: application/json');
//			echo json_encode(array('array_transfer_totales' => $array_transfer_totales, 
//						'array_transfer_detalle' => $array_transfer_detalle));
//		}
//	break;		
//	case 'totales_facturacion_fecha_desde_fecha_hasta': 
//		//consulta el detalle de venta por importe por tfacturacionen un periodo de tiempo
//		$array_facturacion_detalle = facturacion_detalle_codigo_por_mes($_POST['fecha_desde'], $_POST['fecha_hasta']);
//		$array_facturacion_detalle = saco_impuestos($array_facturacion_detalle);
//		if($array_facturacion_detalle == 0){
//			echo json_encode(0); 
//		}else{
//			//saca los totales de los facturacion en funcion.php que totaliza el detalle capturado desde mysql
//			$array_facturacion_totales = saca_totales_facturacion($array_facturacion_detalle);
//			//saca los totales de unidades de la facturacion normal en funcion.php que totaliza el detalle capturado desde mysql por importe
//			$array_facturacion_totales_unidades = saca_totales_facturacion_unidades($array_transfer_detalle);				
//			header('Content-Type: application/json');
//			echo json_encode(array('array_facturacion_totales' => $array_facturacion_totales,
//								   'array_facturacion_totales_unidades' => $array_facturacion_totales_unidades,
//								   'array_facturacion_detalle' => $array_facturacion_detalle));
//		}
//	break;	
//	case 'totales_facturacion_fecha_desde_fecha_hasta_nf': 
//		//consulta el detalle de venta por importe por tfacturacionen un periodo de tiempo
//		$array_facturacion_detalle = facturacion_detalle_codigo_por_mes($_POST['fecha_desde'], $_POST['fecha_hasta']);
//		$array_facturacion_detalle = saco_impuestos($array_facturacion_detalle);
//		if($array_facturacion_detalle == 0){
//			echo json_encode(0); 
//		}else{
//			//saca los totales de los facturacion en funcion.php que totaliza el detalle capturado desde mysql
//			$array_facturacion_totales = saca_totales_facturacion($array_facturacion_detalle);
//			//saca los totales de unidades de la facturacion normal en funcion.php que totaliza el detalle capturado desde mysql por importe
//			$array_facturacion_totales_unidades = saca_totales_facturacion_unidades($array_transfer_detalle);				
//			header('Content-Type: application/json');
//			echo json_encode(array('array_facturacion_totales' => $array_facturacion_totales,
////								   'array_facturacion_totales_unidades' => $array_facturacion_totales_unidades,
//								   'array_facturacion_detalle' => $array_facturacion_detalle));
//		}
//	break;			
	case 'subconsulta_totales_facturacion_fecha_desde_fecha_hasta': 
		//consulta el detalle de venta por importe por subcanal en un periodo de tiempo
		$array_facturacion_detalle = subconsulta_detalle_codigo_por_mes($_POST['subconsulta_jefe'],
																				$_POST['subconsulta_canal'],
																				$_POST['subconsulta_subcanal'],
																	   			$_POST['subconsulta_vendedor'],
																	   			$_POST['subconsulta_producto'],
																	   			$_POST['subconsulta_cliente'],
																				$_POST['subconsulta_clitpo'],
																				$_POST['fecha_desde'], 
																				$_POST['fecha_hasta']);
		
//		echo '<pre>';
//		echo $array_facturacion_detalle;
//		echo '<pre>';
		
		$array_facturacion_detalle = saco_impuestos($array_facturacion_detalle);
		if($array_facturacion_detalle == 0){
			echo json_encode(0); 
		}else{
			//saca los totales de los facturacion en funcion.php que totaliza el detalle capturado desde mysql
			$array_facturacion_totales = saca_totales_facturacion($array_facturacion_detalle);
			header('Content-Type: application/json');
			echo json_encode(array('array_facturacion_totales' => $array_facturacion_totales, 
						'array_facturacion_detalle' => $array_facturacion_detalle));
		}
	break;
//	case 'totales_facturacion_fecha_desde_fecha_hasta_vivo': 
//		//consulta el detalle de venta por importe por tfacturacionen un periodo de tiempo
//		$array_facturacion_detalle = facturacion_detalle_codigo_por_mes_vivo($_POST['fecha_desde'], $_POST['fecha_hasta']);
//		$array_facturacion_detalle = saco_impuestos($array_facturacion_detalle);
//		if($array_facturacion_detalle == 0){
//			echo json_encode(0); 
//		}else{
//			//saca los totales de los facturacion en funcion.php que totaliza el detalle capturado desde mysql
//			$array_facturacion_totales = saca_totales_facturacion($array_facturacion_detalle);
//			header('Content-Type: application/json');
//			echo json_encode(array('array_facturacion_totales' => $array_facturacion_totales, 
//						'array_facturacion_detalle' => $array_facturacion_detalle));
//		}
//	break;		
	case 'subconsulta_totales_facturacion_fecha_desde_fecha_hasta_vivo': 
		//consulta el detalle de venta por importe por subcanal en un periodo de tiempo
		$array_facturacion_detalle = subconsulta_detalle_codigo_por_mes_vivo($_POST['subconsulta_jefe'],
																				$_POST['subconsulta_canal'],
																				$_POST['subconsulta_subcanal'],
																	   			$_POST['subconsulta_vendedor'],
																	   			$_POST['subconsulta_producto'],
																	   			$_POST['subconsulta_cliente'],
																				$_POST['fecha_desde'], 
																				$_POST['fecha_hasta']);
		$array_facturacion_detalle = saco_impuestos($array_facturacion_detalle);
		if($array_facturacion_detalle == 0){
			echo json_encode(0); 
		}else{
			//saca los totales de los facturacion en funcion.php que totaliza el detalle capturado desde mysql
			$array_facturacion_totales = saca_totales_facturacion($array_facturacion_detalle);
			header('Content-Type: application/json');
			echo json_encode(array('array_facturacion_totales' => $array_facturacion_totales, 
						'array_facturacion_detalle' => $array_facturacion_detalle));
		}
	break;
	case 'genero_array_vacio': 
		$array_facturacion_totales = array(['total'=>0]);
		$array_facturacion_detalle = array(['CliDir'=>'',
											'CliDptoRed' => '',
											'CliEstId' => '',
											'CliNomRed' => '',
											'CliRubCCod' => '',
											'CliZipCod' => '',
											'CliZipDpto' => '',
											'CliZipLocR' => '',
											'FacAfeEst' => '',
											'FacCliDptI' => '',
											'FacCliId' => '',
											'FacCliNroD' => '',
											'FacCliRDpt' => '',
											'FacCliRNom' => '',
											'FacCliRedC' => '',
											'FacCotEst' => '',
											'FacCotMon' => '',
											'FacEstad2' => '',
											'FacFch' => '',
											'FacFlgAnul' => '',
											'FacHoraSis' => '',
											'FacImpPrd' => '',
											'FacImpRedB' => '',
											'FacImpRedC' => '',
											'FacItem' => '',
											'FacMonId' => '',
											'FacNetSinI' => '',
											'FacNro' => '',
											'FacNroDef' => '',
											'FacPed' => '',
											'FacPrdCnt' => '',
											'FacPrdCntB' => '',
											'FacPrdId' => '',
											'FacPrdImpB' => '',
											'FacSer' => '',
											'FacSuc' => '',
											'FacSucCia' => '',
											'FacTime' => '',
											'FacTotImpR' => '',
											'FacTpo' => '',
											'FacTpoVtaC' => '',
											'FacTrmVtaC' => '',
											'FacVdorId' => '',
											'GTpVtaDes' => '',
											'JefNom' => '',
											'PrdNf01' => '',
											'PrdNf02' => '',
											'PrdNf03' => '',
											'PrdNf04' => '',
											'PrdNroCert' => '',
											'PrdTxt' => '',
											'PrdTxtAmp' => '',
											'RubCTxt' => '',
											'TpoVtaDsc' => '',
											'TpoVtaGrup' => '',
											'TpoVtaJefe' => '',
											'TrmVtaPgoD' => '',
											'VdorTxt' => '',
											'aximpo' => 0,
											'axunib' => 0,
											'axunid' => 2,
											'fecha' => '',
											'id' => ''
											 ]);
//											
//											'CliDptoRed' => "BUENOS AIRES                  "
//											'CliEstId' => "A "
//											'CliNomRed' => "FCIA. PHARMALURO SCS                    "
//											'CliRubCCod' => "61"
//											'CliZipCod' => "7600"
//											'CliZipDpto' => "2"
//											'CliZipLocR' => "MAR DEL PLATA (BA)            "
//											'FacAfeEst' => "S"
//											'FacCliDptI' => "0"
//											'FacCliId' => "42674"
//											'FacCliNroD' => "33708931069"
//											'FacCliRDpt' => "2"
//											'FacCliRNom' => "FCIA. PHARMALURO SCS                    "
//											'FacCliRedC' => "01"
//											'FacCotEst' => "59.47"
//											'FacCotMon' => "1"
//											'FacEstad2' => "S"
//											'FacFch' => "2019-11-19 00:00:00"
//											'FacFlgAnul' => " "
//											'FacHoraSis' => "17:01:58"
//											'FacImpPrd' => "1761.4"
//											'FacImpRedB' => "10724.3"
//											'FacImpRedC' => "0"
//											'FacItem' => "10"
//											'FacMonId' => "1"
//											'FacNetSinI' => "1761.4"
//											'FacNro' => "1115174"
//											'FacNroDef' => "114946"
//											'FacPed' => "587998"
//											'FacPrdCnt' => "2"
//											'FacPrdCntB' => "0"
//											'FacPrdId' => "50340701          "
//											'FacPrdImpB' => "880.7"
//											'FacSer' => "A"
//											'FacSuc' => "106"
//											'FacSucCia' => "1"
//											'FacTime' => "17:01:58"
//											'FacTotImpR' => "13298.13"
//											'FacTpo' => "F"
//											'FacTpoVtaC' => "75"
//											'FacTrmVtaC' => "39"
//											'FacVdorId' => "85"
//											'GTpVtaDes' => "ORIGINALES                    "
//											'JefNom' => "EDGAR REINDL                  "
//											'PrdNf01' => "03  "
//											'PrdNf02' => "02  "
//											'PrdNf03' => "407 "
//											'PrdNf04' => "5   "
//											'PrdNroCert' => "CERT.55539"
//											'PrdTxt' => "TAMSULOSINA R 0,4 mg x 60 CAPS"
//											'PrdTxtAmp' => "TAMSULOSINA RICHET 0,4 mg x 60 CAPS               "
//											'RubCTxt' => "FARMACIAS                               "
//											'TpoVtaDsc' => "OFERTAS UNF NORTE             "
//											'TpoVtaGrup' => "3 "
//											'TpoVtaJefe' => "6 "
//											'TrmVtaPgoD' => "CC - 60   DIAS FF             "
//											'VdorTxt' => "ADRIAN ORQUEDA                                    "
//											'aximpo' => 1761.4
//											'axunib' => "0"
//											'axunid' => 2
//											'fecha' => "2019-11-20 00:03:56"
//											'id' => "486787"											

										  



		header('Content-Type: application/json');
		echo json_encode(array('array_facturacion_totales' => $array_facturacion_totales, 
								'array_facturacion_detalle' => $array_facturacion_detalle));
	break;		
	case 'meses':  
		$array_meses = meses_desde_hasta($_POST['fecha_desde'], $_POST['fecha_hasta']);
		echo json_encode($array_meses);
	break;	
//	case 'general_canal_fecha_desde_fecha_hasta':  
//		$array_canales_detalle = canales_detalle_codigo_por_mes($_POST['codigo_canal'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
//		echo json_encode($array_canales_detalle);
//	break;
//	case 'totales_detalle_canal_fecha':  
//		$array_canales_detalle = canales_detalle_codigo_por_mes($_POST['codigo_canal'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
//		echo json_encode($array_canales_detalle);
//	break;		
	case 'canales_jefe':
		$array_jefes_canales = jefes_codigo_lista_canal($_POST['codigo_jefe']);
		echo json_encode($array_jefes_canales);
	break;	
	case 'sub_canales_jefe':
		$array_jefes_canales = jefes_codigo_lista_subcanal($_POST['codigo_jefe']);
		echo json_encode($array_jefes_canales);
	break;	
	case 'canales_jefe_desde_hasta':
		$array_jefes_canales = jefes_codigo_lista_canal_desde_hasta($_POST['codigo_jefe'], $_POST['fecha_desde'], $_POST['fecha_hasta']);
		echo json_encode($array_jefes_canales);
	break;	
		
//	case 'gral_jefe_mes':
//		for($x=0; $x<count($_POST['mes']); $x++){
//			$array_jefes_detalle = jefes_detalle_codigo_por_mes($_POST['codigo_jefe'], $_POST['mes'][$x]);
//			if($array_jefes_detalle == 0){
//				echo json_encode(0);
//			}else{
//				//detalle de venta
//				$array_jefes_detalle = saco_impuestos($array_jefes_detalle); //saca impuestos
//				//creo array para pasar todos mis array juntos por JSON
//				$array_json['mes'.($x+1)]['array_jefes_detalle'] = $array_jefes_detalle; //JSON
//
//				//canales de venta
//				$array_jefes_canales = jefes_codigo_lista_canal($_POST['codigo_jefe']);
//				$array_json['mes'.($x+1)]['array_jefes_canales'] = $array_jefes_canales; //JSON
//				for($i=0; $i<count($array_jefes_canales); $i++){
//					$canal = trim(strtolower($array_jefes_canales[$i]['GTpVtaDes'])); //para variable macro
//					$detalle = 'array_detalle_'; //para variable macro
//					$totales = 'array_totales_'; //para variable macro
//					//$array_detalle_temporal = jefes_detalle_canal_codigo_por_mes($_POST['codigo_jefe'], $_POST['mes'],  $array_jefes_canales[$i]['GTpVtaDes']);
//					$array_detalle_temporal = array_filtro_campo_valor($array_jefes_detalle, 'GTpVtaDes', $array_jefes_canales[$i]['GTpVtaDes']);
//					if($array_detalle_temporal == 0){
//						${$detalle.$canal} = 0;
//						${$totales.$canal} = 0;
//						$array_json['mes'.($x+1)][$detalle.$canal] = 0; //JSON
//						$array_json['mes'.($x+1)][$totales.$canal] = 0; //JSON
//					}else{
//						$array_detalle_temporal = saco_impuestos($array_detalle_temporal); //saca impuestos al detalle
//						$array_totales_temporal = saca_totales_jefes($array_detalle_temporal); // totales por canal //saca_totales_jefes en funciones.php
//						//guardo en arrays con macros
//						${$detalle.$canal} = $array_detalle_temporal; //detalles por canal plancho datos en variable macro que sera algo asi como $array_detalle_hospitalarios
//						${$totales.$canal} = $array_totales_temporal; //totales por canal plancho datos en variable macro que sera algo asi como $array_totales_hospitalarios
//						$array_json['mes'.($x+1)][$detalle.$canal] = $array_detalle_temporal; //JSON
//						$array_json['mes'.($x+1)][$totales.$canal] = $array_totales_temporal; //JSON
//					}
//				}			
//
//				//totales de venta
//				$array_jefes_totales = saca_totales_jefes($array_jefes_detalle); //en funciones.php
//				$array_json['mes'.($x+1)]['array_jefes_totales'] = $array_jefes_totales; //JSON
//				echo json_encode($array_json);
//	/*			echo json_encode(array('array_jefes_totales' => $array_jefes_totales, 
//									   'array_jefes_detalle' => $array_jefes_detalle, 
//									   'array_jefes_canales' => $array_jefes_canales));*/
//			}
//		}
//
//	break;	

	case 'mas_datos_jefe':
		$array_jefes = mas_datos_jefes($_POST['codigo_jefe'], $_POST['desde'], $_POST['hasta']);
		echo json_encode($array_jefes);
	break;	
		
}
		
		




////////funciones


function xlsx_vecapro($miarray, $nomxls){
	// Create new Spreadsheet object
	$objPHPExcel = new Spreadsheet();

	// Establecer propiedades
	$objPHPExcel->getProperties()
	->setCreator("RichetWeb")
	->setLastModifiedBy("RichetWeb")
	->setTitle("Documento Excel de RichetWeb")
	->setSubject("Documento Excel de RichetWeb")
	->setDescription("Exportar datos a archivos de Excel")
	->setKeywords("Excel Office 2007 openxml php")
	->setCategory("Excel");

/*	//Titulo de Excel
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A1', 'NAME')
	->setCellValue('B1', 'LAST NAME')
	->setCellValue('C1', 'QM IDIOMATIC PAR')
	->setCellValue('D1', 'QM AVERAGE SCORE IDIOMATIC PAR')
	->setCellValue('E1', 'QM AVERAGE SCORE GENERAL');*/
	
	
	//genero los encabezados del Excel manualmente
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A3', 'NAME')
	->setCellValue('B3', 'LAST NAME')
	->setCellValue('C3', 'QM IDIOMATIC PAR')
	->setCellValue('D3', 'QM AVERAGE SCORE IDIOMATIC PAR')
	->setCellValue('E3', 'QM AVERAGE SCORE GENERAL');	

	//color al encabezado
	$objPHPExcel->getActiveSheet()
    ->getStyle('A3:E3')
    ->getFill()
	->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB('FF8A3D');
	// tipo de letra al encabezado
	$objPHPExcel->getActiveSheet()
	->getStyle('A3:E3')
    ->getFont()
	->setBold(true);
	//alineando encabezado
	$objPHPExcel->getActiveSheet()
	->getStyle('A3:E3')
 	->getAlignment()
	->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
	//borde encabezado
	$objPHPExcel->getActiveSheet()
		->getStyle('A3:E3')
		->getBorders()->applyFromArray(
			 array(
				 'allborders' => array(
					 'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
			 )
	 );

	//alineando columnas
	for($col = 'A'; $col !== 'F'; $col++) {
		$objPHPExcel->getActiveSheet()
			->getColumnDimension($col)
			->setAutoSize(true);
	}
	//bordes
	$objPHPExcel->getActiveSheet()
		->getStyle('A2:E'.(count($miarray)+1))
		->getBorders()->applyFromArray(
			 array(
				 'allborders' => array(
					 'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
			 )
	 );		
		
	//genero for con mis datos
	for ($i=0; $i<count($miarray); $i++)
	{	
		$objPHPExcel->getActiveSheet(0)
		->setCellValue('A'.($i+2),$miarray[$i]['VdorTxt']);
/*		->setCellValue('B'.($i+2),$miarray[$i]['GTpVtaDes'])
		->setCellValue('C'.($i+2),$miarray[$i]['GTpVtaDes'])
		->setCellValue('D'.($i+2),$miarray[$i]['GTpVtaDes'])
		->setCellValue('E'.($i+2),$miarray[$i]['GTpVtaDes']);*/
	}
	// Renombrar Hoja
	$objPHPExcel->getActiveSheet()->setTitle('Score Translators');
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
	// Redirect output to a client's web browser (Xlsx)

	//new code:
	$writer = IOFactory::createWriter($objPHPExcel, 'Xlsx');
	//$writer->save('php://output');
	$writer->save('./archivos/'.$nomxls.'.xlsx');
	
	return 1;	
}




?>