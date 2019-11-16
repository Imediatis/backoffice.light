<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Models\DbAdapters\ProfileDbAdapter;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\DbAdapters\CaisseDbAdapter;
use Digitalis\Core\Models\Entities\Operator;
use Digitalis\Core\Models\GAdapter;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\MailWorker;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Doctrine\ORM\Query\Expr\Join;
use Imediatis\EntityAnnotation\ModelState;


/**
 * OperatorDbAdapter Gestionnaire en relation des base de données des utilisateurs des agences
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class OperatorDbAdapter extends GAdapter
{
	const DEF_PWD = '220512cdce';

	/**
	 * Enregistre l'utilisateur dans le système
	 *
	 * @param Operator $operator
	 * @return boolean
	 */
	public static function save($operator)
	{
		try {
			$old = self::getByLogin($operator->getLogin());
			if (!$old) {
				$profile = ProfileDbAdapter::getByCode($operator->getProfile()->getCode());
				if ($profile) {
					$operator->setProfile($profile);
					DBase::getEntityManager()->persist($operator);
					DBase::getEntityManager()->flush();
					return true;
				} else {
					Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'user-profile-is-not-set-'));
				}
			} else {
				ModelState::setMessage('login_ope', Lexique::GetString(CUR_LANG, 'this-login-code-operator-'));
				Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'this-login-code-operator-'));
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
	 * @param Operator $operator
	 * @return boolean
	 */
	public static function update($operator)
	{
		try {
			$old = self::getById($operator->getId());
			$dlogin = self::getByLogin($operator->getLogin());

			if ($old) {
				if ($dlogin && $dlogin->getId() != $old->getId()) {
					ModelState::setMessage('login_ope', Lexique::GetString(CUR_LANG, 'this-login-code-operator-'));
					Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'this-login-code-operator-'));
				} else {
					$old->setLogin($operator->getLogin());
					$old->setFirstName($operator->getFirstName());
					$old->setLastName($operator->getLastName());
					$old->setEmail($operator->getEmail());
					$old->setProfile($operator->getProfile());
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
			$operator = self::getByLogin($login);
			$profile = ProfileDbAdapter::getByCode($codeProfile);
			if ($operator && $profile) {
				$operator->setProfile($profile);
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
			$operator = self::getByLogin($login);
			if ($operator) {
				$operator->setLastLogin(new \DateTime());
				$operator->setLastIpLogin(MailWorker::getIpAddress());
				$operator->setIsLogged(true);
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
			$operator = self::getByLogin($login);
			if ($operator) {
				if ($operator->getLastIpLogin() == MailWorker::getIpAddress()) {
					$operator->setLastLogout(new \DateTime());
					$operator->setLastIpLogout(MailWorker::getIpAddress());
					$operator->setIsLogged(false);
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
			$operator = self::getByLogin($login);
			if ($operator) {
				if (!is_null($operator->getLastLogin())) {
					$operator->setLastLogout(new \DateTime());
					$operator->setLastIpLogout(MailWorker::getIpAddress());
				}
				$operator->setIsLogged(false);
				DBase::getEntityManager()->flush();
				return $operator;
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
	 * @return Operator
	 */
	public static function getByLogin($login)
	{
		try {
			$repos = DBase::getEntityManager()->getRepository(Operator::class);
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
	 * @return Operator
	 */
	public static function getById($id)
	{
		return self::getFromDb(Operator::class, $id);
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
			$operator = self::getByLogin($login);
			if ($operator) {
				$statut = ($operator->getStatus() == 1 || $operator->getStatus() == 2) ? 0 : (is_null($operator->getLastLogin()) ? 2 : 1);

				$operator->setStatus($statut);
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
			$operator = self::getByLogin($login);
			if ($operator) {
				$operator->setStatus(0);
				$operator->setIsLogged(false);
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
	 * @return Operator
	 */
	public static function checkLogin($login)
	{
		try {
			$suser = SessionManager::getLoggedUser();
			$qb = DBase::getEntityManager()->createQueryBuilder();
			$lusers = $qb->select('o')
				->from('Digitalis\Core\Models\Entities\Operator', 'o')
				->innerJoin('Digitalis\Core\Models\Entities\Profile', 'p', Join::WITH, 'o.profile=p.id')
				->where('o.login=:login AND o.status IN (1,2) AND p.status=1')
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

	/**
	 * Permet de récupérer un utilisateur en fonction de son login et mot de passe
	 *
	 * @param string $login
	 * @param string $pwd
	 * @return Operator
	 */
	public static function getByPwdLogin($login, $pwd)
	{
		try {
			$suser = SessionManager::getLoggedUser();
			$qb = DBase::getEntityManager()->createQueryBuilder();
			$lusers = $qb->select('o')
				->from('Digitalis\Core\Models\Entities\Operator', 'o')
				->where('o.login=:login AND o.status IN (1,2)  AND o.password=:pwd')
				->setParameter(':login', $login)
				->setParameter(':pwd', $pwd)
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
			$operator = self::getByLogin($login);
			if ($operator) {
				return ($operator->getLastLogout() > $operator->getLastLogin()) || in_array($operator->getStatus(), [0, 2]) || ($operator->getLastIpLogout() != MailWorker::getIpAddress() || !$operator->getIsLogged());
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
			$operator = self::getByLogin($login);
			if ($operator) {
				$operator->setLastAction(new \DateTime());
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
			$operator = self::getByLogin($login);
			if ($operator) {
				$operator->setPassword(Data::cryptPwd(self::DEF_PWD));
				$operator->setStatus(2);
				if (!is_null($operator->getLastLogin())) {
					$operator->setLastLogout(new \DateTime());
					$operator->setLastIpLogout(MailWorker::getIpAddress());
				}
				$operator->setIsLogged(false);
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

	/**
	 * Permet d'affecter un opérateur à une caisse
	 *
	 * @param integer $idOperator
	 * @param integer $idCaisse
	 * @return boolean
	 */
	public static function setOperatorBox($idOperator, $idCaisse)
	{
		try {
			$operator = self::getById($idOperator);
			$caisse = CaisseDbAdapter::getById($idCaisse);
			if (!is_null($operator) && !is_null($caisse)) {
				$oldCaisseOperator = CaisseDbAdapter::getBoxes(['operator' => $idOperator]);
				if (isset($oldCaisseOperator[0])) {
					$first = $oldCaisseOperator[0];
					$first->setOperator(null);
				}

				$caisse->setOperator($operator);
				DBase::getEntityManager()->flush();
				return true;
			} else {
				Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'unsuffisant-parameters'));
			}
		} catch (\Exception $exc) {
			//Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
		return false;
	}

	/**
	 * Permet de dissocier un opérateur d'une caisse
	 *
	 * @param string $login
	 * @return boolean
	 */
	public static function unsetOperatorBox($login)
	{
		try {
			$operator = self::getByLogin($login);
			if (!is_null($operator)) {
				$caisse = $operator->getCaisse();
				if ($caisse) {
					$caisse->setOperator(null);
				}
				DBase::getEntityManager()->flush();
				return true;
			} else {
				Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'unsuffisant-parameters'));
			}
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
		return false;
	}
}
