<?php
namespace Digitalis\Core\Controllers;

use Slim\Container;
use Digitalis\Core\Models\DataBase\DBase;
use Slim\Http\Request;
use Digitalis\Core\Models\SysConst;
use Slim\Http\Body;
use Digitalis\Core\Models\SessionManager;
use Slim\Http\Response;

//TODO: Implémenter le content négociation pour rendre ce contrôleur full REST. Pour l'instant toutes les réponse sont retourné en JSON
/**
 * ApiController Controleur de base pour les api
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class ApiController
{
	/**
	 *
	 * @var EntityManager
	 */
	protected $db;

	/**
	 * controlleur actuel
	 *
	 * @var string
	 */
	protected $curentController;

	/**
	 * Reseller courant du système
	 *
	 * @var \Digitalis\Core\Models\Reseller
	 */
	private $reseller;

	/**
	 *
	 * @var Container
	 */
	protected $container;

	function __construct(Container $container)
	{
		$this->db = DBase::getEntityManager();
		$this->container = $container;
		$this->reseller = $container->reseller;
	}

	public function __get($name)
	{
		return $this->container->get($name);
	}

	protected function getBaseUrl(Request $request)
	{
		return $request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . (!is_null($request->getUri()->getPort()) ? ':' . $request->getUri()->getPort() : '') . '/';
	}

	/**
	 * Permet de retourner le résultat de traitement en JSON
	 *
	 * @param Response $response
	 * @param mixed $data Tableau/Objet(implémentant jsonserialize) de donnée à convertir en JSON à l'aide de la methode json_encode
	 * @return Response
	 */
	protected function render(Response $response, $data)
	{
		$body = new Body(fopen('php://temp', 'r+'));
		$body->write(json_encode($data), JSON_PRETTY_PRINT);
		return $response->withHeader('Content-Type', 'applicaiton/json')->withBody($body);
	}

	/**
	 * Initialise le controleur actule
	 *
	 * @param string $class nom de la classe servant de contrôleur
	 * @return void
	 */
	protected function setCurrentController($class)
	{
		$this->curentController = str_replace('Controller', '', substr($class, strrpos($class, SysConst::NAMESPACE_SPARATOR) + 1));
	}


}