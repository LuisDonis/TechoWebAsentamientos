<?php
/**
* @author Desarrollador lrodriguez
* @version 1.0
* @package controlador
**/

class ControllerHome {

	private $rutaServidor;

	function __construct(){
	
		$this->rutaServidor="https://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}

    /**
	 * Funcion principal del controlador
	 * @param $app, $model
	 * @return vista principal con datos Precargados
	 * @access public
	**/
	public function viewHome($app, $models){

        //cargamos los mapas de telefonia movil desde la db. 			
	    $tipoArchivo=$models['home']->getTipoMapa($app);
	    $tipoArchivoAgencia=$models['home']->getTipoAgencia($app);	

             /**
		Modificacion para entrar con index,php	
	    */

		$porciones = explode("/", $this->rutaServidor);
		$servidor="";
		$total=count($porciones);
		//parciamos la url
		 for($i=0; $i < $total; $i++){
		    	if($i==1 || ($i==$total-1)){
				continue;
			}else if($i==2){
				$servidor.="/";
			}else if($porciones[$i]=="index.php"){
				break;			
			}
			$servidor.=$porciones[$i]."/";
			
		    }
	
	//carmagos mapa de 4g
	if(isset($tipoArchivo[0]["tipo"])){
		$tipo=$tipoArchivo[0]["tipo"];
		$fechaPrincipal=date("d-m-Y H:i:s", $tipoArchivo[0]["layer_time"]);
		$ciclo=false;	
		$mapas = array();
		
		if($tipo=="0"){
		    	 $rutas=$models['home']->getMapas($app,1,$tipo);
			 $ciclo=true;
		}else if($tipo=="1" || $tipo=="2" || $tipo=="3"){
		    $rutas=$models['home']->getMapas($app,2,$tipo);
		    $ciclo=true;			
		}

		if($ciclo){
			$c=count($rutas);
		        for($i=0; $i < $c; $i++){
		    	$mapas[$i]=$servidor."mapas/".$rutas[$i]["name"]."/".$rutas[$i]["Tipo"]."/".$rutas[$i]["nombre"]."/".$rutas[$i]["layer_type"];
		    }
		}	
			
	}else{
		$mapas = array();
		$fechaPrincipal="12-10-2015 07:59:18";
	}

	//cargamos mapa de agencia
	if(isset($tipoArchivoAgencia[0]["tipo"])){
		$tipo=$tipoArchivoAgencia[0]["tipo"];
		$ciclo=false;	
		$agencia = array();
		if($tipo=="0"){
		    $rutas=$models['home']->getMapasAgencia($app,1,$tipo);
		    $ciclo=true;	
		}else if($tipo=="1" || $tipo=="2" || $tipo=="3"){
		    $rutas=$models['home']->getMapasAgencia($app,2,$tipo);
		    $ciclo=true;	
		}

		if($ciclo){
		    $c=count($rutas);
		    for($i=0; $i < $c; $i++){
		    	$agencia[$i]=$servidor."mapas/".$rutas[$i]["name"]."/".$rutas[$i]["Tipo"]."/".$rutas[$i]["nombre"]."/".$rutas[$i]["layer_type"];
		    }	
		}	
			
	}else{
		$agencia = array();
	}
	    
	    $data=json_encode($mapas);
	    $agencia=json_encode($agencia);	
	
	    $textoMovil=$models['home']->getTextosMovil($app);
	    $textoInternet=$models['home']->getTextosInternet($app);
	    $departamentos=$models['home']->getDepartamento($app);

		$params['departamentos']=$departamentos;
		$params['texmovil']=$textoMovil;
		$params['texInternet']=$textoInternet;
		$params['mapas']=$data;
		$params['agencias']=$agencia;
		$params['fecha']=$fechaPrincipal;
		$params['menuBar']= $models['home']->menuTop();
		return $app['twig'] -> render('/home/view.home.twig', $params);
	
	}

	/**
	 * Funcion que busca en la db los kmz y arma urls para mostrarlos en la vista
	 * @param $app, $models
	 * @return array->urls
	 * @access public
	**/
	private function loadMaps($app,$models){
		$found_list = array('agencia' => false, 'm5g' => false, 'm2g' => false, 'm3g' => false, 'm4g' => false, 'vi' => false);
		foreach ($found_list as $found_k => $found_v) {
			 $datosMapa=$models['home']->getMapa($app,$found_k);
				
			 if($datosMapa){
			 	$found_list[$found_k] = 'http://' . $_SERVER['SERVER_NAME'] . '/' . substr(dirname($_SERVER['SCRIPT_NAME']), 1) . '/' . basename($_SERVER['PHP_SELF']) . '/home/load' . $found_k;
				//$found_list["f".$found_k]=date("F j, Y, g:i a", $datosMapa[0]["layer_time"]);
				$found_list["f".$found_k]=date("d-m-Y H:i:s", $datosMapa[0]["layer_time"]);
			 }
		}
		return $found_list;
	}

    /**
	 * Funcion que busca los municipios
	 * @param $app, $models
	 * @return json 
	 * @access public
	**/
	public function findMuni($app, $models){
		$response 	= new stdclass();
        $id=$_POST['id'];
		$municipios=$models['home']->getMunicipios($app,$id);
		$response->list 	= $municipios;
		return json_encode($response);
	}

	/**
	 * Funcion que busca las comunidaddes
	 * @param $app, $models
	 * @return json 
	 * @access public
	**/
	public function findComunidad($app, $models){
		$response 	= new stdclass();
        $id=$_POST['id'];
		$comunidad=$models['home']->getComunidad($app,$id);
		$response->list 	= $comunidad;
		return json_encode($response);
	}

    /**
	 * Funcion que busca los poblados
	 * @param $app, $models
	 * @return json 
	 * @access public
	**/
	public function findPoblado($app,$models){
		$response 	= new stdclass();
        $id=$_POST['id'];
		$poblado=$models['home']->getPoblado($app,$id);
		$response->list 	= $poblado;
		return json_encode($response);
	}


    /**
	 * Funcion que buscar las coordenadas de un departamento
	 * @param $app, $models
	 * @return json 
	 * @access public
	**/
	public function findUbicacion($app, $models){
		$response 	= new stdclass();
        $id=$_POST['id1'];
		$ubicacion=$models['home']->getGeoDepto($app,$id);
		$latitud=$ubicacion[0]['provincia_latitude'];
		$longitud=$ubicacion[0]['provincia_longitude'];
		$response->latitud = $latitud;
		$response->longitud=$longitud;
		return json_encode($response);

	}

	/**
	 * Funcion que buscar las coordenadas de un municipio
	 * @param $app, $models
	 * @return json 
	 * @access public
	**/
	public function findUbicacionMuni($app, $models){
		$response 	= new stdclass();
        $id=$_POST['id1'];
		$ubicacion=$models['home']->getGeoMuni($app,$id);
		$latitud=$ubicacion[0]['canton_latitude'];
		$longitud=$ubicacion[0]['canton_longitude'];
		$response->latitud = $latitud;
		$response->longitud=$longitud;
		return json_encode($response);

	}

	/**
	 * Funcion que buscar las coordenadas de una comunidad
	 * @param $app, $models
	 * @return json 
	 * @access public
	**/
	public function findUbiComunidad($app, $models){
		$response 	= new stdclass();
        $id=$_POST['id1'];
		$ubicacion=$models['home']->getGeoComunidad($app,$id);
		$latitud=$ubicacion[0]['distrito_latitude'];
		$longitud=$ubicacion[0]['distrito_longitude'];
		$response->latitud = $latitud;
		$response->longitud=$longitud;
		return json_encode($response);
	}

	/**
	 * Funcion que buscar las coordenadas de un poblado
	 * @param $app, $models
	 * @return json 
	 * @access public
	**/

	public function findUbicacionPoblado($app,$models){
		$response 	= new stdclass();
        $id=$_POST['id1'];
		$ubicacion=$models['home']->getGeoPoblado($app,$id);
		$latitud=$ubicacion[0]['poblado_latitude'];
		$longitud=$ubicacion[0]['poblado_longitude'];
		$response->latitud = $latitud;
		$response->longitud=$longitud;
		return json_encode($response);

	}

	function loadm2g($app,$models){
			$datosMapa=$models['home']->getEspecificoMapa($app,1);
			if ($datosMapa) {
				header('Content-length: ' . $datosMapa[0]['layer_size']);
				header('Content-type: application/vnd.google-earth.kmz');
				header('Content-Disposition: attachment; filename=' . $datosMapa[0]['layer_time'] . '_' . $datosMapa[0]['layer_type']);			
				echo $datosMapa[0]['layer_data'];
				exit;
			}
	}

	function loadm3g($app,$models){
			$datosMapa=$models['home']->getEspecificoMapa($app,2);
			if ($datosMapa) {
				header('Content-length: ' . $datosMapa[0]['layer_size']);
				header('Content-type: application/vnd.google-earth.kmz');
				header('Content-Disposition: attachment; filename=' . $datosMapa[0]['layer_time'] . '_' . $datosMapa[0]['layer_type']);			
				echo $datosMapa[0]['layer_data'];
				exit;
			}
	}

	function loadm4g($app,$models){
			$datosMapa=$models['home']->getEspecificoMapa($app,3);
			if ($datosMapa) {
				header('Content-length: ' . $datosMapa[0]['layer_size']);
				header('Content-type: application/vnd.google-earth.kmz');
				header('Content-Disposition: attachment; filename=' . $datosMapa[0]['layer_time'] . '_' . $datosMapa[0]['layer_type']);			
				echo $datosMapa[0]['layer_data'];
				exit;
			}
		exit;
	}

	function loadi2g($app,$models){
			$datosMapa=$models['home']->getEspecificoMapa($app,4);
			if ($datosMapa) {
				header('Content-length: ' . $datosMapa[0]['layer_size']);
				header('Content-type: application/vnd.google-earth.kmz');
				header('Content-Disposition: attachment; filename=' . $datosMapa[0]['layer_time'] . '_' . $datosMapa[0]['layer_type']);			
				echo $datosMapa[0]['layer_data'];
				exit;
			}
	}

	function loadi3g($app,$models){
			$datosMapa=$models['home']->getEspecificoMapa($app,5);
			if ($datosMapa) {
				header('Content-length: ' . $datosMapa[0]['layer_size']);
				header('Content-type: application/vnd.google-earth.kmz');
				header('Content-Disposition: attachment; filename=' . $datosMapa[0]['layer_time'] . '_' . $datosMapa[0]['layer_type']);			
				echo $datosMapa[0]['layer_data'];
				exit;
			}
	}

	function loadi4g($app,$models){
			$datosMapa=$models['home']->getEspecificoMapa($app,6);
			if ($datosMapa) {
				header('Content-length: ' . $datosMapa[0]['layer_size']);
				header('Content-type: application/vnd.google-earth.kmz');
				header('Content-Disposition: attachment; filename=' . $datosMapa[0]['layer_time'] . '_' . $datosMapa[0]['layer_type']);			
				echo $datosMapa[0]['layer_data'];
				exit;
			}
		exit;
	}

	function loadagencia($app,$models){
			$datosMapa=$models['home']->getEspecificoMapa($app,7);
			if ($datosMapa) {
				header('Content-length: ' . $datosMapa[0]['layer_size']);
				header('Content-type: application/vnd.google-earth.kmz');
				header('Content-Disposition: attachment; filename=' . $datosMapa[0]['layer_time'] . '_' . $datosMapa[0]['layer_type']);			
				echo $datosMapa[0]['layer_data'];
				exit;
			}
		exit;
	}


	/**
	 * Funcion que buscar las coordenadas de un departamento
	 * @param $app, $models
	 * @return json 
	 * @access public
	**/
	public function findMapa2g($app, $models){		
		$porciones = explode("/", $this->rutaServidor);
		$servidor="";
		$total=count($porciones);
		//parciamos la url
		 for($i=0; $i < $total; $i++){
		    	if($i==1){
				continue;
			}else if($i==2){
				$servidor.="/";
			}else if($porciones[$i]=="index.php"){
				break;			
			}
			$servidor.=$porciones[$i]."/";
			
		    }



		$ubicacion=$models['home']->getTipo2g($app);

		if(isset($ubicacion[0]["tipo"])){
			$tipo=$ubicacion[0]["tipo"];
			$iskml=false;
			if($tipo=="0"){
			    	 $rutas=$models['home']->getMapas2g($app,1,$tipo);
				 $iskml=true;	
			}else if($tipo=="1" || $tipo=="2" || $tipo=="3"){
			    	 $rutas=$models['home']->getMapas2g($app,2,$tipo);
				 $iskml=true;
			}

			if($iskml){
			    $c=count($rutas);
			    $agencia = array();
			    for($i=0; $i < $c; $i++){
			    	$agencia[$i]=$servidor."mapas/".$rutas[$i]["name"]."/".$rutas[$i]["Tipo"]."/".$rutas[$i]["nombre"]."/".$rutas[$i]["layer_type"];
			    }
			 }	
			
		}else{
			$agencia = array();
		}
	  return json_encode($agencia);
	}


	/**
	 * Funcion que buscar las coordenadas de un departamento
	 * @param $app, $models
	 * @return json 
	 * @access public
	**/
	public function findMapa3g($app, $models){		
		$porciones = explode("/", $this->rutaServidor);
		$servidor="";
		$total=count($porciones);
		//parciamos la url
		 for($i=0; $i < $total; $i++){
		    	if($i==1){
				continue;
			}else if($i==2){
				$servidor.="/";
			}else if($porciones[$i]=="index.php"){
				break;			
			}
			$servidor.=$porciones[$i]."/";
			
		    }
		$ubicacion=$models['home']->getTipo3g($app);
		if(isset($ubicacion[0]["tipo"])){
			$tipo=$ubicacion[0]["tipo"];
			$iskml=false;
			if($tipo=="0"){
			    	 $rutas=$models['home']->getMapas3g($app,1,$tipo);
				 $iskml=true;	
			}else if($tipo=="1" || $tipo=="2" || $tipo=="3"){
			    	 $rutas=$models['home']->getMapas3g($app,2,$tipo);
				 $iskml=true;
			}

			if($iskml){
			    $c=count($rutas);
			    $agencia = array();
			    for($i=0; $i < $c; $i++){
			    	$agencia[$i]=$servidor."mapas/".$rutas[$i]["name"]."/".$rutas[$i]["Tipo"]."/".$rutas[$i]["nombre"]."/".$rutas[$i]["layer_type"];
			    }
			 }	
			
		}else{
			$agencia = array();
		}
	  return json_encode($agencia);
	}


	/**
	 * Funcion que buscar las coordenadas de un departamento
	 * @param $app, $models
	 * @return json 
	 * @access public
	**/
	public function findMapai2g($app, $models){		
		$porciones = explode("/", $this->rutaServidor);
		$servidor="";
		$total=count($porciones);
		//parciamos la url
		 for($i=0; $i < $total; $i++){
		    	if($i==1){
				continue;
			}else if($i==2){
				$servidor.="/";
			}else if($porciones[$i]=="index.php"){
				break;			
			}
			$servidor.=$porciones[$i]."/";
			
		    }
		$ubicacion=$models['home']->getTipoi2g($app);
		if(isset($ubicacion[0]["tipo"])){
			$tipo=$ubicacion[0]["tipo"];
			$iskml=false;
			if($tipo=="0"){
			    	 $rutas=$models['home']->getMapasi2g($app,1,$tipo);
				 $iskml=true;	
			}else if($tipo=="1" || $tipo=="2" || $tipo=="3"){
			    	 $rutas=$models['home']->getMapasi2g($app,2,$tipo);
				 $iskml=true;
			}

			if($iskml){
			    $c=count($rutas);
			    $agencia = array();
			    for($i=0; $i < $c; $i++){
			    	$agencia[$i]=$servidor."mapas/".$rutas[$i]["name"]."/".$rutas[$i]["Tipo"]."/".$rutas[$i]["nombre"]."/".$rutas[$i]["layer_type"];
			    }
			 }	
			
		}else{
			$agencia = array();
		}
	  return json_encode($agencia);
	}

	/**
	 * Funcion que buscar las coordenadas de un departamento
	 * @param $app, $models
	 * @return json 
	 * @access public
	**/
	public function findMapai3g($app, $models){		
		$porciones = explode("/", $this->rutaServidor);
		$servidor="";
		$total=count($porciones);
		//parciamos la url
		 for($i=0; $i < $total; $i++){
		    	if($i==1){
				continue;
			}else if($i==2){
				$servidor.="/";
			}else if($porciones[$i]=="index.php"){
				break;			
			}
			$servidor.=$porciones[$i]."/";
			
		    }
		$ubicacion=$models['home']->getTipoi3g($app);
		if(isset($ubicacion[0]["tipo"])){
			$tipo=$ubicacion[0]["tipo"];
			$iskml=false;
			if($tipo=="0"){
			    	 $rutas=$models['home']->getMapasi3g($app,1,$tipo);
				 $iskml=true;	
			}else if($tipo=="1" || $tipo=="2" || $tipo=="3"){
			    	 $rutas=$models['home']->getMapasi3g($app,2,$tipo);
				 $iskml=true;
			}

			if($iskml){
			    $c=count($rutas);
			    $agencia = array();
			    for($i=0; $i < $c; $i++){
			    	$agencia[$i]=$servidor."mapas/".$rutas[$i]["name"]."/".$rutas[$i]["Tipo"]."/".$rutas[$i]["nombre"]."/".$rutas[$i]["layer_type"];
			    }
			 }	
			
		}else{
			$agencia = array();
		}
	  return json_encode($agencia);
	}

	/**
	 * Funcion que buscar las coordenadas de un departamento
	 * @param $app, $models
	 * @return json 
	 * @access public
	**/
	public function findMapai4g($app, $models){		
		$porciones = explode("/", $this->rutaServidor);
		$servidor="";
		$total=count($porciones);
		//parciamos la url
		 for($i=0; $i < $total; $i++){
		    	if($i==1){
				continue;
			}else if($i==2){
				$servidor.="/";
			}else if($porciones[$i]=="index.php"){
				break;			
			}
			$servidor.=$porciones[$i]."/";
			
		    }
		$ubicacion=$models['home']->getTipoi4g($app);
		if(isset($ubicacion[0]["tipo"])){
			$tipo=$ubicacion[0]["tipo"];
			$iskml=false;
			if($tipo=="0"){
			    	 $rutas=$models['home']->getMapasi4g($app,1,$tipo);
				 $iskml=true;	
			}else if($tipo=="1" || $tipo=="2" || $tipo=="3"){
			    	 $rutas=$models['home']->getMapasi4g($app,2,$tipo);
				 $iskml=true;
			}

			if($iskml){
			    $c=count($rutas);
			    $agencia = array();
			    for($i=0; $i < $c; $i++){
			    	$agencia[$i]=$servidor."mapas/".$rutas[$i]["name"]."/".$rutas[$i]["Tipo"]."/".$rutas[$i]["nombre"]."/".$rutas[$i]["layer_type"];
			    }
			 }	
			
		}else{
			$agencia = array();
		}
	  return json_encode($agencia);
	}

	/**

	*/
	public function buildHomeSection($app, $models)
	{
		
		$porciones = explode("/", $this->rutaServidor);
		$servidor="";
		$total=count($porciones);
		//parciamos la url
		for($i=0; $i < $total; $i++){
	    	if($i==1 || ($i==$total-1)){
			continue;
		}else if($i==2){
			$servidor.="/";
		}else if($porciones[$i]=="index.php"){
			break;			
		}
			$servidor.=$porciones[$i]."/";
		
	    }
	    $params['content']= $models['home']->getHomeSections($app);
	    $params['menuBar']= $models['home']->menuTop();
	    return $app['twig'] -> render('/home/view.home.twig', $params);
	    //return json_encode($homeSections);
	    //print_r($models['home']->getHomeSections($app)); die;
	}
	/**

	*/
}
