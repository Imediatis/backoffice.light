<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\Entities\TypePiece;
use Digitalis\Core\Models\GAdapter;
use Digitalis\Core\Models\Lexique;

/**
 * TypePieceDbAdapter Gestionnaire en relation avec la base de données des types de pièces
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class TypePieceDbAdapter extends GAdapter
{
	/**
	 * Récupère un type de pièce à partir de son identifiant
	 *
	 * @param integer $id
	 * @return TypePiece|null
	 */
	public static function getById($id)
	{
		return self::getFromDb(TypePiece::class, $id);
	}

	/**
	 * Récupère un type de pièce en fonction de son code
	 *
	 * @param string $code
	 * @return TypePiece|null
	 */
	public static function getByCode($code)
	{
		try {
			$reposit = DBase::getEntityManager()->getRepository(TypePiece::class);
			return $reposit->findOneByCode($code);
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
		return null;
	}

	/**
	 * Enregistre un type de pièce
	 *
	 * @param TypePiece $tpiece
	 * @return boolean
	 */
	public static function save($tpiece)
	{
		try {
			$old = self::getByCode($tpiece->getCode());
			if (!$old) {
				DBase::getEntityManager()->persist($tpiece);
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
	 * Met à jour un type de pièce
	 *
	 * @param TypePiece $tpiece
	 * @return boolean
	 */
	public static function update($tpiece)
	{
		try {
			$old = self::getById($tpiece->getId());
			if ($old) {
				$dupl = self::getByCode($tpiece->getCode());
				if ($dupl && $dupl->getId() != $old->getId()) {
					Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_exist));
				} else {
					$old->setCode($tpiece->getCode());
					$old->setLabel($tpiece->getLabel());
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
	 * Récupère les type des pièces en fonction des critères
	 *
	 * @param array $criteria
	 * @param array $orderBy
	 * @param integer $limit
	 * @param integer $offset
	 * @return TypePiece[]
	 */
	public static function getTypePieces(array $criteria = [], array $orderBy = ['code' => 'asc'], $limit = null, $offset = null)
	{
		return self::getAll(TypePiece::class, $criteria, $orderBy, $limit, $offset);
	}

	/**
	 * Récupère les types de pièce sous forme de tableau associatif
	 *
	 * @param array $criteria
	 * @param array $orderBy
	 * @return array
	 */
	public static function getAssoc(array $criteria = [], array $orderBy = ['code' => 'asc'])
	{
		$tpieces = self::getTypePieces($criteria, $orderBy);
		$output = [];
		foreach ($tpieces as $value) {
			$output[$value->getCode()] = $value->getLabel() . ' (' . $value->getCode() . ')';
		}
		return $output;
	}
}
