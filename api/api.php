<?php

require 'include/RedBean/rb.php'; // include RedBean SQL libs
require 'include/slim/Slim/Slim.php'; // include Slim REST framework
require 'include/jwt/JWT.php'; // include JWT framework
require 'exceptions.php'; // include namespace exceptions
require 'config.inc.php'; // include setttings & configuration file

R::setup('mysql:host='.$config['sql']['server'].';dbname='.$config['sql']['database'], $config['sql']['username'], $config['sql']['password']);

\Slim\Slim::registerAutoloader();
$app = new Slim\Slim();

// FIXME: If request body is empty/not JSON, an Exception will be thrown when property_exists() is called.

include("api/authentication.api.php");

$app->run();

?>