<?php
require_once '../src/Core/Handlers/ShutdownErrorHandler.php';

use Digitalis\Core\Models\EnvironmentManager as EnvMngr;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require '../vendor/autoload.php';

new EnvMngr();

define('APPNAME', EnvMngr::getAppname());
define('DANGER', 'danger');
define('SUCCESS', 'success');
define('clientManager', 'clientManager');

$config = [
    'settings' => [
        'displayErrorDetails' => EnvMngr::displayError(),
        'determineRouteBeforeAppMiddleware' => true
    ]
];

$app = new App($config);
//
//INITIALISATION DE LA LANGUE
//
$curlang = strtolower(substr($app->getContainer()->request->getUri()->getPath(), 1, 2));
$curlang = strlen($curlang) > 0 ? $curlang : SessionManager::getCurrentLang();
if (!EnvMngr::isAuthLang($curlang)) {
    $curlang = 'fr';
}

define('CUR_LANG', $curlang);
define('R_CUR_LANG', '/' . CUR_LANG);
define('data_unavailable', 'data-unavailable');
define('data_exist', 'data-exist');
define('operation_success', 'operation-success');
define('an_error_occured', 'an-error-occurred-during-');
SessionManager::set(SysConst::CUR_LANG, $curlang);

require 'dependencies.php';
require 'middleware.php';

/**
 * Définition des headers pour un bon fonctionnement dans le cadre d'application fullstack (js)
 */
$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', "*")
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

require 'routes.php';

/**
 * Nettoyage des urls
 */
$app->add(function (Request $request, Response $response, callable $next) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if ($path != '/' && substr($path, -1) == '/') {
        // permanently redirect paths with a trailing slash
        // to their non-trailing counterpart
        $uri = $uri->withPath(substr($path, 0, -1));

        if ($request->getMethod() == 'GET') {
            return $response->withRedirect((string)$uri, 301);
        } else {
            return $next($request->withUri($uri), $response);
        }
    }

    return $next($request, $response);
});