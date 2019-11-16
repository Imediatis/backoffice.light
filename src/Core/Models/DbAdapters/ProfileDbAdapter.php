<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\GAdapter;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\Entities\Profile;


/**
 * ProfileDbAdapter Gestionnaire de profile avec la base de données
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class ProfileDbAdapter extends GAdapter
{
	/**
	 * Permet d'enregistrer un nouveau profil
	 *
	 * @param Profile $profile
	 * @return boolean
	 */
	public static function save($profile)
	{
		try {
			$old = self::getByCode($profile->getCode());
			if (!$old) {
				DBase::getEntityManager()->persist($profile);
				DBase::getEntityManager()->flush();
				return true;
			} else {
				Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_exist));
			}
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
		}
		return false;
	}

	/**
	 * Permet de faire la mise à jour du profil
	 *
	 * @param Profile $profile
	 * @return boolean
	 */
	public static function update($profile)
	{
		try {
			$old = self::getFromDb(Profile::class, $profile->getId());
			if ($old) {
				$dcode = self::getByCode($profile->getCode());
				if ($old->getCode() == $profile->getCode()) {
					$old->setDescription($profile->getDescription());
					DBase::getEntityManager()->flush();
					return true;
				} else {
					if (!$dcode) {
						$old->setCode($profile->getCode());
						$old->setDescription($profile->getDescription());
						DBase::getEntityManager()->flush();
						return true;
					} else {
						Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_exist));
					}
				}
			} else {
				Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_unavailable));
			}
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
		}
		return false;
	}

	/**
	 * Permet de récupérer un profile à partir de son code
	 *
	 * @param string $code
	 * @return Profile
	 */
	public static function getByCode($code)
	{
		try {
			$reposit = DBase::getEntityManager()->getRepository(Profile::class);
			return $reposit->findOneByCode($code);
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
		}
		return null;
	}

	/**
	 * Active ou désactive un profil d'utilisateurf
	 *
	 * @param integer $id
	 * @return boolean
	 */
	public static function activate($id)
	{
		try {
			$profile = self::getById($id);
			if ($profile) {
				$profile->setStatus(!$profile->getStatus());
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
	 * Récupère le profile à partir de l'identifiant
	 *
	 * @param integer $id
	 * @return Profile|null
	 */
	public static function getById($id)
	{
		return self::getFromDb(Profile::class, $id);
	}

	/**
	 * Permet de récupérer tout les profiles
	 *
	 * @param array $creteria
	 * @param array $orderBy
	 * @param integer $limit
	 * @param integer $offset
	 * @return Profile[]|ArrayCollection
	 */
	public static function getProfiles($creteria = array(), $orderBy = array('code' => 'asc'), $limit = null, $offset = null)
	{
		return self::getAll(Profile::class, $creteria, $orderBy, $limit, $offset);
	}

	public static function getOptionForSelect($selected)
	{
		$output = '';
		$profiles = self::getProfiles();
		foreach ($profiles as $value) {
			$output .= sprintf('<option value="%d" %s>%s</option>', $value->getId(), ($value->getId() == $selected ? "selected" : ""), $value->getDescription());
		}
		return $output;
	}
}
