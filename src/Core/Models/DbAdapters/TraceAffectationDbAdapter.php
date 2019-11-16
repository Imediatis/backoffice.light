<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\Entities\TraceAffectation;
use Digitalis\Core\Models\GAdapter;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;


/**
 * TraceAffectationDbAdapter Gestionnaire des logs d'affectation avec la base de données
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class TraceAffectationDbAdapter extends GAdapter
{
	/**
	 * Obtient un enregistrement à partir de son identifiant
	 *
	 * @param integer $id
	 * @return TraceAffectation
	 */
	public static function getById($id)
	{
		return self::getFromDb(TraceAffectation::class, $id);
	}

	/**
	 * Permet d'enregistrer une trace d'affectation
	 *
	 * @param TraceAffectation $trace
	 * @return boolean
	 */
	public static function save($trace)
	{
		try {
			$trace->setUserAffect(SessionManager::getLoggedUser()->getLogin());
			DBase::getEntityManager()->persist($trace);
			DBase::getEntityManager()->flush();
			return true;
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
		return false;
	}

	/**
	 * Met à jour un enregistrement
	 *
	 * @param TraceAffectation $trace
	 * @return boolean
	 */
	public static function update($trace)
	{
		try {
			$old = self::getById($trace->getId());
			if ($old) {
				$old->setEndDate(new \DateTime());
				$old->setUserRemove(SessionManager::getLoggedUser()->getLogin());
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
	 * Retourne les traces de logs
	 *
	 * @param array $criteria
	 * @param array $orderBy
	 * @param integer $offSet
	 * @param integer $limit
	 * @return TraceAffectation[]|ArrayCollection
	 */
	public static function getTraces(array $criteria = [], array $orderBy = ['id' => 'asc'], $offSet = null, $limit = null)
	{
		return self::getAll(TraceAffectation::class, $criteria, $orderBy, $limit, $offSet);
	}

	/**
	 * Retourne une trace qui a été entamé et non bouclé
	 *
	 * @param string $operator Code de l'opérateur concerné
	 * @param string $caisse Code de la caisse concerné
	 * @return void
	 */
	public static function getStartedTrace($operator, $caisse)
	{
		try {
			$qb = DBase::getEntityManager()->createQueryBuilder();
			$lusers = $qb->select('t')
				->from('Digitalis\Core\Models\Entities\TraceAffectation', 't')
				->where('t.codeOperator=:operator AND t.codeCaisse = :caisse AND t.endDate is NULL')
				->setParameter(':operator', $operator)
				->setParameter(':caisse', $caisse)
				->getQuery()
				->getResult();
			if (count($lusers) >= 1) {
				return $lusers[0];
			}
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
		return null;
	}
}
