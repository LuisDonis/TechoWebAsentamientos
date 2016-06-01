<?php
/**
* @author Desarrollador lrodriguez
* @version 1.0
* @package modelo
**/
class ModelHome extends ModelMaster {

	private $prefix;
	function __construct($prefix){
		$this->prefix=$prefix;	
	}

	/**
	 * Funcion que devuelve la lista de departamentos
	 * @param $app
	 * @return string
	 * @access public
	**/
	public function getDepartamento($app) {
		 	$query  = 'SELECT provincia_id, provincia_name FROM ' .$this->prefix.'departamento where provincia_id>0 ORDER BY provincia_name, provincia_order';
		 	$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		return $list;
	}

	/**
	 * Funcion que devuelve la lista de municipios
	 * @param $app, $id departamento
	 * @return string
	 * @access public
	**/
	public function getMunicipios($app,$id) {
		 	$query = 'SELECT canton_id, canton_name FROM ' . $this->prefix.'municipio WHERE canton_provincia = '. $id .' ORDER BY canton_name';
		 	$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		return $list;
	}

	/**
	 * Funcion que devuelve la lista de comunidades
	 * @param $app, $id municipio
	 * @return string
	 * @access public
	**/
	public function getComunidad($app,$id) {
		 	$query = 'SELECT distrito_id, distrito_name FROM ' . $this->prefix.'distrito WHERE distrito_canton = '. $id .' ORDER BY distrito_name';
		 	$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		return $list;
	}

	/**
	 * Funcion que devuelve la lista de poblados
	 * @param $app, $id comunidad
	 * @return string
	 * @access public
	**/
	public function getPoblado($app,$id) {
		 	$query = 'SELECT poblado_id, poblado_name FROM ' . $this->prefix.'poblado WHERE poblado_distrito = '. $id .' ORDER BY poblado_name';
		 	$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		return $list;
	}


	public function getArchivo($app,$menu) {
		    $sql = 'SELECT * FROM '. $this->prefix.'layers WHERE layer_type = "'. $menu .'" ORDER BY layer_time DESC LIMIT 1';
		 	$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}

    /**
	 * Funcion que devuelve los textos moviles administrables
	 * @param $app
	 * @return string
	 * @access public
	**/
	public function getTextosMovil($app) {
		 	$query  = 'SELECT id, texto FROM ' .$this->prefix.'servicio_texto WHERE id_servicio = 1';
		 	$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		return $list;
	}

	/**
	 * Funcion que devuelve los textos de internet administrables
	 * @param $app
	 * @return string
	 * @access public
	**/
	public function getTextosInternet($app) {
		 	$query  = 'SELECT id, texto FROM ' .$this->prefix.'servicio_texto WHERE id_servicio = 2';
		 	$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		return $list;

	}	

    /**
	 * Funcion que devuelve las coordenadas de un departamento
	 * @param $app
	 * @return string
	 * @access public
	**/
	public function getGeoDepto($app,$id){
		$query  = 'SELECT provincia_latitude, provincia_longitude FROM ' .$this->prefix.'departamento WHERE provincia_id = ' . $id;
		 	$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		return $list;
	}

	/**
	 * Funcion que devuelve las coordenadas de un municipio
	 * @param $app, $id departamento
	 * @return string
	 * @access public
	**/
	public function getGeoMuni($app,$id){
		$query  = 'SELECT canton_latitude, canton_longitude FROM ' .$this->prefix.'municipio WHERE canton_id = ' . $id;
		 	$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		return $list;
	}

	/**
	 * Funcion que devuelve las coordenadas de una comunidad
	 * @param $app, $id muni
	 * @return string
	 * @access public
	**/
	public function getGeoComunidad($app,$id){
		$query  = 'SELECT distrito_latitude, distrito_longitude FROM ' .$this->prefix.'distrito WHERE distrito_id = ' . $id;
		 	$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		return $list;
	}

    /**
	 * Funcion que devuelve las coordenadas de un poblado
	 * @param $app, $id comunidad
	 * @return string
	 * @access public
	**/
	public function getGeoPoblado($app,$id){
		$query  = 'SELECT poblado_latitude, poblado_longitude FROM ' .$this->prefix.'poblado WHERE poblado_id = ' . $id;
		 	$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		return $list;
	}

    /**
	 * Funcion que devuelve el nombre del kmz
	 * @param $app, $id
	 * @return string
	 * @access public
	**/ 
	public function getMapa($app,$id){
		$sql = 'SELECT layer_time FROM ' .$this->prefix .'layers WHERE layer_type = "'. $id .'" ORDER BY layer_time DESC LIMIT 1';
		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}

	/**
	 * Funcion que devuelve la informacion completa del kmz
	 * @param $app, $id
	 * @return string
	 * @access public
	**/ 
	public function getEspecificoMapa($app,$id){
		//$sql = 'SELECT layer_size, layer_time, layer_type, layer_data FROM ' .$this->prefix .'layers WHERE layer_type = "'. $id .'" ORDER BY layer_time DESC LIMIT 1';
		  $sql=	'Select Archivo.layer_size, Archivo.layer_time, Archivo.layer_type, Archivo.layer_data from tnq_ruta as Carpeta, tnq_layers as Archivo, tnq_cobertura as Cobertura, tnq_tipo_servicio as Servicio where Cobertura.servicio=Servicio.id and  Archivo.ruta=Carpeta.id and Cobertura.id=Carpeta.cobertura and Carpeta.cobertura='.$id .' and Carpeta.tipo=2 ORDER BY Archivo.layer_time DESC LIMIT 1';
		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}

	/**
	 * Funcion que devuelve la informacion completa del kmz
	 * @param $app, $id
	 * @return string
	 * @access public
	**/ 
	public function getTipoMapa($app){
		$sql = 'SELECT tipo, layer_time FROM ' . $this->prefix .'layers WHERE ruta  in (Select id FROM '. $this->prefix . 'ruta where cobertura=3) ORDER BY layer_time DESC LIMIT 1';
		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}


	/**
	 * Funcion que devuelve la informacion completa del kmz
	 * @param $app, $id
	 * @return string
	 * @access public
	**/ 
	public function getMapas($app,$id,$tipo){
		$sql = 'Select Servicio.name, Cobertura.nombre as Tipo, Carpeta.nombre, Archivo.layer_type from tnq_ruta as Carpeta, tnq_layers as Archivo, tnq_cobertura as Cobertura, tnq_tipo_servicio as Servicio where Cobertura.servicio=Servicio.id and  Archivo.ruta=Carpeta.id and Cobertura.id=Carpeta.cobertura and Carpeta.cobertura=3 and Carpeta.tipo='.$id . ' and Archivo.tipo='.$tipo;
		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}


	/**
	 * Funcion que devuelve la informacion completa del kmz
	 * @param $app, $id
	 * @return string
	 * @access public
	**/ 
	public function getTipoAgencia($app){
		$sql = 'SELECT tipo FROM ' . $this->prefix .'layers WHERE ruta  in (Select id FROM '. $this->prefix . 'ruta where cobertura=7) ORDER BY layer_time DESC LIMIT 1';
		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}


	public function getMapasAgencia($app,$id,$tipo){
		$sql = 'Select Servicio.name, Cobertura.nombre as Tipo, Carpeta.nombre, Archivo.layer_type from tnq_ruta as Carpeta, tnq_layers as Archivo, tnq_cobertura as Cobertura, tnq_tipo_servicio as Servicio where Cobertura.servicio=Servicio.id and  Archivo.ruta=Carpeta.id and Cobertura.id=Carpeta.cobertura and Carpeta.cobertura=7 and Carpeta.tipo='.$id . ' and Archivo.tipo='.$tipo;

		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}

	/**
	 * Funcion que devuelve la informacion completa del kmz
	 * @param $app, $id
	 * @return string
	 * @access public
	**/ 
	public function getTipo2g($app){
		$sql = 'SELECT tipo FROM ' . $this->prefix .'layers WHERE ruta  in (Select id FROM '. $this->prefix . 'ruta where cobertura=1) ORDER BY layer_time DESC LIMIT 1';
		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}


	public function getMapas2g($app,$id,$tipo){
		$sql = 'Select Servicio.name, Cobertura.nombre as Tipo, Carpeta.nombre, Archivo.layer_type from tnq_ruta as Carpeta, tnq_layers as Archivo, tnq_cobertura as Cobertura, tnq_tipo_servicio as Servicio where Cobertura.servicio=Servicio.id and  Archivo.ruta=Carpeta.id and Cobertura.id=Carpeta.cobertura and Carpeta.cobertura=1 and Carpeta.tipo='.$id . ' and Archivo.tipo='.$tipo;

		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}

	/**
	 * Funcion que devuelve la informacion completa del kmz
	 * @param $app, $id
	 * @return string
	 * @access public
	**/ 
	public function getTipo3g($app){
		$sql = 'SELECT tipo FROM ' . $this->prefix .'layers WHERE ruta  in (Select id FROM '. $this->prefix . 'ruta where cobertura=2) ORDER BY layer_time DESC LIMIT 1';
		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}


	public function getMapas3g($app,$id,$tipo){
		$sql = 'Select Servicio.name, Cobertura.nombre as Tipo, Carpeta.nombre, Archivo.layer_type from tnq_ruta as Carpeta, tnq_layers as Archivo, tnq_cobertura as Cobertura, tnq_tipo_servicio as Servicio where Cobertura.servicio=Servicio.id and  Archivo.ruta=Carpeta.id and Cobertura.id=Carpeta.cobertura and Carpeta.cobertura=2 and Carpeta.tipo='.$id . ' and Archivo.tipo='.$tipo;

		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}

	/**
	 * Funcion que devuelve la informacion completa del kmz
	 * @param $app, $id
	 * @return string
	 * @access public
	**/ 
	public function getTipoi2g($app){
		$sql = 'SELECT tipo FROM ' . $this->prefix .'layers WHERE ruta  in (Select id FROM '. $this->prefix . 'ruta where cobertura=4) ORDER BY layer_time DESC LIMIT 1';
		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}


	public function getMapasi2g($app,$id,$tipo){
		$sql = 'Select Servicio.name, Cobertura.nombre as Tipo, Carpeta.nombre, Archivo.layer_type from tnq_ruta as Carpeta, tnq_layers as Archivo, tnq_cobertura as Cobertura, tnq_tipo_servicio as Servicio where Cobertura.servicio=Servicio.id and  Archivo.ruta=Carpeta.id and Cobertura.id=Carpeta.cobertura and Carpeta.cobertura=4 and Carpeta.tipo='.$id . ' and Archivo.tipo='.$tipo;

		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}

	public function getTipoi3g($app){
		$sql = 'SELECT tipo FROM ' . $this->prefix .'layers WHERE ruta  in (Select id FROM '. $this->prefix . 'ruta where cobertura=5) ORDER BY layer_time DESC LIMIT 1';
		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}


	public function getMapasi3g($app,$id,$tipo){
		$sql = 'Select Servicio.name, Cobertura.nombre as Tipo, Carpeta.nombre, Archivo.layer_type from tnq_ruta as Carpeta, tnq_layers as Archivo, tnq_cobertura as Cobertura, tnq_tipo_servicio as Servicio where Cobertura.servicio=Servicio.id and  Archivo.ruta=Carpeta.id and Cobertura.id=Carpeta.cobertura and Carpeta.cobertura=5 and Carpeta.tipo='.$id . ' and Archivo.tipo='.$tipo;

		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}

	public function getTipoi4g($app){
		$sql = 'SELECT tipo FROM ' . $this->prefix .'layers WHERE ruta  in (Select id FROM '. $this->prefix . 'ruta where cobertura=6) ORDER BY layer_time DESC LIMIT 1';
		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}


	public function getMapasi4g($app,$id,$tipo){
		$sql = 'Select Servicio.name, Cobertura.nombre as Tipo, Carpeta.nombre, Archivo.layer_type from tnq_ruta as Carpeta, tnq_layers as Archivo, tnq_cobertura as Cobertura, tnq_tipo_servicio as Servicio where Cobertura.servicio=Servicio.id and  Archivo.ruta=Carpeta.id and Cobertura.id=Carpeta.cobertura and Carpeta.cobertura=6 and Carpeta.tipo='.$id . ' and Archivo.tipo='.$tipo;

		$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}

 
	/**
	LLOPEZ
	*/
		/**
	* Muestra el menu principal segun el tipo de usuario
	*
	* @var $_SESSION[role]
	* @return String
	*/
	public function menuTop(){
		$menu  ="<div class='navbar navbar-inverse'>";
		$menu .="<div class='navbar-inner'>";
		$menu .="<a class='brand' href='#'>Mapa Asentamientos</a>";
		$menu .="<ul class='nav'>";
        $menu .="<li><a href='./timeline'>Histograma</a></li>";
		$menu .="<li><a href='./noticias'>Noticias</a></li>";
		$menu .="<li><a href='./contacto'>Contactos</a></li>";
		$menu .="<li><a href='./descarga'>Descargas</a></li>";
		$menu .="</ul></div></div>";
		return $menu;
		}

	public function getHomeSections($app)
	{
		$sql = 'SELECT * FROM '. $this->prefix.'home_section';
	 	$list 	= $app['dbs']['mysql_silex']->fetchAll($sql);
		return $list;
	}

	/**

	*/

}
