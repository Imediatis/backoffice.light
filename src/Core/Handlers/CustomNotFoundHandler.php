<?php

namespace Digitalis\Core\Handlers;

use Slim\Http\Uri;
use Slim\Container;
use Slim\Http\Body;
use Slim\Http\Request;
use Slim\Http\Response;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Middlewares\ActionLoggerMiddleware;
use Digitalis\Core\Models\EnvironmentManager as EnvMngr;

/**
 * Description of CustomNotFoundHandler
 * cette classe permet de gérer les routes non existantes.
 *
 * @author sylvin.kamdem
 */
class CustomNotFoundHandler extends AbstractHandler
{
    const TITLE = "404 Page not found";
    /**
     *
     * @var ContainerInterface
     */
    private $container;


    /**
     * Uri courante
     *
     * @var Slim\Http\Uri
     */
    private $uri;

    function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response)
    {
        $log = new ActionLoggerMiddleware($this->container);
        $log->writeLog($request, ActionLoggerMiddleware::TARGET_NOT_FOUND);

        $contenttype = $this->determineContentType($request);

        $body = new Body(fopen('php://temp', 'r+'));
        switch ($contenttype) {
            case 'application/json':
                $body->write($this->renderJsonErrorMessage());
                break;
            case 'application/xml':
            case 'text/xml':
                $body->write($this->renderXmlErrorMessage());
                break;
            case 'text/html':
            default:
                $body->write($this->renderHtmlErrorMessage());
                break;
        }
        $uri = $request->getUri();
        $curUrl = (string)$uri;
        $routeToForce = $this->container->baseUrl;// $uri->getScheme() . '://' . $uri->getHost() . (!is_null($uri->getPort()) ? ':' . $uri->getPort() : '') . '/';

        if ($curUrl == $routeToForce) {
            $loggedUser = SessionManager::getLoggedUser();
            if ($loggedUser) {
                return $this->container->view->render($response, "Core/Views/Home/index.twig");
            } else {
                return $response->withRedirect($this->container->baseUrl . CUR_LANG, 301);
            }
        }

        return $this->container->view->render($response, SysConst::CORE_SHARED_VIEW_F . "404.html");
    }



    /**
     * Render HTML error page
     *
     * @return string
     */
    protected function renderHtmlErrorMessage()
    {
        return EnvMngr::getformat404Pagenotfound();

    }


    /**
     * Render JSON error
     *
     * @return string
     */
    protected function renderJsonErrorMessage()
    {
        return json_encode([
            'message' => self::TITLE,
            'status' => (boolean)false,
            'data' => null,
            'code' => null,
            'found' => (boolean)false,
            'modelStateError' => null,
            'saved' => (boolean)false,
            'updated' => (boolean)false
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Render XML error
     *
     * @return string
     */
    protected function renderXmlErrorMessage()
    {
        return '<?xml version="1.0" encoding="UTF-8" ?><error>\n  <message>' . self::TITLE . '</message>\n</error>';

    }

}
