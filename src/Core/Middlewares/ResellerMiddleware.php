<?php
namespace Digitalis\Core\Middlewares;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Digitalis\Core\Models\Reseller;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\EnvironmentManager;

/**
 * ResellerMiddleware Getionnaier pour le contrôle des différents clients
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class ResellerMiddleware
{
	/**
	 *
	 * @var \Slim\Container
	 */
	private $container;

	/**
	 * Environnemen Twig
	 *
	 * @var \Twig_Environment
	 */
	private $twig;

	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->twig = $this->container->view->getEnvironment();
	}

	public function __invoke(Request $request, Response $response, $next)
	{
		$sreseller =  SessionManager::getReseller();
		$reseller = !is_null($sreseller) ? $sreseller : new Reseller(EnvironmentManager::getResellerFile());
		if (!$reseller->currentExist) {
			return $response->withRedirect("http://imediatis.net", 301);
		}
		SessionManager::set(SysConst::S_RESELLER, serialize($reseller));
		$this->twig->addGlobal(SysConst::T_RESELLER, $reseller->forTwig());

		return $next($request, $response);
	}

}