<?php
namespace Digitalis\Core\Models;

/**
 * SysConst contient les constantes manipulées dans le système
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class SysConst
{
    const NO_AUTH_ROUTES = [
        self::R_G_LOGIN,
        self::R_P_LOGIN,
        self::R_LOGOUT,
        'account.logout.force',
        'account.changepwd',
        'account.pchangepwd'
    ];
    const CLIENT_OS = "CLIENT-OS";
    const R_ROUTE = "requested_route";
    const ROOT_NAME = "IME-DIG";
    const MODEL = 'Model';
    const AUTH_USER = "LOGGED-USER";
    const SUBS_MODEL = "SUBSCR-MODEL";
    const SUBS_ERR_MODEL = "error-modelState";
    const SUBS_S_SUBS = "SAVE-SUBS";
    const NAMESPACE_SPARATOR = "\\";
    const TWIG = ".twig";
    const VIEWS = "Views";
    const CORE = "Core";
    const CORE_SHARED_VIEW_F = "Core/Views/shared/";
    const T_CORE_SHARE_VIEW_F = "coreShareViewsF";
    const APP_ENV = "appEnv";
    const HOME = "home";
    const R_G_LOGIN = "login";
    const R_P_LOGIN = "plogin";
    const R_LOGOUT = "logout";
    const R_G_SUBSCRIBE = "subscribe";
    const R_P_SUBSCRIBE = "psubscribe";
    const T_SUBSMODEL = "subsModel";

    const MODEL_ERRORS = 'modelErrors';
    const FLASH = "flash";
    const T_USER = "User";
    const S_RESELLER = "ime-reseller";
    const T_RESELLER = "Reseller";
    const CUR_LANG = "CUR_LANG";
    const T_SESSION = "session";
    const ICSRF = "iCsrf";
    const TITLE = "title";
    const SELECTED_ITEM = "selected-item";
}