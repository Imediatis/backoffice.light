<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\Entities\Caisse;
use Digitalis\Core\Models\GAdapter;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;


/**
 * CaisseDbAdapter Gestionnaire en relation avec la base de données des caisses
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class CaisseDbAdapter extends GAdapter
{
    /**
     * Récupère une caisse en fonction de l'identifiant passé en paramètre
     *
     * @param integer $id
     * @return Caisse
     */
    public static function getById($id)
    {
        try {
            return self::getFromDb(Caisse::class, $id);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return null;
    }

    /**
     * Récupère une caisse en fonction du code passé en paramètre
     *
     * @param string $code
     * @return Caisse
     */
    public static function getByCode($code)
    {
        try {
            $reposit = DBase::getEntityManager()->getRepository(Caisse::class);
            return $reposit->findOneByCode($code);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return null;
    }

    /**
     * Récupère une caisse en fonction de la cké passée en paramètre
     *
     * @param string $key
     * @return Caisse
     */
    public static function getByKey($key)
    {
        try {
            $reposit = DBase::getEntityManager()->getRepository(Caisse::class);
            return $reposit->findOneByKey($key);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return null;
    }

    /**
     * Génère la clé pour une caisse
     *
     * @return string
     */
    public static function genCaisseKey()
    {
        $key = Data::randomString(8, 1, 1, 1);
        $old = self::getByKey($key);
        while (!is_null($old)) {
            $key = Data::randomString(8, 1, 1, 1);
            $old = self::getByKey($key);
        }
        return strtoupper($key);
    }

    /**
     * Pemet de mettre à jour la clé d'une caisse
     *
     * @param string $code Code de la caisse dont-il faut mettre à jour la clé
     * @return boolean
     */
    public static function changeBoxKey($code)
    {
        try {
            $caisse = self::getByCode($code);
            if ($caisse) {
                $caisse->setKey(self::genCaisseKey());
                DBase::getEntityManager()->flush();
                return true;
            } else {
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_unavailable));
            }
        } catch (\Exception $exc) {
            SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, an_error_occured));
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return false;
    }

    /**
     * Enregistre une caisse
     *
     * @param Caisse $caisse
     * @return boolean
     */
    public static function save($caisse)
    {
        try {
            $old = self::getByCode($caisse->getCode());
            if (!$old) {
                DBase::getEntityManager()->persist($caisse);
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
     * Met à jour une caisse
     *
     * @param Caisse $caisse
     * @return boolean
     */
    public static function update($caisse)
    {
        try {
            $old = self::getById($caisse->getId());
            if ($old) {
                $duplicate = self::getByCode($caisse->getCode());
                if (!is_null($duplicate) && $duplicate->getId() != $old->getId()) {
                    Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_exist));
                } else {
                    $old->setCode($caisse->getCode());
                    $old->setAgence($caisse->getAgence());
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
     * Permet d'ouvrir/fermer une caisse selon son état actuelle
     *
     * @param string $code
     * @return boolean
     */
    public static function openCloseBox($code)
    {
        try {
            $caisse = self::getByCode($code);
            if ($caisse) {
                if (!$caisse->getIsOpened() && !$caisse->getAgence()->getIsOpened()) {
                    Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'can-not-open-this-box-the'));
                } else {
                    $caisse->setIsOpened(!$caisse->getIsOpened());
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
     * Permet de récupérer les caisses selons les paramètres
     *
     * @param array $criteria
     * @param array $orderBy
     * @param integer $limit
     * @param integer $offset
     * @return Caisse[]|ArrayCollection
     */
    public static function getBoxes(array $criteria = [], array $orderBy = ['code' => 'asc'], $limit = null, $offset = null)
    {
        return self::getAll(Caisse::class, $criteria, $orderBy, $limit, $offset);
    }

    /**
     * Permet de retourner les caisses sous forme d'option pour la liste déroulante
     *
     * @param integer $selected
     * @param array $criteria
     * @param array $orderBy
     * @param string $defaultItem
     * @return string
     */
    public static function getBoxesForOptions($selected = null, $criteria = [], $orderBy = ['code' => 'asc'], $defaultItem = null)
    {
        $boxes = self::getBoxes($criteria, $orderBy);
        $defaultItem = !is_null($defaultItem) ? $defaultItem : Lexique::GetString(CUR_LANG, 'teller');

        $output = '<option value="">' . $defaultItem . '...</option>';

        foreach ($boxes as $value) {
            $isselected = $selected == $value->getId() ? "selected" : "";
            $output .= sprintf('<option value="%s" %s>%s</option>', $value->getId(), $isselected, $value->getCode());
        }
        return $output;
    }

    /**
     * retire l'opérateur d'une caisse
     *
     * @param string $code
     * @return boolean
     */
    public static function unsetBoxOperator($code)
    {
        try {
            $caisse = self::getByCode($code);
            if ($caisse) {
                $caisse->setOperator(null);
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

    /**
     * Met à jour le montant max journalier de la caisse
     *
     * @param integer $idCaisse Identifiant de la caisse
     * @param integer $montant Montant à définir
     * @return boolean
     */
    public static function updateMaxAmount($idCaisse, $montant, $statut)
    {
        try {
            if (is_int($montant) && $montant >= 0) {
                $caisse = self::getById($idCaisse);
                if ($caisse) {
                    $caisse->setmaxDailyAmount($montant);
                    $caisse->setStatus($statut);
                    DBase::getEntityManager()->flush();
                    return true;
                } else {
                    Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_unavailable));
                }
            } else {
                Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'invalid-amount'));
            }
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return false;
    }
}
