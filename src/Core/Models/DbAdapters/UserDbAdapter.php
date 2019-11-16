<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Models\DbAdapters\ProfileDbAdapter;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\Entities\Profile;
use Digitalis\Core\Models\Entities\User;
use Digitalis\Core\Models\GAdapter;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\MailWorker;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Doctrine\ORM\Query\Expr\Join;

/**
 * UserDbAdapter Gestionnaire des utilisateurs avec la base de donnée
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class UserDbAdapter extends GAdapter
{
    const DEF_PWD = '220512cdce';

    /**
     * Enregistre l'utilisateur dans le système
     *
     * @param User $user
     * @return boolean
     */
    public static function save($user)
    {
        try {
            $old = self::getByLogin($user->getLogin());
            if (!$old) {
                $profile = ProfileDbAdapter::getByCode($user->getProfile()->getCode());
                if ($profile) {
                    $user->setProfile($profile);
                    DBase::getEntityManager()->persist($user);
                    DBase::getEntityManager()->flush();
                    return true;
                } else {
                    Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'user-profile-is-not-set-'));
                }
            } else {
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'user-exist'));
            }
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return false;
    }

    /**
     * Fait la mise à jour des champs basiques de l'utilisateur (nom, prénom, fontion)
     *
     * @param User $user
     * @return boolean
     */
    public static function update($user)
    {
        try {
            $old = self::getById($user->getId());
            $dlogin = self::getByLogin($user->getLogin());

            if ($old) {
                if ($dlogin && $dlogin->getId() != $old->getId()) {
                    Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'this-login-is-used'));
                } else {
                    $old->setLogin($user->getLogin());
                    $old->setFirstName($user->getFirstName());
                    $old->setLastName($user->getLastName());
                    $old->setFunction($user->getFunction());
                    $old->setProfile($user->getProfile());
                    DBase::getEntityManager()->flush();
                    return true;
                }
            } else {
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'unable-to-update-informat'));
            }
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return false;
    }

    /**
     * Permet de faire la mise à jour du profile d'utilisateur
     *
     * @param string $login
     * @param string $codeProfile
     * @return boolean
     */
    public static function changeProfile(string $login, string $codeProfile)
    {
        try {
            $user = self::getByLogin($login);
            $profile = ProfileDbAdapter::getByCode($codeProfile);
            if ($user && $profile) {
                $user->setProfile($profile);
                DBase::getEntityManager()->flush();
                return true;
            } else {
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_unavailable));
            }
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return false;
    }

    /**
     * Met à jour le mot de passe de l'utilisateur
     *
     * @param string $login
     * @param string $currentPwd
     * @param string $newPwd
     * @return boolean
     */
    public static function pwdUpdate($login, $currentPwd, $newPwd)
    {
        try {
            $dbuser = self::getByLogin($login);
            if ($dbuser) {
                if (password_verify($currentPwd, $dbuser->getPassword())) {
                    $dbuser->setPassword(Data::cryptPwd($newPwd));
                    $dbuser->setStatus(1);

                    $dbuser->setLastLogin(new \DateTime());
                    $date = new \DateTime();
                    $dbuser->setLastLogout($date->add(new \DateInterval('PT30S')));
                    $ip = MailWorker::getIpAddress();
                    $dbuser->setLastIpLogin($ip);
                    $dbuser->setLastIpLogout($ip);
                    DBase::getEntityManager()->flush();
                    return true;
                } else {
                    Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'user-name-or-password-inc'));
                }
            } else {
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'user-name-or-password-inc'));
            }
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return false;
    }

    /**
     * enregistre les informations de dernière connexion de l'utilisateur
     *
     * @param string $login
     * @return boolean
     */
    public static function setLastLogin($login)
    {
        try {
            $user = self::getByLogin($login);
            if ($user) {
                $user->setLastLogin(new \DateTime());
                $user->setLastIpLogin(MailWorker::getIpAddress());
                $user->setIsLogged(true);
                DBase::getEntityManager()->flush();
                return true;
            } else {
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_unavailable));
            }
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return false;
    }

    public static function sameIpLogout($login)
    {
        try {
            $user = self::getByLogin($login);
            if ($user) {
                if ($user->getLastIpLogin() == MailWorker::getIpAddress()) {
                    $user->setLastLogout(new \DateTime());
                    $user->setLastIpLogout(MailWorker::getIpAddress());
                    $user->setIsLogged(false);
                    DBase::getEntityManager()->flush();
                    return true;
                }
            } else {
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_unavailable));
            }
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return false;
    }

    /**
     * Permet d'enregistrer les informations de dernière déconnexion de l'utilisateur
     *
     * @param string $login
     * @return boolean
     */
    public static function setLastLogout($login)
    {
        try {
            $user = self::getByLogin($login);
            if ($user) {
                if (!is_null($user->getLastLogin())) {
                    $user->setLastLogout(new \DateTime());
                    $user->setLastIpLogout(MailWorker::getIpAddress());
                }
                $user->setIsLogged(false);
                DBase::getEntityManager()->flush();
                return true;
            } else {
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_unavailable));
            }
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return false;
    }

    /**
     * Permet de récupérer un utilisateur à partir de son login
     *
     * @param string $login
     * @return User
     */
    public static function getByLogin($login)
    {
        try {
            $repos = DBase::getEntityManager()->getRepository(User::class);
            return $repos->findOneByLogin($login);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return null;
    }

    /**
     * Permet de récupérer l'utilisateur en fonction de l'identifiant
     *
     * @param integer $id
     * @return User
     */
    public static function getById($id)
    {
        return self::getFromDb(User::class, $id);
    }

    /**
     * Active ou désactive l'utilisateur
     *
     * @param string $login
     * @return boolean
     */
    public static function activate($login)
    {
        try {
            $user = self::getByLogin($login);
            if ($user) {
                $statut = ($user->getStatus() == 1 || $user->getStatus() == 2) ? 0 : (is_null($user->getLastLogin()) ? 2 : 1);

                $user->setStatus($statut);
                DBase::getEntityManager()->flush();
                return true;
            } else {
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'unable-to-update-informat'));
            }
        } catch (\Exception $exc) {
            SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, an_error_occured));
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return false;
    }

    /**
     * désactive l'utilisateur
     *
     * @param string $login
     * @return boolean
     */
    public static function deactivateUser($login)
    {
        try {
            $user = self::getByLogin($login);
            if ($user) {
                $user->setStatus(0);
                $user->setIsLogged(false);
                DBase::getEntityManager()->flush();
                return true;
            }
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return false;
    }

    /**
     * Permet de vérifier qu'un compte respecte les contraintes pour se connecter
     *
     * @param string $login
     * @return \Digitalis\Core\Models\Entities\User
     */
    public static function checkLogin($login)
    {
        try {
            $suser = SessionManager::getLoggedUser();
            $qb = DBase::getEntityManager()->createQueryBuilder();
            $lusers = $qb->select('u')
                ->from(User::class, 'u')
                ->innerJoin(Profile::class, 'p', Join::WITH, 'u.profile=p.id')
                ->where('u.login=:login AND u.status IN (1,2) AND p.status=1')
                ->setParameter(':login', $login)
                ->getQuery()
                ->getResult();
            if (count($lusers) == 1) {
                return $lusers[0];
            }
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return null;
    }

    public static function loggedOut($login)
    {
        try {
            $user = self::checkLogin($login);
            if ($user) {
                return ($user->getLastLogout() > $user->getLastLogin()) || in_array($user->getStatus(), [0, 2]) || ($user->getLastIpLogout() != MailWorker::getIpAddress() || !$user->getIsLogged());
            }
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return true;
    }

    public static function setLastAction()
    {
        try {
            $logged = SessionManager::getLoggedUser();
            $login = !is_null($logged) ? $logged->getLogin() : null;
            $user = self::getByLogin($login);
            if ($user) {
                $user->setLastAction(new \DateTime());
                DBase::getEntityManager()->flush();
                return true;
            }
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return false;
    }

    public static function resetPwd($login)
    {
        try {
            $user = self::getByLogin($login);
            if ($user) {
                $user->setPassword(Data::cryptPwd(self::DEF_PWD));
                $user->setStatus(2);
                if (!is_null($user->getLastLogin())) {
                    $user->setLastLogout(new \DateTime());
                    $user->setLastIpLogout(MailWorker::getIpAddress());
                }
                $user->setIsLogged(false);
                DBase::getEntityManager()->flush();
                return true;
            } else {
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_unavailable));
            }
        } catch (\Exception   $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return flase;
    }
}
