<?php

/*$app->options('/api', function(){
	echo json_encode(array("success" => true));
});*/

$app->post('/register', function() use ($app){
	global $config;

	try{
		$requestData = json_decode($app->request->getBody());

		if(!property_exists($requestData, 'username') || !property_exists($requestData, 'email') || !property_exists($requestData, 'password')){
			throw new IncompleteRequestException("You must supply a username, password and email address to register.");
		}

		$user = R::findOne("user", "username = :username", array(":username" => $requestData->username));
		if($user->id){
			throw new UnableToComplyException("That username is already taken. Please choose another username.");
		}

		$user = R::findOne("user", "email = :email", array(":email" => $requestData->email));
		if($user->id){
			throw new UnableToComplyException("That email address is already registered. Please login instead.");
		}

		unset($user);

		$user = R::dispense("user");
		$user->username = $requestData->username;
		$user->password = sha1($requestData->password);
		$user->email = $requestData->password;
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
		$app->halt(500, json_encode(array("error" => $e->getMessage())));
	}
});