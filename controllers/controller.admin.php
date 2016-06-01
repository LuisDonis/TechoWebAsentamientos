<?php
/**
* @author Desarrollador lrodriguez
* @version 1.0
* @package controlador
**/

class ControllerAdmin {

	/**
	 * Funcion inicial del administrador verifica session y redirige a pantalla inicial
	 * @param $app, $model
	 * @return vista login, o vista de usuario
	 * @access public
	**/
	public function login($app, $model){
		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
            $menu=$model['admin']->menuTop();
			$cuerpo=$model['admin']->getUsers($app);
		    return	$app['twig']->render( '/admin/usuarios.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo));		     
	    }

	}

	/**
	 * Funcion que verifica las credeciales del usuario
	 * @param $app, $model
	 * @param $_POST (user y pass)
	 * @return json ingreso exitoso y fallido
	 * @access public
	**/
	public function logear($app, $model){
		$usuario=$this->sanitizeVar(base64_decode($_POST["dataForm1"]));
		$pass=$this->sanitizeVar(base64_decode($_POST["dataForm2"]));
		$regex = '/^[a-zA-Z]((\.|_|-)?[a-zA-Z0-9]+){3}$/D';
			if (!preg_match($regex, $usuario)) {
				 $jsondata['login'] 	= 'error';
		   		 return json_encode($jsondata); 
			}
		$data = $model['admin']->validateParams($usuario,$pass, $app);
	    $jsondata['login'] 	= $data;
	    return json_encode($jsondata); 
	}

    /**
	 * Funcion redirecciona a pagina superAdmin y admin
	 * @param $app, $model
	 * @return vista de listado de usuarios
	 * @access public
	**/
	public function admin($app, $model){
		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
					return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
			$menu=$model['admin']->menuTop();
			$cuerpo=$model['admin']->getUsers($app);
		    return	$app['twig']->render( '/admin/usuarios.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo));
	          }
	}

	

    /**
	 * Funcion que redirecciona a pagina para buscar los textos
	 * @param $app, $model
	 * @return vista de textos
	 * @access public
	**/
	public function textos($app, $model){
		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
			$menu=$model['admin']->menuTop($app);
			$cuerpo=$model['admin']->formularioTexto($app);
		    return	$app['twig']->render( '/admin/textos.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo));
	    }
	}


	/**
	 * Funcion que redirecciona a pagina para buscar regiones
	 * @param $app, $model
	 * @return vista de tregiones
	 * @access public
	**/
	public function regiones($app, $model){
		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
			$menu=$model['admin']->menuTop($app);
			$cuerpo=$model['admin']->formularioRegiones($app);
		    return	$app['twig']->render( '/admin/textos.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo));
	    }
	}


	/**
	 * Funcion que redirecciona a pagina para editar la region seleccionada
	 * @param $app, $model
	 * @return vista de textos
	 * @access public
	**/
	public function editarRegion($app, $model){
		$region=$_POST["region"];
		$pais=$_POST["pais"];

		if($region=="1"){
			$regionActual=$_POST["region1"];
		}else if($region=="2"){
			$regionActual=$_POST["region2"];
		}else if($region=="3"){
			$regionActual=$_POST["region3"];
		}else if($region=="4"){
			$regionActual=$_POST["region4"];
		}

		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
			$menu=$model['admin']->menuTop($app);
			$cuerpo=$model['admin']->editRegion($app,$region,$pais,$regionActual);
		    return	$app['twig']->render( '/admin/textos.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo));  
	    }
	}

	/**
	 * Funcion que redirecciona a pagina para editar los textox
	 * @param $app, $model
	 * @return vista de textos
	 * @access public
	**/
	public function editarTexto($app, $model){
		$servicio=$_POST["servicio"];
		$pais=$_POST["pais"];
		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
			$menu=$model['admin']->menuTop($app);
			$cuerpo=$model['admin']->editTexto($app,$servicio,$pais);
		    return	$app['twig']->render( '/admin/textos.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo));  
	    }
	}

    function get_zip_originalsize($filename) {
	    $size = 0;
	    $resource = zip_open($filename);
	    while ($dir_resource = zip_read($resource)) {
		$size += zip_entry_filesize($dir_resource);
	    }
	    zip_close($resource);

	    return $size;
	}

	function findFile($directorio,$busqueda){
		$carpeta= false;
		$ficheros1 = scandir($directorio);
			foreach($ficheros1 as $val) {
				if($val==$busqueda){
					$carpeta=true;
				}
			}
		return $carpeta;	
	}

	function eliminarFile($directorio,$archivo){
	     return unlink($directorio."/".$archivo);
	}

    /**
	 * Funcion que mata varriables de session
	 * @param $app, $model
	 * @return vista de login
	 * @access public
	**/
	public function logout($app, $model){
			@session_name("loginUsuario");
			@session_start();
			$_SESSION["authenticated"]	= FALSE;
			@session_destroy();
			return $app->redirect('./login');
	}

	
    /**
	 * Funcion que muestra la vista para crear usuarios
	 * @param $app, $model
	 * @return vista de createUser
	 * @access public
	**/
	public function createUsers($app, $model){
		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
		    $menu=$model['admin']->menuTop();
			$cuerpo=$model['admin']->formularioNuevoUsuario($app);
			return $app['twig']->render( '/admin/createUser.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu,'campos'=>$cuerpo) );
	    }
	}

	/**
	 * Funcion que muestra la vista para crear departamentos
	 * @param $app, $model
	 * @return vista de createUser
	 * @access public
	**/
	public function createDepar($app, $model){
		$redirect=$this->validateSessionActive();

		if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
		    $menu=$model['admin']->menuTop();
			$cuerpo=$model['admin']->formularioNuevoDepartamento($app,$_POST['paisActual']);
			return $app['twig']->render( '/admin/createDepart.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu,'campos'=>$cuerpo) );
	    }
	}

	/**
	 * Funcion que muestra la vista para crear municipios
	 * @param $app, $model
	 * @return vista de createUser
	 * @access public
	**/
	public function createMuni($app, $model){
		$redirect=$this->validateSessionActive();

		if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
		    $menu=$model['admin']->menuTop();
			$cuerpo=$model['admin']->formularioNuevoMunicipio($app,$_POST['paisActual']);
			return $app['twig']->render( '/admin/createDepart.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu,'campos'=>$cuerpo) );
	    }
	}


	/**
	 * Funcion que muestra la vista para crear comunidad
	 * @param $app, $model
	 * @return vista de createUser
	 * @access public
	**/
	public function createComunidad($app, $model){
		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
		    $menu=$model['admin']->menuTop();
			$cuerpo=$model['admin']->formularioNuevoComunidad($app,$_POST['paisActual']);
			return $app['twig']->render( '/admin/createDepart.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu,'campos'=>$cuerpo) );
	    }
	}


	/**
	 * Funcion que muestra la vista para crear poblado
	 * @param $app, $model
	 * @return vista de createUser
	 * @access public
	**/
	public function createPoblado($app, $model){
		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
		    $menu=$model['admin']->menuTop();
			$cuerpo=$model['admin']->formularioNuevoPoblado($app,$_POST['paisActual']);
			return $app['twig']->render( '/admin/createDepart.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu,'campos'=>$cuerpo) );
	    }
	}

    /**
	 * Funcion que valida los datos al crear un usuario
	 * @param $app, $model
	 * @param $_POST, name, pass, pais, rol y email
	 * @return json, status=1 exitoso
	 * @access public
	**/
	public function validateFormUser($app, $model){
		$response 	= new stdclass();
		$nombre=$_POST['name'];
		$pass=$_POST['pass'];
		$pais=$_POST['pais'];
		$rol=$_POST['rol'];
		$email=$_POST['email'];

		if($nombre=='' || $pass=='' || $pais=='' || $email==''){
			$response->status 	= 0;
			$response->message 	= 'Por favor, ingrese todos los datos.';
			return $response;
		}else{
			$regex = '/^[a-zA-Z]((\.|_|-)?[a-zA-Z0-9]+){3}$/D';

			if (!preg_match($regex, $nombre)) {
				$response->status 	= 2;
				$response->message 	= 'Formato de usuario incorrecto. Debe contener al menos 4 caracteres e iniciar con letra.';
				return json_encode($response);	
			}
			$regexpass='/^(?=^.{6,}$)((?=.*[A-Za-z0-9])(?=.*[A-Z])(?=.*[a-z]))^.*$/';

			if(!preg_match($regexpass, $pass)){
				$response->status 	= 2;
				$response->message 	= 'Contraseña muy debil. Debe contener al menos 6 caracteres. Mayúscula, minusculas y números.';
				return json_encode($response);	
			}
			
			$result=$model['admin']->insertUser($nombre,$pass,$pais,$rol,$email,$app);
			if($result){
				$response->status 	= 1;
				$response->message 	= 'Usuario ingresado correctamente';
				return json_encode($response);
			}else{
				$response->status 	= 2;
				$response->message 	= 'El nombre del usuario ya se encuentra registrado.';
				return json_encode($response);
			}
			
		}
	}

    /**
	 * Funcion que cambia el estado a bloqueado de un usuario registro
	 * @param $app, $model
	 * @param $_POST, id de usuario
	 * @return json, status=1 exitoso
	 * @access public
	**/
	public function blockUser($app, $model){
	$response 	= new stdclass();
	$id=$_POST['id'];
		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
           $result= $model['admin']->blockUser($id,$app);
           		if($result){
					$response->status 	= 1;
					$response->message 	= 'Usuario bloqueado correctamente';
					return json_encode($response);
				}else{
					$response->status 	= 2;
				    $response->message 	= 'Error al bloquear al usuario.';
				    return json_encode($response);
				}
	    }
	}

	/**
	 * Funcion que cambia el estado a desbloqueado de un usuario registro
	 * @param $app, $model
	 * @param $_POST, id de usuario
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function activeUser($app, $model){
	$response 	= new stdclass();
	$id=$_POST['id'];

	$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
					return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
       $result= $model['admin']->activateUser($id,$app)	;
       	if($result){
			$response->status 	= 1;
			$response->message 	= 'Usuario activado correctamente';
			return json_encode($response);
			}else{
				$response->status 	= 2;
				$response->message 	= 'Error al activar al usuario.';
				return json_encode($response);
			}     
	    }
	}

	/**
	 * Funcion que cambia el estado a bloqueado de un usuario registro
	 * @param $app, $model
	 * @param $_POST, id de usuario
	 * @return json, status=1 exitoso
	 * @access public
	**/
	public function blockRegion($app, $model){
	$response 	= new stdclass();
	$id=$_POST['id'];
	$pais=$_POST['pais'];
	$region=$_POST['region'];
		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
           $result= $model['admin']->blockRegion($app,$id,$pais,$region);
           		if($result){
					$response->status 	= 1;
					$response->message 	= 'Región bloqueado correctamente';
					return json_encode($response);
				}else{
					$response->status 	= 2;
				    $response->message 	= 'Error al bloquear la región.';
				    return json_encode($response);
				}
	    }
	}		


	/**
	 * Funcion que cambia el estado a bloqueado de un usuario registro
	 * @param $app, $model
	 * @param $_POST, id de usuario
	 * @return json, status=1 exitoso
	 * @access public
	**/
	public function activeRegion($app, $model){
	$response 	= new stdclass();
	$id=$_POST['id'];
	$pais=$_POST['pais'];
	$region=$_POST['region'];
		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
           $result= $model['admin']->activeRegion($app,$id,$pais,$region);
           		if($result){
					$response->status 	= 1;
					$response->message 	= 'Región bloqueado correctamente';
					return json_encode($response);
				}else{
					$response->status 	= 2;
				    $response->message 	= 'Error al bloquear la región.';
				    return json_encode($response);
				}
	    }
	}		

    /**
	 * Funcion que elimina un usuario de la db
	 * @param $app, $model
	 * @param $_POST, id de usuario
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function deleteUser($app, $model){
	 $response 	= new stdclass(); 
	 $id=$_POST['id'];   
     $redirect = $model['admin']->deleteUser($id,$app);
	     if($redirect){
	     	$response->status 	= 0;
			$response->message 	= 'Usuario Eliminado Correctamente.';
			return json_encode($response);
		}else{
				$response->status 	= 1;
				$response->message 	= 'Error al Eliminar el Usuario.';
				return json_encode($response);
		}
	}


	/**
	 * Funcion que elimina un usuario de la db
	 * @param $app, $model
	 * @param $_POST, id de usuario
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function deleteRegion($app, $model){
	 $response 	= new stdclass(); 
	 $id=$_POST['id'];
	 $pais=$_POST['pais'];
	 $region=$_POST['region'];

     $redirect = $model['admin']->deleteRegion($app,$id,$pais,$region);
	     if($redirect){
	     	$response->status 	= 0;
			$response->message 	= 'Region Eliminado Correctamente.';
			return json_encode($response);
		}else{
				$response->status 	= 1;
				$response->message 	= 'Error al Eliminar la Region.';
				return json_encode($response);
		}
	}

    /**
	 * Funcion que muestra la vista de editarUsuario
	 * @param $app, $model
	 * @param $_POST, id de usuario
	 * @return vista editUser
	 * @access public
	**/	
	public function editUser($app, $model){
	  $id=$_POST['cadena'];	
	  $redirect=$this->validateSessionActive();
	  if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
	    }else {
	        $menu=$model['admin']->menuTop();
			$cuerpo=$model['admin']->editUsuario($app,$id);
			return	$app['twig']->render( '/admin/editUser.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'campos'=>$cuerpo));
	    }
	}


	/**
	 * Funcion que muestra la vista de editarRegion
	 * @param $app, $model
	 * @param $_POST, id_region, pais, id
	 * @return vista editUser
	 * @access public
	**/	
	public function editRegion($app, $model){
	  $id=$_POST['cadena'];	
	  $region=$_POST['region'];	
	  $pais=$_POST['paisActual'];	
	  $redirect=$this->validateSessionActive();
	  if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
	    }else {
	        $menu=$model['admin']->menuTop();
			$cuerpo=$model['admin']->editarRegion($app,$id,$region,$pais);
			return	$app['twig']->render( '/admin/editRegion.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'campos'=>$cuerpo));
	    }
	}

    /**
	 * Funcion que validad los datos al editar un usuario
	 * @param $app, $model
	 * @param $_POST, name, pass, pais, email, id
	 * @return vista editUser
	 * @access public
	**/	
	public function validEditUser($app, $model){
	    $response 	= new stdclass();
		$nombre=$_POST['name'];
		$pass=$_POST['pass'];
		$pais=$_POST['pais'];
		$email=$_POST['email'];
		$id=$_POST['id'];
		if($nombre=='' || $pass=='' || $pais=='' || $email==''){
			$response->status 	= 0;
			$response->message 	= 'Por favor, ingrese todos los datos.';
			return $response;
		}else{

			$regexpass='/^(?=^.{6,}$)((?=.*[A-Za-z0-9])(?=.*[A-Z])(?=.*[a-z]))^.*$/';
			if(!preg_match($regexpass, $pass)){
				$response->status 	= 2;
				$response->message 	= 'Contraseña muy debil. Debe contener al menos 6 caracteres. Mayúscula, minusculas y números.';
				return json_encode($response);	
		     }
			
			$result=$model['admin']->updateUser($app,$nombre,$pass,$pais,$email,$id);
			if($result){
				$response->status 	= 1;
				$response->message 	= 'Usuario actualizado correctamente';
				return json_encode($response);
			}else{
					$response->status 	= 2;
				$response->message 	= 'Error, al actualizar el usuario.';
				return json_encode($response);
			}
			
		}

	}


	/**
	 * Funcion que valida los datos al crear un departamento
	 * @param $app, $model
	 * @param $_POST, name, latitud, longitud, pais
	 * @return json, status=1 exitoso
	 * @access public
	**/
	public function validateFormDepart($app, $model){
		$response 	= new stdclass();
		$nombre=$_POST['name'];
		$latitud=$_POST['latitud'];
		$longitud=$_POST['longitud'];
		$pais=$_POST['pais'];

		if($nombre=='' || $latitud=='' || $longitud==''){
			$response->status 	= 0;
			$response->message 	= 'Por favor, ingrese todos los datos.';
			return $response;
		}else{
			
			$result=$model['admin']->insertDepartamento($app,$nombre,$latitud,$longitud,$pais);
			if($result){
				$response->status 	= 1;
				$response->message 	= 'Region ingresada correctamente';
				return json_encode($response);
			}else{
				$response->status 	= 2;
				$response->message 	= 'El nombre de la region ya se encuentra registrada.';
				return json_encode($response);
			}
			
		}
	}

	/**
	 * Funcion que valida los datos al crear un municipio
	 * @param $app, $model
	 * @param $_POST, name, latitud, longitud, departamento pais
	 * @return json, status=1 exitoso
	 * @access public
	**/
	public function validateFormMunicipio($app, $model){
		$response 	= new stdclass();
		$nombre=$_POST['name'];
		$latitud=$_POST['latitud'];
		$longitud=$_POST['longitud'];
		$departamento=$_POST['departamento'];
		$pais=$_POST['pais'];

		if($nombre=='' || $latitud=='' || $longitud==''){
			$response->status 	= 0;
			$response->message 	= 'Por favor, ingrese todos los datos.';
			return $response;
		}else{
			
			$result=$model['admin']->insertMunicipio($app,$nombre,$latitud,$longitud,$departamento,$pais);
			if($result){
				$response->status 	= 1;
				$response->message 	= 'Region ingresada correctamente';
				return json_encode($response);
			}else{
				$response->status 	= 2;
				$response->message 	= 'El nombre de la region ya se encuentra registrada.';
				return json_encode($response);
			}
			
		}
	}


	/**
	 * Funcion que valida los datos al crear una comunidad
	 * @param $app, $model
	 * @param $_POST, name, latitud, longitud, municipio pais
	 * @return json, status=1 exitoso
	 * @access public
	**/
	public function validateFormComunidad($app, $model){
		$response 	= new stdclass();
		$nombre=$_POST['name'];
		$latitud=$_POST['latitud'];
		$longitud=$_POST['longitud'];
		$municipio=$_POST['municipio'];
		$pais=$_POST['pais'];

		if($nombre=='' || $latitud=='' || $longitud==''){
			$response->status 	= 0;
			$response->message 	= 'Por favor, ingrese todos los datos.';
			return $response;
		}else{
			
			$result=$model['admin']->insertComunidad($app,$nombre,$latitud,$longitud,$municipio,$pais);
			if($result){
				$response->status 	= 1;
				$response->message 	= 'Region ingresada correctamente';
				return json_encode($response);
			}else{
				$response->status 	= 2;
				$response->message 	= 'El nombre de la region ya se encuentra registrada.';
				return json_encode($response);
			}
			
		}
	}


	/**
	 * Funcion que valida los datos al crear un poblado
	 * @param $app, $model
	 * @param $_POST, name, latitud, longitud, municipio pais
	 * @return json, status=1 exitoso
	 * @access public
	**/
	public function validateFormPoblado($app, $model){
		$response 	= new stdclass();
		$nombre=$_POST['name'];
		$latitud=$_POST['latitud'];
		$longitud=$_POST['longitud'];
		$comunidad=$_POST['comunidad'];
		$pais=$_POST['pais'];

		if($nombre=='' || $latitud=='' || $longitud==''){
			$response->status 	= 0;
			$response->message 	= 'Por favor, ingrese todos los datos.';
			return $response;
		}else{
			
			$regex = '/^[a-zA-Z]((\.|_|-)?[a-zA-Z0-9]+){3}$/D';
			if (!preg_match($regex, $nombre)) {
				$response->status 	= 2;
				$response->message 	= 'Formato de region incorrecto. Debe contener al menos 4 caracteres e iniciar con letra.';
				return json_encode($response);	
		     }	

			$result=$model['admin']->insertPoblado($app,$nombre,$latitud,$longitud,$comunidad,$pais);
			if($result){
				$response->status 	= 1;
				$response->message 	= 'Region ingresada correctamente';
				return json_encode($response);
			}else{
				$response->status 	= 2;
				$response->message 	= 'El nombre de la region ya se encuentra registrada.';
				return json_encode($response);
			}
			
		}
	}


	/**
	 * Funcion que validad los datos al editar una region
	 * @param $app, $model
	 * @param $_POST, name, latitud, longitud, id, pais, region
	 * @return vista editUser
	 * @access public
	**/	
	public function validEditRegion($app, $model){
	    $response 	= new stdclass();
		$nombre=$_POST['name'];
		$latitud=$_POST['latitud'];
		$longitud=$_POST['longitud'];
		$pais=$_POST['pais'];
		$region=$_POST['region'];
		$id=$_POST['id'];
		if($nombre=='' || $latitud=='' || $longitud=='' ){
			$response->status 	= 0;
			$response->message 	= 'Por favor, ingrese todos los datos.';
			return $response;
		}else{

			$regex = '/^[a-zA-Z]((\.|_|-)?[a-zA-Z0-9]+){3}$/D';
			if (!preg_match($regex, $nombre)) {
				$response->status 	= 2;
				$response->message 	= 'Formato de region incorrecto. Debe contener al menos 4 caracteres e iniciar con letra.';
				return json_encode($response);	
		     }
			
			$result=$model['admin']->updateRegion($app,$nombre,$latitud,$longitud,$pais,$region,$id);
			if($result){
				$response->status 	= 1;
				$response->message 	= 'Región actualizado correctamente';
				return json_encode($response);
			}else{
				$response->status 	= 2;
				$response->message 	= 'Error, al actualizar la región.';
				return json_encode($response);
			}
			
		}

	}
								


	/**
	 * Funcion que validad los textos
	 * @param $app, $model
	 * @param $_POST, name, pass, pais, email, id
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function validTextos($app,$model){

    $response 	= new stdclass();
	$texto1=$_POST['texto1'];
	$texto2=$_POST['texto2'];
	$texto3=$_POST['texto3'];
	$texto4=$_POST['texto4'];
	$idtexto1=$_POST['idtexto1'];
	$idtexto2=$_POST['idtexto2'];
	$idtexto3=$_POST['idtexto3'];
	$idtexto4=$_POST['idtexto4'];
	$pais=$_POST['pais'];

	if($texto1=='' || $texto2=='' || $texto3=='' || $texto4==''){
		$response->status 	= 0;
		$response->message 	= 'Por favor, ingrese todos los datos.';
		return $response;
	}else{
		$result=$model['admin']->updateTextos($app,$texto1,$texto2,$texto3,$texto4,$idtexto1,$idtexto2,$idtexto3,$idtexto4,$pais);
		if($result){
			$response->status 	= 1;
			$response->message 	= 'Textos actualizado correctamente';
			return json_encode($response);
		}else{
				$response->status 	= 2;
			$response->message 	= 'Error, al actualizar los textos.';
			return json_encode($response);
		}
		
	}

}							
	/**
	 * Funcion devuelve el html de los municipios
	 * @param $app, $model
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function getMunicipios($app,$model){

    $response 	= new stdclass();
	$depar=$_POST['depar'];
	$pais=$_POST['pais'];
		$result=$model['admin']->editEspecificMuni($app,$depar,$pais);
		if($result){
			$response->status 	= 1;
			$response->message 	= $result;
			return json_encode($response);
		}else{
			$response->status 	= 2;
			$response->message 	= 'Error, al actualizar los textos.';
			return json_encode($response);
		}
	}	


	/**
	 * Funcion que llena el selec de los municipios
	 * @param $app, $model
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function getSelecMuni($app,$model){

	    $response 	= new stdclass();
		$depar=$_POST['depar'];
		$pais=$_POST['pais'];

	    $municipios=$model['admin']->getMunicipios($app,$depar,$pais);
		$response->list 	= $municipios;
		return json_encode($response);
		
	}

	/**
	 * Funcion devuelve el html de las comunidades
	 * @param $app, $model
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function getComunidades($app,$model){

    $response 	= new stdclass();
	$muni=$_POST['muni'];
	$pais=$_POST['pais'];
		$result=$model['admin']->editEspecificComuni($app,$muni,$pais);
		if($result){
			$response->status 	= 1;
			$response->message 	= $result;
			return json_encode($response);
		}else{
			$response->status 	= 2;
			$response->message 	= 'Error, al actualizar los textos.';
			return json_encode($response);
		}
	}


	/**
	 * Funcion que llena el selec de las comunidades
	 * @param $app, $model
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function getSelecComunidad($app,$model){

	    $response 	= new stdclass();
		$muni=$_POST['muni'];
		$pais=$_POST['pais'];

	    $comunidades=$model['admin']->getComunidad($app,$muni,$pais);
		$response->list 	= $comunidades;
		return json_encode($response);
		
	}


	/**
	 * Funcion devuelve el html de los poblados
	 * @param $app, $model
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function getPoblados($app,$model){

    $response 	= new stdclass();
	$comunidad=$_POST['comunidad'];
	$pais=$_POST['pais'];
		$result=$model['admin']->editEspecificPoblado($app,$comunidad,$pais);
		if($result){
			$response->status 	= 1;
			$response->message 	= $result;
			return json_encode($response);
		}else{
			$response->status 	= 2;
			$response->message 	= 'Error, al actualizar los textos.';
			return json_encode($response);
		}
	}	




	/**
	* Valida que la session de login continua activa
	*
	* @var boolean
	* @return object
	*/
	private function validateSessionActive()
	{
		#inicia sesión
		@session_name("login_usuario");
		@session_start();
		$response 			= new stdClass();
		$response->redirect = FALSE;

		#validar que el usuario esta logueado
		if ( !(@$_SESSION["authenticated"]) ) {

			#el usuario NO inicio sesion
			$response->redirect = FALSE;
			$response->url 		= 'index.php/login';

		} else {
			#el usuario inicio sesion
			$fechaGuardada 			= $_SESSION["lastaccess"];
			$ahora 					= date("Y-n-j H:i:s");
			$tiempo_transcurrido 	= (strtotime($ahora)-strtotime($fechaGuardada));

			#comparar el tiempo transcurrido 
			if($tiempo_transcurrido >= 6000) {

				#si el tiempo es mayo del indicado como tiempo de vida de la session
				session_destroy(); #destruir la sesión y se redirecciona a lagin
				$response->redirect = FALSE;
				$response->url 		= 'index.php/login';
				#sino, se actualiza la fecha de la session

			}else {

				#actualizar tiempo de session
				$_SESSION["lastaccess"] = $ahora;
				$response->redirect 	= TRUE;
				$response->url 			= 'index.php/home';

			}
		}
		return $response;
	}


    /**
	 * Funcion que devuelve la extension de un archivo
	 * @param $app, $model, $file
	 * @return string
	 * @access private
	**/	
	private function extension($file) {
	return strtolower(str_replace('.', '', substr($file, strrpos($file, '.'))));
	}



	/**
	* Recibe valores para sanitizar
	*
	* @var String || array || Object
	* @var Bool false valor por defecto, true=array,False=string
	* @return retorna valores sanitizados
	*/
	private function sanitizeVar( $var, $type = false )
	{
		#type = true for array
		$sanitize = new stdClass();
		if ( $type ){

			foreach ($var as $key => $value) {
				$sanitize->$key = $this->clearString( $value );
			}
			return $sanitize;
		} else {
			return  $this->clearString( $var );
		}
	}

	
	/**
	* Recibe String para aliminar carcteres especiales
	*
	* @var String
	* @return retorna string libre de caracteres especiales
	*/
	private function clearString( $string )
	{
		$string = strip_tags($string);
		$string = htmlspecialchars($string);
		$string = addslashes($string);
		return $string;
	}


	
	/**
	Funciones nuevas de mapa por partes
	*/

	/**
	 * Funcion que redirecciona a pagina de mapas
	 * @param $app, $model
	 * @return vista de mapas
	 * @access public
	**/
	public function editor($app, $model){
		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
					return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
			$menu=$model['admin']->menuTop($app);
			$cuerpo=$model['admin']->formularioIntermedio($app);
		    return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'0', 'appAlertMensaje'=>''));
	    }
	}


	/**
	 * Funcion que redirecciona a pagina de mapas
	 * @param $app, $model
	 * @return vista de mapas
	 * @access public
	**/
	public function pasoDos($app, $model){
		$redirect=$this->validateSessionActive();

		$pais=$_POST["pais"];
		if ( !$redirect->redirect ){
					return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
			$menu=$model['admin']->menuTop($app);

			if($_POST["tipomapa"]==0){
				$cuerpo=$model['admin']->getUpload($app,$pais);
				return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'0', 'appAlertMensaje'=>''));
			}else{
                $cuerpo=$model['admin']->formPasoDos($app,$pais);
                return	$app['twig']->render( '/admin/textos.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo));
			}
			
		    
	    }
	}


	/**
	 * Funcion que redirecciona a pagina de mapas
	 * @param $app, $model
	 * @return vista de mapas
	 * @access public
	**/
	public function multiMapas($app, $model){
		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
					return $app['twig'] -> render('/admin/layout.view.twig');
		}else {

			$menu=$model['admin']->menuTop($app);
			$error=false;
		
			if(isset($_SESSION["newsession"])){
				$pais=$_SESSION["paisActual"];
				$tipoMapa=$_SESSION["coberturaActual"];
				$tipoCobertura=$_SESSION["coberturaTipo"];
				$tipoCobertura=$_SESSION["coberturaTipo"];
				$mensaje=$_SESSION["mensajeAlerta"];

				//limpiamos variables de session
				unset($_SESSION["newsession"]);
			    unset($_SESSION["paisActual"]);
			    unset($_SESSION["coberturaActual"]);
			    unset($_SESSION["coberturaTipo"]);

			    $error=true;

			}else{
				$pais=$_POST["pais"];
				$tipoMapa=$_POST["tipomapa"];
				$tipoCobertura=$_POST["tipocobertura"];
				
			}

                $cuerpo=$model['admin']->getAllMaps($app,$pais,$tipoMapa,$tipoCobertura);
                
 			if($error){
			 	 return	$app['twig']->render( '/admin/listado.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>$mensaje));
			 }else{
			 	return	$app['twig']->render( '/admin/listado.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'0', 'appAlertMensaje'=>''));
			 }  
	    }
	}


	/**
	 * Funcion devuelve el html de los poblados
	 * @param $app, $model
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function getMapas($app,$model){

    $response 	= new stdclass();
	$departamento=$_POST['departamento'];
	$cobertura=$_POST['cobertura'];
	$pais=$_POST['pais'];
	$servicio=$_POST['service'];	

		$result=$model['admin']->editEspecificMapa($app,$departamento,$cobertura,$pais,$servicio);
		if($result){
			$response->status 	= 1;
			$response->message 	= $result;
			return json_encode($response);
		}else{
			$response->status 	= 2;
			$response->message 	= 'Error, al actualizar los textos.';
			return json_encode($response);
		}
	}	


	/**
	 * Funcion devuelve el html de los poblados
	 * @param $app, $model
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function getCobertura($app,$model){

		$response 	= new stdclass();
		$servicio=$_POST['servicio'];

	    $cobertura=$model['admin']->getCobertura($app,$servicio);
		$response->list 	= $cobertura;
		return json_encode($response);
	}


	/**
	 * Funcion devuelve el html de los poblados
	 * @param $app, $model
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function formLoadMap($app,$model){

		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
					return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
			$menu=$model['admin']->menuTop($app);
			$pais=$_POST["paisActual"];
			$tipoMapa=$_POST["tipoMapa"];
			$tipoCobertura=$_POST["tipoCobertura"];
            $cuerpo=$model['admin']->loadAllMaps($app,$pais,$tipoMapa,$tipoCobertura);
                
		    return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'0', 'appAlertMensaje'=>''));
	    }
	}	



	/**
	 * Funcion devuelve el html de los poblados
	 * @param $app, $model
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function subirUnArchivo($app,$model){
			$pais=$_POST["pais"];
			$departamento1=$_POST["upmapa"];
			$mapa=$_POST["mapa"];
			$cobertura=$_POST["cobertura"];
			$tipoCobertura=$_POST["tipocobertura"];

			$tipoMapa=$_POST["tipomapa"];
			//llenamos variables de session para mostrar erro
			$_SESSION["newsession"]=TRUE;
			$_SESSION["paisActual"]=$pais;
			$_SESSION["coberturaActual"]=$tipoMapa;
			$_SESSION["coberturaTipo"]=$tipoCobertura;
			$_SESSION["mensajeAlerta"]="";

		    $hoy = date("Y-m-d H:i:s");
			$timestamp = strtotime($hoy);

		//consultamos el nombre del departamento
        $departamento=$model['admin']->findDepar($app,$pais,$departamento1);
        //obtenemos la extension del archivo
		$extension = $this->extension($_FILES["ag"]['name']);


		//obtenemos el menu y cuerpo del sitio
		$menu=$model['admin']->menuTop($app);
		$cuerpo=$model['admin']->formularioIntermedio($app);


        //obtenemos la ruta segun el pais
	    $rutaBase="/home/app";
		if($pais==1){
		      $directorio=$rutaBase.DIR_KMZGT;
		}else if($pais==2){
			  $directorio=$rutaBase.DIR_KMZSV;
		}else if($pais==3){
			  $directorio=$rutaBase.DIR_KMZHN;
		}else if($pais==4){
			 $directorio=$rutaBase.DIR_KMZNI;
		}else if($pais==5){
			 $directorio=$rutaBase.DIR_KMZCR;
		}

		 //obtenemos nombre y ruta de la carpeta
		$directorio_completo=$directorio.$mapa."/".$cobertura."/".$departamento;
		//creamos la carpeta en caso de que no exista
		if(!$this->findFile($directorio.$mapa."/".$cobertura,$departamento)){
			if(mkdir($directorio_completo)){
				chmod($directorio_completo, 0777);
			}else{
				//retornamos error al crear la carpeta
				/*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Error al crear directorio, por favor verificar permisos.'));*/
				$_SESSION["mensajeAlerta"]="Error al crear directorio, por favor verificar permisos.";
				$respuesta =$this->multiMapas($app, $model);
				return $respuesta;
			}
			
		}

		//obtenemos el tamaño 
		if($extension=="kml"){
				$size=$_FILES["ag"]["size"];
		}else if($extension=="zip"){
				$file=$_FILES["ag"]["tmp_name"];
				$size=$this->get_zip_originalsize($file);
		}else if($extension=="kmz"){
			$completo=$directorio.$mapa."/".$cobertura."/".$departamento;
			$retorno=$this->subirArchivoKMZ($app,$model,$_FILES["ag"],$pais,$departamento,$departamento1,$tipoCobertura,$completo);
			if($retorno=="error"){
				  $_SESSION["mensajeAlerta"]="Ha ocurrido un error al subir el archivo, por favor intenta de nuevo.";
				  $respuesta =$this->multiMapas($app, $model);
				 return $respuesta;
			}else{
				$_SESSION["mensajeAlerta"]="El fichero es válido y se subió con éxito.";
				$respuesta =$this->multiMapas($app, $model);
				return $respuesta;
			}
		}else{
			//retornamos error de formato
			/*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Por favor, verificar la extension del archivo.'));*/
			$_SESSION["mensajeAlerta"]="Por favor, verificar la extension del archivo.";
			$respuesta =$this->multiMapas($app, $model);
			return $respuesta;
		}

		if($size>2621440){
				//retornamos archivo muy grande	
			/*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Por favor, verificar el tamaño del archivo.'));*/
			$_SESSION["mensajeAlerta"]="Por favor, verificar el tamaño del archivo.";
			$respuesta =$this->multiMapas($app, $model);
			return $respuesta;
		}

       

		//buscamos en la db la ruta y si no existe la creamos
		  $existRuta=$model['admin']->findRuta($app,$pais,$departamento1,$tipoCobertura,1);

		   if(empty($existRuta)){
		   		//insertamos en DB la nueva ruta
		   	     $ruta=$model['admin']->insertRuta($app,$pais,$departamento,$departamento1,$tipoCobertura,1);
		   }else{
		   		$ruta=$existRuta[0]['id'];
		   }
		


		//si es kml solo lu subimos
		if($extension=="kml"){
			$dir_subida = $directorio_completo."/";
			$fichero_subido = $dir_subida . basename($_FILES['ag']['name']);

			if(!$this->findFile($directorio_completo, basename($_FILES['ag']['name']))){
				if (move_uploaded_file($_FILES['ag']['tmp_name'], $fichero_subido)) {
				    chmod($directorio_completo."/".basename($_FILES['ag']['name']), 0777);
				    //guardamos en la DB datos del archivo
				    $ingresoArchivo=$model['admin']->insertFile($app,$pais,basename($_FILES['ag']['name']),$size,$timestamp,$ruta,"",0);
				    /*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'El fichero es válido y se subió con éxito.'));*/
					$_SESSION["mensajeAlerta"]="El fichero es válido y se subió con éxito.";
					$respuesta =$this->multiMapas($app, $model);
				return $respuesta;
				} else {
				    /*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Ha ocurrido al subir el archivo, por favor intenta de nuevo.'));*/
					 $_SESSION["mensajeAlerta"]="Ha ocurrido al subir el archivo, por favor intenta de nuevo.";
					$respuesta =$this->multiMapas($app, $model);
					return $respuesta;
				}
			}else{
					//mostrar error de archivo ya existe.
				     /*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Ya existe un archivo con el mismo nombre.'));*/
				$_SESSION["mensajeAlerta"]="Ya existe un archivo con el mismo nombre.";
				$respuesta =$this->multiMapas($app, $model);
				return $respuesta;
			}
					
		}else if($extension=="zip"){
			   	$total="";
				$files = @zip_open($file);
				if ($files) {
					$total=0;
					$isKml=0;
				  while ($cursor =@zip_read($files)) {
				  	   $total=$total+1;
				  	   $name=@zip_entry_name($cursor);
				  	   $nombre=$name;
				  	   $file_names[]=$name;
				       if(!is_dir($name)){ 
				        if(!strpos($name,".kml"))
				          {
				          	    $isKml=$isKml+1;
			 	          }
				        } 
				  }

				  if($isKml==0 && $total==1){

				  	if(!$this->findFile($directorio_completo, $nombre)){

				  		$zip = new ZipArchive;
								$res = $zip->open($file);
								if ($res === TRUE) {
								    $zip->extractTo($directorio_completo);
								    $zip->close();
								    chmod($directorio_completo."/".$nombre, 0777);
								    //guardamos en la DB
								      $ingresoArchivo=$model['admin']->insertFile($app,$pais,$nombre,$size,$timestamp,$ruta,"",0);
								    /*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'El fichero es válido y se subió con éxito.'));*/
									$_SESSION["mensajeAlerta"]="El fichero es válido y se subió con éxito.";
									$respuesta =$this->multiMapas($app, $model);
									return $respuesta;
								} else {
								    /*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Error al descomprimir el archivo.'));*/
									$_SESSION["mensajeAlerta"]="Error al descomprimir el archivo.";
									$respuesta =$this->multiMapas($app, $model);
									return $respuesta;
								} 
						
					}else{
							//mostrar error de archivo ya existe.
						    /*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Ya existe un archivo con el mismo nombre.'));*/
						$_SESSION["mensajeAlerta"]="Ya existe un archivo con el mismo nombre.";
							$respuesta =$this->multiMapas($app, $model);
							return $respuesta;
					}

				  		
				  }else{
				  	//error de tamaño y de formato
				  	/*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Por favor verificar la cantidad o la extension del archivo.'));*/
					$_SESSION["mensajeAlerta"]="Por favor verificar la cantidad o la extension del archivo.";
					$respuesta =$this->multiMapas($app, $model);
					return $respuesta;	
				  }

				   @zip_close($files);
				 }
		}else{
			/*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Por favor, verificar la extension del archivo.'));*/
			$_SESSION["mensajeAlerta"]="Por favor, verificar la extension del archivo.";
			$respuesta =$this->multiMapas($app, $model);
			return $respuesta;	
		}

		die;

	}


 /**
	 * Funcion que elimina un usuario de la db
	 * @param $app, $model
	 * @param $_POST, id de usuario
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function deleteMapa($app, $model){
			 $response 	= new stdclass(); 
			 $id=$_POST['id'];  
			 $pais=$_POST['pais'];   

			 $ruta=$model['admin']->findDirectorioMapa($app,$id,$pais);
			 //$rutaBase=$_SERVER["DOCUMENT_ROOT"];
			  $rutaBase="/home/app";
				if($pais==1){
				      $directorio=$rutaBase.DIR_KMZGT;
				}else if($pais==2){
					  $directorio=$rutaBase.DIR_KMZSV;
				}else if($pais==3){
					  $directorio=$rutaBase.DIR_KMZHN;
				}else if($pais==4){
					 $directorio=$rutaBase.DIR_KMZNI;
				}else if($pais==5){
					 $directorio=$rutaBase.DIR_KMZCR;
				}
			$directorio=$directorio.$ruta[0]["name"]."/".$ruta[0]["nombre"]."/".$ruta[0]["Carpeta"];
		     $redirect = $model['admin']->deleteMapa($app,$id,$pais);

			     if($redirect){
			     	$response->status 	= 0;
					$response->message 	= 'Archivo Eliminado Correctamente.';
					$this->eliminarFile($directorio,$ruta[0]["layer_type"]);
					return json_encode($response);
				}else{
					$response->status 	= 1;
					$response->message 	= 'Error al Eliminar el Archivo.';
					return json_encode($response);
				}
	}


	/**
	 * Funcion que redirecciona a pagina para editar la region seleccionada
	 * @param $app, $model
	 * @return vista de textos
	 * @access public
	**/
	public function editMapa($app, $model){
		$archivo=$_POST["cadena"];
		$pais=$_POST["paisActual"];
		$cobertura=$_POST["tipoCobertura"];
		$servicio=$_POST["tipoMapa"];

		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
			$menu=$model['admin']->menuTop($app);
			$cuerpo=$model['admin']->editMapa($app,$pais,$archivo,$cobertura,$servicio);
		    return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'0', 'appAlertMensaje'=>''));  
	    }
	}


	/**
	 * Funcion devuelve el html de los poblados
	 * @param $app, $model
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function actualizarUnArchivo($app,$model){
			$pais=$_POST["pais"];
			$archivo=$_POST["archivo"];

			$cobertura=$_POST["cobertura"];
			$servicio=$_POST["servicio"];



			//llenamos variables de session para mostrar erro
			$_SESSION["newsession"]=TRUE;
			$_SESSION["paisActual"]=$pais;
			$_SESSION["coberturaActual"]=$servicio;
			$_SESSION["coberturaTipo"]=$cobertura;
			$_SESSION["mensajeAlerta"]="";

		    	$hoy = date("Y-m-d H:i:s");
			$timestamp = strtotime($hoy);

		//consultamos el nombre del departamento
        $nombres=$model['admin']->findDirectorioMapa($app,$archivo,$pais);

        $nombreArchivo=$nombres[0]["layer_type"];//departamento
        $departamento=$nombres[0]["Carpeta"];//departamento
        $cobertura= $nombres[0]["nombre"];//cobertura
        $mapa=$nombres[0]["name"];//servicio
        //obtenemos la extension del archivo
		$extension = $this->extension($_FILES["ag"]['name']);
		//obtenemos el menu y cuerpo del sitio
		$menu=$model['admin']->menuTop($app);
		$cuerpo=$model['admin']->formularioIntermedio($app);


        //obtenemos la ruta segun el pais
		$rutaBase="/home/app";
		if($pais==1){
		      $directorio=$rutaBase.DIR_KMZGT;
		}else if($pais==2){
			  $directorio=$rutaBase.DIR_KMZSV;
		}else if($pais==3){
			  $directorio=$rutaBase.DIR_KMZHN;
		}else if($pais==4){
			 $directorio=$rutaBase.DIR_KMZNI;
		}else if($pais==5){
			 $directorio=$rutaBase.DIR_KMZCR;
		}



		//obtenemos el tamaño 
		if($extension=="kml"){
				$size=$_FILES["ag"]["size"];
		}else if($extension=="zip"){
				$file=$_FILES["ag"]["tmp_name"];
				$size=$this->get_zip_originalsize($file);
		}else{
			//retornamos error de formato
			/*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Por favor, verificar la extension del archivo.'));*/
			$_SESSION["mensajeAlerta"]="Por favor, verificar la extension del archivo.";
			$respuesta =$this->multiMapas($app, $model);
			return $respuesta;
		}

		
		if($size>2621440){
				//retornamos archivo muy grande	
			/*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Por favor, verificar el tamaño del archivo.'));*/
			$_SESSION["mensajeAlerta"]="Por favor, verificar el tamaño del archivo.";
			$respuesta =$this->multiMapas($app, $model);
			return $respuesta;
		}

        //obtenemos nombre y ruta de la carpeta
		$directorio_completo=$directorio.$mapa."/".$cobertura."/".$departamento;
		//creamos la carpeta en caso de que no exista
		if(!$this->findFile($directorio.$mapa."/".$cobertura,$departamento)){
			if(mkdir($directorio_completo)){
				chmod($directorio_completo, 0777);
			}else{
				//retornamos error al crear la carpeta
				/*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Error al crear directorio, por favor verificar permisos.'));*/
				$_SESSION["mensajeAlerta"]="Error al crear directorio, por favor verificar permisos.";
				$respuesta =$this->multiMapas($app, $model);
				return $respuesta;
			}
			
		}


		//si es kml solo lu subimos
		if($extension=="kml"){
			$dir_subida = $directorio_completo."/";
			$fichero_subido = $dir_subida . basename($_FILES['ag']['name']);

			//eliminamos
			$this->eliminarFile($directorio_completo,$nombreArchivo);

			if(!$this->findFile($directorio_completo, basename($_FILES['ag']['name']))){
				if (move_uploaded_file($_FILES['ag']['tmp_name'], $fichero_subido)) {
				    chmod($directorio_completo."/".basename($_FILES['ag']['name']), 0777);
				    //actualizamos en la DB datos del archivo
				    $actualizarArchivo=$model['admin']->updateArchivo($app,$pais,basename($_FILES['ag']['name']),$size,$timestamp,$archivo);
				    /*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'El fichero es válido y se subió con éxito.'));*/
				        $_SESSION["mensajeAlerta"]="El fichero es válido y se subió con éxito.";
					$respuesta =$this->multiMapas($app, $model);
					return $respuesta;
				} else {
				    /*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Ha ocurrido al subir el archivo, por favor intenta de nuevo.'));*/
					$_SESSION["mensajeAlerta"]="Ha ocurrido al subir el archivo, por favor intenta de nuevo.";
					$respuesta =$this->multiMapas($app, $model);
					return $respuesta;
				}
			}else{
					//mostrar error de archivo ya existe.
				     /*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Ya existe un archivo con el mismo nombre.'));*/
					$_SESSION["mensajeAlerta"]="Ya existe un archivo con el mismo nombre.";
					$respuesta =$this->multiMapas($app, $model);
					return $respuesta;
			}
					
		}else if($extension=="zip"){
			   	$total="";
				$files = @zip_open($file);
				if ($files) {
					$total=0;
					$isKml=0;
				  while ($cursor =@zip_read($files)) {
				  	   $total=$total+1;
				  	   $name=@zip_entry_name($cursor);
				  	   $nombre=$name;
				  	   $file_names[]=$name;
				       if(!is_dir($name)){ 
				        if(!strpos($name,".kml"))
				          {
				          	    $isKml=$isKml+1;
			 	          }
				        } 
				  }

				  if($isKml==0 && $total==1){

				  	//eliminamos
				  	$this->eliminarFile($directorio_completo,$nombreArchivo);

				  	if(!$this->findFile($directorio_completo, $nombre)){

				  		$zip = new ZipArchive;
								$res = $zip->open($file);
								if ($res === TRUE) {
								    $zip->extractTo($directorio_completo);
								    $zip->close();
								    chmod($directorio_completo."/".$nombre, 0777);
								    //actualizamos en la DB
								     $actualizarArchivo=$model['admin']->updateArchivo($app,$pais,$nombre,$size,$timestamp,$archivo);
								    /*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'El fichero es válido y se subió con éxito.'));*/
									 $_SESSION["mensajeAlerta"]="El fichero es válido y se subió con éxito.";
									 $respuesta =$this->multiMapas($app, $model);
									 return $respuesta;
								} else {
								    /*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Error al descomprimir el archivo.'));*/
									$_SESSION["mensajeAlerta"]="Error al descomprimir el archivo.";
									$respuesta =$this->multiMapas($app, $model);
									return $respuesta;
								} 
						
					}else{
							//mostrar error de archivo ya existe.
						   /* return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Ya existe un archivo con el mismo nombre.'));*/
							$_SESSION["mensajeAlerta"]="Ya existe un archivo con el mismo nombre.";
							$respuesta =$this->multiMapas($app, $model);
							return $respuesta;
					}

				  		
				  }else{
				  	//error de tamaño y de formato
				  	/*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Por favor verificar la cantidad o la extension del archivo.'));*/
					$_SESSION["mensajeAlerta"]="Por favor verificar la cantidad o la extension del archivo.";
					$respuesta =$this->multiMapas($app, $model);
					return $respuesta;
				  }

				   @zip_close($files);
				 }
		}else{
			/*return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'Por favor, verificar la extension del archivo.'));*/
			$_SESSION["mensajeAlerta"]="Por favor, verificar la extension del archivo.";
			$respuesta =$this->multiMapas($app, $model);
		        return $respuesta;
		}

		die;

	}


public function subirVariosArchivo($app, $model){
			
	$msg_alert = array();
	$map_ary = array(
		'ag' => 'agencia',
		'2g' => 'c2g',
		'3g' => 'c3g',
		'4g' => 'c4g',
		'v2g' => 'v2g',
		'v3g' => 'v3g',
		'v4g' => 'v4g',
	);

	$pais=$_POST['pais'];
	$subida=$_POST['subida'];
	foreach ($map_ary as $map_k => $map_v) {
			$temporal = $_FILES[$map_k]['tmp_name'];
		    if (empty($temporal)) {
			   continue;
		    }
			$msg_alert[$map_k]=$this->subirUnArchivoCompleto($app,$model,$pais,$_FILES[$map_k],$map_k,$subida);
	}

	$mensaje="";
	$correcto=true;
	foreach ($map_ary as $map_k => $map_v) {
			if(@$msg_alert[$map_k]!="true" and @$msg_alert[$map_k]!=null ){
				$correcto=false;
					if($map_k=="ag"){
						$mensaje.="Error al subir el archivo de agencias. ";
					}else if($map_k=="2g"){
						$mensaje.="Error al subir el archivo de Movil 2G. ";
					}else if($map_k=="3g"){
						$mensaje.="Error al subir el archivo de Movil 3G. ";
					}else if($map_k=="4g"){
						$mensaje.="Error al subir el archivo de Movil 4G. ";
					}else if($map_k=="v2g"){
						$mensaje.="Error al subir el archivo de Internet 2G. ";
					}else if($map_k=="v3g"){
						$mensaje.="Error al subir el archivo de Internet 3G. ";
					}else if($map_k=="v4g"){
						$mensaje.="Error al subir el archivo de Internet 4G. ";
					}
			}
	}

	$menu=$model['admin']->menuTop($app);
	$cuerpo=$model['admin']->formularioIntermedio($app);
	if($correcto){
		return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>'El fichero es válido y se subió con éxito.'));
	}else{
		return	$app['twig']->render( '/admin/mapas.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo, 'appAlert'=>'1', 'appAlertMensaje'=>$mensaje));
	}

}


/**
	 * Funcion devuelve el html de los poblados
	 * @param $app, $model
	 * @return json, status=1 exitoso
	 * @access public
	**/	
	public function subirUnArchivoCompleto($app,$model,$pais,$archivo,$tipo,$subida){

		if($tipo=="ag"){
			$tipoCobertura=7;
			$cobertura="Agencia";
			$mapa="Agencias";
		}else if($tipo=="2g"){
			$tipoCobertura=1;
			$cobertura="2G";
			$mapa="Movil";
		}else if($tipo=="3g"){
			$tipoCobertura=2;
			$cobertura="3G";
			$mapa="Movil";
		}else if($tipo=="4g"){
			$tipoCobertura=3;
			$cobertura="4G";
			$mapa="Movil";
		}else if($tipo=="v2g"){
			$tipoCobertura=4;
			$cobertura="2G";
			$mapa="Internet";
		}else if($tipo=="v3g"){
			$tipoCobertura=5;
			$cobertura="3G";
			$mapa="Internet";
		}else if($tipo=="v4g"){
			$tipoCobertura=6;
			$cobertura="4G";
			$mapa="Internet";
		}
	
		$departamento1=0;
		$departamento="Pais";

	    $hoy = date("Y-m-d H:i:s");
		$timestamp = strtotime($hoy);
        //obtenemos la extension del archivo
		$extension = $this->extension($archivo['name']);
        //obtenemos la ruta segun el pais
		$rutaBase="/home/app";
		if($pais==1){
		      $directorio=$rutaBase.DIR_KMZGT;
		}else if($pais==2){
			  $directorio=$rutaBase.DIR_KMZSV;
		}else if($pais==3){
			  $directorio=$rutaBase.DIR_KMZHN;
		}else if($pais==4){
			 $directorio=$rutaBase.DIR_KMZNI;
		}else if($pais==5){
			 $directorio=$rutaBase.DIR_KMZCR;
		}


	     //obtenemos nombre y ruta de la carpeta
		$directorio_completo=$directorio.$mapa."/".$cobertura."/".$departamento;
			/*var_dump($directorio_completo);
			die;*/
		//creamos la carpeta en caso de que no exista
		if(!$this->findFile($directorio.$mapa."/".$cobertura,$departamento)){
			if(mkdir($directorio_completo)){
				chmod($directorio_completo, 0777);
			}else{
				//retornamos error al crear la carpeta
				return "permisos";
			}
			
		}
		

		//obtenemos el tamaño 
		if($extension=="kml"){
				$size=$_FILES["ag"]["size"];
		}else if($extension=="zip"){
				$file=$_FILES["ag"]["tmp_name"];
				$size=$this->get_zip_originalsize($file);
		}else if($extension=="kmz"){
			$retorno=$this->subirArchivoKMZ($app,$model,$archivo,$pais,$departamento,$departamento1,$tipoCobertura,$directorio_completo,$subida);
			if($retorno=="error"){
				return "error";
			}else{
				return "true";
			}
		}
		else{
			//retornamos error de formato
			return "formato";
		}

		if($size>2621440){
				//retornamos archivo muy grande	
			return "tamaño";
		}

       

		//buscamos en la db la ruta y si no existe la creamos
		  $existRuta=$model['admin']->findRuta($app,$pais,$departamento1,$tipoCobertura,2);
		   if(empty($existRuta)){
		   		//insertamos en DB la nueva ruta
		   	     $ruta=$model['admin']->insertRuta($app,$pais,$departamento,$departamento1,$tipoCobertura,2);
		   }else{
		   		$ruta=$existRuta[0]['id'];
		   }
		

		///buscar archivo para este tipo de mapa y eliminarlo fisicamente y en db
		  $existeArchivo=$model['admin']->findArchivo($app,$pais,$departamento1,$tipoCobertura,2);
	          if(!empty($existeArchivo)){
				$redirect = $model['admin']->deleteMapa($app,$existeArchivo[0]["layer_id"],$pais);
				$this->eliminarFile($directorio_completo,$existeArchivo[0]["layer_type"]);
		  }
			
		//si es kml solo lu subimos
		if($extension=="kml"){
			$dir_subida = $directorio_completo."/";
			$fichero_subido = $dir_subida . basename($archivo['name']);

			if(!$this->findFile($directorio_completo, basename($archivo['name']))){
				if (move_uploaded_file($archivo['tmp_name'], $fichero_subido)) {
				    chmod($directorio_completo."/".basename($archivo['name']), 0777);
				    //guardamos en la DB datos del archivo
				    $ingresoArchivo=$model['admin']->insertFile($app,$pais,basename($archivo['name']),$size,$timestamp,$ruta,"",1);
				    return "true";
				} else {
				    return	"error";
				}
			}else{
					//mostrar error de archivo ya existe.
				     return	"repetido";
			}
					
		}else if($extension=="zip"){
			   	$total="";
				$files = @zip_open($file);
				if ($files) {
					$total=0;
					$isKml=0;
				  while ($cursor =@zip_read($files)) {
				  	   $total=$total+1;
				  	   $name=@zip_entry_name($cursor);
				  	   $nombre=$name;
				  	   $file_names[]=$name;
				       if(!is_dir($name)){ 
				        if(!strpos($name,".kml"))
				          {
				          	    $isKml=$isKml+1;
			 	          }
				        } 
				  }

				  if($isKml==0 && $total==1){

				  	if(!$this->findFile($directorio_completo, $nombre)){

				  		$zip = new ZipArchive;
								$res = $zip->open($file);
								if ($res === TRUE) {
								    $zip->extractTo($directorio_completo);
								    $zip->close();
								    chmod($directorio_completo."/".$nombre, 0777);
								    //guardamos en la DB
								      $ingresoArchivo=$model['admin']->insertFile($app,$pais,$nombre,$size,$timestamp,$ruta,"",1);
								    return "true";
								} else {
								    return	"descomprimir";
								} 
						
					}else{
							//mostrar error de archivo ya existe.
						    return	"repetido";
					}

				  		
				  }else{
				  	//error de tamaño y de formato
				  	return	"nokml";
				  }

				   @zip_close($files);
				 }
		}else{
			return	"extension";
		}

	}

	public function subirArchivoKMZ($app,$model,$archivo,$pais,$departamento,$departamento1,$tipoCobertura,$completo,$subida=0){

		
		$msg_alert = "";

		$temporal = $archivo['tmp_name'];
		if (!@is_uploaded_file($temporal)) {
				$msg_alert ='error';
		}

		if ($archivo['error']) {
				$msg_alert = 'error';
		 }
		      
		 $size_allow = intval(ini_get('upload_max_filesize')) * 1048576;
		 $size_curre = $archivo['size']; 
		   if ($size_curre > $size_allow) {
				$msg_alert = "error";
		   }

	try {  

		 if (!@file_exists($temporal)) {
				$msg_alert = "error";
		  }
				
		 if (!@is_readable($temporal)) {
				$msg_alert= "error";
				
		  }  

		  @chmod($temporal, 0777);
				$content = @file_get_contents($temporal);
				if (empty($content)) {
					$msg_alert = 'error';
				}
				
			$map_size = $archivo['size'];
				//mandamos al modelo para que inserte en db	
				//$insert=$model['admin']->insertArchivo($map_v,$map_size,$content,$pais,$app);
				//buscamos en la db la ruta y si no existe la creamos
			  $existRuta=$model['admin']->findRuta($app,$pais,$departamento1,$tipoCobertura,2);

			$ruta="";
			   if(empty($existRuta)){
			   		//insertamos en DB la nueva ruta
			   	     $ruta=$model['admin']->insertRuta($app,$pais,$departamento,$departamento1,$tipoCobertura,2);
			   }else{
			   	    $ruta=$existRuta[0]['id'];
			   }

                           	 ///buscar archivo para este tipo de mapa y eliminarlo fisicamente y en db
			  $existeArchivo=$model['admin']->findArchivo2($app,$pais,$departamento1,$tipoCobertura,2);
		          if(!empty($existeArchivo)){
					$redirect = $model['admin']->deleteMapa($app,$existeArchivo[0]["layer_id"],$pais);
					$this->eliminarFile($completo,$existeArchivo[0]["layer_type"]);
			  }                 
				
			    $hoy = date("Y-m-d H:i:s");
				$timestamp = strtotime($hoy);
			/*	$insert=$model['admin']->insertFile($app,$pais,basename($archivo['name']),$map_size,$timestamp,$ruta,$content,2);

				if($insert!=1){
					$msg_alert = "error";
				 }else{
				 	$msg_alert = "true";
				 }*/

				  	$dir_subida = $completo."/";
					$fichero_subido = $dir_subida . basename($archivo['name']);
						if(!$this->findFile($completo, basename($archivo['name']))){
							if (move_uploaded_file($archivo['tmp_name'], $fichero_subido)) {
							    chmod($completo."/".basename($archivo['name']), 0777);
							    //guardamos en la DB datos del archivo
							    if($subida==0){
							    		$insert=$model['admin']->insertFile($app,$pais,basename($archivo['name']),$map_size,$timestamp,$ruta,"null",3);	
							    }else{
							    		$insert=$model['admin']->insertFile($app,$pais,basename($archivo['name']),$map_size,$timestamp,$ruta,"null",2);	
							    }
							    

							    if($insert!=1){
										$msg_alert = "error";
									 }else{

									 	 $msg_alert = "true";
									 }

							   
							} else {
							    $msg_alert = "error";
							}
						}else{
							$msg_alert = "error";
						}


		} catch(Exception $e) {
                 $msg_alert = "error";
		}	
			return $msg_alert;
}

	/**

	*/
	public function home($app, $model){
		$redirect=$this->validateSessionActive();
		if ( !$redirect->redirect ){
			return $app['twig'] -> render('/admin/layout.view.twig');
		}else {
			$menu=$model['admin']->menuTop($app);
			$cuerpo=$model['admin']->formularioRegiones($app);
		    return	$app['twig']->render( '/admin/textos.twig', array( 'TITLE_PAGE' => "Administrador",'menu'=>$menu, 'usuarios'=>$cuerpo));
	    }
	}

	/**

*/
}
