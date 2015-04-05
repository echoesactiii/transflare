<?php

require 'include/RedBean/rb.php'; // include RedBean SQL libs
require 'include/tonic/web/dispatch.php'; // include Tonic REST framework
require 'include/jwt/JWT.php'; // include JWT framework
require 'exceptions.php'; // include namespace exceptions
require 'config.inc.php'; // include setttings & configuration file

class R extends RedBean_Facade {}

R::setup('mysql:host='.$config['sql']['server'].';dbname='.$config['sql']['database'], $config['sql']['username'], $config['sql']['password']);

$app = new Tonic\Application(array(
	"mount" => array(
		"TransFlare" => $config['web']['base_url']
	),
	"load" => 'api/*.api.php'
));

$request = new Tonic\Request();

if($request->contentType == 'application/json'){
	$request->data = json_decode($request->data);
}

$resource = $app->getResource($request);
$response = $resource->exec();

// CORS
$response->accessControlAllowOrigin = "*";
$response->accessControlAllowMethods = "POST, OPTIONS, GET, PUT, DELETE";
$response->accessControlAllowHeaders = "X-Token, Content-Type, Content-Length";

$response->output();

?>