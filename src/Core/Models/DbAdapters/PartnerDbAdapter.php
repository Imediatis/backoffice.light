<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\Entities\Partner;
use Digitalis\Core\Models\GAdapter;
use Digitalis\Core\Models\Lexique;

/**
 * PartnerDbAdapter Gestionnair en relation avec la base de données
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class PartnerDbAdapter extends GAdapter
{
	/**
	 * Permet de récupérer un partenaire à partir de son code
	 *
	 * @param string $code
	 * @return Partner
	 */
	public static function getByCode($code)
	{
		try {
			$reposit = DBase::getEntityManager()->getRepository(Partner::class);
			return $reposit->findOneByCode($code);
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
	}

	/**
	 * Permet de récupérer un Partenaire à partir de son identifiant
	 *
	 * @param integer $id
	 * @return Partner
	 */
	public static function getById($id)
	{
		try {
			return self::getFromDb(Partner::class, $id);
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
	}

	/**
	 * Permet d'enregistrer le nouveau partenaire dans la bse de données
	 *
	 * @param Partner $npartner
	 * @return boolean
	 */
	public static function save($npartner)
	{
		try {
			$old = self::getByCode($npartner->getCode());
			if (!$old) {
				DBase::getEntityManager()->persist($npartner);
				DBase::getEntityManager()->flush();
				return true;
			} else {
				Data::setErrorMessage(CUR_LANG, data_exist);
			}
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
		return false;
	}

	/**
	 * Permet de mettre à jour le partenaire
	 *
	 * @param Partner $partner
	 * @return boolean
	 */
	public static function update($partner)
	{
		try {
			$old = self::getById($partner->getId());
			if ($old) {
				$dub = self::getByCode($old->getCode());
				if (!is_null($dub) && $dub->getId() == $old->getId()) {
					$old->setCode($partner->getCode());
					$old->setName($partner->getName());
					DBase::getEntityManager()->flush();
					return true;
				} else {
					Data::setErrorMessage(CUR_LANG, data_exist);
				}
			} else {
				Data::setErrorMessage(CUR_LANG, data_unavailable);
			}
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
		return false;
	}

	/**
	 * Permet de modifier le statut du partenaire (Activer/Désactiver)
	 *
	 * @param integer $id Identifiant du partenaire à mettre à jour
	 * @return boolean
	 */
	public static function setStatus($id)
	{
		try {
			$old = self::getById($id);
			if ($old) {
				$old->setStatus(!$old->getStatus());
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
	 * Permet de récupérer les partenaires selon les paramètres passés
	 *
	 * @param array $criteria
	 * @param array $orderBy
	 * @param integer $limit
	 * @param integer $offset
	 * @return Partner[]
	 */
	public static function getPartners(array $criteria = [], array $orderBy = ['code' => 'asc'], $limit = null, $offset = null)
	{
		return self::getAll(Partner::class, $criteria, $orderBy, $limit, $offset);
	}

	/**
	 * Permet de retourner les Partenaire financier sous forme d'option pour la liste déroulante
	 *
	 * @param integer $selected
	 * @param array $criteria
	 * @param array $orderBy
	 * @param string $defaultItem
	 * @return string
	 */
	public static function getPartnersForOptions($selected = null, $criteria = [], $orderBy = ['code' => 'asc'], $defaultItem = null)
	{
		$partners = self::getPartners($criteria, $orderBy);
		$defaultItem = !is_null($defaultItem) ? $defaultItem : Lexique::GetString(CUR_LANG, 'partner');

		$output = '<option value="">' . $defaultItem . '...</option>';

		foreach ($partners as $value) {
			$isselected = $selected == $value->getId() ? "selected" : "";

			$output .= sprintf('<option value="%d" %s>%s</option>', $value->getId(), $isselected, $value->getName());
		}
		return $output;
	}
}
