<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\Entities\Entreprise;
use Digitalis\Core\Models\GAdapter;
use Digitalis\Core\Models\Lexique;


/**
 * EntrepriseDbAdapter Gestionnaire en relation avec la base de données des entreprises
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class EntrepriseDbAdapter extends GAdapter
{
    /**
     * Récupère une entreprise en fonction de son identifiant
     *
     * @param integer $id
     * @return Entreprise
     */
    public static function getById($id)
    {
        try {
            return self::getFromDb(Entreprise::class, $id);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return null;
    }

    /**
     * Récupère une entreprise en fonction de la référence passé en paramètre
     *
     * @param string $ref
     * @return Entreprise
     */
    public static function getByReference($ref)
    {
        try {
            $reposit = DBase::getEntityManager()->getRepository(Entreprise::class);
            return $reposit->findOneByReference($ref);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return null;
    }

    /**
     * Récupère une entreprise en fonction de son nom de domaine
     *
     * @param string $domain
     * @return Entreprise
     */
    public static function getByDomain($domain)
    {
        try {
            $reposit = DBase::getEntityManager()->getRepository(Entreprise::class);
            return $reposit->findOneByDomain($domain);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return null;
    }

    /**
     * Détermine si une occurence d'entreprise existe déjà en se basant sur la référence, le nom de domaine et l'identifiant (dans le cas d'une mise à jour)
     *
     * @param string $ref
     * @param string $domain
     * @param integer $id
     * @return boolean
     */
    public static function isDuplicate($ref, $domain, $id = null)
    {
        try {
            $qb = DBase::getEntityManager()->createQueryBuilder();
            $entrep = $qb->select('e')
                ->from('Digitalis\Core\Models\Entities\Entreprise', 'e')
                ->where('(e.reference=:ref OR e.domain=:domain)');

            if (!is_null($id)) {
                $entrep = $entrep->andWhere('e.id!=:id')->setParameter(':id', $id);
            }

            $entrep = $entrep->setParameter(':ref', $ref)
                ->setParameter(':domain', $domain)
                ->getQuery()
                ->getResult();
            return count($entrep) >= 1;
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return false;
    }

    /**
     * Récupère toutes les entreprises selon les critères passé en paramètre
     *
     * @param array $criteria
     * @param array $orderBy
     * @param integer $limit
     * @param integer $offset
     * @return Entreprise[]|ArrayCollection
     */
    public static function getEntreprises(array $criteria = [], array $orderBy = ['name' => 'asc'], $limit = null, $offset = null)
    {
        return self::getAll(Entreprise::class, $criteria, $orderBy, $limit, $offset);
    }

    /**
     * Enregistre une entreprise
     *
     * @param Entreprise $entrep
     * @return boolean
     */
    public static function save($entrep)
    {
        try {
            if (!self::isDuplicate($entrep->getReference(), $entrep->getDomain())) {
                DBase::getEntityManager()->persist($entrep);
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
     * Met à jour une entreprise
     *
     * @param Entreprise $entrep
     * @return boolean
     */
    public static function update($entrep)
    {
        try {
            $old = self::getById($entrep->getId());
            if ($old) {
                if (!self::isDuplicate($entrep->getReference(), $entrep->getDomain(), $old->getId())) {
                    $old->setAddress($entrep->getAddress());
                    $old->setCity($entrep->getCity());
                    $old->setDomain($entrep->getDomain());
                    $old->setReference($entrep->getReference());
                    $old->setName($entrep->getName());
                    $old->setPhone1($entrep->getPhone1());
                    $old->setPhone2($entrep->getPhone2());
                    $old->setEmail1($entrep->getEmail1());
                    $old->setEmail2($entrep->getEmail2());
                    DBase::getEntityManager()->flush();
                    return true;
                } else {
                    Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_exist));
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
     * Permet de retourner les Entreprise sous forme d'option pour la liste déroulante
     *
     * @param integer $selected
     * @param array $criteria
     * @param array $orderBy
     * @param string $defaultItem
     * @return string
     */
    public static function getEnterprisesForOptions($selected = null, $criteria = [], $orderBy = ['name' => 'asc'], $defaultItem = null)
    {
        $entreprises = self::getEntreprises($criteria, $orderBy);
        $defaultItem = !is_null($defaultItem) ? $defaultItem : Lexique::GetString(CUR_LANG, 'enterprise');

        $output = '<option value="">' . $defaultItem . '...</option>';

        foreach ($entreprises as $value) {
            $isselected = $selected == $value->getId() ? "selected" : "";

            $output .= sprintf('<option value="%d" %s>%s</option>', $value->getId(), $isselected, $value->getName());
        }
        return $output;
    }
}
