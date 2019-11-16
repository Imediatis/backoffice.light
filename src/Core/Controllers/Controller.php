<?php
//TODO : implémenter le content negociation, afin de fournir le service d'un api full rest
namespace Digitalis\Core\Controllers;

use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\Security\AntiForgeryToken;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Slim\Container;
use Slim\Http\Body;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Description of Controller
 *
 * @author Sylvin
 */
class Controller
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
     * moteur de vu du système
     *
     * @var Slim\Views\Twig
     */
    protected $View;

    /**
     * Environnement twig
     *
     * @var \Twig_Environment
     */
    protected $envTwig;


    /**
     * Reseller courant du système
     *
     * @var \Digitalis\Core\Models\Reseller
     */
    protected $reseller;

    /**
     * Conteneur de traitement
     *
     * @var Container
     */
    protected $container;

    /**
     * Routeur
     *
     * @var \Slim\Router
     */
    protected $router;

    /**
     * Instantie le controleur
     *
     * @param Container $container
     */
    function __construct(Container $container)
    {
        $this->db = DBase::getEntityManager();
        $this->container = $container;
        $this->View = $this->container->view;
        $this->envTwig = $this->container->view->getEnvironment();
        $this->reseller = $container->reseller;
        $this->router = $container->router;
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
     *
     * @return \PHPMailer\PHPMailer\PHPMailer
     */
    protected function mailer()
    {
        return $this->container->mailer;
    }

    /**
     * Permet de faire une redirection vers une autre page
     *
     * @param Response $response
     * @param string $route
     * @param integer $statut statut de redirection
     * @param array $params Paramètres suplémentaire de l'url
     * @return void
     */
    protected function redirect(Response $response, $route, $statut = 302, $params = [])
    {
        return $response->withStatus($statut)->withHeader('Location', $this->router->pathFor($route, $params));
    }

    /**
     * Permet de générer le rendu d'une methode de classe
     *
     * @param ResponseInterface $response
     * @param string $filename Nom du template de la page à faire le rendu. fournir ce nom de fichier sans son extension. l'extension ".twig" est complété automatiquement.
     * ne pas également ajouter le nom du controleur pour la résolution automatique du dossier où se trouve le fichier.
     * Il est automatiquement fournier grace à l'appelle de setCurrentController dans le constructeur du controleur dont on veut faire le rendu d'une de ses vue.
     * donc le constructeur de chaque contrôleur doit être sous la forme
     * function __construct($container){
     *  parent::__construct($container);
     *  parent::setCurrentControler(__CLASS__);
     * }
     * @param boolean $isCode Valeur déterminant si le template à charger est un template de base du système
     * @param mixed $params
     * @return void
     */
    protected function render(Response $response, $filename, $isCore = false, $params = [])
    {
        if ($isCore) {
            $view = join("/", [SysConst::CORE, SysConst::VIEWS, $this->curentController, $filename . SysConst::TWIG]);
        } else {
            $view = join("/", [$this->reseller->folder, SysConst::VIEWS, $this->curentController, $filename . SysConst::TWIG]);
        }
        SessionManager::remove(SysConst::MODEL);
        SessionManager::remove(SysConst::MODEL_ERRORS);
        $this->View->render($response, $view, $params);
    }

    /**
     * Permet de retourner le résultat de traitement de la methode du controleur sous forme de chaîne JSO
     *
     * @param Response $response
     * @param array|object $output Tableau ou objet implémentant jsonserializable
     * @return Response
     */
    protected function renderJson(Response $response, $output)
    {
        $output[AntiForgeryToken::HIDDEN_FIELD] = AntiForgeryToken::getAntiForgeryToken()->getPassword();
        $body = new Body(fopen('php://temp', 'r+'));
        $body->write(json_encode($output, JSON_PRETTY_PRINT));
        $string = json_encode($output);
        return $response->withHeader('Content-Type', 'applicaiton/json')->withBody($body);
    }

    /**
     * Initialise le controleur actule
     *
     * @param string $class
     * @return void
     */
    protected function setCurrentController($class)
    {
        $this->curentController = str_replace('Controller', '', substr($class, strrpos($class, SysConst::NAMESPACE_SPARATOR) + 1));
    }

    /**
     * Permet d'ajouter à la session une message d'un certain type pour l'affichage dans les vues
     *
     * @param string $message
     * @param string $type
     * @return void
     */
    protected function title($title)
    {
        $this->envTwig->addGlobal('title', $title);
    }
}
