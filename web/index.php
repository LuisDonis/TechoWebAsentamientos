<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use \Doctrine\Common\Cache\ApcCache;
use \Doctrine\Common\Cache\ArrayCache;
use Silex\Provider\FormServiceProvider;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR .  'config' . DIRECTORY_SEPARATOR . "bootstrap.php" ;


$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views/',
));

$app->register(new FormServiceProvider());
Request::enableHttpMethodParameterOverride();
$models = Array();
$ctrls = Array();

//$models['cupon'] = new ModelCupon();
$models['admin'] = new ModelAdmin($prefix);
$models['home'] = new ModelHome($prefix);
//$ctrls['home'] = new ControllerHome();


$app['debug'] = true;


require_once DIR_ROUTES .DS . 'router.home.php';

$app->run();



