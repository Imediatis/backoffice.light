<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\Entities\Country;
use Digitalis\Core\Models\GAdapter;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;

/**
 * CountryDbAdapter Gestionnaire des pays avec la base de donnnées
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class CountryDbAdapter extends GAdapter
{
    /**
     * Enregistre le pays dans la base de données
     *
     * @param Country $country
     * @return boolean
     */
    public static function save($country)
    {
        try {
            $old = self::getByCode($country->getAlpha2(), $country->getAlpha3());
            if (is_null($old)) {
                DBase::getEntityManager()->persist($country);
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
     * Permet de faire la mise à jour du pays dans la base de données
     *
     * @param Country $country
     * @return boolean
     */
    public static function update($country)
    {
        try {
            $old = self::getById($country->getId());
            if ($old) {
                $dub = self::getByCode($country->getAlpha2(), $country->getAlpha3());
                if (!is_null($dub) && $dub->getId() != $old->getId()) {
                    Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_exist));
                } else {
                    $old->setAlpha2($country->getAlpha2());
                    $old->setAlpha3($country->getAlpha3());
                    $old->setEnName($country->getEnName());
                    $old->setFrName($country->getFrName());
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
    }

    /**
     * Récupère un pays en fonction du code
     *
     * @param string $code
     * @return Country
     */
    public static function getByCode($alpha2, $alpha3)
    {
        try {
            $qb = DBase::getEntityManager()->createQueryBuilder();
            $lusers = $qb->select('c')
                ->from('Digitalis\Core\Models\Entities\Country', 'c')
                ->where('c.alpha2=:code2 OR c.alpha3=:code3')
                ->setParameter(':code2', $alpha2)
                ->setParameter(':code3', $alpha3)
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

    /**
     * Récupère un pays en fonction de son pays
     *
     * @param integer $id
     * @return Country
     */
    public static function getById($id)
    {
        try {
            return self::getFromDb(Country::class, $id);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return null;
    }

    /**
     * Récupère les pays du système
     *
     * @param array $criteria
     * @param array $orderBy
     * @param integer $limit
     * @param integer $offset
     * @return Country[]|ArrayCollection
     */
    public static function getCountries(array $criteria = [], array $orderBy = ['alpha3' => 'asc'], $limit = null, $offset = null)
    {
        $output = [];
        try {
            $output = self::getAll(Country::class, $criteria, $orderBy, $limit, $offset);
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
    public static function getCountriesForOptions($selected = null, $criteria = [], $orderBy = ['alpha3' => 'asc'], $defaultItem = null)
    {
        $countries = self::getCountries($criteria, $orderBy);
        $defaultItem = !is_null($defaultItem) ? $defaultItem : Lexique::GetString(CUR_LANG, 'country');

        $output = '<option value="">' . $defaultItem . '...</option>';

        foreach ($countries as $value) {
            $isselected = $selected == $value->getId() ? "selected" : "";
            $name = strtolower(CUR_LANG) == 'en' ? $value->getEnName() : $value->getFrName();

            $output .= sprintf('<option value="%s" %s>%s</option>', $value->getId(), $isselected, $name);
        }
        return $output;
    }
}
