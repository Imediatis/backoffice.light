<?php
namespace Digitalis\Core\Models\DbAdapters;

use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\DbAdapters\CaisseDbAdapter;
use Digitalis\Core\Models\Entities\Affectation;
use Digitalis\Core\Models\Entities\Agence;
use Digitalis\Core\Models\Entities\Caisse;
use Digitalis\Core\Models\Entities\Entreprise;
use Digitalis\Core\Models\Entities\Operator;
use Digitalis\Core\Models\Entities\Partner;
use Digitalis\Core\Models\GAdapter;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Debug\Exception\FatalErrorException;

/**
 * AgenceDbAdapter Gestionnaire en relation avec la base de données des agences
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class AgenceDbAdapter extends GAdapter
{
    /**
     * Récupérer une agence en fonction de son identifiant
     *
     * @param integer $id Identifiant de l'agence qu'on veut récupérer
     * @return Agence
     */
    public static function getById($id)
    {
        try {
            return self::getFromDb(Agence::class, $id);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return null;
    }

    /**
     * Récupère une agence en fonction de son code
     *
     * @param string $code Code de l'agence à récupérer
     * @return Agence
     */
    public static function getByCode($code)
    {
        try {
            $repository = DBase::getEntityManager()->getRepository(Agence::class);
            return $repository->findOneByCode($code);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return null;
    }

    /**
     * Récupère une agence en fonction de la clé passé en paramètre
     *
     * @param string $key
     * @return Agence
     */
    public static function getByKey($key)
    {
        try {
            $reposit = DBase::getEntityManager()->getRepository(Agence::class);
            return $reposit->findOneByKey($key);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return null;
    }

    /**
     * Permet de générer la clé de l'agence
     *
     * @return string
     */
    public static function genAgenceKey()
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
     * Pemet de mettre à jour la clé d'une agence
     *
     * @param string $code Code de l'agence dont-il faut mettre à jour la clé
     * @return boolean
     */
    public static function changeAgenceKey($code)
    {
        try {
            $agence = self::getByCode($code);
            if ($agence) {
                $agence->setKey(self::genAgenceKey());
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
     * Permet d'enregistre une agence
     *
     * @param Agence $agence
     * @return boolean
     */
    public static function save($agence)
    {
        try {
            $old = self::getByCode($agence->getCode());
            if (!$old) {
                DBase::getEntityManager()->persist($agence);
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
     * Permet de faire la mise à jour d'une agence
     *
     * @param Agence $agence
     * @return boolean
     */
    public static function update($agence)
    {
        try {
            $old = self::getById($agence->getId());
            if ($old) {
                $duplicate = self::getByCode($agence->getCode());
                if (!is_null($duplicate) && $duplicate->getId() != $old->getId()) {
                    Data::setErrorMessage(Lexique::GetString(CUR_LANG, data_exist));
                } else {
                    $old->setCode($agence->getCode());
                    $old->setLabel($agence->getLabel());
                    $old->setAddress($agence->getAddress());
                    $old->setPhone1($agence->getPhone1());
                    $old->setPhone2($agence->getPhone2());
                    $old->setEmail($agence->getEmail());
                    $old->setEntreprise($agence->getEntreprise());
                    $old->setCity($agence->getCity());
                    $codecaisses = $old->getCodeCaisses();
                    foreach ($agence->getCaisses() as $item) {
                        if (!in_array($item->getCode(), $codecaisses)) {
                            $old->addCaisse($item);
                        }
                    }
                    DBase::getEntityManager()->flush();
                    return true;
                }
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
     * Permet de récupérer les agences selon les paramètres passés
     *
     * @param array $criteria Les options de filtre du résultal. c'est un tableau associatif ces champ de la classe avec les valeurs recherché
     * @param array $orderBy Les options de tri du résultat; tableau associatif ['code'=>'asc'] par défaut
     * @param integer $limit Nombre d'élément à récupérer
     * @param integer $offset Page à partir de laquelle les éléments doivent être récupéré
     * @return Agence[]|ArrayCollection
     */
    public static function getAgences(array $criteria = [], array $orderBy = ['code' => 'asc'], $limit = null, $offset = null)
    {
        return self::getAll(Agence::class, $criteria, $orderBy, $limit, $offset);
    }

    /**
     * Permet de retourner les agences sous forme d'option pour la liste déroulante
     *
     * @param integer $selected
     * @param array $criteria
     * @param array $orderBy
     * @param string $defaultItem
     * @return string
     */
    public static function getBranchesForOptions($selected = null, $criteria = [], $orderBy = ['code' => 'asc'], $defaultItem = null)
    {
        $branches = self::getAgences($criteria, $orderBy);
        $defaultItem = !is_null($defaultItem) ? $defaultItem : Lexique::GetString(CUR_LANG, 'branch');

        $output = '<option value="">' . $defaultItem . '...</option>';

        foreach ($branches as $value) {
            $isselected = $selected == $value->getId() ? "selected" : "";
            $output .= sprintf('<option value="%s" %s>%s</option>', $value->getId(), $isselected, $value->getLabel() . ' (' . $value->getCode() . ')');
        }
        return $output;
    }

    /**
     * Détermine si des caisses d'une agence sont ouverte
     *
     * @param string $branchCode
     * @return boolean
     */
    public static function hasOpenedBoxes($branchCode)
    {
        $queryBuilder = DBase::getEntityManager()->createQueryBuilder();

        $nbCaisse = $queryBuilder->select('COUNT(c.id)')
            ->from(Caisse::class, 'c')
            ->innerJoin(Agence::class, 'a', Join::WITH, 'c.agence=a.id')
            ->where('a.code=:code AND c.isOpened=1')
            ->setParameter(':code', $branchCode)
            ->getQuery()
            ->getSingleScalarResult();

        return $nbCaisse > 0;
    }

    public static function operatorWorkWithThisBranch($branchCode, $login)
    {
        $qb = DBase::getEntityManager()->createQueryBuilder();
        $nb = $qb->select('COUNT(o.id)')
            ->from(Operator::class, 'o')
            ->innerJoin(Caisse::class, 'c', Join::WITH, 'c.operator=o.id')
            ->innerJoin(Agence::class, 'a', Join::WITH, 'c.agence=a.id')
            ->where('a.code=:code AND o.login=:login')
            ->setParameter(':code', $branchCode)
            ->setParameter(':login', $login)
            ->getQuery()
            ->getSingleScalarResult();
        return $nb == 1;
    }

    /**
     * Permet d'ouvrir/fermer une agence selon son état actuelle
     *
     * @param string $code
     * @return boolean
     */
    public static function openCloseBranch($code)
    {
        try {
            $agence = self::getByCode($code);

            if ($agence) {
                if ($agence->getIsOpened()) {
                    $openedCaisse = CaisseDbAdapter::getBoxes(['agence' => $agence->getId(), 'isOpened' => 1]);
                    if (count($openedCaisse) > 0) {
                        Data::setErrorMessage(Lexique::GetString(CUR_LANG, 'can-not-close-this-branch'));
                        return false;
                    }
                }
                $agence->setIsOpened(!$agence->getIsOpened());
                DBase::getEntityManager()->flush();
                return true;
            }
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return false;
    }

    /**
     * Permet de récupérer les partenaires avec lesquelles l'agence peut travailler. ce sont les partenaire de l'entreprise qui porte l'agence
     *
     * @param string $branchCode
     * @return Partner[]
     */
    public static function getPartners($branchCode)
    {
        $output = [];
        try {
            $qb = DBase::getEntityManager()->createQueryBuilder();
            $output = $qb->select('p')
                ->from(Agence::class, 'b')
                ->innerJoin(Entreprise::class, 'e', Join::WITH, 'b.entreprise=e.id')
                ->join('e.affectations', 'a')
                ->innerJoin(Partner::class, 'p', Join::WITH, 'a.partner=p.id')
                ->where('p.status=1 AND b.code=:code')
                ->setParameter(':code', $branchCode)
                ->getQuery()
                ->getResult();
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        return $output;
    }
}
