<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\Entities\City;
use Digitalis\Core\Models\GAdapter;
use Digitalis\Core\Models\Lexique;


/**
 * CityDbAdapter Gestionnaire en relation avec la base de données des villes
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class CityDbAdapter extends GAdapter
{
    /**
     * Permet d'enregistrer une région
     *
     * @param City $city
     * @return boolean
     */
    public static function save($city)
    {
        try {
            $old = self::getByCode($city->getCode());
            if (!$old) {
                DBase::getEntityManager()->persist($city);
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
     * @param City $city
     * @return boolean
     */
    public static function update($city)
    {
        try {
            $old = self::getById($city->getId());
            if ($old) {
                $duplicate = self::getByCode($city->getCode());
                if (!is_null($duplicate) && $duplicate->getId() != $old->getId()) {
                    Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_exist));
                } else {
                    $old->setCode($city->getCode());
                    $old->setLabel($city->getLabel());
                    $old->setRegion($city->getRegion());
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
     * @return City
     */
    public static function getById($id)
    {
        try {
            return self::getFromDb(City::class, $id);
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
     * @return City
     */
    public static function getByCode($code)
    {
        try {
            $reposit = DBase::getEntityManager()->getRepository(City::class);
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
     * @return City[]|ArrayCollection
     */
    public static function getCities(array $criteria = [], array $orderBy = ['code' => 'asc'], $limit = null, $offset = null)
    {
        $output = [];
        try {
            $output = self::getAll(City::class, $criteria, $orderBy, $limit, $offset);
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
    public static function getCitiesForOptions($selected = null, $criteria = [], $orderBy = ['code' => 'asc'], $defaultItem = null)
    {
        $cities = self::getCities($criteria, $orderBy);
        $defaultItem = !is_null($defaultItem) ? $defaultItem : Lexique::GetString(CUR_LANG, 'city');

        $output = '<option value="">' . $defaultItem . '...</option>';

        foreach ($cities as $value) {
            $isselected = $selected == $value->getId() ? "selected" : "";
            $output .= sprintf('<option value="%s" %s>%s</option>', $value->getId(), $isselected, $value->getCode());
        }
        return $output;
    }
}
