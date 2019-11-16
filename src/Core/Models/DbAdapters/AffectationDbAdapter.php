<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\Entities\Affectation;
use Digitalis\Core\Models\GAdapter;
use Digitalis\Core\Models\Lexique;

/**
 * AffectationDbAdapter Gestionnaire en relation avec la base de donnéees des affectations
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class AffectationDbAdapter extends GAdapter
{
	/**
	 * Permet de récupérer une affectation à partir de l'identifiant
	 *
	 * @param integer $id
	 * @return Affectation
	 */
	public static function getById($id)
	{
		return self::getFromDb(Affectation::class, $id);
	}

	/**
	 * Permet de récupérer les affectations en fonction des paramètres passés
	 *
	 * @param array $criteria
	 * @param array $orderBy
	 * @param integer $limit
	 * @param integer $offset
	 * @return Affectation[]
	 */
	public static function getAffectations(array $criteria = [], array $orderBy = [], $limit = null, $offset = null)
	{
		return self::getAll(Affectation::class, $criteria, $orderBy, $limit, $offset);
	}

	/**
	 * Permet de récupérer une affectation en fonction de l'entreprise et le partenaire financier
	 *
	 * @param integer $entId
	 * @param integer $partId
	 * @return Affectation
	 */
	public static function getByEntPart($entId, $partId)
	{
		$affectations = self::getAffectations(['entreprise' => $entId, 'partner' => $partId]);
		if (isset($affectations[0])) {
			return $affectations[0];
		}
		return null;
	}
	/**
	 * Permet d'enregistrer une affectation
	 *
	 * @param Affectation $affectation
	 * @return boolean
	 */
	public static function save($affectation)
	{
		try {
			$old = self::getByEntPart($affectation->getEntreprise()->getId(), $affectation->getPartner()->getId());
			if (is_null($old)) {
				DBase::getEntityManager()->persist($affectation);
				DBase::getEntityManager()->flush();
				return true;
			} else {
				Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_exist));
			}
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
		return false;
	}

	/**
	 * Permet de faire la mise à jour de l'affecation
	 *
	 * @param Affectation $affect
	 * @return boolean
	 */
	public static function update($affect)
	{
		return true;
	}

	/**
	 * Permet de supprimer une affectation
	 *
	 * @param integer $id
	 * @return boolean
	 */
	public static function remove($id)
	{
		return self::delete(Affectation::class, $id);
	}
}
