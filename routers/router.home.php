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


$app -> get('/{module}/{task}', function($module,$task) use ($app, $models,$ctrls, $prefix) {
	$ctrlname = "Controller" . ucfirst(strtolower($module));
	$ctrl = new $ctrlname();
	return $ctrl->$task($app,$models);

})
->bind("route")
->method('GET|POST')
->value('module', 'home')
->value('task', 'buildHomeSection');

$app -> get('/{module}/{task}', function($module,$task) use ($app, $models,$ctrls, $prefix) {
	$ctrlname = "Controller" . ucfirst(strtolower($module));
	$ctrl = new $ctrlname();
	return $ctrl->$task($app,$models);

})
->bind("route")
->method('GET|POST')
->value('module', 'contacto')
->value('task', 'viewContacto');