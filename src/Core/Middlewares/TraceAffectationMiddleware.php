<?php
namespace Digitalis\Core\Middlewares;

use Digitalis\Core\Models\DbAdapters\CaisseDbAdapter;
use Digitalis\Core\Models\DbAdapters\OperatorDbAdapter;
use Digitalis\Core\Models\DbAdapters\TraceAffectationDbAdapter;
use Digitalis\Core\Models\Entities\Operator;
use Digitalis\Core\Models\Entities\TraceAffectation;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * TraceAffectationMiddleware Gestionnaire des mouvements des opérateurs dans les caisses
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class TraceAffectationMiddleware
{
	/**
	* Conteneur
	*
	* @var Slim\Container
	*/
	private $container;

	public function __construct($container)
	{
		$this->container = $container;
	}

	public function __invoke(Request $request, Response $response, $next)
	{
		$idCaisse = InputValidator::getInt('idCaisse');
		$idOperateur = InputValidator::getInt('idOperator');

		$operator = OperatorDbAdapter::getById($idOperateur);

		$beforCaisse = CaisseDbAdapter::getById($idCaisse);

		if ($beforCaisse && $operator) {
			//
			//Remplacement de l'opérateur d'une caisse
			//
			$optoremove = $beforCaisse->getOperator();
			if ($optoremove && $operator->getLogin() != $optoremove->getLogin()) {
				$laTrace = TraceAffectationDbAdapter::getStartedTrace($optoremove->getLogin(), $beforCaisse->getCode());
				if ($laTrace) {
					TraceAffectationDbAdapter::update($laTrace);
				}
			}
			//
			//L'Opératuer change de caisse
			//
			$oldcaisse = $operator->getCaisse();
			if ($oldcaisse && $oldcaisse->getCode() != $beforCaisse->getCode()) {
				$laTrace = TraceAffectationDbAdapter::getStartedTrace($operator->getLogin(), $oldcaisse->getCode());
				if ($laTrace) {
					TraceAffectationDbAdapter::update($laTrace);
				}
			}
		}

		$nreponse = $next($request, $response);

		$afterCaisse = CaisseDbAdapter::getById($idCaisse);
		$startedTrace = TraceAffectationDbAdapter::getStartedTrace($operator->getLogin(), $afterCaisse->getCode());

		if (!is_null($afterCaisse) && !is_null($operator) && is_null($startedTrace)) {
			TraceAffectationDbAdapter::save(new TraceAffectation($operator->getLogin(), $afterCaisse->getCode()));
		}

		return $nreponse;
	}
}
