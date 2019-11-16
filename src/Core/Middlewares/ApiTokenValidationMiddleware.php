<?php
namespace Digitalis\Core\Middlewares;

use Digitalis\Core\Models\ApiResponse;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Slim\Container;
use Slim\Http\Body;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * ApiTokenValidationMiddleware Middleware pour la gestion des token des application qui se connecte au système
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class ApiTokenValidationMiddleware
{
	/**
	 * Conteneur
	 *
	 * @var Slim\Container
	 */
	private $container;


	/**
	 * client
	 *
	 * @var \Digitalis\Core\Models\Reseller
	 */
	private $reseller;

	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->reseller = $container->reseller;
	}

	public function __invoke(Request $request, Response $response, $next)
	{
		if ($request->hasHeader('HTTP_AUTH_KEY')) {
			$sauth = trim($request->getHeader('HTTP_AUTH_KEY')[0]);
			if (InputValidator::isValideBasicAuth($sauth)) {
				$tauth = explode(' ', $sauth);
				if (isset($tauth[1]) && strcmp($tauth[1], $this->reseller->getApiToken()) == 0) {
					return $next($request, $response);
				}
			}
		}

		$output = new ApiResponse();
		$output->status = false;
		$output->message = 'Invalid request token';
		$body = new Body(fopen('php://temp', 'r+'));
		$body->write(json_encode($output), JSON_PRETTY_PRINT);
		return $response->withStatus(401, 'Unauthorized action')
			->withHeader('Content-Type', 'applicaiton/json')
			->withBody($body);
	}
}
