<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\Entities\Region;
use Digitalis\Core\Models\GAdapter;
use Digitalis\Core\Models\Lexique;


/**
 * RegionDbAdapter Gestionnaire en relation avec la base de données des régions
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class RegionDbAdapter extends GAdapter
{
    /**
     * Permet d'enregistrer une région
     *
     * @param Region $region
     * @return boolean
     */
    public static function save($region)
    {
        try {
            $old = self::getByCode($region->getCode());
            if (!$old) {
                DBase::getEntityManager()->persist($region);
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
     * Permet de faire la mise à jour de la région
     *
     * @param Region $region
     * @return boolean
     */
    public static function update($region)
    {
        try {
            $old = self::getById($region->getId());
            if ($old) {
                $duplicate = self::getByCode($region->getCode());
                if (!is_null($duplicate) && $duplicate->getId() != $old->getId()) {
                    Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_exist));
                } else {
                    $old->setCode($region->getCode());
                    $old->setLabel($region->getLabel());
                    $old->setCountry($region->getCountry());
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
     * Récupère une région en fonction de son identifiant
     *
     * @param integer $id
     * @return Region
     */
    public static function getById($id)
    {
        try {
            return self::getFromDb(Region::class, $id);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return null;
    }

    /**
     * Récupère la région en fonction du code passé en paramètre
     *
     * @param string $code
     * @return Region
     */
    public static function getByCode($code)
    {
        try {
            $reposit = DBase::getEntityManager()->getRepository(Region::class);
            return $reposit->findOneByCode($code);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return null;
    }

    /**
     * Récupère les régions
     *
     * @param array $criteria
     * @param array $orderBy
     * @param integer $limit
     * @param integer $offset
     * @return Region[]|ArrayCollection
     */
    public static function getRegions(array $criteria = [], array $orderBy = ['code' => 'asc'], $limit = null, $offset = null)
    {
        $output = [];
        try {
            $output = self::getAll(Region::class, $criteria, $orderBy, $limit, $offset);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return $output;
    }

    /**
     * Permet de retourner les pays sous forme d'option pour la liste déroulante
     *
     * @param integer $selected
     * @param array $criteria
     * @param array $orderBy
     * @param string $defaultItem
     * @return string
     */
    public static function getRegionsForOptions($selected = null, $criteria = [], $orderBy = ['code' => 'asc'], $defaultItem = null)
    {
        $regions = self::getRegions($criteria, $orderBy);
        $defaultItem = !is_null($defaultItem) ? $defaultItem : Lexique::GetString(CUR_LANG, 'region');

        $output = '<option value="">' . $defaultItem . '...</option>';

        foreach ($regions as $value) {
            $isselected = $selected == $value->getId() ? "selected" : "";

            $output .= sprintf('<option value="%s" %s>%s</option>', $value->getId(), $isselected, $value->getCode());
        }
        return $output;
    }
}
