<?php

namespace Digitalis\Core\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Body;
use Digitalis\Core\Models\EnvironmentManager as EnvMngr;
use Digitalis\Core\Models\Lexique;

class HomeController extends Controller
{

    public function __construct($container)
    {
        parent::__construct($container);
        parent::setCurrentController(__class__);
    }

    public function index(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'home'));
        $param = ['name' => rtrim(str_ireplace('index.php', '', $request->getUri()->getBasePath()), '/')];
        $this->render($response, 'index', true, $param);
    }

}
