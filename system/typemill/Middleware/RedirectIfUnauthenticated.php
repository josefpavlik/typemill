<?php

namespace Typemill\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Slim\Routing\RouteParser;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Typemill\Models\User;

class RedirectIfUnauthenticated implements MiddlewareInterface
{			
	public function __construct(RouteParser $router)
	{
		$this->router 	= $router;
	}

	public function process(Request $request, RequestHandler $handler) :response
	{
		$authenticated = ( 
				(isset($_SESSION['username'])) && 
				(isset($_SESSION['login']))
			)
			? true : false;

		if($authenticated)
		{
			# here we have to load userdata and pass them through request or response
			$user = new User();

			if($user->setUser($_SESSION['username']))
			{
				$userdata = $user->getUserData();

				$request = $request->withAttribute('username', $userdata['username']);
				$request = $request->withAttribute('userrole', $userdata['userrole']);

			    # this executes code from routes first and then executes middleware
				$response = $handler->handle($request);

				return $response;
			}			
		}

		# this executes only middleware code and not code from route
		$response = new Response();
		
		return $response->withHeader('Location', $this->router->urlFor('auth.show'))->withStatus(302);
	}
}