<?php
namespace Digitalis\Core\Middlewares;

use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DbAdapters\LogBranchBoxDbAdapter;
use Digitalis\Core\Models\Entities\LogBranchBox;
use Digitalis\Core\Models\MailWorker;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * BranchBoxLogMiddleware Middleware pour logger les actions sur les agences et caisses
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class BranchBoxLogMiddleware
{
	const BRANCH = "BRANCH";
	const BOX = "BOX";
	/**
	 * Conteneur
	 *
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * Routeur de l'application
	 *
	 * @var \Slim\Route
	 */
	private $route;

	private static $actionToLog = [
		'branch.update' => "Initiation de la mise à joure de l'agence",
		'branch.pupdate' => "Validation de la mise à jour de l'agence",
		'branch.details' => "Affichage des détails de l'agence",
		'branch.openclose' => "Initiation d'une action d'ouverture/fermeture de l'agence",
		'branch.popenclose' => "Validation de l'ouverture/fermeture de l'agence.",
		'branch.keygen' => "Modification de la clé de l'agence",
		'box.update' => "Initiation de la mise à joure de la caisse",
		'box.pupdate' => "Validation de la mise à jour de la caisse",
		'box.details' => "Affichage des détails de la caisse",
		'box.openclose' => "Initiation de l'action d'ouverture/fermeture de la caisse",
		'box.popenclose' => "Validation de l'ouverture/fermeture de la caisse.",
		'box.keygen' => "Modification de la clé de la caisse",
		'api.boxOpenClose' => 'Initiation de l\'ouverture/fermeture de la caisse par le front : ',
		'api.branchOpenClose' => 'Initiation de l\'ouverture/fermeture de l\'agence par le front : '
	];

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function __invoke(Request $request, Response $response, $next)
	{
		$nrepose =	null;
		$currentroute = SysConst::HOME;
		$loggedUser = SessionManager::getLoggedUser();
		$login = !is_null($loggedUser) ? $loggedUser->getLogin() : null;
		InputValidator::InitSlimRequest($request);
		$login = is_null($login) ?  base64_decode(InputValidator::getString('login')) : $login;
		$status = InputValidator::getInt('status');

		$route = $request->getAttribute('route');
		if (!empty($route)) {
			$currentroute = $route->getName();
			$this->route = $route;
		}

		if (isset(self::$actionToLog[$currentroute])) {
			$nrepose = $next($request, $response);

			$action = self::$actionToLog[$currentroute];
			$code = "no-code";
			$logaction = new LogBranchBox();
			$logaction->setComponent(strtoupper(explode('.', $currentroute)[0]));

			switch ($currentroute) {
				case 'branch.update':
				case 'branch.details':
				case 'box.update':
				case 'box.details':
				case 'branch.openclose':
				case 'box.openclose':

					$code = base64_decode($this->route->getArgument('code'));
					$caction = strtoupper(base64_decode($this->route->getArgument('action')));
					$action .= $caction ? ', ' . $caction : null;
					break;
				case 'branch.keygen':
				case 'box.keygen':
					$code = base64_decode(InputValidator::getString('code'));
					break;
				case 'branch.pupdate':
				case 'branch.popenclose':
				case 'box.pupdate':
				case 'box.popenclose':
					$code = InputValidator::getString('code');
					$caction = strtoupper(InputValidator::getString('action'));
					$action .= $caction ? ' : ' . $caction : null;
					break;
				case 'api.boxOpenClose':
					$code = base64_decode($this->route->getArgument('boxCode'));
					$action .= $status ? 'open' : 'close';
					break;
				case 'api.branchOpenClose':
					$code = base64_decode($this->route->getArgument('branchCode'));
					$action .= $status ? 'open' : 'close';
					break;
				default:
					break;
			}

			$logaction->setCode($code);
			$logaction->setAction($action);
			$logaction->setLocation(MailWorker::getIpAddress());

			$logaction->setUserAction($login);
			LogBranchBoxDbAdapter::save($logaction);
		}

		return $nrepose ? $nrepose : $next($request, $response);
	}
}
