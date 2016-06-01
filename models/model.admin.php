<?php
/**
* @author Desarrollador lrodriguez
* @version 1.0
* @package modelo
**/
	
class ModelAdmin extends ModelMaster {

    private $prefix;
    public $tamanoMax=0;

	function __construct($prefix){
		$this->prefix=$prefix;
		$this->tamanoMax=intval(ini_get('upload_max_filesize')) * 1048576;
	}

    
	/**
	 * Funcion valida la autenticacion del usuario
	 * @param $usuario, pass, app
	 * @return string 
	 * @access public
	**/ 
	public function validateParams( $usuario, $pass, $app)
	{
		if ( empty($usuario) || empty($pass) ){
			return 'error';
		} else {
            $pass=md5($pass);
			#validar credencieales
				$exist 		= FALSE;
				$query 		= 'SELECT id, name, pais, usertype FROM '.$this->prefix.'admin_users WHERE `name`= "'.addslashes( htmlspecialchars( strip_tags($usuario))).'" AND `password` = "'.addslashes( strip_tags($pass)).'" AND block = 1 ';
				$result 	= $app['dbs']['mysql_silex']->fetchAssoc($query);

				if ( !empty($result) ){
					$exist = TRUE;
				}
           

			if( $exist === TRUE ){

				#inicia sesión
				@session_name("loginUsuario");
				@session_start();

				$_SESSION["authenticated"]	= TRUE; #asignar que el usuario se autentico
				$_SESSION["lastaccess"]		= date("Y-n-j H:i:s"); #definir la fecha y hora de inicio de sesión en formato aaaa-mm-dd hh:mm:ss
				$_SESSION["uid"]			= (int)$result['id']; #asigna a session ID de usuario registrado
				$_SESSION["role"]			= (int)$result['usertype']; #asigna a session ID de usuario registrado
				$_SESSION["pais_user"]			= (int)$result['pais']; #asigna pais Tipo de usuario registrado

				 $app['session']->set('pais', array('idpais' => $_SESSION["pais_user"]));

				if(($_SESSION["role"] == 0) || ($_SESSION["role"] == 1)){
					return 'success';	
				}else if($_SESSION["role"] == 2){
					return 'editor';
				}
				
			} else {
				return 'error';
			}
		}
		exit;
	}

	/**
	* Muestra el menu principal segun el tipo de usuario
	*
	* @var $_SESSION[role]
	* @return String
	*/
	public function menuTop(){
		$menu  ="<div class='navbar navbar-inverse'>";
		$menu .="<div class='navbar-inner'>";
		$menu .="<a class='brand' href='#'>CoberturaClaro</a>";
		$menu .="<ul class='nav'>";
		if($_SESSION["role"] == 0 || $_SESSION["role"]==1){
		$menu .="<li><a href='./admin'>Usuarios</a></li>";
		}
                $menu .="<li><a href='./textos'>Textos</a></li>";
		$menu .="<li><a href='./regiones'>Regiones</a></li>";
		$menu .="<li><a href='./editor'>Subir Mapa</a></li>";
		$menu .="<li><a href='./logout'>Cerrar Session</a></li>";
		$menu .="</ul></div></div>";
		return $menu;		    
	}

	/**
	* Obtiene el listado de usuarios
	*
	* @var object
	* @var string
	* @return array
	*/
	public function getUsers($app)
	{
	 
	   if($_SESSION["role"] == 0 ){
        $query  = 'SELECT user.id,user.name,pais.nombre as pais,user.mail,user.block FROM `'.$this->prefix.'admin_users` user,'.'`'.$this->prefix.'cobertura_pais` pais where user.pais=pais.id';

	   }else{
	   	$query  = 'SELECT user.id,user.name,pais.nombre as pais,user.mail,user.block FROM `'.$this->prefix.'admin_users` user,'.'`'.$this->prefix.'cobertura_pais` pais where user.pais='.$_SESSION["pais_user"].' and user.pais=pais.id and user.usertype>1';
	   }
        $list 	= $app['dbs']['mysql_silex']->fetchAll($query);
	   $html="<input type='button' value='Crear Usuario' class='btn btn-danger' onclick='create_user()'>";
	   $html.='<br/><br/><br/>';   
	   $html.='<div id="tabla" class="table_users">';
	   $html.='<table class="table table-hover table-striped">';
	   $html.='<th class="header">ID</th>';
	   $html.='<th class="header">Nombre</th>';    
	   $html.='<th class="header">Mail</th>';     
	   $html.='<th class="header">Pais</th>';   
	   $html.='<th class="header" colspan="3" style="width:28%;">Opciones</th>'; 
	   $contador=1;

		   foreach ($list as $key) {
		     $html.='<tr>';
		     $html.='<td>'.$key['id'].'</td>';
		     $html.='<td>'.$key['name'].'</td>';
		     $html.='<td>'.$key['mail'].'</td>';
		     $html.='<td>'.$key['pais'].'</td>';
		     //$html.='<td> <span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_user(this.id)" title="Editar"><i class="icon-edit"></i></span> ';
		     $html.='<td><form id="editar'.$contador.'" method="post" action="./editUser">';
		     $html.= '<span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_user('.$contador.')" title="Editar"><i class="icon-edit"></i></span>';
		     $html.= '<input type="hidden" name="cadena" value="'.$key['id'].'">';
		     $html.= '</form></td>';
   
            
		     $html.='<td><span class="btn btn-danger btn-small" id="'.$key['id'].'" onClick="eliminar_user(this.id)" title="Eliminar"><i class="icon-remove"></i></span></td>';
		    if($key['block']=='1') 
		       $html.=' <td><span class="btn btn-warning btn-small" id="'.$key['id'].'" onClick="bloquear_user(this.id)" title="Bloquear"><i class="icon-ban-circle"></i></span></td>';
		     else 
		       $html.=' <td><span class="btn btn-success btn-small" id="'.$key['id'].'" onClick="active_user(this.id)" title="Activar"><i class="icon-ok-circle"></i></span></td>';
		                                
		    $html.='</tr>';

		    $contador=$contador+1;
		   }
   		$html.='</table>';
   		$html.='</div>';
   
   	return $html;
	}


    /**
	 * Funcion arama el formulario de nuevo Usuario
	 * @param $app
	 * @return string 
	 * @access public
	**/ 
	public function formularioNuevoUsuario($app){
		$formulario  ="<div class='well'><h3>Ingreso Nuevo Usuario</h3>";
		$formulario .= "<form id='mensajeError' accept-charset='utf-8' method=''>";
		$formulario .= "<div class='ruleta'>";
		$formulario .= "<div class='rul_1'>Nombre Usuario:</div>";
		$formulario .= "<div class='rul_2'><input id='nombre' name='nombre' type='text' placeholder='Nombre del usuario' /></div>";
		$formulario .= "<div class='rul_1'>Constraseña:</div>";
		$formulario .= "<div class='rul_2'><input id='pass' name='pass' type='password' placeholder='Password de acceso' /></div>";
			if($_SESSION["role"] == 0){
				$formulario .= "<div class='rul_1'>Administrar Pa&iacute;s de:</div>";
				$formulario .= "<div class='rul_2'>".$this->getPais($app)."</div>";
				$formulario .= "<div class='rul_1'>Rol de Usuario:</div>";
				$formulario .= "<div class='rul_2'>".$this->getRol($app)."</div>";
			}else{
				$formulario.="<input type='hidden' id='pais' name='pais' value='".$_SESSION["pais_user"]."'>";
				$formulario.="<input type='hidden' id='rol' name='rol' value='2'>";
			}
		$formulario .= "<div class='rul_1'>Email del Usuario:</div>";
		$formulario .= "<div class='rul_2'><input id='email' name='email' type='text' placeholder='Correo Electr&oacute;nico' /></div>";
		$formulario .= "<center><input type='button' id='boton_ruleta' name='boton_ruleta' class='btn btn-danger' value='Guardar' onclick='createUser();'/></center>";
		$formulario .= "</div></div></form>";

		return $formulario;
	}


	/**
	 * Funcion arama el formulario de nuevo departamento
	 * @param $app
	 * @return string 
	 * @access public
	**/ 
	public function formularioNuevoDepartamento($app,$pais){
		$formulario  ="<div class='well'><h3>Ingreso Nuevo Departamento</h3>";
		$formulario .= "<form id='mensajeError' accept-charset='utf-8' method=''>";
		$formulario .= "<div class='ruleta'>";
		$formulario .= "<div class='rul_1'>Nombre Departamento:</div>";
		$formulario .= "<div class='rul_2'><input id='nombre' name='nombre' type='text' placeholder='Nombre del Departamento' /></div>";
		$formulario .= "<div class='rul_1'>Latitud:</div>";
		$formulario .= "<div class='rul_2'><input id='latitud' name='latitud' type='text' placeholder='Latitud' /></div>";
		$formulario .= "<div class='rul_1'>Longitud:</div>";
		$formulario .= "<div class='rul_2'><input id='longitud' name='longitud' type='text' placeholder='Longitud' /></div>";
		$formulario .= "<input type='hidden' id='pais' name='pais' value='".$pais."'>";
		$formulario .= "<center><input type='button' id='boton_depar' name='boton_depar' class='btn btn-danger' value='Guardar' onclick='createDepar();'/></center>";
		$formulario .= "</div></div></form>";

		return $formulario;
	}


	/**
	 * Funcion arama el formulario de nuevo departamento
	 * @param $app
	 * @return string 
	 * @access public
	**/ 
	public function formularioNuevoMunicipio($app,$pais){
		$formulario  ="<div class='well'><h3>Ingreso Nuevo Municipio</h3>";
		$formulario .= "<form id='mensajeError' accept-charset='utf-8' method=''>";
		$formulario .= "<div class='ruleta'>";
		$formulario .= "<div class='rul_1'>Departamento:</div>";
	    $formulario .= "<div class='rul_1'>".$this->getDepartamentos($app,$pais,1)."</div>"; 
		$formulario .= "<div class='rul_1'>Nombre Municipio:</div>";
		$formulario .= "<div class='rul_2'><input id='nombre' name='nombre' type='text' placeholder='Nombre del Municipio' /></div>";
		$formulario .= "<div class='rul_1'>Latitud:</div>";
		$formulario .= "<div class='rul_2'><input id='latitud' name='latitud' type='text' placeholder='Latitud' /></div>";
		$formulario .= "<div class='rul_1'>Longitud:</div>";
		$formulario .= "<div class='rul_2'><input id='longitud' name='longitud' type='text' placeholder='Longitud' /></div>";
		$formulario .= "<input type='hidden' id='pais' name='pais' value='".$pais."'>";
		$formulario .= "<center><input type='button' id='boton_depar' name='boton_depar' class='btn btn-danger' value='Guardar' onclick='createMunicipio();'/></center>";
		$formulario .= "</div></div></form>";

		return $formulario;
	}


	/**
	 * Funcion arama el formulario de nueva comunidad
	 * @param $app,$pais
	 * @return string 
	 * @access public
	**/ 
	public function formularioNuevoComunidad($app,$pais){
		$formulario  ="<div class='well'><h3>Ingreso Nueva Comunidad</h3>";
		$formulario .= "<form id='mensajeError' accept-charset='utf-8' method=''>";
		$formulario .= "<div class='ruleta'>";
		$formulario .= "<div class='rul_1'>Departamento:</div>";
	    $formulario .= "<div class='rul_1'>".$this->getDepartamentos($app,$pais,2)."</div>"; 
	    $formulario .= "<div class='rul_1'>Municipio:</div>";
	    $formulario .= "<select id='municipio' name='municipio' disabled='disabled'><option value='0'>Municipio</option></select>";

		$formulario .= "<div class='rul_1'>Nombre Comunidad:</div>";
		$formulario .= "<div class='rul_2'><input id='nombre' name='nombre' type='text' placeholder='Nombre del Comunidad' /></div>";
		$formulario .= "<div class='rul_1'>Latitud:</div>";
		$formulario .= "<div class='rul_2'><input id='latitud' name='latitud' type='text' placeholder='Latitud' /></div>";
		$formulario .= "<div class='rul_1'>Longitud:</div>";
		$formulario .= "<div class='rul_2'><input id='longitud' name='longitud' type='text' placeholder='Longitud' /></div>";
		$formulario .= "<input type='hidden' id='paisActual' name='paisActual' value='".$pais."'>";
		$formulario .= "<center><input type='button' id='boton_depar' name='boton_depar' class='btn btn-danger' value='Guardar' onclick='createComunidad();'/></center>";
		$formulario .= "</div></div></form>";

		return $formulario;
	}


	/**
	 * Funcion arama el formulario de nueva comunidad
	 * @param $app,$pais
	 * @return string 
	 * @access public
	**/ 
	public function formularioNuevoPoblado($app,$pais){
		$formulario  ="<div class='well'><h3>Ingreso Nuevo Poblado</h3>";
		$formulario .= "<form id='mensajeError' accept-charset='utf-8' method=''>";
		$formulario .= "<div class='ruleta'>";
		$formulario .= "<div class='rul_1'>Departamento:</div>";
	    $formulario .= "<div class='rul_1'>".$this->getDepartamentos($app,$pais,3)."</div>"; 
	    $formulario .= "<div class='rul_1'>Municipio:</div>";
	    $formulario .= "<select id='deparmunicipio' name='deparmunicipio' disabled='disabled'><option value='0'>Municipio</option></select>";
	    $formulario .= "<div class='rul_1'>Comunidad:</div>";
	    $formulario .= "<select id='comunidad' name='comunidad' disabled='disabled'><option value='0'>Comunidad</option></select>";

		$formulario .= "<div class='rul_1'>Nombre Poblado:</div>";
		$formulario .= "<div class='rul_2'><input id='nombre' name='nombre' type='text' placeholder='Nombre del Poblado' /></div>";
		$formulario .= "<div class='rul_1'>Latitud:</div>";
		$formulario .= "<div class='rul_2'><input id='latitud' name='latitud' type='text' placeholder='Latitud' /></div>";
		$formulario .= "<div class='rul_1'>Longitud:</div>";
		$formulario .= "<div class='rul_2'><input id='longitud' name='longitud' type='text' placeholder='Longitud' /></div>";
		$formulario .= "<input type='hidden' id='paisActual' name='paisActual' value='".$pais."'>";
		$formulario .= "<center><input type='button' id='boton_depar' name='boton_depar' class='btn btn-danger' value='Guardar' onclick='createPoblado();'/></center>";
		$formulario .= "</div></div></form>";

		return $formulario;
	}


	/**
	 * Funcion arma la lista de pais en un select
	 * @param $app
	 * @return string 
	 * @access public
	**/ 
	public function getPais($app){
			$query="SELECT * from ".$this->prefix."cobertura_pais";
	            $res = $app['dbs']['mysql_silex']->fetchAll($query);
	            $html ="<select id='pais'name='pais'>";
	            foreach ($res as $key ) {
	            $html.="<option value = '".$key['id']."'>".$key['nombre']."</option>";    
	            }
	            $html.="</select>";

	    return $html;
	}

	/**
	 * Funcion arma la lista de roles en select
	 * @param $app
	 * @return string 
	 * @access public
	**/ 
	public function getRol($app){

			$query="SELECT * from ".$this->prefix."admin_roles where rol_id>0";
	            $res = $app['dbs']['mysql_silex']->fetchAll($query);
	            $html ="<select id='rol' name='rol'>";
	            foreach ($res as $key ) {
	            $html.="<option value = '".$key['rol_id']."'>".$key['name']."</option>";    
	            }
	            $html.="</select>";
	    return $html;
	}

    /**
	 * Funcion que inserta a la db un usuario
	 * @param $app, nombre, pass, rol, email
	 * @return string 
	 * @access public
	**/ 
	public function insertUser($nombre,$pass,$pais,$rol,$email,$app){
		$usuario=$this->findUsuario($nombre,$app);
		if($usuario){
			return false;
		}
				$paramsTable = array();
				$pass=md5($pass);
				$generico=1;
				$hoy = date("Y-m-d H:i:s");
				$table = $this->prefix."admin_users";
				$paramsTable['name'] 	= $app->escape($nombre);
				$paramsTable['date_register'] 	= $app->escape($hoy);
				$paramsTable['block'] 	= $app->escape($generico);
				$paramsTable['password'] 	= $app->escape($pass);
				$paramsTable['usertype'] 	= $app->escape($rol);
				$paramsTable['mail'] 	= $app->escape($email);
				$paramsTable['pais'] 	= $app->escape($pais);
				$insert 			    = $app['dbs']['mysql_silex']->insert($table ,$paramsTable);
				return $insert;
		
	}

    /**
	 * Funcion que busca en la db un nombre de usuario registrado
	 * @param $usuario, $app
	 * @return string 
	 * @access private
	**/ 
	private function findUsuario($usuario,$app){
		$query  = 'SELECT name from `'.$this->prefix.'admin_users`';
	    $list 	= $app['dbs']['mysql_silex']->fetchAll($query);
	    $retorno= false;
	    $usuario=strtoupper($usuario);
	    foreach ($list as $key) {
	    		$nombre=strtoupper($key['name']);
			     
			    if($nombre==$usuario) {
			    		$retorno=true;
			    		break;
			    }   
			   }
		return $retorno;
	}

	/**
	 * Funcion que inserta a la db un departamento
	 * @param $app, nombre, pass, rol, email
	 * @return string 
	 * @access public
	**/ 
	public function insertDepartamento($app,$nombre,$latitud,$longitud,$pais){
		$departamento=$this->findDepartamento($nombre,$app,$pais);
		if($departamento){
			return false;
		}
				$paramsTable = array();
				$generico=1;
				$generico1=0;
				$table = $this->prefix."departamento";
				$paramsTable['provincia_active'] = $app->escape($generico);
				$paramsTable['provincia_name'] 	= $app->escape($nombre);
				$paramsTable['provincia_alias'] = $app->escape($nombre);
				$paramsTable['provincia_order'] 	= $app->escape($generico1);
				$paramsTable['provincia_latitude'] 	= $app->escape($latitud);
				$paramsTable['provincia_longitude'] 	= $app->escape($longitud);

				if($pais=="1"){
					$list = $app['dbs']['mysql_silex']->insert($table ,$paramsTable);
				}else if($pais=="2"){
					$list = $app['dbs']['mysql_silex1']->insert($table ,$paramsTable);
				}else if($pais=="3"){
					$list = $app['dbs']['mysql_silex2']->insert($table ,$paramsTable);
				}else if($pais=="4"){
					$list = $app['dbs']['mysql_silex3']->insert($table ,$paramsTable);
				}else if($pais=="5"){
					$list = $app['dbs']['mysql_silex4']->insert($table ,$paramsTable);
				}

				return $list;
		
	}


	/**
	 * Funcion que inserta a la db un municipio
	 * @param $app, nombre, latitud, longitud, pais
	 * @return string 
	 * @access public
	**/ 
	public function insertMunicipio($app,$nombre,$latitud,$longitud,$departamento,$pais){
		$municipio=$this->findMunicipio($nombre,$app,$departamento,$pais);
		if($municipio){
			return false;
		}
				$paramsTable = array();
				$generico=1;
				$generico1=0;
				$table = $this->prefix."municipio";
				$paramsTable['canton_active'] = $app->escape($generico);
				$paramsTable['canton_name'] 	= $app->escape($nombre);
				$paramsTable['canton_alias'] = $app->escape($nombre);
				$paramsTable['canton_order'] 	= $app->escape($generico1);
				$paramsTable['canton_latitude'] 	= $app->escape($latitud);
				$paramsTable['canton_longitude'] 	= $app->escape($longitud);
				$paramsTable['canton_provincia'] 	= $app->escape($departamento);

				if($pais=="1"){
					$list = $app['dbs']['mysql_silex']->insert($table ,$paramsTable);
				}else if($pais=="2"){
					$list = $app['dbs']['mysql_silex1']->insert($table ,$paramsTable);
				}else if($pais=="3"){
					$list = $app['dbs']['mysql_silex2']->insert($table ,$paramsTable);
				}else if($pais=="4"){
					$list = $app['dbs']['mysql_silex3']->insert($table ,$paramsTable);
				}else if($pais=="5"){
					$list = $app['dbs']['mysql_silex4']->insert($table ,$paramsTable);
				}

				return $list;
		
	}


	/**
	 * Funcion que inserta a la db una comunidad
	 * @param $app, nombre, latitud, longitud, pais
	 * @return string 
	 * @access public
	**/ 
	public function insertComunidad($app,$nombre,$latitud,$longitud,$municipio,$pais){
		$comunidad=$this->findComunidad($nombre,$app,$municipio,$pais);
		if($comunidad){
			return false;
		}
				$paramsTable = array();
				$generico=1;
				$generico1=0;
				$table = $this->prefix."distrito";
				$paramsTable['distrito_active'] = $app->escape($generico);
				$paramsTable['distrito_name'] 	= $app->escape($nombre);
				$paramsTable['distrito_alias'] = $app->escape($nombre);
				$paramsTable['distrito_order'] 	= $app->escape($generico1);
				$paramsTable['distrito_latitude'] 	= $app->escape($latitud);
				$paramsTable['distrito_longitude'] 	= $app->escape($longitud);
				$paramsTable['distrito_canton'] 	= $app->escape($municipio);

				if($pais=="1"){
					$list = $app['dbs']['mysql_silex']->insert($table ,$paramsTable);
				}else if($pais=="2"){
					$list = $app['dbs']['mysql_silex1']->insert($table ,$paramsTable);
				}else if($pais=="3"){
					$list = $app['dbs']['mysql_silex2']->insert($table ,$paramsTable);
				}else if($pais=="4"){
					$list = $app['dbs']['mysql_silex3']->insert($table ,$paramsTable);
				}else if($pais=="5"){
					$list = $app['dbs']['mysql_silex4']->insert($table ,$paramsTable);
				}

				return $list;
		
	}


	/**
	 * Funcion que inserta a la db un poblado
	 * @param $app, nombre, latitud, longitud, pais
	 * @return string 
	 * @access public
	**/ 
	public function insertPoblado($app,$nombre,$latitud,$longitud,$comunidad,$pais){
		$poblado=$this->findPoblado($nombre,$app,$comunidad,$pais);
		if($poblado){
			return false;
		}
				$paramsTable = array();
				$generico=1;
				$generico1=0;
				$table = $this->prefix."poblado";
				$paramsTable['poblado_active'] = $app->escape($generico);
				$paramsTable['poblado_name'] 	= $app->escape($nombre);
				$paramsTable['poblado_alias'] = $app->escape($nombre);
				$paramsTable['poblado_order'] 	= $app->escape($generico1);
				$paramsTable['poblado_latitude'] 	= $app->escape($latitud);
				$paramsTable['poblado_longitude'] 	= $app->escape($longitud);
				$paramsTable['poblado_distrito'] 	= $app->escape($comunidad);

				if($pais=="1"){
					$list = $app['dbs']['mysql_silex']->insert($table ,$paramsTable);
				}else if($pais=="2"){
					$list = $app['dbs']['mysql_silex1']->insert($table ,$paramsTable);
				}else if($pais=="3"){
					$list = $app['dbs']['mysql_silex2']->insert($table ,$paramsTable);
				}else if($pais=="4"){
					$list = $app['dbs']['mysql_silex3']->insert($table ,$paramsTable);
				}else if($pais=="5"){
					$list = $app['dbs']['mysql_silex4']->insert($table ,$paramsTable);
				}

				return $list;
		
	}



    /**
	 * Funcion que busca en la db un nombre de departamento registrado
	 * @param $usuario, $app
	 * @return string 
	 * @access private
	**/ 
	private function findDepartamento($usuario,$app,$pais){
		$query  = 'SELECT provincia_name from `'.$this->prefix.'departamento` where provincia_id<>0';
		if($pais=="1"){
					$list = $app['dbs']['mysql_silex']->fetchAll($query); 
				}else if($pais=="2"){
					$list = $app['dbs']['mysql_silex1']->fetchAll($query);
				}else if($pais=="3"){
					$list = $app['dbs']['mysql_silex2']->fetchAll($query);
				}else if($pais=="4"){
					$list = $app['dbs']['mysql_silex3']->fetchAll($query);
				}else if($pais=="5"){
					$list = $app['dbs']['mysql_silex4']->fetchAll($query);
				}
	    $retorno= false;
	    $usuario=strtoupper($usuario);
	    foreach ($list as $key) {
	    		$nombre=strtoupper($key['provincia_name']);
			     
			    if($nombre==$usuario) {
			    		$retorno=true;
			    		break;
			    }   
			   }
		return $retorno;
	}

	 /**
	 * Funcion que busca en la db un nombre de municipio registrado
	 * @param $usuario, $app, $pais, $departamento
	 * @return string 
	 * @access private
	**/ 
	private function findMunicipio($usuario,$app,$departamento,$pais){
		$query  = 'SELECT canton_name from `'.$this->prefix.'municipio` where canton_provincia = ' . $departamento;
		if($pais=="1"){
					$list = $app['dbs']['mysql_silex']->fetchAll($query); 
				}else if($pais=="2"){
					$list = $app['dbs']['mysql_silex1']->fetchAll($query);
				}else if($pais=="3"){
					$list = $app['dbs']['mysql_silex2']->fetchAll($query);
				}else if($pais=="4"){
					$list = $app['dbs']['mysql_silex3']->fetchAll($query);
				}else if($pais=="5"){
					$list = $app['dbs']['mysql_silex4']->fetchAll($query);
				}
	    $retorno= false;
	    $usuario=strtoupper($usuario);
	    foreach ($list as $key) {
	    		$nombre=strtoupper($key['canton_name']);
			     
			    if($nombre==$usuario) {
			    		$retorno=true;
			    		break;
			    }   
			   }
		return $retorno;
	}


	 /**
	 * Funcion que busca en la db un nombre de comunidad registrado
	 * @param $usuario, $app, $pais, $departamento
	 * @return string 
	 * @access private
	**/ 
	private function findComunidad($usuario,$app,$municipio,$pais){
		$query  = 'SELECT distrito_name from `'.$this->prefix.'distrito` where distrito_canton = ' . $municipio;
		if($pais=="1"){
					$list = $app['dbs']['mysql_silex']->fetchAll($query); 
				}else if($pais=="2"){
					$list = $app['dbs']['mysql_silex1']->fetchAll($query);
				}else if($pais=="3"){
					$list = $app['dbs']['mysql_silex2']->fetchAll($query);
				}else if($pais=="4"){
					$list = $app['dbs']['mysql_silex3']->fetchAll($query);
				}else if($pais=="5"){
					$list = $app['dbs']['mysql_silex4']->fetchAll($query);
				}
	    $retorno= false;
	    $usuario=strtoupper($usuario);
	    foreach ($list as $key) {
	    		$nombre=strtoupper($key['distrito_name']);
			     
			    if($nombre==$usuario) {
			    		$retorno=true;
			    		break;
			    }   
			   }
		return $retorno;
	}


	 /**
	 * Funcion que busca en la db un nombre de comunidad registrado
	 * @param $usuario, $app, $pais, $departamento
	 * @return string 
	 * @access private
	**/ 
	private function findPoblado($usuario,$app,$comunidad,$pais){
		$query  = 'SELECT poblado_name from `'.$this->prefix.'poblado` where poblado_distrito = ' . $comunidad;
		if($pais=="1"){
					$list = $app['dbs']['mysql_silex']->fetchAll($query); 
				}else if($pais=="2"){
					$list = $app['dbs']['mysql_silex1']->fetchAll($query);
				}else if($pais=="3"){
					$list = $app['dbs']['mysql_silex2']->fetchAll($query);
				}else if($pais=="4"){
					$list = $app['dbs']['mysql_silex3']->fetchAll($query);
				}else if($pais=="5"){
					$list = $app['dbs']['mysql_silex4']->fetchAll($query);
				}
	    $retorno= false;
	    $usuario=strtoupper($usuario);
	    foreach ($list as $key) {
	    		$nombre=strtoupper($key['distrito_name']);
			     
			    if($nombre==$usuario) {
			    		$retorno=true;
			    		break;
			    }   
			   }
		return $retorno;
	}


    /**
	 * Funcion que cambia el estado a 0 de un usuario
	 * @param $id, $app
	 * @return string 
	 * @access public
	**/ 
	public function blockUser($id,$app)
	 {
	   $update=$app['dbs']['mysql_silex']->update($this->prefix.'admin_users', array('block' =>0), array('id' => $id));                     
	   return $update;

	 }
    
    /**
	 * Funcion que cambia el es estado a 1 de un usuario
	 * @param $id, $app
	 * @return string 
	 * @access public
	**/ 
	public function activateUser($id,$app)
	 {
	   $update=$app['dbs']['mysql_silex']->update($this->prefix.'admin_users', array('block' =>1), array('id' => $id));
	   return $update;
	 }


	 /**
	 * Funcion que cambia el estado a 0 de un usuario
	 * @param $id, $app
	 * @return string 
	 * @access public
	**/ 
	public function blockRegion($app,$id,$pais,$region)
	 {
	 	 try {
	   		if($pais==1){
	   			if($region==1){
	   				$update=$app['dbs']['mysql_silex']->update($this->prefix.'departamento', array('provincia_active' =>0), array('provincia_id' => $id));    
	   			}else if($region==2){
	   				$update=$app['dbs']['mysql_silex']->update($this->prefix.'municipio', array('canton_active' =>0), array('canton_id' => $id));
	   			}else if($region==3){
	   				$update=$app['dbs']['mysql_silex']->update($this->prefix.'distrito', array('distrito_active' =>0), array('distrito_id' => $id));
	   			}else if($region==4){
	   				$update=$app['dbs']['mysql_silex']->update($this->prefix.'poblado', array('poblado_active' =>0), array('poblado_id' => $id));
	   			}
	   		}else if($pais==2){
	   			if($region==1){
	   				$update=$app['dbs']['mysql_silex1']->update($this->prefix.'departamento', array('provincia_active' =>0), array('provincia_id' => $id));    
	   			}else if($region==2){
	   				$update=$app['dbs']['mysql_silex1']->update($this->prefix.'municipio', array('canton_active' =>0), array('canton_id' => $id));
	   			}else if($region==3){
	   				$update=$app['dbs']['mysql_silex1']->update($this->prefix.'distrito', array('distrito_active' =>0), array('distrito_id' => $id));
	   			}else if($region==4){
	   				$update=$app['dbs']['mysql_silex1']->update($this->prefix.'poblado', array('poblado_active' =>0), array('poblado_id' => $id));
	   			}
	   		}else if($pais==3){
	   			if($region==1){
	   				$update=$app['dbs']['mysql_silex2']->update($this->prefix.'departamento', array('provincia_active' =>0), array('provincia_id' => $id));    
	   			}else if($region==2){
	   				$update=$app['dbs']['mysql_silex2']->update($this->prefix.'municipio', array('canton_active' =>0), array('canton_id' => $id));
	   			}else if($region==3){
	   				$update=$app['dbs']['mysql_silex2']->update($this->prefix.'distrito', array('distrito_active' =>0), array('distrito_id' => $id));
	   			}else if($region==4){
	   				$update=$app['dbs']['mysql_silex2']->update($this->prefix.'poblado', array('poblado_active' =>0), array('poblado_id' => $id));
	   			}
	   		}else if($pais==4){
	   			if($region==1){
	   				$update=$app['dbs']['mysql_silex3']->update($this->prefix.'departamento', array('provincia_active' =>0), array('provincia_id' => $id));    
	   			}else if($region==2){
	   				$update=$app['dbs']['mysql_silex3']->update($this->prefix.'municipio', array('canton_active' =>0), array('canton_id' => $id));
	   			}else if($region==3){
	   				$update=$app['dbs']['mysql_silex3']->update($this->prefix.'distrito', array('distrito_active' =>0), array('distrito_id' => $id));
	   			}else if($region==4){
	   				$update=$app['dbs']['mysql_silex3']->update($this->prefix.'poblado', array('poblado_active' =>0), array('poblado_id' => $id));
	   			}
	   		}
	   		else if($pais==5){
	   			if($region==1){
	   				$update=$app['dbs']['mysql_silex4']->update($this->prefix.'departamento', array('provincia_active' =>0), array('provincia_id' => $id));    
	   			}else if($region==2){
	   				$update=$app['dbs']['mysql_silex4']->update($this->prefix.'municipio', array('canton_active' =>0), array('canton_id' => $id));
	   			}else if($region==3){
	   				$update=$app['dbs']['mysql_silex4']->update($this->prefix.'distrito', array('distrito_active' =>0), array('distrito_id' => $id));
	   			}else if($region==4){
	   				$update=$app['dbs']['mysql_silex4']->update($this->prefix.'poblado', array('poblado_active' =>0), array('poblado_id' => $id));
	   			}
	   		}
	     
	   }
	   catch(Exception $exception) 
	   { 
	     return null;
	   } 
	                  
	   return $update;

	 }

	 /**
	 * Funcion que cambia el estado a 0 de un usuario
	 * @param $id, $app
	 * @return string 
	 * @access public
	**/ 
	public function activeRegion($app,$id,$pais,$region)
	 {
	 	 try {
	   		if($pais==1){
	   			if($region==1){
	   				$update=$app['dbs']['mysql_silex']->update($this->prefix.'departamento', array('provincia_active' =>1), array('provincia_id' => $id));    
	   			}else if($region==2){
	   				$update=$app['dbs']['mysql_silex']->update($this->prefix.'municipio', array('canton_active' =>1), array('canton_id' => $id));
	   			}else if($region==3){
	   				$update=$app['dbs']['mysql_silex']->update($this->prefix.'distrito', array('distrito_active' =>1), array('distrito_id' => $id));
	   			}else if($region==4){
	   				$update=$app['dbs']['mysql_silex']->update($this->prefix.'poblado', array('poblado_active' =>1), array('poblado_id' => $id));
	   			}
	   		}else if($pais==2){
	   			if($region==1){
	   				$update=$app['dbs']['mysql_silex1']->update($this->prefix.'departamento', array('provincia_active' =>1), array('provincia_id' => $id));    
	   			}else if($region==2){
	   				$update=$app['dbs']['mysql_silex1']->update($this->prefix.'municipio', array('canton_active' =>1), array('canton_id' => $id));
	   			}else if($region==3){
	   				$update=$app['dbs']['mysql_silex1']->update($this->prefix.'distrito', array('distrito_active' =>1), array('distrito_id' => $id));
	   			}else if($region==4){
	   				$update=$app['dbs']['mysql_silex1']->update($this->prefix.'poblado', array('poblado_active' =>1), array('poblado_id' => $id));
	   			}
	   		}else if($pais==3){
	   			if($region==1){
	   				$update=$app['dbs']['mysql_silex2']->update($this->prefix.'departamento', array('provincia_active' =>1), array('provincia_id' => $id));    
	   			}else if($region==2){
	   				$update=$app['dbs']['mysql_silex2']->update($this->prefix.'municipio', array('canton_active' =>1), array('canton_id' => $id));
	   			}else if($region==3){
	   				$update=$app['dbs']['mysql_silex2']->update($this->prefix.'distrito', array('distrito_active' =>1), array('distrito_id' => $id));
	   			}else if($region==4){
	   				$update=$app['dbs']['mysql_silex2']->update($this->prefix.'poblado', array('poblado_active' =>1), array('poblado_id' => $id));
	   			}
	   		}else if($pais==4){
	   			if($region==1){
	   				$update=$app['dbs']['mysql_silex3']->update($this->prefix.'departamento', array('provincia_active' =>1), array('provincia_id' => $id));    
	   			}else if($region==2){
	   				$update=$app['dbs']['mysql_silex3']->update($this->prefix.'municipio', array('canton_active' =>1), array('canton_id' => $id));
	   			}else if($region==3){
	   				$update=$app['dbs']['mysql_silex3']->update($this->prefix.'distrito', array('distrito_active' =>1), array('distrito_id' => $id));
	   			}else if($region==4){
	   				$update=$app['dbs']['mysql_silex3']->update($this->prefix.'poblado', array('poblado_active' =>1), array('poblado_id' => $id));
	   			}
	   		}
	   		else if($pais==5){
	   			if($region==1){
	   				$update=$app['dbs']['mysql_silex4']->update($this->prefix.'departamento', array('provincia_active' =>1), array('provincia_id' => $id));    
	   			}else if($region==2){
	   				$update=$app['dbs']['mysql_silex4']->update($this->prefix.'municipio', array('canton_active' =>1), array('canton_id' => $id));
	   			}else if($region==3){
	   				$update=$app['dbs']['mysql_silex4']->update($this->prefix.'distrito', array('distrito_active' =>1), array('distrito_id' => $id));
	   			}else if($region==4){
	   				$update=$app['dbs']['mysql_silex4']->update($this->prefix.'poblado', array('poblado_active' =>1), array('poblado_id' => $id));
	   			}
	   		}
	     
	   }
	   catch(Exception $exception) 
	   { 
	     return null;
	   } 
	                  
	   return $update;

	 }


     /**
	 * Funcion que elimina un usuario de la db
	 * @param $id, $app
	 * @return string 
	 * @access public
	**/ 
	 public function deleteUser($id,$app)
	 {
	   try {
	     $del=$app['dbs']['mysql_silex']->delete($this->prefix.'admin_users', array('id' => $id));
	     
	   }
	   catch(Exception $exception) 
	   { 
	     return null;
	   }  
	    return $del;
	 }

	  /**
	 * Funcion que elimina una region de la db
	 * @param $id, $app
	 * @return string 
	 * @access public
	**/ 
	 public function deleteRegion($app,$id,$pais,$region)
	 {
	   try {
	   		if($pais==1){
	   			if($region==1){
	   				$del=$app['dbs']['mysql_silex']->delete($this->prefix.'departamento', array('provincia_id' => $id));
	   			}else if($region==2){
	   				$del=$app['dbs']['mysql_silex']->delete($this->prefix.'municipio', array('canton_id' => $id));
	   			}else if($region==3){
	   				$del=$app['dbs']['mysql_silex']->delete($this->prefix.'distrito', array('distrito_id' => $id));
	   			}else if($region==4){
	   				$del=$app['dbs']['mysql_silex']->delete($this->prefix.'poblado', array('poblado_id' => $id));
	   			}
	   		}else if($pais==2){
	   			if($region==1){
	   				$del=$app['dbs']['mysql_silex1']->delete($this->prefix.'departamento', array('provincia_id' => $id));
	   			}else if($region==2){
	   				$del=$app['dbs']['mysql_silex1']->delete($this->prefix.'municipio', array('canton_id' => $id));
	   			}else if($region==3){
	   				$del=$app['dbs']['mysql_silex1']->delete($this->prefix.'distrito', array('distrito_id' => $id));
	   			}else if($region==4){
	   				$del=$app['dbs']['mysql_silex1']->delete($this->prefix.'poblado', array('poblado_id' => $id));
	   			}
	   		}else if($pais==3){
	   			if($region==1){
	   				$del=$app['dbs']['mysql_silex2']->delete($this->prefix.'departamento', array('provincia_id' => $id));
	   			}else if($region==2){
	   				$del=$app['dbs']['mysql_silex2']->delete($this->prefix.'municipio', array('canton_id' => $id));
	   			}else if($region==3){
	   				$del=$app['dbs']['mysql_silex2']->delete($this->prefix.'distrito', array('distrito_id' => $id));
	   			}else if($region==4){
	   				$del=$app['dbs']['mysql_silex2']->delete($this->prefix.'poblado', array('poblado_id' => $id));
	   			}
	   		}else if($pais==4){
	   			if($region==1){
	   				$del=$app['dbs']['mysql_silex3']->delete($this->prefix.'departamento', array('provincia_id' => $id));
	   			}else if($region==2){
	   				$del=$app['dbs']['mysql_silex3']->delete($this->prefix.'municipio', array('distrito_canton_id' => $id));
	   			}else if($region==3){
	   				$del=$app['dbs']['mysql_silex3']->delete($this->prefix.'distrito', array('distrito_id' => $id));
	   			}else if($region==4){
	   				$del=$app['dbs']['mysql_silex3']->delete($this->prefix.'poblado', array('poblado_id' => $id));
	   			}
	   		}
	   		else if($pais==5){
	   			if($region==1){
	   				$del=$app['dbs']['mysql_silex4']->delete($this->prefix.'departamento', array('provincia_id' => $id));
	   			}else if($region==2){
	   				$del=$app['dbs']['mysql_silex4']->delete($this->prefix.'municipio', array('canton_id' => $id));
	   			}else if($region==3){
	   				$del=$app['dbs']['mysql_silex4']->delete($this->prefix.'distrito', array('distrito_id' => $id));
	   			}else if($region==4){
	   				$del=$app['dbs']['mysql_silex4']->delete($this->prefix.'poblado', array('poblado_id' => $id));
	   			}
	   		}
	     
	   }
	   catch(Exception $exception) 
	   { 
	     return null;
	   }  
	    return $del;
	 }


	 /**
	 * Funcion que devuelve el formulario de editarUsuario
	 * @param $id, $app
	 * @return string 
	 * @access public
	**/ 
	 public function editUsuario($app,$id){

		$datos=$this->findUser($app,$id);
		$usuario=$datos[0]['name'];
		$email=$datos[0]['mail'];
		$pais=$datos[0]['pais'];
		$rol=$datos[0]['usertype'];

		$formulario  ="<div class='well'><h3>Editar Usuario</h3>";
		$formulario .= "<form id='mensajeError' accept-charset='utf-8' method=''>";
		$formulario .= "<div class='ruleta'>";
		$formulario .= "<div class='rul_1'>Nombre Usuario:</div>";
		$formulario .= "<div class='rul_2'><input id='nombre' name='nombre' type='text' value='".$usuario."' readonly/></div>";
		$formulario .= "<div class='rul_1'>Constraseña:</div>";
		$formulario .= "<div class='rul_2'><input id='pass' name='pass' type='password' placeholder='Password de acceso' /></div>";
		if($_SESSION["role"] == 0){
	       $formulario .= "<div class='rul_1'>Administrar Pa&iacute;s de:</div>";
		   $formulario .= "<div class='rul_2'>".$this->getPaisUser($app)."</div>";
		}else{
		   $formulario.="<input type='hidden' id='pais' name='pais' value='".$_SESSION["pais_user"]."'>";
		}
		
		$formulario .= "<div class='rul_1'>Email del Usuario:</div>";
		$formulario .= "<div class='rul_2'><input id='email' name='email' type='text' placeholder='Correo Electr&oacute;nico'  value='".$email."'/></div>";
		$formulario .= "<center><input type='button' id='boton_ruleta' name='boton_ruleta' class='btn btn-danger' value='Guardar' onclick='editUser(".$id.");'/></center>";
		$formulario .= "</div></div></form>";

		return $formulario;

	}


	/**
	 * Funcion que devuelve todo los datos de un usuario
	 * @param $id, $app
	 * @return string 
	 * @access public
	**/ 
	public function findUser($app,$id)
	 {
	$query  = 'SELECT name, mail, pais, usertype FROM `'.$this->prefix.'admin_users` where id='.$id;
	 $list 	= $app['dbs']['mysql_silex']->fetchAll($query);
	 return $list;
	 }



	 /**
	 * Funcion que devuelve el formulario de editarRegion
	 * @param $id, $app
	 * @return string 
	 * @access public
	**/ 
	 public function editarRegion($app,$id,$region,$pais){

		$datos=$this->findRegion($app,$id,$region,$pais);
		$nombre=$datos[0]['name'];
		$latitud=$datos[0]['latitude'];
		$longitud=$datos[0]['longitud'];

		$formulario  ="<div class='well'><h3>Editar Region</h3>";
		$formulario .= "<form id='mensajeError' accept-charset='utf-8' method=''>";
		$formulario .= "<div class='ruleta'>";
		$formulario .= "<div class='rul_1'>Nombre:</div>";
		$formulario .= "<div class='rul_2'><input id='nombre' name='nombre' type='text' value='".$nombre."'/></div>";
		$formulario .= "<div class='rul_1'>Latitud:</div>";
		$formulario .= "<div class='rul_2'><input id='latitud' name='latitud' type='text' value='".$latitud."' /></div>";
		$formulario .= "<div class='rul_1'>Longitud:</div>";
		$formulario .= "<div class='rul_2'><input id='longitud' name='longitud' type='text' value='".$longitud."'/></div>";
		$formulario.="<input type='hidden' id='pais' name='pais' value='".$pais."'>";
		$formulario.="<input type='hidden' id='region' name='region' value='".$region."'>";
		$formulario .= "<center><input type='button' id='boton_ruleta' name='boton_ruleta' class='btn btn-danger' value='Guardar' onclick='editRegion(".$id.");'/></center>";
		$formulario .= "</div></div></form>";

		return $formulario;

	}


	/**
	 * Funcion que devuelve todo los datos de una region
	 * @param $id, $app
	 * @return string 
	 * @access public
	**/ 
	public function findRegion($app,$id,$region,$pais)
	 {
	 	if($region==1){
          $query  = 'SELECT provincia_latitude AS "latitude", provincia_longitude AS "longitud", provincia_name AS "name" FROM `'.$this->prefix.'departamento` where provincia_id= ' . $id;
	 	}else if($region==2){
	 	  $query  = 'SELECT canton_latitude AS "latitude", canton_longitude  AS "longitud", canton_name  AS "name" FROM `'.$this->prefix.'municipio` where canton_id= ' . $id;	
	 	}else if($region==3){
	 	  $query  = 'SELECT distrito_latitude AS "latitude", distrito_longitude AS "longitud", distrito_name AS "name" FROM `'.$this->prefix.'distrito` where distrito_id= ' . $id;	
	 	}else if($region==4){
	 	  $query  = 'SELECT poblado_latitude AS "latitude", poblado_longitude AS "longitud", poblado_name AS "name" FROM `'.$this->prefix.'poblado` where poblado_id= ' . $id;		
	 	}

	 	 if($pais=="1"){
		 		$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		 }else if($pais=="2"){
		 	    $list 	= $app['dbs']['mysql_silex1']->fetchAll($query);
		 }else if($pais=="3"){
		 	    $list 	= $app['dbs']['mysql_silex2']->fetchAll($query);
		 }else if($pais=="4"){
		 	    $list 	= $app['dbs']['mysql_silex3']->fetchAll($query);
		 }else if($pais=="5"){
		 	    $list 	= $app['dbs']['mysql_silex4']->fetchAll($query);
		 }

	 return $list;
	 }


     /**
	 * Funcion que devuelve los paises dentro de un select
	 * @param $id, $app
	 * @return string 
	 * @access public
	**/ 
	 public function getPaisUser($app){

			$query="SELECT * from ".$this->prefix."cobertura_pais";
	            $res = $app['db']->fetchAll($query);
	            $html ="<select id='pais'name='pais'>";
	            $otros="";
	            foreach ($res as $key ) {

	            	//if($key['id']==$id){
	            	$otros.="<option value = '".$key['id']."'>".$key['nombre']."</option>";    

	            	//}else{
	            	// $otros.="<option value = '".$key['id']."'>".$key['nombre']."</option>";    
	            	//}
	            }
	            $html.=$otros;
	            $html.="</select>";
	    return $html;

	}


    /**
	 * Funcion que actualiza los datos un usuario
	 * @param $id, $app
	 * @return string 
	 * @access public
	**/ 
	public function updateUser($app,$nombre,$pass,$pais,$email,$id)
	 {
		$pass=md5($pass);
		$sql="UPDATE ".$this->prefix."admin_users set password ='".$pass."',mail ='".$email."',pais ='".$pais."' where id = ".$id;
		return $app['dbs']['mysql_silex']->ExecuteQuery($sql);
	}


    /**
	 * Funcion que devuelve el formulario de subir archivo
	 * @param $app
	 * @return string 
	 * @access public
	**/ 
	public function getUpload($app,$pais){

                 $tipoSubida=1;
	        $html= '<form id="upVariosArchivos" method="post" action="./subirVariosArchivo" enctype="multipart/form-data" >';
		   $html.="<input type='hidden' id='pais' name='pais' value='".$pais."'>";
		   $html.='<input type="hidden" id="subida" name="subida" value="'.$tipoSubida.'" />';
	      
	       $html.='<div id="tabla" class="table_users">';
		   $html.='<table class="table table-hover table-striped">';
		   $html.='<th class="header">Categoria</th>';
		   $html.='<th class="header">Archivo</th>'; 
		   $html.='<tr><td>Agencia</td><td><input name="ag" type="file" id="ag"></td></tr>';
		   $html.='<tr><td>Cobertura 2G</td><td><input name="2g" type="file" id="2g"></td></tr>';
		   $html.='<tr><td>Cobertura 3G</td><td><input name="3g" type="file" id="3g"></td></tr>';
		   $html.='<tr><td>Cobertura 4G</td><td><input name="4g" type="file" id="4g"></td></tr>';
		   $html.='<tr><td>Velocidad 2G</td><td><input name="v2g" type="file" id="v2g"></td></tr>';
		   $html.='<tr><td>Velocidad 3G</td><td><input name="v3g" type="file" id="v3g"></td></tr>';
		   $html.='<tr><td>Velocidad 4G</td><td><input name="v4g" type="file" id="v4g"></td></tr>';
		   
		   $html.='<tr><td></td><td><input name="boton" type"button" id="boton" class="btn btn-danger" value="Subir" onclick="subirVariosArchivo();"></td></tr>';	 
	   	   $html.='</div>';
	   	   
	       $html.='</form>';
	   	return $html;

	}

	 /**
	 * Funcion que inserta en la db el los datos del archivo kmz por pais
	 * @param $app, pais, contenido
	 * @return string 
	 * @access public
	**/ 
	public function insertArchivo($map_v,$map_size,$content,$pais,$app){
	   #crea un nuevo registro
				$paramsTable = array();

				$layer_type=$map_v;
			    $layer_size=$map_size;
			    $layer_time= time();
			    $layer_data=$content;
				
				$table = $this->prefix."layers";

				$paramsTable['layer_type'] 	= $app->escape($layer_type);
				$paramsTable['layer_size'] 	= $app->escape($layer_size);
				$paramsTable['layer_time'] 	= $app->escape($layer_time);
				$paramsTable['layer_data'] 	= $layer_data;

				if($pais=="1"){
					$insert = $app['dbs']['mysql_silex']->insert($table ,$paramsTable);
				    return $insert;
				}else if($pais=="2"){
					$insert = $app['dbs']['mysql_silex1']->insert($table ,$paramsTable);
				    return $insert;
				}else if($pais=="3"){
					$insert = $app['dbs']['mysql_silex2']->insert($table ,$paramsTable);
				    return $insert;
				}else if($pais=="4"){
					$insert = $app['dbs']['mysql_silex3']->insert($table ,$paramsTable);
				    return $insert;
				}else if($pais=="5"){
					$insert = $app['dbs']['mysql_silex4']->insert($table ,$paramsTable);
				    return $insert;
					
				}

		
	}

    /**
	 * Funcion que arma la vista de formulario de texto
	 * @param $app
	 * @return string 
	 * @access public
	**/ 
	public function formularioTexto($app){
		$formulario = "<form id='editText' method='post' action='./editarTexto'>";
		$formulario .= "<div class='ruleta'>";
		
			$formulario .= "<div class='rul_1'>Tipo de Servicio:</div>";
			$formulario .= "<div class='rul_1'>".$this->getServicio($app)."</div>";
		if($_SESSION["role"] == 0){
			$formulario .= "<div class='rul_1'>Seleccionar Pais:</div>";
			$formulario .= "<div class='rul_2'>".$this->getPais($app)."</div>";
		}else{
			$formulario.="<input type='hidden' id='pais' name='pais' value='".$_SESSION["pais_user"]."'>";
		}
		$formulario .= "<center><input type='submit' id='boton_ruleta' name='boton_ruleta' class='btn btn-danger' value='Buscar'/></center>";
		$formulario .= "</div></div></form>";

		return $formulario;

	}


	/**
	 * Funcion que arma la vista de formulario de regiones
	 * @param $app
	 * @return string 
	 * @access public
	**/ 
	public function formularioRegiones($app){
		$formulario = "<form id='editRegion' method='post' action='./editarRegion'>";
		$formulario .= "<div class='ruleta'>";
		
			$formulario .= "<div class='rul_1'>Region:</div>";
			$formulario .= "<div class='rul_1'>".$this->getRegion($app)."</div>";
		if($_SESSION["role"] == 0){
			$formulario .= "<div class='rul_1'>Seleccionar Pais:</div>";
			$formulario .= "<div class='rul_2'>".$this->getPais($app)."</div>";
		}else{
			$formulario.="<input type='hidden' id='pais' name='pais' value='".$_SESSION["pais_user"]."'>";
		}
		$formulario .= "<center><input type='submit' id='boton_ruleta' name='boton_ruleta' class='btn btn-danger' value='Buscar'/></center>";
		$formulario .= "</div></div></form>";

		return $formulario;

	}

	/**
	 * Funcion que arma select con los servicio desponibles
	 * @param $app
	 * @return string 
	 * @access public
	**/ 
	public function getServicio($app){

			$query="SELECT * from ".$this->prefix."tipo_servicio where estado=0";
	            $res = $app['dbs']['mysql_silex']->fetchAll($query);
	            $html ="<select id='servicio' name='servicio'>";
	            foreach ($res as $key ) {
	            $html.="<option value = '".$key['id']."'>".$key['name']."</option>";    
	            }
	            $html.="</select>";

	    return $html;
	} 
	/**
	 * Funcion que arma select con los nombre de las regiones segun pais
	 * @param $app
	 * @return string 
	 * @access public
	**/ 
	public function getRegion($app){
		    if($_SESSION["pais_user"]==0){
		    	 $query="SELECT * from ".$this->prefix."pais_regiones where id_pais=1";
		    }else{
		    	$query="SELECT * from ".$this->prefix."pais_regiones where id_pais=". $_SESSION["pais_user"];
		    }
		    
		        $nombre="";
	            $res = $app['dbs']['mysql_silex']->fetchAll($query);
	            $html ="<select id='region' name='region'>";
	            foreach ($res as $key ) {
	            $html.="<option value = '".$key['tipo_region']."'>".$key['region']."</option>"; 
	            $nombre.="<input type='hidden' id='region".$key['tipo_region']."' name='region".$key['tipo_region']."' value='".$key['region']."'>";
	            }
	            $html.="</select>";

	    return $html.$nombre;
	} 


	/**
	* Obtiene el listado de todas las regiones por pais
	*
	* @var objects
	* @var string
	* @return array
	*/
	public function editRegion($app,$region,$pais,$regionActual)
	{

	  if($region==1){
	  	$query  = 'SELECT provincia_id AS "id", provincia_active AS "block", provincia_name AS "name" FROM `'.$this->prefix.'departamento` where provincia_id<>0';
	  	 $html = "<form id='editRegion' method='post' action='./createDepar'>";
	  	 $html .= '<input type="hidden" id="paisActual" name="paisActual" value="'.$pais.'">';
	  	 $html .= "<input type='submit' value='Crear ". $regionActual  ."' class='btn btn-danger' >";
		 $html .= "</form>";
	  }else if($region==2){
	  	$query  = 'SELECT canton_id AS "id", canton_active  AS "block", canton_name  AS "name" FROM `'.$this->prefix.'municipio`';
	  	 $html = "<form id='editRegion' method='post' action='./createMuni'>";
	  	 $html .= '<input type="hidden" id="paisActual" name="paisActual" value="'.$pais.'">';
	  	 $html  .="<input type='submit' value='Crear ". $regionActual . "' class='btn btn-danger'>";
		 $html .= "</form>";
	  	 
	  	 $html .= "<div class='rul_1'>Departamento:</div>";
	     $html .= "<div class='rul_1'>".$this->getDepartamentos($app,$pais,1)."</div>"; 
	  }else if($region==3){
	  	$query  = 'SELECT distrito_id AS "id", distrito_active AS "block", distrito_name AS "name" FROM `'.$this->prefix.'distrito`';
	  	 $html = "<form id='editRegion' method='post' action='./createComunidad'>";
	  	 $html .= '<input type="hidden" id="paisActual" name="paisActual" value="'.$pais.'">';
	  	 $html .="<input type='submit' value='Crear ". $regionActual ."' class='btn btn-danger'>";
		 $html .= "</form>";
	  	 
	  	 $html .= "<div class='rul_1'>Departamento:</div>";
	     $html .= "<div class='rul_1'>".$this->getDepartamentos($app,$pais,2)."</div>"; 
	     $html .= "<div class='rul_1'>Municipio:</div>";
	     $html .= "<select id='municipio' name='municipio' disabled='disabled'><option value='0'>Municipio</option></select>";
	  }else if($region==4){
	  	$query  = 'SELECT poblado_id AS "id", poblado_active AS "block", poblado_name AS "name" FROM `'.$this->prefix.'poblado`';
	  	 $html = "<form id='editRegion' method='post' action='./createPoblado'>";
	  	 $html .= '<input type="hidden" id="paisActual" name="paisActual" value="'.$pais.'">';
	  	 $html .="<input type='submit' value='Crear ". $regionActual  . " 'class='btn btn-danger'>";
		 $html .= "</form>";
	  	
         $html .= "<div class='rul_1'>Departamento:</div>";
	     $html .= "<div class='rul_1'>".$this->getDepartamentos($app,$pais,3)."</div>"; 
	     $html .= "<div class='rul_1'>Municipio:</div>";
	     $html .= "<select id='deparmunicipio' name='deparmunicipio' disabled='disabled'><option value='0'>Municipio</option></select>";
	     $html .= "<div class='rul_1'>Comunidad:</div>";
	     $html .= "<select id='comunidad' name='comunidad' disabled='disabled'><option value='0'>Comunidad</option></select>";
	  }

	     if($pais=="1"){
		 		$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		 }else if($pais=="2"){
		 	    $list 	= $app['dbs']['mysql_silex1']->fetchAll($query);
		 }else if($pais=="3"){
		 	    $list 	= $app['dbs']['mysql_silex2']->fetchAll($query);
		 }else if($pais=="4"){
		 	    $list 	= $app['dbs']['mysql_silex3']->fetchAll($query);
		 }else if($pais=="5"){
		 	    $list 	= $app['dbs']['mysql_silex4']->fetchAll($query);
		 }
  
	  // $html.='<br/><br/><br/>';  
	   $html.='<div id="tabla" class="table_users">';
	   $html.='<table class="table table-hover table-striped">';
	   $html.='<th class="header">ID</th>';
	   $html.='<th class="header">Nombre</th>';    
	   $html.='<th class="header" colspan="3" style="width:28%;">Opciones</th>'; 
	   $contador=1;

		   foreach ($list as $key) {
		     $html.='<tr>';
		     $html.='<td>'.$key['id'].'</td>';
		     $html.='<td>'.$key['name'].'</td>';
		     //$html.='<td> <span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_user(this.id)" title="Editar"><i class="icon-edit"></i></span> ';
		     $html.='<td><form id="editar'.$contador.'" method="post" action="./editRegion">';
		     $html.= '<span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_region('.$contador.')" title="Editar"><i class="icon-edit"></i></span>';
		     $html.= '<input type="hidden" name="cadena" value="'.$key['id'].'">';
		     $html .= '<input type="hidden" id="paisActual" name="paisActual" value="'.$pais.'">';
		     $html.= '<input type="hidden" name="region" value="'.$region.'">';
		     $html.= '</form></td>';
  
		     $html.='<td><span class="btn btn-danger btn-small" id="'.$key['id'].'" onClick="eliminar_region(this.id,'.$pais.','.$region.')" title="Eliminar"><i class="icon-remove"></i></span></td>';
		    if($key['block']=='1') 
		       $html.=' <td><span class="btn btn-warning btn-small" id="'.$key['id'].'" onClick="bloquear_region(this.id,'.$pais.','.$region.')" title="Bloquear"><i class="icon-ban-circle"></i></span></td>';
		     else 
		       $html.=' <td><span class="btn btn-success btn-small" id="'.$key['id'].'" onClick="activar_region(this.id,'.$pais.','.$region.')" title="Activar"><i class="icon-ok-circle"></i></span></td>';
		                                
		    $html.='</tr>';

		    $contador=$contador+1;
		   }
   		$html.='</table>';
   		$html.='</div>';
   		
   
   	return $html;
	}

	/**
	* Obtiene el listado de municipios
	*
	* @var objects
	* @var string
	* @return array
	*/
	public function editEspecificMuni($app,$depar,$pais)
	{

	  	$query  = 'SELECT canton_id AS "id", canton_active AS "block", canton_name AS "name" FROM `'.$this->prefix.'municipio`  where canton_provincia=' . $depar ;
	     if($pais=="1"){
		 		$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		 }else if($pais=="2"){
		 	    $list 	= $app['dbs']['mysql_silex1']->fetchAll($query);
		 }else if($pais=="3"){
		 	    $list 	= $app['dbs']['mysql_silex2']->fetchAll($query);
		 }else if($pais=="4"){
		 	    $list 	= $app['dbs']['mysql_silex3']->fetchAll($query);
		 }else if($pais=="5"){
		 	    $list 	= $app['dbs']['mysql_silex4']->fetchAll($query);
		 }
	   
	   $html ='<div id="tabla" class="table_users">';
	   $html.='<table class="table table-hover table-striped">';
	   $html.='<th class="header">ID</th>';
	   $html.='<th class="header">Nombre</th>';    
	   $html.='<th class="header" colspan="3" style="width:28%;">Opciones</th>'; 
	   $contador=1;

		   foreach ($list as $key) {
		     $html.='<tr>';
		     $html.='<td>'.$key['id'].'</td>';
		     $html.='<td>'.$key['name'].'</td>';
		     //$html.='<td> <span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_user(this.id)" title="Editar"><i class="icon-edit"></i></span> ';
		     $html.='<td><form id="editar'.$contador.'" method="post" action="./editRegion">';
		     $html.= '<span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_region('.$contador.')" title="Editar"><i class="icon-edit"></i></span>';
		     $html.= '<input type="hidden" name="cadena" value="'.$key['id'].'">';
		     $html.= '<input type="hidden" name="region" value="2">';
		     $html .= '<input type="hidden" id="paisActual" name="paisActual" value="'.$pais.'">';
		     $html.= '</form></td>';
   
            
		     $html.='<td><span class="btn btn-danger btn-small" id="'.$key['id'].'" onClick="eliminar_region(this.id,'.$pais.',2)" title="Eliminar"><i class="icon-remove"></i></span></td>';
		    if($key['block']=='1') 
		       $html.=' <td><span class="btn btn-warning btn-small" id="'.$key['id'].'" oonClick="bloquear_region(this.id,'.$pais.',2)" title="Bloquear"><i class="icon-ban-circle"></i></span></td>';
		     else 
		       $html.=' <td><span class="btn btn-success btn-small" id="'.$key['id'].'" onClick="activar_region(this.id,'.$pais.',2)" title="Activar"><i class="icon-ok-circle"></i></span></td>';
		                                
		    $html.='</tr>';

		    $contador=$contador+1;
		   }
   		$html.='</table>';
   		$html.='</div>';
   		$html.= '<input type="hidden" id="paisActual" name="paisActual" value="'.$pais.'">';
   
   	return $html;
	}

	/**
	* Obtiene el listado especifico de comunidades
	*
	* @var objects
	* @var string
	* @return array
	*/
	public function editEspecificComuni($app,$muni,$pais)
	{

	  	$query  = 'SELECT distrito_id AS "id", distrito_active AS "block", distrito_name AS "name" FROM `'.$this->prefix.'distrito`  where distrito_canton=' . $muni ;
	     if($pais=="1"){
		 		$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		 }else if($pais=="2"){
		 	    $list 	= $app['dbs']['mysql_silex1']->fetchAll($query);
		 }else if($pais=="3"){
		 	    $list 	= $app['dbs']['mysql_silex2']->fetchAll($query);
		 }else if($pais=="4"){
		 	    $list 	= $app['dbs']['mysql_silex3']->fetchAll($query);
		 }else if($pais=="5"){
		 	    $list 	= $app['dbs']['mysql_silex4']->fetchAll($query);
		 }
	   
	   $html ='<div id="tabla" class="table_users">';
	   $html.='<table class="table table-hover table-striped">';
	   $html.='<th class="header">ID</th>';
	   $html.='<th class="header">Nombre</th>';    
	   $html.='<th class="header" colspan="3" style="width:28%;">Opciones</th>'; 
	   $contador=1;

		   foreach ($list as $key) {
		     $html.='<tr>';
		     $html.='<td>'.$key['id'].'</td>';
		     $html.='<td>'.$key['name'].'</td>';
		     //$html.='<td> <span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_user(this.id)" title="Editar"><i class="icon-edit"></i></span> ';
		     $html.='<td><form id="editar'.$contador.'" method="post" action="./editRegion">';
		     $html.= '<span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_region('.$contador.')" title="Editar"><i class="icon-edit"></i></span>';
		     $html.= '<input type="hidden" name="cadena" value="'.$key['id'].'">';
		     $html.= '<input type="hidden" name="region" value="3">';
		     $html .= '<input type="hidden" id="paisActual" name="paisActual" value="'.$pais.'">';
		     $html.= '</form></td>';
   
            
		     $html.='<td><span class="btn btn-danger btn-small" id="'.$key['id'].'" onClick="eliminar_region(this.id,'.$pais.',3)" title="Eliminar"><i class="icon-remove"></i></span></td>';
		    if($key['block']=='1') 
		       $html.=' <td><span class="btn btn-warning btn-small" id="'.$key['id'].'" onClick="bloquear_region(this.id,'.$pais.',3)" title="Bloquear"><i class="icon-ban-circle"></i></span></td>';
		     else 
		       $html.=' <td><span class="btn btn-success btn-small" id="'.$key['id'].'" onClick="activar_region(this.id,'.$pais.',3)" title="Activar"><i class="icon-ok-circle"></i></span></td>';
		                                
		    $html.='</tr>';

		    $contador=$contador+1;
		   }
   		$html.='</table>';
   		$html.='</div>';
   		$html.= '<input type="hidden" id="paisActual" name="paisActual" value="'.$pais.'">';
   
   	return $html;
	}


	/**
	* Obtiene el listado especifico de poblados
	*
	* @var objects
	* @var string
	* @return array
	*/
	public function editEspecificPoblado($app,$comunidad,$pais)
	{

	  	$query  = 'SELECT poblado_id AS "id", poblado_active AS "block", poblado_name AS "name" FROM `'.$this->prefix.'poblado`  where poblado_distrito=' . $comunidad ;
	     if($pais=="1"){
		 		$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		 }else if($pais=="2"){
		 	    $list 	= $app['dbs']['mysql_silex1']->fetchAll($query);
		 }else if($pais=="3"){
		 	    $list 	= $app['dbs']['mysql_silex2']->fetchAll($query);
		 }else if($pais=="4"){
		 	    $list 	= $app['dbs']['mysql_silex3']->fetchAll($query);
		 }else if($pais=="5"){
		 	    $list 	= $app['dbs']['mysql_silex4']->fetchAll($query);
		 }
	   
	   $html ='<div id="tabla" class="table_users">';
	   $html.='<table class="table table-hover table-striped">';
	   $html.='<th class="header">ID</th>';
	   $html.='<th class="header">Nombre</th>';    
	   $html.='<th class="header" colspan="3" style="width:28%;">Opciones</th>'; 
	   $contador=1;

		   foreach ($list as $key) {
		     $html.='<tr>';
		     $html.='<td>'.$key['id'].'</td>';
		     $html.='<td>'.$key['name'].'</td>';
		     //$html.='<td> <span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_user(this.id)" title="Editar"><i class="icon-edit"></i></span> ';
		     $html.='<td><form id="editar'.$contador.'" method="post" action="./editRegion">';
		     $html.= '<span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_region('.$contador.')" title="Editar"><i class="icon-edit"></i></span>';
		     $html.= '<input type="hidden" name="cadena" value="'.$key['id'].'">';
		     $html.= '<input type="hidden" name="region" value="4">';
		     $html .= '<input type="hidden" id="paisActual" name="paisActual" value="'.$pais.'">';
		     $html.= '</form></td>';
   
            
		     $html.='<td><span class="btn btn-danger btn-small" id="'.$key['id'].'" onClick="eliminar_region(this.id,'.$pais.',4)" title="Eliminar"><i class="icon-remove"></i></span></td>';
		    if($key['block']=='1') 
		       $html.=' <td><span class="btn btn-warning btn-small" id="'.$key['id'].'" onClick="bloquear_region(this.id,'.$pais.',4)" title="Bloquear"><i class="icon-ban-circle"></i></span></td>';
		     else 
		       $html.=' <td><span class="btn btn-success btn-small" id="'.$key['id'].'" onClick="activar_region(this.id,'.$pais.',4)" title="Activar"><i class="icon-ok-circle"></i></span></td>';
		                                
		    $html.='</tr>';

		    $contador=$contador+1;
		   }
   		$html.='</table>';
   		$html.='</div>';
   		$html.= '<input type="hidden" id="paisActual" name="paisActual" value="'.$pais.'">';
   
   	return $html;
	}

	/**
	* Obtiene el listado de departamentos
	*
	* @var objects
	* @var string
	* @return array
	*/
	private function getDepartamentos($app,$pais,$tipo)
	{

	  	$query  = 'SELECT provincia_id AS "id", provincia_active AS "block", provincia_name AS "name" FROM `'.$this->prefix.'departamento` where provincia_id<>0';
	     if($pais=="1"){
		 		$res 	= $app['dbs']['mysql_silex']->fetchAll($query);
		 }else if($pais=="2"){
		 	    $res 	= $app['dbs']['mysql_silex1']->fetchAll($query);
		 }else if($pais=="3"){
		 	    $res 	= $app['dbs']['mysql_silex2']->fetchAll($query);
		 }else if($pais=="4"){
		 	    $res 	= $app['dbs']['mysql_silex3']->fetchAll($query);
		 }else if($pais=="5"){
		 	    $res 	= $app['dbs']['mysql_silex4']->fetchAll($query);
		 }

		 $html="";
			 if($tipo==1){
			 		$html .="<select id='depar' name='depar'>";
			 }else if($tipo==2){
			 		$html .="<select id='deparmuni' name='deparmuni'>";
			 }else if($tipo==3){
			 		$html .="<select id='deparmunicomuni' name='deparmunicomuni'>";
			 }
        
        foreach ($res as $key ) {
        $html.="<option value = '".$key['id']."'>".$key['name']."</option>"; 
        }
        $html.="</select>";
	    return $html;
 
   	return $html;
	}


	/**
	 * Funcion que arma la vista para editarTexto
	 * @param $app, servicio, pais
	 * @return string 
	 * @access public
	**/ 
	public function editTexto($app,$servicio,$pais){

		$datos=$this->findText($app,$servicio,$pais);

		$formulario  ="<div class='well'><h3>Actualizar Textos</h3>";
		$formulario .= "<form id='mensajeError' accept-charset='utf-8' method=''>";
		$formulario .= "<div class='ruleta'>";
		$formulario .= "<div class='rul_1'>Texto1:</div>";
		$formulario .= "<div class='rul_2'><input id='texto1' name='texto1' type='text' value='".$datos[0]['texto']."'/></div>";
		$formulario.="<input type='hidden' id='idtexto1' name='idtexto1' value='".$datos[0]['id']."'>";
		$formulario .= "<div class='rul_1'>Texto2:</div>";
		$formulario .= "<div class='rul_2'><input id='texto2' name='texto2' type='text' value='".$datos[1]['texto']."'/></div>";
		$formulario.="<input type='hidden' id='idtexto2' name='idtexto2' value='".$datos[1]['id']."'>";
		$formulario .= "<div class='rul_1'>Texto3:</div>";
		$formulario .= "<div class='rul_2'><input id='texto3' name='texto3' type='text' value='".$datos[2]['texto']."'/></div>";
		$formulario.="<input type='hidden' id='idtexto3' name='idtexto3' value='".$datos[2]['id']."'>";
		$formulario .= "<div class='rul_1'>Texto4:</div>";
		$formulario .= "<div class='rul_2'><input id='texto4' name='texto4' type='text' value='".$datos[3]['texto']."'/></div>";
		$formulario.="<input type='hidden' id='idtexto4' name='idtexto4' value='".$datos[3]['id']."'>";
		$formulario.="<input type='hidden' id='pais' name='pais' value='".$pais."'>";
		
		$formulario .= "<center><input type='button' id='boton_ruleta' name='boton_ruleta' class='btn btn-danger' value='Actualizar' onclick='editTex();'/></center>";
		$formulario .= "</div></div></form>";

	return $formulario;

	}   

	/**
	 * Funcion busca los textos por servicio y pais
	 * @param $app, servicio, pais
	 * @return string 
	 * @access public
	**/ 	
	public function findText($app,$servicio,$pais)
	 {
		 $query  = 'SELECT id, texto FROM `'.$this->prefix.'servicio_texto` where id_servicio='.$servicio;
		 if($pais=="1"){
		 		$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		        return $list;
		 }else if($pais=="2"){
		 	    $list 	= $app['dbs']['mysql_silex1']->fetchAll($query);
		        return $list;
		 }else if($pais=="3"){
		 	    $list 	= $app['dbs']['mysql_silex2']->fetchAll($query);
		        return $list;
		 }else if($pais=="4"){
		 	    $list 	= $app['dbs']['mysql_silex3']->fetchAll($query);
		        return $list;
		 }else if($pais=="5"){
		 	    $list 	= $app['dbs']['mysql_silex4']->fetchAll($query);
		        return $list;
		 }
		 
	 }

	 /**
	 * Funcion que actualiza los textos segun pais
	 * @param $textos y id
	 * @return string 
	 * @access public
	**/ 

	 public function updateTextos($app,$texto1,$texto2,$texto3,$texto4,$idtexto1,$idtexto2,$idtexto3,$idtexto4,$pais)
		 {
		 	if($pais=="1"){
		 		$sql="UPDATE ".$this->prefix."servicio_texto SET texto = CASE id WHEN ". $idtexto1 . " THEN '" . $texto1. "' WHEN ". $idtexto2 . " THEN '" . $texto2. "' WHEN ". $idtexto3 . " THEN '" . $texto3. "' WHEN ". $idtexto4 . " THEN '" . $texto4. "' END WHERE id IN (".$idtexto1.",". $idtexto2 .",". $idtexto3.",".$idtexto4.")";
			    return $app['dbs']['mysql_silex']->ExecuteQuery($sql);
		 	}else if($pais=="2"){
		 		$sql="UPDATE ".$this->prefix."servicio_texto SET texto = CASE id WHEN ". $idtexto1 . " THEN '" . $texto1. "' WHEN ". $idtexto2 . " THEN '" . $texto2. "' WHEN ". $idtexto3 . " THEN '" . $texto3. "' WHEN ". $idtexto4 . " THEN '" . $texto4. "' END WHERE id IN (".$idtexto1.",". $idtexto2 .",". $idtexto3.",".$idtexto4.")";
			    return $app['dbs']['mysql_silex1']->ExecuteQuery($sql);
		 	}else if($pais=="3"){
		 		$sql="UPDATE ".$this->prefix."servicio_texto SET texto = CASE id WHEN ". $idtexto1 . " THEN '" . $texto1. "' WHEN ". $idtexto2 . " THEN '" . $texto2. "' WHEN ". $idtexto3 . " THEN '" . $texto3. "' WHEN ". $idtexto4 . " THEN '" . $texto4. "' END WHERE id IN (".$idtexto1.",". $idtexto2 .",". $idtexto3.",".$idtexto4.")";
			    return $app['dbs']['mysql_silex2']->ExecuteQuery($sql);
		 	}else if($pais=="4"){
		 		$sql="UPDATE ".$this->prefix."servicio_texto SET texto = CASE id WHEN ". $idtexto1 . " THEN '" . $texto1. "' WHEN ". $idtexto2 . " THEN '" . $texto2. "' WHEN ". $idtexto3 . " THEN '" . $texto3. "' WHEN ". $idtexto4 . " THEN '" . $texto4. "' END WHERE id IN (".$idtexto1.",". $idtexto2 .",". $idtexto3.",".$idtexto4.")";
			    return $app['dbs']['mysql_silex3']->ExecuteQuery($sql);
		 	}else if($pais=="5"){
		 		$sql="UPDATE ".$this->prefix."servicio_texto SET texto = CASE id WHEN ". $idtexto1 . " THEN '" . $texto1. "' WHEN ". $idtexto2 . " THEN '" . $texto2. "' WHEN ". $idtexto3 . " THEN '" . $texto3. "' WHEN ". $idtexto4 . " THEN '" . $texto4. "' END WHERE id IN (".$idtexto1.",". $idtexto2 .",". $idtexto3.",".$idtexto4.")";
			    return $app['dbs']['mysql_silex4']->ExecuteQuery($sql);
		 	}
			
	    }


	 /**
	 * Funcion que actualiza la region seleccionada
	 * @param $textos y id
	 * @return string 
	 * @access public
	**/ 

	 public function updateRegion($app,$nombre,$latitud,$longitud,$pais,$region,$id)
		 {
		 	
		 	if($region=="1"){
			    $sql="UPDATE ".$this->prefix."departamento set provincia_name ='".$nombre."',provincia_latitude ='".$latitud."',provincia_longitude ='".$longitud."' where provincia_id = ".$id;
		 	}else if($region=="2"){
		 		$sql="UPDATE ".$this->prefix."municipio set canton_name ='".$nombre."',canton_latitude ='".$latitud."',canton_longitude ='".$longitud."' where canton_id = ".$id;
		 	}else if($region=="3"){
		 		$sql="UPDATE ".$this->prefix."distrito set distrito_name ='".$nombre."',distrito_latitude ='".$latitud."',distrito_longitude ='".$longitud."' where distrito_id = ".$id;
		 	}else if($region=="4"){
		 		$sql="UPDATE ".$this->prefix."poblado set poblado_name ='".$nombre."',poblado_latitude ='".$latitud."',poblado_longitude ='".$longitud."' where poblado_id = ".$id;
		 	}

		 	if($pais=="1"){
			    return $app['dbs']['mysql_silex']->ExecuteQuery($sql);
		 	}else if($pais=="2"){
			    return $app['dbs']['mysql_silex1']->ExecuteQuery($sql);
		 	}else if($pais=="3"){
			    return $app['dbs']['mysql_silex2']->ExecuteQuery($sql);
		 	}else if($pais=="4"){
			    return $app['dbs']['mysql_silex3']->ExecuteQuery($sql);
		 	}else if($pais=="5"){
			    return $app['dbs']['mysql_silex4']->ExecuteQuery($sql);
		 	}
			
	    }

	/**
	 * Funcion que devuelve la lista de municipios
	 * @param $app, $id departamento
	 * @return string
	 * @access public
	**/
	public function getMunicipios($app,$id,$pais) {
		 	$query = 'SELECT canton_id, canton_name FROM ' . $this->prefix.'municipio WHERE canton_provincia = '. $id .' ORDER BY canton_name';
			 if($pais=="1"){
			 		$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
			        return $list;
			 }else if($pais=="2"){
			 	    $list 	= $app['dbs']['mysql_silex1']->fetchAll($query);
			        return $list;
			 }else if($pais=="3"){
			 	    $list 	= $app['dbs']['mysql_silex2']->fetchAll($query);
			        return $list;
			 }else if($pais=="4"){
			 	    $list 	= $app['dbs']['mysql_silex3']->fetchAll($query);
			        return $list;
			 }else if($pais=="5"){
			 	    $list 	= $app['dbs']['mysql_silex4']->fetchAll($query);
			        return $list;
			 }
			return $list;
	} 


	/**
	 * Funcion que devuelve la lista de comunidades
	 * @param $app, $id departamento
	 * @return string
	 * @access public
	**/
	public function getComunidad($app,$id,$pais) {
		 	$query = 'SELECT distrito_id, distrito_name FROM ' . $this->prefix.'distrito WHERE distrito_canton = '. $id .' ORDER BY distrito_name';
			 if($pais=="1"){
			 		$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
			        return $list;
			 }else if($pais=="2"){
			 	    $list 	= $app['dbs']['mysql_silex1']->fetchAll($query);
			        return $list;
			 }else if($pais=="3"){
			 	    $list 	= $app['dbs']['mysql_silex2']->fetchAll($query);
			        return $list;
			 }else if($pais=="4"){
			 	    $list 	= $app['dbs']['mysql_silex3']->fetchAll($query);
			        return $list;
			 }else if($pais=="5"){
			 	    $list 	= $app['dbs']['mysql_silex4']->fetchAll($query);
			        return $list;
			 }
			return $list;
	}    

/**
    NUevos metodos archivo por partes
    **/

	 /** Funcion que arma la vista de formulario de regiones
	 * @param $app
	 * @return string 
	 * @access public
	**/ 
	public function formularioIntermedio($app){
		$formulario = "<form id='intermedio' method='post' action='./pasoDos'>";
		$formulario .= "<div class='ruleta'>";
			 if($_SESSION["role"] == 0){
			$formulario .= "<div class='rul_1'>Seleccionar Pais:</div>";
			$formulario .= "<div class='rul_2'>".$this->getPais($app)."</div>";
			}else{
				$formulario.="<input type='hidden' id='pais' name='pais' value='".$_SESSION["pais_user"]."'>";
			}
			$formulario .= "<div class='rul_1'>Tipo de Carga:</div>";
			$formulario .= "<div class='rul_1'>";
			$formulario .= "<select id='tipomapa' name='tipomapa'>";
	        $formulario .= "<option value = '0'>Mapa Completo</option>";    
	        $formulario .= "<option value = '1'>Mapa Por Partes</option>";  
	        $formulario .= "</select></div>";
		$formulario .= "<center><input type='submit' id='boton_ruleta' name='boton_ruleta' class='btn btn-danger' value='Siguiente'/></center>";
		$formulario .= "</div></div></form>";

		return $formulario;

	}


	/**
	 * Funcion que arma la vista de formulario de regiones
	 * @param $app
	 * @return string 
	 * @access public
	**/ 
	public function formPasoDos($app,$pais){

		$formulario = "<form id='pasodos' method='post' action='./multiMapas'>";
		$formulario .= "<div class='ruleta'>";
		    
			$formulario.="<input type='hidden' id='pais' name='pais' value='".$pais."'>";
			$formulario .= "<div class='rul_1'>Tipo de Mapa:</div>";
			$formulario .= "<div class='rul_1'>";
			$formulario .=  $this->getServicioCompleto($app,$pais);
		$formulario .= "<left><input style='margin-right: 10px; width:60px' type='boton' id='anterior' name='anterior' class='btn btn-danger' value='Anterior' onclick='myAnterior()'/></left>";	
		$formulario .= "<right><input type='submit' id='boton_ruleta' name='boton_ruleta' class='btn btn-danger' value='Siguiente'/></right>";
		$formulario .= "</div></div></form>";

		return $formulario;

	}

	/**
	 * Funcion que arma select con los servicio desponibles
	 * @param $app
	 * @return string 
	 * @access public
	**/ 
	public function getServicioCompleto($app,$pais){

			    $query="SELECT * from ".$this->prefix."tipo_servicio ";

	            $res = $this->getConsulta($app,$pais,$query);

	            $primero="";
	            $contador=0;

	            $html ="<select id='tipomapa' name='tipomapa'>";
	            foreach ($res as $key ) {
		            $html.="<option value = '".$key['id']."'>".$key['name']."</option>"; 
			            if($contador==0){
			            	$primero= $key['id'];
			            }
		             $contador=$contador+1;
	            }

	            $html .="</select>";
		        $html .= "</div>";
		        $html .= "<div class='rul_1'>Cobertura:</div>";
				$html .= "<div class='rul_2'>";

				$query="SELECT * from ".$this->prefix."cobertura where servicio= " . $primero ;

	            $res = $this->getConsulta($app,$pais,$query);
	            $html .="<select id='tipocobertura' name='tipocobertura'>";
	            foreach ($res as $key ) {
	            $html.="<option value = '".$key['id']."'>".$key['nombre']."</option>";    
	            }
		        $html .= "</select>";
				$html .= "</div>";

	    return $html;
	} 


	


	/**
	* Obtiene el listado de departamentos
	*
	* @var objects
	* @var string
	* @return array
	*/
	public function getAllMaps($app,$pais,$tipoMapa,$tipoCobertura)
	{

	  	$query  = 'SELECT provincia_id AS "id", provincia_active AS "block", provincia_name AS "name" FROM `'.$this->prefix.'departamento`';
	  	 $res = $this->getConsulta($app,$pais,$query);

		$html  = "<div style='width:25%; float:left;'><form id='regreso' method='post' action='./pasoDos'>";
	  	$html .= '<input type="hidden" id="pais" name="pais" value="'.$pais.'">';
	  	$html .= '<input type="hidden" id="tipomapa" name="tipomapa" value="1">';
		$html .= "<input style='width:100px;' type='submit' value='Anterior' class='btn btn-danger' >";
		$html .= "</form></div>"; 
		$html .= "<form id='loadmap' method='post' action='./formLoadMap'>";
		$html .= '<input type="hidden" id="paisActual" name="paisActual" value="'.$pais.'">';
		$html .= '<input type="hidden" id="tipoCobertura" name="tipoCobertura" value="'.$tipoCobertura.'">';
		$html .= '<input type="hidden" id="tipoMapa" name="tipoMapa" value="'.$tipoMapa.'">';
		$html .= "<input type='submit' value='Cargar Mapa' class='btn btn-danger' >";
		$html .= "</form>";
		$html .= "<div class='rul_1'>Departamento:</div>";
		$html .= "<div class='rul_1'>";

			 $cont=0;
			 $actual="";
			
			 $html .="<select id='mapadep' name='mapadep'>";
		        foreach ($res as $key ) {
		        $html.="<option value = '".$key['id']."'>".$key['name']."</option>"; 
			        if($cont==0){
			        	$actual=$key['id'];
			        }
			        $cont=$cont+1;
		        }
	        $html.="</select>";
	        $html.="</div>"; 

	        
	        	if($actual==0){
	        	$query = 'Select * from tnq_layers where ruta IN (Select id from tnq_ruta where cobertura='.$tipoCobertura. ' and departamento='.$actual.') and (tipo=3 || tipo=0);' ;	
	                  }else{
	        	$query = 'Select * from tnq_layers where ruta=(Select id from tnq_ruta where cobertura='.$tipoCobertura. ' and departamento='.$actual.');' ;
	                }
	   
	    
	    $list = $this->getConsulta($app,$pais,$query);

	$html.='<div id="tabla" class="table_users">';
	$html.='<table class="table table-hover table-striped">';
	$html.='<th class="header">ID</th>';
	$html.='<th class="header">Nombre</th>';    
	$html.='<th class="header" colspan="3" style="width:28%;">Opciones</th>'; 
	$contador=1;

	foreach ($list as $key) {
	$html.='<tr>';
	$html.='<td>'.$key['layer_id'].'</td>';
	$html.='<td>'.$key['layer_type'].'</td>';
	//$html.='<td> <span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_user(this.id)" title="Editar"><i class="icon-edit"></i></span> ';
	$html.='<td><form id="editarMapa'.$contador.'" method="post" action="./editMapa">';
	$html.= '<span class="btn btn-info btn-small" id="'.$key['layer_id'].'" onClick="editar_mapa('.$contador.')" title="Editar"><i class="icon-edit"></i></span>';
	$html.= '<input type="hidden" name="cadena" value="'.$key['layer_id'].'">';
	$html .= '<input type="hidden" id="paisActual" name="paisActual" value="'.$pais.'">';
	$html .= '<input type="hidden" id="tipoCobertura" name="tipoCobertura" value="'.$tipoCobertura.'">';
	$html .= '<input type="hidden" id="tipoMapa" name="tipoMapa" value="'.$tipoMapa.'">';
	$html.= '</form></td>';

	$html.='<td><span class="btn btn-danger btn-small" id="'.$key['layer_id'].'" onClick="eliminar_mapa(this.id,'.$pais.')" title="Eliminar"><i class="icon-remove"></i></span></td>';

	$html.='</tr>';

	$contador=$contador+1;
	}
	$html.='</table>';
	$html.='</div>';     



   	return $html;
	}  


	/**
	 * Funcion que devuelve la lista de comunidades
	 * @param $app, $id departamento
	 * @return string
	 * @access public
	**/
	public function getCobertura($app,$id) {
		 	$query = 'SELECT * FROM ' . $this->prefix.'cobertura WHERE servicio = '. $id;
			 $list 	= $app['dbs']['mysql_silex']->fetchAll($query);
			
			return $list;
	}    



	/**
	* Obtiene el listado especifico de poblados
	*
	* @var objects
	* @var string
	* @return array
	*/
	public function editEspecificMapa($app,$departamento,$cobertura,$pais,$servicio)
	{
                if($departamento==0){
 			$query = 'Select * from tnq_layers where ruta IN (Select id from tnq_ruta where cobertura='.$cobertura. ' and departamento='.$departamento.') and (tipo=3||tipo=0);' ; 
		}else{
			 $query = 'Select * from tnq_layers where ruta=(Select id from tnq_ruta where cobertura='.$cobertura. ' and departamento='.$departamento.');' ; 
		}

	
	     if($pais=="1"){
		 		$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
		 }else if($pais=="2"){
		 	    $list 	= $app['dbs']['mysql_silex1']->fetchAll($query);
		 }else if($pais=="3"){
		 	    $list 	= $app['dbs']['mysql_silex2']->fetchAll($query);
		 }else if($pais=="4"){
		 	    $list 	= $app['dbs']['mysql_silex3']->fetchAll($query);
		 }else if($pais=="5"){
		 	    $list 	= $app['dbs']['mysql_silex4']->fetchAll($query);
		 }
	   
	   $html ='<div id="tabla" class="table_users">';
	   $html.='<table class="table table-hover table-striped">';
	   $html.='<th class="header">ID</th>';
	   $html.='<th class="header">Nombre</th>';    
	   $html.='<th class="header" colspan="3" style="width:28%;">Opciones</th>'; 
	   $contador=1;

		   foreach ($list as $key) {
		     $html.='<tr>';
		     $html.='<td>'.$key['layer_id'].'</td>';
		     $html.='<td>'.$key['layer_type'].'</td>';
		     //$html.='<td> <span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_user(this.id)" title="Editar"><i class="icon-edit"></i></span> ';
		     $html.='<td><form id="editarMapa'.$contador.'" method="post" action="./editMapa">';
			$html.= '<span class="btn btn-info btn-small" id="'.$key['layer_id'].'" onClick="editar_mapa('.$contador.')" title="Editar"><i class="icon-edit"></i></span>';
			$html.= '<input type="hidden" name="cadena" value="'.$key['layer_id'].'">';
			$html .= '<input type="hidden" id="paisActual" name="paisActual" value="'.$pais.'">';
			$html .= '<input type="hidden" id="tipoCobertura" name="tipoCobertura" value="'.$cobertura.'">';
			$html .= '<input type="hidden" id="tipoMapa" name="tipoMapa" value="'.$servicio.'">';
			$html.= '</form></td>';
   
            
		    $html.='<td><span class="btn btn-danger btn-small" id="'.$key['layer_id'].'" onClick="eliminar_mapa(this.id,'.$pais.')" title="Eliminar"><i class="icon-remove"></i></span></td>';

		    $html.='</tr>';

		    $contador=$contador+1;
		   }
   		$html.='</table>';
   		$html.='</div>';
   		$html.= '<input type="hidden" id="paisActual" name="paisActual" value="'.$pais.'">';
   
   	return $html;
	}



	 /**
	 * Funcion que devuelve el formulario de subir archivo
	 * @param $app
	 * @return string 
	 * @access public
	**/ 
	public function loadAllMaps($app,$pais,$tipoMapa,$tipoCobertura){

		$query  = 'SELECT provincia_id AS "id", provincia_active AS "block", provincia_name AS "name" FROM `'.$this->prefix.'departamento`';
	  	$res = $this->getConsulta($app,$pais,$query);

	  	$query  = 'SELECT name FROM `'.$this->prefix.'tipo_servicio` where id='. $tipoMapa;
	  	$mapa = $this->getConsulta($app,$pais,$query)[0]['name'];

	  	
	  	$query  = 'SELECT nombre FROM `'.$this->prefix.'cobertura` where id='. $tipoCobertura;
	  	$cobertura=  $this->getConsulta($app,$pais,$query)[0]['nombre'];
	       
	    $html= '<form id="upUnArchivo" method="post" action="./subirUnArchivo" enctype="multipart/form-data" >';
			
	    $html .= "<div class='rul_1'>Region de:</div>";
		 $html .="<select id='upmapa' name='upmapa'>";
		$cont=0;
	        foreach ($res as $key ) {
	        $html.="<option value = '".$key['id']."'>".$key['name']."</option>"; 
		        if($cont==0){
		        	$actual=$key['id'];
		        }
		        $cont=$cont+1;
	        }
        $html.="</select>";	
	      
	       $html.='<div id="tabla" class="table_users">';
		   $html.='<table class="table table-hover table-striped">';
		   $html.='<th class="header">Categoria</th>';
		   $html.='<th class="header">Archivo</th>'; 
		   $html.='<tr><td>'.$mapa .' '. $cobertura.'</td><td><input name="ag" type="file" id="ag"></td></tr>';  
		   $html.= '<input type="hidden" id="pais" name="pais" value="'.$pais.'">';
		   $html.= '<input type="hidden" id="mapa" name="mapa" value="'.$mapa.'">';
		   $html.= '<input type="hidden" id="cobertura" name="cobertura" value="'.$cobertura.'">';
		   $html.= '<input type="hidden" id="tipomapa" name="tipomapa" value="'.$tipoMapa.'">';	
		   $html.= '<input type="hidden" id="tipocobertura" name="tipocobertura" value="'.$tipoCobertura.'">';
		   $html.='<tr><td></td><td><input name="boton" type"button" id="boton" class="btn btn-danger" value="Subir" onclick="subirUnArchivo();"></td></tr>';	 
	   	   $html.='</div>';
	   	   
	       $html.='</form>';
	   	return $html;

	}

 /**
	 * Funcion que busca en la db un nombre de departamento registrado
	 * @param $usuario, $app
	 * @return string 
	 * @access private
	**/ 
	public function findDepar($app,$pais,$id){
		$query  = 'SELECT provincia_name from `'.$this->prefix.'departamento` where provincia_id='.$id;
		if($pais=="1"){
					$list = $app['dbs']['mysql_silex']->fetchAll($query); 
				}else if($pais=="2"){
					$list = $app['dbs']['mysql_silex1']->fetchAll($query);
				}else if($pais=="3"){
					$list = $app['dbs']['mysql_silex2']->fetchAll($query);
				}else if($pais=="4"){
					$list = $app['dbs']['mysql_silex3']->fetchAll($query);
				}else if($pais=="5"){
					$list = $app['dbs']['mysql_silex4']->fetchAll($query);
				}
		return $list[0]["provincia_name"];
	}	 


	/**
	 * Funcion que busca en la db un nombre de departamento registrado
	 * @param $usuario, $app
	 * @return string 
	 * @access private
	**/ 
	public function findRuta($app,$pais,$departamento,$cobertura,$tipo){
		$query  = 'SELECT id, nombre from `'.$this->prefix.'ruta` where departamento='.$departamento . ' and cobertura='.$cobertura . ' and tipo='.$tipo ;
		if($pais=="1"){
					$list = $app['dbs']['mysql_silex']->fetchAll($query); 
				}else if($pais=="2"){
					$list = $app['dbs']['mysql_silex1']->fetchAll($query);
				}else if($pais=="3"){
					$list = $app['dbs']['mysql_silex2']->fetchAll($query);
				}else if($pais=="4"){
					$list = $app['dbs']['mysql_silex3']->fetchAll($query);
				}else if($pais=="5"){
					$list = $app['dbs']['mysql_silex4']->fetchAll($query);
				}	
		return $list;
	}


	/**
	 * Funcion que inserta a la db un departamento
	 * @param $app, nombre, pass, rol, email
	 * @return string 
	 * @access public
	**/ 
	public function insertRuta($app,$pais,$departamento,$departamento1,$cobertura,$tipo){

				$paramsTable = array();
			
				$table = $this->prefix."ruta";
				$paramsTable['nombre'] = $app->escape($departamento);
				$paramsTable['departamento'] 	= $app->escape($departamento1);
				$paramsTable['cobertura'] = $app->escape($cobertura);
				$paramsTable['tipo'] 	= $app->escape($tipo);
				
				if($pais=="1"){
					$list = $app['dbs']['mysql_silex']->insert($table ,$paramsTable);
					$id = $app['dbs']['mysql_silex']->lastInsertId();
				}else if($pais=="2"){
					$list = $app['dbs']['mysql_silex1']->insert($table ,$paramsTable);
					$id = $app['dbs']['mysql_silex1']->lastInsertId();
				}else if($pais=="3"){
					$list = $app['dbs']['mysql_silex2']->insert($table ,$paramsTable);
					$id = $app['dbs']['mysql_silex2']->lastInsertId();
				}else if($pais=="4"){
					$list = $app['dbs']['mysql_silex3']->insert($table ,$paramsTable);
					$id = $app['dbs']['mysql_silex3']->lastInsertId();
				}else if($pais=="5"){
					$list = $app['dbs']['mysql_silex4']->insert($table ,$paramsTable);
					$id = $app['dbs']['mysql_silex4']->lastInsertId();
				}

				return $id;
		
	}

	/**
	 * Funcion que inserta a la db un departamento
	 * @param $app, nombre, pass, rol, email
	 * @return string 
	 * @access public
	**/ 
	public function insertFile($app,$pais,$nombre,$size,$fecha,$ruta,$content,$tipo){

				$paramsTable = array();
				$layer_data=$content;

				
			
				$table = $this->prefix."layers";
				$paramsTable['layer_type'] = $app->escape($nombre);
				$paramsTable['layer_size'] 	= $app->escape($size);
				$paramsTable['layer_time'] = $app->escape($fecha);
				$paramsTable['ruta'] 	= $app->escape($ruta);
				$paramsTable['tipo'] 	= $app->escape($tipo);
				$paramsTable['layer_data'] 	= $layer_data;	

				

				if($pais=="1"){
					$list = $app['dbs']['mysql_silex']->insert($table ,$paramsTable);
				}else if($pais=="2"){
					$list = $app['dbs']['mysql_silex1']->insert($table ,$paramsTable);
				}else if($pais=="3"){
					$list = $app['dbs']['mysql_silex2']->insert($table ,$paramsTable);
				}else if($pais=="4"){
					$list = $app['dbs']['mysql_silex3']->insert($table ,$paramsTable);
				}else if($pais=="5"){
					$list = $app['dbs']['mysql_silex4']->insert($table ,$paramsTable);
				}

				
				

				return $list;
		
	}


	/**
	 * Funcion que elimina un usuario de la db
	 * @param $id, $app
	 * @return string 
	 * @access public
	**/ 
	 public function deleteMapa($app,$id,$pais)
	 {

	   try {
		   	if($pais==1){
		   		 $del=$app['dbs']['mysql_silex']->delete($this->prefix.'layers', array('layer_id' => $id));
		   	}else if($pais==2){
		   		$del=$app['dbs']['mysql_silex1']->delete($this->prefix.'layers', array('layer_id' => $id));
		   	}else if($pais==3){
		   		$del=$app['dbs']['mysql_silex2']->delete($this->prefix.'layers', array('layer_id' => $id));
		   	}else if($pais==4){
		   		$del=$app['dbs']['mysql_silex3']->delete($this->prefix.'layers', array('layer_id' => $id));
		   	}else if($pais==5){
		   		$del=$app['dbs']['mysql_silex4']->delete($this->prefix.'layers', array('layer_id' => $id));
		   	}
	   }
	   catch(Exception $exception) 
	   { 
	     return null;
	   }  
	    return $del;
	 }	


	 /**
	 * Funcion que busca en la db un nombre de departamento registrado
	 * @param $usuario, $app
	 * @return string 
	 * @access private
	**/ 
	public function findDirectorioMapa($app,$id,$pais){
		$query= 'Select Archivo.layer_type, Ruta.nombre as Carpeta, Cobertura.nombre, Servicio.name from tnq_layers as Archivo, tnq_ruta as Ruta, tnq_cobertura as Cobertura, tnq_tipo_servicio as Servicio where Servicio.id=Cobertura.servicio and Cobertura.id=Ruta.cobertura and Ruta.id=Archivo.ruta and Archivo.layer_id='.$id; 
		if($pais=="1"){
					$list = $app['dbs']['mysql_silex']->fetchAll($query); 
				}else if($pais=="2"){
					$list = $app['dbs']['mysql_silex1']->fetchAll($query);
				}else if($pais=="3"){
					$list = $app['dbs']['mysql_silex2']->fetchAll($query);
				}else if($pais=="4"){
					$list = $app['dbs']['mysql_silex3']->fetchAll($query);
				}else if($pais=="5"){
					$list = $app['dbs']['mysql_silex4']->fetchAll($query);
				}	
		return $list;
	} 


	 /**
	 * Funcion que devuelve el formulario de editarUsuario
	 * @param $id, $app
	 * @return string 
	 * @access public
	**/ 
	 public function editMapa($app,$pais,$archivo,$cobertura,$servicio){

	 	$query='SELECT layer_type from '. $this->prefix.'layers where layer_id='.$archivo; 
	 	$retorno=$this->getConsulta($app,$pais,$query);
		//$datos=$this->findUser($app,$id);
		$nombre="archivo";
		$html= '<form id="updateArchivo" method="post" action="./actualizarUnArchivo" enctype="multipart/form-data" >';
		
	       $html.='<div id="tabla" class="table_users">';
		   $html.='<table class="table table-hover table-striped">';
		   $html.='<th class="header">Categoria</th>';
		   $html.='<th class="header">Archivo</th>'; 
		   $html.='<tr><td>'.$retorno[0]["layer_type"].'</td><td><input name="ag" type="file" id="ag"></td></tr>';  
		   $html.= '<input type="hidden" id="pais" name="pais" value="'.$pais.'">';
		   $html.= '<input type="hidden" id="archivo" name="archivo" value="'.$archivo.'">';
		    $html.= '<input type="hidden" id="cobertura" name="cobertura" value="'.$cobertura.'">';
		   $html.= '<input type="hidden" id="servicio" name="servicio" value="'.$servicio.'">';
		   $html.='<tr><td></td><td><input name="boton" type"button" id="boton" class="btn btn-danger" value="Subir" onclick="updateArchivo();"></td></tr>';	 
	   	   $html.='</div>';
	   	   
	       $html.='</form>';
	   	return $html;
	}

   private function getConsulta($app,$pais,$query) {
			 if($pais=="1"){
			 		$list 	= $app['dbs']['mysql_silex']->fetchAll($query);
			        return $list;
			 }else if($pais=="2"){
			 	    $list 	= $app['dbs']['mysql_silex1']->fetchAll($query);
			        return $list;
			 }else if($pais=="3"){
			 	    $list 	= $app['dbs']['mysql_silex2']->fetchAll($query);
			        return $list;
			 }else if($pais=="4"){
			 	    $list 	= $app['dbs']['mysql_silex3']->fetchAll($query);
			        return $list;
			 }else if($pais=="5"){
			 	    $list 	= $app['dbs']['mysql_silex4']->fetchAll($query);
			        return $list;
			 }
			return $list;
	} 


	/**
	 * Funcion que actualiza la region seleccionada
	 * @param $textos y id
	 * @return string 
	 * @access public
	**/ 

	 public function updateArchivo($app,$pais,$nombre,$size,$fecha,$id)
		 {
		 	$sql="UPDATE ".$this->prefix."layers set layer_type ='".$nombre."',layer_size ='".$size."',layer_time ='".$fecha."' where layer_id = ".$id;

		 	if($pais=="1"){
			    return $app['dbs']['mysql_silex']->ExecuteQuery($sql);
		 	}else if($pais=="2"){
			    return $app['dbs']['mysql_silex1']->ExecuteQuery($sql);
		 	}else if($pais=="3"){
			    return $app['dbs']['mysql_silex2']->ExecuteQuery($sql);
		 	}else if($pais=="4"){
			    return $app['dbs']['mysql_silex3']->ExecuteQuery($sql);
		 	}else if($pais=="5"){
			    return $app['dbs']['mysql_silex4']->ExecuteQuery($sql);
		 	}
			
	    } 


	/**
	 * Funcion que busca en la db un nombre de departamento registrado
	 * @param $usuario, $app
	 * @return string 
	 * @access private
	**/ 
	public function findArchivo($app,$pais,$departamento1,$tipoCobertura,$tipoCarpeta){
		$query  = 'SELECT layer_id, layer_type from `'.$this->prefix.'layers` where ruta=(SELECT id from `'.$this->prefix.'ruta` where tipo='.$tipoCarpeta. ' and cobertura='.$tipoCobertura. ' and departamento='.$departamento1.') and tipo=1';
		if($pais=="1"){
					$list = $app['dbs']['mysql_silex']->fetchAll($query); 
				}else if($pais=="2"){
					$list = $app['dbs']['mysql_silex1']->fetchAll($query);
				}else if($pais=="3"){
					$list = $app['dbs']['mysql_silex2']->fetchAll($query);
				}else if($pais=="4"){
					$list = $app['dbs']['mysql_silex3']->fetchAll($query);
				}else if($pais=="5"){
					$list = $app['dbs']['mysql_silex4']->fetchAll($query);
				}	
		return $list;
	}


        /**
	 * Funcion que busca en la db un nombre de departamento registrado
	 * @param $usuario, $app
	 * @return string 
	 * @access private
	**/ 
	public function findArchivo2($app,$pais,$departamento1,$tipoCobertura,$tipoCarpeta){
		$query  = 'SELECT layer_id, layer_type from `'.$this->prefix.'layers` where ruta=(SELECT id from `'.$this->prefix.'ruta` where tipo='.$tipoCarpeta. ' and cobertura='.$tipoCobertura. ' and departamento='.$departamento1.') and tipo=2';

		if($pais=="1"){
					$list = $app['dbs']['mysql_silex']->fetchAll($query); 
				}else if($pais=="2"){
					$list = $app['dbs']['mysql_silex1']->fetchAll($query);
				}else if($pais=="3"){
					$list = $app['dbs']['mysql_silex2']->fetchAll($query);
				}else if($pais=="4"){
					$list = $app['dbs']['mysql_silex3']->fetchAll($query);
				}else if($pais=="5"){
					$list = $app['dbs']['mysql_silex4']->fetchAll($query);
				}	
		return $list;
	}
  

}
		