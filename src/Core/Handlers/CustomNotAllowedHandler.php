<?php
namespace Digitalis\Core\Handlers;

use Digitalis\Core\Middlewares\ActionLoggerMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Body;
use Digitalis\Core\Models\EnvironmentManager as EnvMngr;

//TODO: Implémenter un meilleur rendu html pour le action non autorisé

/**
 * Description of CustomnotAllowedHandler
 * Cette classe permet de gérér les requetes clientes qui arrivent
 * et pas avec le bon verbe HTTP (GET, POST, PUT, OPTIONS, DELETE)
 * @author sylvin.kamdem
 */
class CustomNotAllowedHandler extends AbstractHandler
{
    /**
     *
     * @var ContainerInterface
     */
    private $container;
    const TITLE = "Not allowed";

    function __construct($container)
    {
        $this->container = $container;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, $methods)
    {
        $okMethode = implode(', ', $methods);
        $log = new ActionLoggerMiddleware($this->container);
        $log->writeLog($request, ActionLoggerMiddleware::NOT_ALLOWED_METHODE . ' Expected(' . $okMethode . ')');

        $message = 'Methode must be one of:' . implode(', ', $methods);
        $contenttype = $this->determineContentType($request);
        $body = new Body(fopen('php://temp', 'r+'));
        switch ($contenttype) {
            case 'application/json':
                $body->write($this->renderJsonErrorMessage($message));
                break;
            case 'application/xml':
            case 'text/xml':
                $body->write($this->renderXmlErrorMessage($message));
                break;
            case 'text/html':
            default:
                $body->write($this->renderHtmlErrorMessage($message));
                break;
        }
        return $response->withStatus(405)
            ->withHeader('Allowed', $okMethode)
            ->withHeader('Content-type', $contenttype)
            ->withBody($body);
    }

    private function renderJsonErrorMessage($message)
    {
        return json_encode([
            'title' => 'Not allowed methode', 'message' => $message, 'status' => (boolean)false,
            'data' => null,
            'code' => null,
            'found' => (boolean)false,
            'modelStateError' => null,
            'saved' => (boolean)false,
            'updated' => (boolean)false
        ]);
    }

    private function renderXmlErrorMessage($message)
    {
        return '<?xml version="1.0" encoding="UTF-8" ?>' .
            "<error>\n  <title>" . self::TITLE . ": " . $message . "</title>\n" .
            "<message>" . $message . "</message>\n"
            . "</error>";

    }

    private function renderHtmlErrorMessage($message)
    {
        //TODO implémenter une meilleure interface pour l'affichage du not allowed
        return sprintf("<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            "<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana," .
            "sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{" .
            "display:inline-block;width:65px;}</style></head><body><h1>%s</h1><p>%s</p></body></html>", self::TITLE, self::TITLE, $message);
    }

}
