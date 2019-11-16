<?php

namespace Digitalis\Core\Models\Menu;

use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\Menu\MenuItem;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Slim\Http\Request;
use Slim\Router;

/**
 * MenuManager Gestionnaire de menu du système
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class MenuManager
{
    private static $menu = [];
    const SESSION_NAME = "menu";
    const GENERAL_MENU = "gMenue";

    const MASK_MENU_ITEM = '<li class="%s"><a href="%s">%s</a>%s</li>';

    public static function initMenu()
    {
        self::$menu = [];
    }

    public static function add(MenuItem $item)
    {
        self::$menu[] = $item;
        self::writeToSession();
    }

    public static function writeToSession()
    {
        SessionManager::set(self::SESSION_NAME, serialize(self::$menu));
    }

    /**
     * Retourne le menu de l'application
     *
     * @return MenuItem[]
     */
    public static function getMenu()
    {
        $svalue = SessionManager::get(self::SESSION_NAME);
        return $svalue ? unserialize($svalue) : self::$menu;
    }

    /**
     * Contruit les menus enfants d'un menu
     *
     * @param Router $router
     * @param Request $request
     * @param MenuItem[] $children les enfants du menu à afficher
     * @return string
     */
    private static function buildChildren(Router $router, Request $request, array $children)
    {
        $output = null;
        $currentmenu = SysConst::HOME;
        $hasActiveChild = false;
        $lroute = $request->getAttribute('route');
        if (!empty($lroute)) {
            $currentmenu = $lroute->getName();
        }
        $tcurrent = explode('.', $currentmenu);

        foreach ($children as $item) {
            try {
                $mchild = null;
                if ($item->getIsGroup()) {
                    $hasActiveChild = $item->hasActiveChild($currentmenu);
                    foreach ($item->getChildren() as $_child) {
                        try {
                            $_tchild = explode('.', $_child->getRouteName());
                            $mchild .= sprintf(
                                self::MASK_MENU_ITEM,
                                ($tcurrent[0] == $_tchild[0] ? 'active' : ''),
                                (!is_null($_child->getRouteName()) ? $router->pathFor($_child->getRouteName()) : '#!'),
                                $_child->fullLabel(),
                                null
                            );
                        } catch (\Exception $exc) {
                            SessionManager::set(SysConst::FLASH, $exc->getMessage(), false);
                            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
                            ErrorHandler::writeLog($exc);
                        } catch (\Error $err) {
                            SessionManager::set(SysConst::FLASH, $err->getMessage(), false);
                            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
                            ErrorHandler::writeLog($err);
                        }
                    }
                    $mchild = '<ul class="nav nav-third-level" >' . $mchild . '</ul>';
                }
                $titem = explode('.', $item->getRouteName());
                $output .= sprintf(
                    self::MASK_MENU_ITEM,
                    (($hasActiveChild || ($tcurrent[0] == $titem[0])) ? 'active' : ''),
                    (!is_null($item->getRouteName()) ? $router->pathFor($item->getRouteName()) : '#!'),
                    $item->fullLabel(),
                    $mchild
                );
            } catch (\Exception $exc) {
                SessionManager::set(SysConst::FLASH, $exc->getMessage(), false);
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
                ErrorHandler::writeLog($exc);
            } catch (\Error $err) {
                SessionManager::set(SysConst::FLASH, $err->getMessage(), false);
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
                ErrorHandler::writeLog($err);
            }
        }
        return !is_null($output) ? '<ul class="nav nav-second-level">' . $output . '</ul>' : null;
    }

    /**
     * Construit le menu de l'application
     *
     * @param Router $router
     * @param Request $request
     * @return string
     */
    public static function buildMenu(Router $router, Request $request)
    {
        $menu = self::getMenu();
        $output = '';
        $currentmenu = SysConst::HOME;
        $lroute = $request->getAttribute('route');
        if (!empty($lroute)) {
            $currentmenu = $lroute->getName();
        }

        foreach ($menu as $itemMenu) {
            $hasActiveChild = $itemMenu->hasActiveChild($currentmenu);
            try {
                $mchild = null;
                if ($itemMenu->getIsGroup()) {
                    $mchild = self::buildChildren($router, $request, $itemMenu->getChildren());
                }
                $tcurrent = explode('.', $currentmenu);
                $titem = explode('.', $itemMenu->getRouteName());

                $output .= sprintf(
                    self::MASK_MENU_ITEM,
                    (($hasActiveChild || ($tcurrent[0] == $titem[0])) ? 'active' : ''),
                    (!is_null($itemMenu->getRouteName()) ? $router->pathFor($itemMenu->getRouteName()) : '#!'),
                    $itemMenu->fullLabel(),
                    $mchild
                );
            } catch (\Exception $exc) {
                SessionManager::set(SysConst::FLASH, $exc->getMessage(), false);
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
                ErrorHandler::writeLog($exc);
            } catch (\Error $err) {
                SessionManager::set(SysConst::FLASH, $err->getMessage(), false);
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
                ErrorHandler::writeLog($err);
            }
        }

        return $output;
    }
}
