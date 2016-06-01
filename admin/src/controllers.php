<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#Home
/**
* Funcion principal para recoleccion de datos de facebook
*
* @var array, instancia de la app
* @var object, instancia BUSI
* @var object, facebook
* @var object, instancia directa para consumo de variables del archivo .INI
* @var mix resto de instancias a utilizar
* @return null
*/
$app->match('/', function () use ($app, $busi, $analytics, $models,$prefix) {
	//$app['twig']->addGlobal('layout', $template.'/layout.twig');
  return 'inicio';

})->method('GET|POST');


//Se obtienen terminos y condiciones
$app->match('/terminos', function () use ($app,$models,$prefix) {
  
  $terms=$models->_getTermsAndConditions();
  $terminos=htmlspecialchars_decode($terms);
  return new Response(
						$app['twig']->render( 'terminos.twig', array( 'TITLE_PAGE' => "Terminos y Condiciones","terminos" => $terminos) )
					);

})->method('GET|POST');


