<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\GAdapter;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;


/**
 * LogBranchBoxDbAdapter gestionnaire des logs des caisses et agences
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class LogBranchBoxDbAdapter extends GAdapter
{

	/**
	 * Enregistre un log
	 *
	 * @param \Digitalis\Core\Models\Entities\LogBranchBox $log
	 * @return boolean
	 */
	public static function save($log)
	{
		try {
			DBase::getEntityManager()->persist($log);
			DBase::getEntityManager()->flush();
			return true;
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
		}
		return false;
	}

	public static function update($logaction)
	{ }
}
