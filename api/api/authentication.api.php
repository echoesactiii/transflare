<?php

namespace TransFlare;

use Tonic\Resource,
    Tonic\Response,
    Tonic\ConditionException,
    \R, \JWT;

/**
 * @uri /authentication
 */
class Authentication extends Resource {
	/**    
	* @method OPTIONS
	*/
	function options(){
	    return new Response(200);
	}

	/**
	 * @method GET
	 * ! Authenticated
	 * Purpose: Return session data associated with token.
	 */
	function get(){
		$response = new Response();
		$response->contentType = "application/json";

		try{
			$user = $this->fromToken($this->request->xToken);
		
			$response->code = 200;
			$response->body = json_encode($user);
			return $response;
		}catch(AuthenticationFailedException $e){
			$response->code = 401;
			$response->body = json_encode(array("error" => "Token was invalid or could not be verified: ".$e->getMessage()));
			return $response;
		}
	}

	/**
	 * @method POST
	 * Purpose: Authenticate user & return session token.
	 */
	function post(){
		global $config;
		$response = new Response();
		$response->contentType = "application/json";

		try{
			if($this->request->data->username && $this->request->data->password){
				$user = R::findOne("user", "username = :username AND password = :password", array(":username" => $this->request->data->username, ":password" => sha1($this->request->data->password)));
				if(!$user->id){
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
				$response->body = json_encode(array("token" => $authToken->encode($token->export(), $config['security']['encrypt_key'])));
				$response->code = 200;
				return $response;
			}else{
				throw new IncompleteRequestException("You must supply a username and password to login.");
			}
		}catch(AuthenticationFailedException $e){
			$response->code = 401;
			$response->body = json_encode(array("error" => $e->getMessage()));
			return $response;
		}catch(IncompleteRequestException $e){
			$response->code = 400;
			$response->body = json_encode(array("error" => $e->getMessage()));
			return $response;
		}
	}

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
}

/**
 * @uri /register
 */
class Register extends Resource {
	/**    
	* @method OPTIONS
	*/
	function options(){
	    return new Response(200);
	}

	/**
	 * @method POST
	 * Purpose: Register a user & return session token.
	 */
	function post(){
		global $config;
		$response = new Response();
		$response->contentType = "application/json";

		try{
			if(!$this->request->data->username || $this->request->data->email || $this->request->data->password){
				throw new IncompleteRequestException("You must supply a username, password and email address to register.");
			}

			$user = R::findOne("user", "username = :username", array(":username" => $this->request->data->username));
			if($user->id){
				throw new UnableToComplyException("That username is already taken. Please choose another username.");
			}

			$user = R::findOne("user", "email = :email", array(":email" => $this->request->data->email));
			if($user->id){
				throw new UnableToComplyException("That email address is already registered. Please login instead.");
			}

			unset($user);

			$user = R::dispense("user");
			$user->username = $this->request->data->username;
			$user->password = sha1($this->request->data->password);
			$user->email = $this->request->data->password;
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
			$response->body = json_encode(array("token" => $authToken->encode($token->export(), $config['security']['encrypt_key'])));
			$response->code = 200;
			return $response;			
		}catch(UnableToComplyException $e){
			$response->code = 403;
			$response->body = json_encode(array("error" => $e->getMessage()));
			return $response;
		}catch(IncompleteRequestException $e){
			$response->code = 400;
			$response->body = json_encode(array("error" => $e->getMessage()));
			
}