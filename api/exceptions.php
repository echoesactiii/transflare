<?php

namespace TransFlare;

// AuthenticationFailedException: A login, token or other key was invalid.
class AuthenticationFailedException extends \Exception {};
// UnableToComplyException: Couldn't honour the request being made.
class UnableToComplyException extends \Exception {};
// IncompleteRequestException: A required parameter was not submitted.
class IncompleteRequestException extends \Exception{};

?>