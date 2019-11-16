<?php
namespace Digitalis\Core\Middlewares;

use Digitalis\Core\Models\DbAdapters\UserDbAdapter;
use Digitalis\Core\Handlers\AbstractHandler;
use Digitalis\Core\Models\EntityAnnotation\Security\InputValidator;
use Digitalis\Core\Models\JsonResponse;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Container;
use Slim\Http\Body;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

/**
 * Middleware permettant de contrôler que l'utilisateur est connecter
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class AuthenticationMiddleware extends AbstractHandler
{

    /**
     *
     * @var Container
     */
    private $container;
    /**
     *
     * @var Route
     */
    private $route;
    function __construct(Container $container)
    {
        $this->container = $container;
    }
    /**
     * Retourne une réponse json
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function renderJson(Request $request, Response $response)
    {
        $body = new Body(fopen('php://temp', 'r+'));
        $out = new JsonResponse();
        $out->message = Lexique::GetString(CUR_LANG, 'your-session-has-expiredp');
        $out->isSuccess = false;
        $body->write(json_encode($out, JSON_PRETTY_PRINT));
        return $response->withStatus(200)
            ->withHeader('Content-type', 'application/json')
            ->withBody($body);
    }
    /**
     *
     * @param  Request $request  PSR7 request
     * @param  Response     $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $this->route = $request->getAttribute('route');
        $loggedUser = SessionManager::getLoggedUser();
        $login = !is_null($loggedUser) ? $loggedUser->getLogin() : null;
        if ($this->route) {
            $name = $this->route->getName();
            if (!in_array($name, SysConst::NO_AUTH_ROUTES)) {
                if (!$loggedUser) {
                    $conntentType = $this->determineContentType($request);
                    session_destroy();
                    if ($request->isXhr())
                        return $this->renderJson($request, $response);

                    return $response->withStatus(302)->withHeader('Location', $this->container->router->pathFor(SysConst::R_G_LOGIN));
                } elseif (UserDbAdapter::loggedOut($loggedUser->getLogin())) {
                    UserDbAdapter::setLastLogout($loggedUser->getLogin());
                    session_destroy();
                    if ($request->isXhr())
                        return $this->renderJson($request, $response);

                    return $response->withStatus(302)->withHeader('Location', $this->container->router->pathFor(SysConst::R_G_LOGIN));
                }
            }
        }
        UserDbAdapter::setLastAction();
        SessionManager::refreshLoggedUser($login);
        return $next($request, $response);
    }
}
