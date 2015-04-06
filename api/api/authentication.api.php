<?php

// POST to /register to register a user.
$app->post('/register', function() use ($app){
	global $config;

	try{
		$requestData = json_decode($app->request->getBody());

		if(!property_exists($requestData, 'username') || !property_exists($requestData, 'email') || !property_exists($requestData, 'password')){
			throw new IncompleteRequestException("You must supply a username, password and email address to register.");
		}

		$user = R::findOne("user", "username = :username", array(":username" => $requestData->username));
		if($user){
			throw new UnableToComplyException("That username is already taken. Please choose another username.");
		}

		$user = R::findOne("user", "email = :email", array(":email" => $requestData->email));
		if($user){
			throw new UnableToComplyException("That email address is already registered. Please login instead.");
		}

		unset($user);

		$user = R::dispense("user");
		$user->username = $requestData->username;
		$user->password = sha1($requestData->password);
		$user->email = $requestData->email;
		$user->isHelper = false;
		$uid = R::store($user);

		$user = R::load("user", $uid);

		$token = R::dispense('session');

		$token->iat = time();
		$token->jti = uniqid(php_uname('n')."/", true);
		$token->destroyed = false;

		$token->sub = $user->username;
		$token->uid = $user->id;
		$token->email = $user->email;

		R::store($token);

		$authToken = new JWT();
		echo json_encode(array("token" => $authToken->encode($token->export(), $config['security']['encrypt_key'])));		
	}catch(UnableToComplyException $e){
		$app->halt(403, json_encode(array("error" => $e->getMessage())));
	}catch(IncompleteRequestException $e){
		$app->halt(400, json_encode(array("error" => $e->getMessage())));
	}catch(Exception $e){
		$app->halt(500, json_encode(array("error" => $e->getMessage(), "trace" => $e->getTrace())));
	}
});

// GET to /authentcation to verify a token.
$app->get("/authentication", function () use ($app){
	global $config;

	try{
		$user = fromToken($app->request->headers->get('X-Token'));
	
		echo json_encode($user);
	}catch(AuthenticationFailedException $e){
		$app->halt(401, json_encode(array("error" => "Token was invalid or could not be verified: ".$e->getMessage())));
	}catch(Exception $e){
		$app->halt(500, json_encode(array("error" => $e->getMessage(), "trace" => $e->getTrace())));
	}
});

// POST to /autentication to login & get a token.
$app->post("/authentication", function () use ($app){
	global $config;

	try{
		$requestData = json_decode($app->request->getBody());

		if(property_exists($requestData, 'username') && property_exists($requestData, 'password')){
			$user = R::findOne("user", "username = :username AND password = :password", array(":username" => $requestData->username, ":password" => sha1($requestData->password)));
			if(!$user){
				throw new AuthenticationFailedException("Invalid username & password.");
			}

			$token = R::dispense('session');

			$token->iat = time();
			$token->jti = uniqid(php_uname('n')."/", true);
			$token->destroyed = false;

			$token->sub = $user->username;
			$token->uid = $user->id;
			$token->email = $user->email;

			R::store($token);

			$authToken = new JWT();
			echo json_encode(array("token" => $authToken->encode($token->export(), $config['security']['encrypt_key'])));
		}else{
			throw new IncompleteRequestException("You must supply a username and password to login.");
		}
	}catch(AuthenticationFailedException $e){
		$app->halt(401, json_encode(array("error" => $e->getMessage())));
	}catch(IncompleteRequestException $e){
		$app->halt(400, json_encode(array("error" => $e->getMessage())));
	}catch(Exception $e){
		$app->halt(500, json_encode(array("error" => $e->getMessage(), "trace" => $e->getTrace())));
	}
});

// ** This is not an API endpoint, this is used internally by other API endpoints. ** //
/* Purpose: Validate the authenticity of a token. */
function fromToken($authToken){
	global $config;

	if(!$authToken){ throw new AuthenticationFailedException(); }

	$JWT = new JWT();
	try{
		$token = $JWT->decode($authToken, $config['security']['encrypt_key']);
	}catch(\Exception $e){
		throw new AuthenticationFailedException("Your session has expired. Please login again.");
	}

	$dbToken = R::findOne('session', 'jti = :jti', array(':jti' => $token->jti));

	if($dbToken->destroyed == false && $dbToken->iat < time()){
		return($token);
	}else{
		throw new AuthenticationFailedException("Your session has expired. Please login again.");
	}
}