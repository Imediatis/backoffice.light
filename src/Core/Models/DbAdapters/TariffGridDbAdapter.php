<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\Entities\TariffGrid;
use Digitalis\Core\Models\GAdapter;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;

/**
 * GrilleTariffaireDbAdapter Gestionnaire en relation avec la base de données de la grille tariffaire
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class TariffGridDbAdapter extends GAdapter
{

	/**
	 * Permet de récupérer la grille à partir de son identifiant
	 *
	 * @param integer $id
	 * @return TariffGrid
	 */
	public static function getById($id)
	{
		return self::getFromDb(TariffGrid::class, $id);
	}

	/**
	 * Permet de récupérer l'éventuel grille de chauvechement de la grille à enregistrer
	 *
	 * @param TariffGrid $grille
	 * @return TariffGrid[]|ArrayCollection
	 */
	public static function getChevauche($grille)
	{
		try {
			$qb = DBase::getEntityManager()->createQueryBuilder();
			$grilles = $qb->select('g')
				->from('Digitalis\Core\Models\Entities\TariffGrid', 'g')
				->where('((g.min<=:min AND g.max>=:min) OR (g.min<=:max AND g.max>=:max)) AND g.entreprise=:entId')
				->setParameter(':min', $grille->getMin())
				->setParameter(':max', $grille->getMax())
				->setParameter(':entId', $grille->getEntreprise()->getId())
				->getQuery()
				->getResult();
			if (count($grilles) > 0) {
				return $grilles;
			} else {
				return null;
			}
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
		return null;
	}

	/**
	 * Permet d'enregistrer la grille
	 *
	 * @param TariffGrid $grille
	 * @return boolean
	 */
	public static function save($grille)
	{
		try {
			$chevauch = self::getChevauche($grille);
			if (is_null($chevauch)) {
				$grille->setUserCreate(SessionManager::getLoggedUser()->getLogin());
				DBase::getEntityManager()->persist($grille);
				DBase::getEntityManager()->flush();
				return true;
			} else {
				Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'invalid-grid-line-'));
			}
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
		return false;
	}

	/**
	 * Met à jour la grille
	 *
	 * @param TariffGrid $var
	 * @return boolean
	 */
	public static function update($var)
	{
		return true;
	}
}
