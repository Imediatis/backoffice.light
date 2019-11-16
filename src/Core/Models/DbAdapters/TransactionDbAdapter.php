<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\Entities\Transaction;
use Digitalis\Core\Models\GAdapter;

/**
 * TransactionDbAdapter Gestionnaire en relation avec la base de données des transactions des clients
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class TransactionDbAdapter extends GAdapter
{
	/**
	 * Permet de récupérer la transaction à partir de son identifiant
	 *
	 * @param integer $id
	 * @return Transaction
	 */
	public static function getById($id)
	{
		return self::getFromDb(Transaction::class, $id);
	}

	/**
	 * Permet de récupérer la transaction à partir de son numéro de référence
	 *
	 * @param string $ref
	 * @return Transaction
	 */
	public static function getByReference($ref)
	{
		try {
			$reposit = DBase::getEntityManager()->getRepository(Transaction::class);
			return $reposit->findOneByReference($ref);
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
		return null;
	}

	/**
	 * Permet de générer la référence d'une transaction
	 *
	 * @param string $codeCaisse Code de la caisse qui valide la transaction
	 * @return string
	 */
	public static function genRererence($codeCaisse)
	{
		$mask = "%s.%s.%s";
		try {
			$ref = sprintf($mask, $codeCaisse, (new \DateTime())->format("ymdHi"), Data::randomString(2, true));
			while (self::getByReference($ref)) {
				$ref = sprintf($mask, $codeCaisse, (new \DateTime())->format("ymdHi"), Data::randomString(2, true));
			}
			return $ref;
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
		return null;
	}

	/**
	 * Permet d'enregistrer la transaction dans la base de données
	 *
	 * @param Transaction $trans
	 * @return boolean
	 */
	public static function save($trans)
	{
		try {
			$trans->setReference(self::genRererence($trans->getCaisse()->getCode()));
			DBase::getEntityManager()->persist($trans);
			DBase::getEntityManager()->flush();
			return true;
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
		return false;
	}

	public static function update($trans)
	{
		return true;
	}

	/**
	 * Permet de récupére les transactions en fonction des critères passé en paramètres
	 *
	 * @param array $criteria
	 * @param array $orderBy
	 * @param integer $limit
	 * @param integer $offset
	 * @return Transaction[]
	 */
	public static function getTransactions(array $criteria = [], array $orderBy = ['transDate' => 'desc'], $limit = null, $offset = null)
	{
		return self::getAll(Transaction::class, $criteria, $orderBy, $limit, $offset);
	}
}
