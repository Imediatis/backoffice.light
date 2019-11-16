<?php
namespace Digitalis\Core\Middlewares;

use Digitalis\Core\Models\EnvironmentManager;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\Menu\MenuManager;
use Digitalis\Core\Models\Security\AntiForgeryToken;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * InjectorMiddleware Classe permettant d'injecter les variables goblale php aux vues
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class InjectorMiddleware
{

    /**
     * Environnement Twig
     *
     * @var \Twig_Environment
     */
    private $twig;


    /**
     * Conteneur
     *
     * @var \Slim\Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->twig = $this->container->view->getEnvironment();
    }

    private function addGlobal(string $var, $value)
    {
        $this->twig->addGlobal($var, $value);
        SessionManager::remove($var);
    }

    private function functionExtender(Request $request)
    {
        //
        //INJECTION DE LA FONCTION getString du LEXIQUE
        //
        $this->twig->addFunction(new TwigFunction('LexiqueGetString', function ($key) {
            return Lexique::GetString(SessionManager::getCurrentLang(), $key);
        }));

        //
        //INJECTION DE LA FONCTION getCode du LEXIQUE
        //
        $this->twig->addFunction(new TwigFunction('LexiqueGetCode', function ($key) {
            return Lexique::getCode(SessionManager::getCurrentLang(), $key);
        }));

        //
        //INJECTE LA FONCTION QUI PERMET DE DETERMINER QUEL MENU EST ACTIF (menu manuel)
        //
        $this->twig->addFunction(new TwigFunction('isActive', function ($menu) use ($request) {
            $route = $request->getAttribute('route');
            if (!empty($route)) {
                $name = $route->getName();
                return $name == $menu ? 'active' : '';
            }
            return;
        }));

        //
        //INJECTION POUR LE CSRF
        //
        $this->twig->addFunction(new TwigFunction('getCsrfInput', function () {
            return AntiForgeryToken::getAntiForgeryToken()->genField();
        }, ['is_safe' => ['html']]));

        $this->twig->addFilter(new TwigFilter('base64_encode', 'base64_encode'));

        $this->twig->addFilter(new TwigFilter('base64_decode', 'base64_decode'));

        $this->twig->addFilter(new TwigFilter('stripslashes', 'stripslashes'));

        $this->twig->addFilter(new TwigFilter('html_entity_decode', 'html_entity_decode'));

        //
        //INJECTION DU MENU DE L'APPLICATION
        //
        $this->twig->addFunction(new TwigFunction('buildMenu', function () use ($request) {
            return MenuManager::buildMenu($this->container->router, $request);
        }, ['is_safe' => ['html']]));

        //
        //INJECTION DE LA METHODE POUR AFFICHER LE MESSAGE FLASH
        //
        $this->twig->addFunction(new TwigFunction('flashMsg', function () {
            $flash = SessionManager::get(SysConst::FLASH);
            $mask = '<div class="col-md-12"><div class="alert alert-%s alert-dismissible animated bounceInRight" role="alert">%s
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div></div>';
            $output = '';
            if (is_array($flash)) {
                foreach ($flash as $key => $value) {
                    $output .= sprintf($mask, $key, stripslashes($value));
                }
            }
            SessionManager::remove(SysConst::FLASH);
            SessionManager::remove(SysConst::ICSRF);
            return $output;
        }, ['is_safe' => ['html']]));

        //
        //INJECTION DE LA METHODE POUR LE LIEN RETOUR
        //
        $this->twig->addFunction(new TwigFunction('goBack', function ($routeName, $params = []) use ($request) {
            return sprintf('<a href="%s" class="btn btn-info btn-outline btn-sm btn-block "><i class="fa fa-arrow-left g-mr-5"></i>%s</a>', $this->container->router->pathFor($routeName, $params), Lexique::GetString(CUR_LANG, 'go-back'));
        }, ['is_safe' => ['html']]));

        //
        //CONSTRUCTEUR DES OPTIONS D'UNE LISTE DEROULANTE
        //
        $this->twig->addFunction(new TwigFunction('selectedList', function ($List, $selected = null, $defVal = null) {

            $output = '<option value="">' . Lexique::GetString(CUR_LANG, $defVal) . ' ...</option>';
            if (is_array($List)) {
                foreach ($List as $key => $value) {
                    $isselected = $selected == $key ? "selected" : "";
                    $output .= sprintf('<option value="%s" %s>%s</option>', $key, $isselected, $value);
                }
            }
            return $output;
        }, ['is_safe' => ['html']]));
    }

    public function __invoke(Request $request, Response $response, $next)
    {

        $this->twig->addGlobal(SysConst::T_SESSION, SessionManager::allSession());
        $this->twig->addGlobal(SysConst::APP_ENV, EnvironmentManager::getEnvironment());
        $this->twig->addGlobal(SysConst::CUR_LANG, SessionManager::getCurrentLang());

        $this->twig->addGlobal(SysConst::FLASH, SessionManager::get(SysConst::FLASH));
        $this->twig->addGlobal(SysConst::MODEL_ERRORS, SessionManager::get(SysConst::MODEL_ERRORS));
        $this->twig->addGlobal(SysConst::MODEL, SessionManager::get(SysConst::MODEL));

        $user = SessionManager::getLoggedUser();
        $this->twig->addGlobal(SysConst::T_USER, !is_null($user) ? $user->forTwig() : null);

        $this->functionExtender($request);

        return $next($request, $response);
    }
}
