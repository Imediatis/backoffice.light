<?php
namespace Digitalis\Core\Middlewares;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;

/**
 * RouteCaseInsensitiveMiddleware Ce middleware permet de générer la casse des différentes urls
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class RouteCaseInsensitiveMiddleware
{
	/**
	 * Conteneur du code
	 *
	 * @var \Slim\Container
	 */
	private $container;

	/**
	 * Routeur de l'application
	 *
	 * @var \Slim\Router
	 */
	private $router;

	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->router = $container->get('router');
	}



	public function __invoke(Request $request, Response $response, $next)
	{
		$uri = $request->getUri();
		$currentPattern = strtolower($uri->getPath());

		foreach ($this->router->getRoutes() as $key => $route) {
			if (strtolower($route->getPattern()) == $currentPattern) {
				$uri = $request->getUri()->withPath($route->getPattern());
				return $next($request->withUri($uri), $response);
			}
		}
		return $next($request, $response);
	}

}