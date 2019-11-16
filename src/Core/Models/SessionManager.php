<?php
namespace Digitalis\Core\Models;

use Digitalis\Core\Models\DbAdapters\UserDbAdapter;
use Digitalis\Core\Models\Entities\User;
use Digitalis\Core\Models\Security\LoggedUser;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\ViewModel\SubscribeViewModel;


/**
 * SessionManager Gestionnaire d'accès à variable globale session
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class SessionManager
{


    public static function initSession()
    {
        if (!isset($_SESSION[SysConst::ROOT_NAME])) {
            $_SESSION[SysConst::ROOT_NAME] = [];
        }
    }

    /**
     * Permet de détruire une variable de session dont l'index est passé en paramètre
     *
     * @param string $key
     * @return void
     */
    public static function remove(string $key)
    {
        unset($_SESSION[SysConst::ROOT_NAME][$key]);
    }

    /**
     * Permet d'obtenir une variable de session dont l'index est passé en paramètre
     *
     * @param string $key
     * @return mixed|null retourne la valeur attendu ou null
     */
    public static function get(string $key)
    {
        return isset($_SESSION[SysConst::ROOT_NAME][$key]) ? $_SESSION[SysConst::ROOT_NAME][$key] : null;
    }

    public static function allSession()
    {
        return isset($_SESSION[SysConst::ROOT_NAME]) ? $_SESSION[SysConst::ROOT_NAME] : [];
    }

    /**
     * Permet d'initialiser une variable de session dont l'indexe et la valeur sont passée en paramètre
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(string $key, $value, $skey = SUCCESS)
    {
        if ($key == SysConst::FLASH) {
            $_SESSION[SysConst::ROOT_NAME][$key][$skey] = $value;
        } else {
            $_SESSION[SysConst::ROOT_NAME][$key] = $value;
            if ($key == SysConst::MODEL_ERRORS) {
                $_SESSION[SysConst::ROOT_NAME][SysConst::FLASH][DANGER] = "Certains champs ne sont pas bien renseignés";
            }
        }
    }

    /**
     * Permet de récupérer l'utilisateur connecté
     *
     * @return \Digitalis\Core\Models\Security\LoggedUser
     */
    public static function getLoggedUser()
    {
        $user = isset($_SESSION[SysConst::ROOT_NAME][SysConst::AUTH_USER]) ? unserialize($_SESSION[SysConst::ROOT_NAME][SysConst::AUTH_USER]) : null;
        $dbuser = !is_null($user) ? UserDbAdapter::getByLogin($user->getLogin()) : null;
        return !is_null($dbuser) ? $user : null;
    }

    /**
     * Tente de mettre à jour les paramètre de l'utilisateur actuellement connecter pour affichage
     *
     * @param User $user
     * @return void
     */
    public static function updateLoggedUser($user)
    {
        $suser = self::getLoggedUser();
        if ($suser->getLogin() == $user->getLogin()) {
            $loggeduser = new LoggedUser(
                $user->getLogin(),
                $user->getLastName(),
                $user->getFirstName(),
                $user->getProfile()->getDescription(),
                $user->getFunction()
            );
            self::set(SysConst::AUTH_USER, serialize($loggeduser));
        }
    }

    public static function refreshLoggedUser($login)
    {
        $user = UserDbAdapter::getByLogin($login);
        if ($user) {
            self::updateLoggedUser($user);
        }
    }

    /**
     * Retourne la demande d'abonnement
     * 
     * @return \Digitalis\Core\Models\ViewModel\SubscribeViewModel
     */
    public static function getSubscription()
    {
        return isset($_SESSION[SysConst::ROOT_NAME][SysConst::SUBS_MODEL]) ? unserialize($_SESSION[SysConst::ROOT_NAME][SysConst::SUBS_MODEL]) : new SubscribeViewModel();
    }

    /**
     * Retourne le client courant
     *
     * @return Reseller|null
     */
    public static function getReseller()
    {
        return isset($_SESSION[SysConst::ROOT_NAME][SysConst::S_RESELLER]) ? unserialize($_SESSION[SysConst::ROOT_NAME][SysConst::S_RESELLER]) : null;
    }

    /**
     * Permet de récupérer la langue courante
     *
     * @return string
     */
    public static function getCurrentLang()
    {
        $acceptedlang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)) : null;
        $acceptedlang = strlen($acceptedlang) > 0 ? $acceptedlang : 'fr';
        return isset($_SESSION[SysConst::ROOT_NAME][SysConst::CUR_LANG]) && strlen($_SESSION[SysConst::ROOT_NAME][SysConst::CUR_LANG]) > 0 ? $_SESSION[SysConst::ROOT_NAME][SysConst::CUR_LANG] : $acceptedlang;
    }

    /**
     * Récupère le contexte Request qui a été sauvegardé en session
     *
     * @return \Slim\Http\Request
     */
    public static function getSessionRequest()
    {
        return isset($_SESSION[SysConst::ROOT_NAME][SysConst::S_REQUEST]) ? $_SESSION[SysConst::ROOT_NAME][SysConst::S_REQUEST] : null;
    }
}
