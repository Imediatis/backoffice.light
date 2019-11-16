<?php
namespace Digitalis\Core\Models;

use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\Lexique;




/**
 * GEntity Classe généralisant les methodes standard des entités
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
abstract class GAdapter
{
    public static abstract function save($objet);
    public static abstract function update($nobjet);
    /**
     * Permet de récupérer un élément de la base de données
     *
     * @param string $className Nom de la classe dont il faut récupérer l'élément en fonction de son identification
     * @param mixed $id
     * @return mixed
     */
    protected static function getFromDb($className, $id)
    {
        if (is_null($id)) {
            return null;
        }
        try {
            return DBase::getEntityManager()->find($className, $id);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return null;
    }

    /**
     * Permet de récupérer toutes/une partie des occurences d'une entité dont le nom est passé en paramètre
     *
     * @param string $className Nom de la classe dont on veut récupérer les éléments
     * @param array $criteria critère de filtre des données à récupérer
     * @param array $orderBy Tableau d'ordre de tri des données
     * @param integer $limit Le nombre d'élément à récupérer. 0 pour signifier tout
     * @param integer $offset Page à partir de la quelle il faut récupérer les données. 0 première page
     * @return mixed
     */
    protected static function getAll($className, array $criteria = array(), array $orderBy = array(), $limit = null, $offset = null)
    {
        $out = [];
        try {
            $repos = DBase::getEntityManager()->getRepository($className);
            $data = $repos->findAll();
            $out = $repos->findBy($criteria, $orderBy, $limit, $offset);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return $out;
    }

    /**
     * Supprime l'objet dont l'identifiant est renseigner de la base de données.
     * en cas d'erreur lors du traitement un message d'erreur est enregistré dans dans la classe Data
     * pour avoir ce message Il suffit de faire appelle à cette fonction : <b>Data::getErrorMessage()</b>;
     * @param string $className nom de la classe de l'objet à supprimer
     * @param mixed $id Identifiant de l'objet à supprimer
     * @return boolean
     */
    public static function delete($className, $id)
    {
        try {
            if (is_null($id)) {
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'identifier-can-not-be-nul'));
                return false;
            }
            $old = static::getFromDb($className, $id);
            if ($old) {
                DBase::getEntityManager()->remove($old);
                DBase::getEntityManager()->flush();
                return true;
            } else {
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_unavailable));
                return false;
            }
        } catch (\Exception $exc) {
            ErrorHandler::writeLog($exc);
            if (Data::isUsedData($exc->getMessage())) {
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'unable-to-delete'));
            } else {
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            }
            return false;
        }
    }

    public function toJson()
    {
        return json_encode(get_object_vars($this));
    }

    public function __toString()
    {
        return $this->toJson();
    }
}
