<?php
// USES

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use \Doctrine\Common\Cache\ApcCache;
use \Doctrine\Common\Cache\ArrayCache;
use Silex\Provider\FormServiceProvider;
use Doctrine\DBAL\Connections;



//INDEX
define('DS', DIRECTORY_SEPARATOR);
define('DIR_MODELS', dirname(__DIR__) . DS . 'models' . DS);
define('DIR_CTRLS', dirname(__DIR__) . DS . 'controllers' . DS);
define('DIR_ROUTES', dirname(__DIR__) . DS . 'routers' . DS);
define('DIR_LIBS', dirname(__DIR__) . DS . 'libs' . DS);


//BOOTSTRAP
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/clib.php';

require_once DIR_LIBS .DS . 'model.master.php' ;
require_once DIR_MODELS . 'model.home.php';
require_once DIR_MODELS . 'model.admin.php';
require_once DIR_CTRLS .DS . 'controller.home.php' ;
require_once DIR_CTRLS . 'controller.contacto.php' ;
require_once DIR_CTRLS .DS . 'controller.admin.php' ;


$app = new Silex\Application();
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->before(function ($request) {
       $request->getSession()->start();
});
$confp      = new Lib('conn');
$prefix     =$confp->_v('PREFIX');

//definimos las rutas de acceso a los archivos para CENAM
define('DIR_KMZGT', $confp->_v('RUTAGT') . DS . 'web' . DS . 'mapas'. DS);
define('DIR_KMZSV', $confp->_v('RUTASV') . DS . 'web' . DS . 'mapas'. DS);
define('DIR_KMZHN', $confp->_v('RUTAHN') . DS . 'web' . DS . 'mapas'. DS);
define('DIR_KMZNI', $confp->_v('RUTANI') . DS . 'web' . DS . 'mapas'. DS);
define('DIR_KMZCR', $confp->_v('RUTACR') . DS . 'web' . DS . 'mapas'. DS);

//Registro de la base de datos
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'dbs.options'  => array(
      'mysql_silex' =>array(
          'driver'        => $confp->_v('DRIVER'),
          'host'          => $confp->_v('HOST'),
          'dbname'        => $confp->_v('DBNAME'),
          'user'          => $confp->_v('USER'),
          'password'      => $confp->_v('PASSWORD'),
          'charset'       => $confp->_v('CHARSET'),
          'driverOptions' => array(1002 => 'SET NAMES utf8'),
      ),
      'mysql_silex1' =>array(
          'driver'        => $confp->_v('DRIVER'),
          'host'          => $confp->_v('HOST'),
          'dbname'        => $confp->_v('DBNAMESV'),
          'user'          => $confp->_v('USERSV'),
          'password'      => $confp->_v('PASSWORDSV'),
          'charset'       => $confp->_v('CHARSET'),
          'driverOptions' => array(1002 => 'SET NAMES utf8'),
      ),
      'mysql_silex2' =>array(
          'driver'        => $confp->_v('DRIVER'),
          'host'          => $confp->_v('HOST'),
          'dbname'        => $confp->_v('DBNAMEHN'),
          'user'          => $confp->_v('USERHN'),
          'password'      => $confp->_v('PASSWORDHN'),
          'charset'       => $confp->_v('CHARSET'),
          'driverOptions' => array(1002 => 'SET NAMES utf8'),
      ),
      'mysql_silex3' =>array(
          'driver'        => $confp->_v('DRIVER'),
          'host'          => $confp->_v('HOST'),
          'dbname'        => $confp->_v('DBNAMENI'),
          'user'          => $confp->_v('USERNI'),
          'password'      => $confp->_v('PASSWORDNI'),
          'charset'       => $confp->_v('CHARSET'),
          'driverOptions' => array(1002 => 'SET NAMES utf8'),
      ),
      'mysql_silex4' =>array(
          'driver'        => $confp->_v('DRIVER'),
          'host'          => $confp->_v('HOST'),
          'dbname'        => $confp->_v('DBNAMECR'),
          'user'          => $confp->_v('USERCR'),
          'password'      => $confp->_v('PASSWORDCR'),
          'charset'       => $confp->_v('CHARSET'),
          'driverOptions' => array(1002 => 'SET NAMES utf8'),
      ),
   ),   
));
