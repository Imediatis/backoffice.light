<?php
namespace Digitalis\Core\Models\Entities;

use Digitalis\Core\Models\DbAdapters\CaisseDbAdapter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Caisse Caisse du système
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="boxes")
 */
class Caisse
{
    /**
     * Identifiant de la caisse
     * 
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="box_id",type="integer")
     * @var integer
     */
    private $id;

    /**
     * code de la caisse découle du code de l'agence
     * 
     * @ORM\Column(name="box_code",length=15,unique=true)
     * @var string
     */
    private $code;

    /**
     * Clé de sécurité de la caisse
     * 
     * @ORM\Column(name="box_key",length=12,unique=true)
     * @var string
     */
    private $key;

    /**
     * Statut de la caisse
     * 
     * @ORM\Column(name="box_status",type="smallint",options={"default":1})
     * @var integer
     */
    private $status;

    /**
     * Détermine si la caisse est ouverte ou pas
     * 
     * @ORM\Column(name="box_isopened",type="boolean",options={"default":false})
     * @var boolean
     */
    private $isOpened;

    /**
     * Date de création de la caisse
     * 
     * @ORM\Column(name="box_datecreate",type="datetime")
     * @var \DateTime
     */
    private $dateCreate;

    /**
     * Personne qui crée la caisse
     * 
     * @ORM\Column(name="box_usercreate",length=30,nullable=true)
     * @var string
     */
    private $userCreate;

    /**
     * Opérateur affecté à cette caisse
     * 
     * @ORM\OneToOne(targetEntity=\Digitalis\Core\Models\Entities\Operator::class,cascade={"persist","merge","refresh"}, inversedBy="caisse")
     * @ORM\JoinColumn(name="ope_id",referencedColumnName="ope_id",nullable=true)
     * @var Operator
     */
    private $operator;

    /**
     * Agence à laquelle appartient la caisse
     * @ORM\ManyToOne(targetEntity=\Digitalis\Core\Models\Entities\Agence::class,cascade={"merge","persist"},inversedBy="caisses")
     * @ORM\JoinColumn(name="agc_id",referencedColumnName="agc_id",nullable=false,onDelete="RESTRICT")
     * @var Agence
     */
    private $agence;

    /**
     * Montant maximal journalier pour la caisse
     * 
     * @ORM\Column(name="box_maxdailyamount",type="integer",options={"default":10000000})
     * @var integer
     */
    private $maxDailyAmount;

    /**
     * Les transaction réalisé par cet opérateur
     * 
     * @ORM\OneToMany(targetEntity=\Digitalis\Core\Models\Entities\Transaction::class, mappedBy="caisse",cascade={"merge","persist"},fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"transDate"="desc"})
     * @var Transaction[]|ArrayCollection
     */
    private $transactions;

    public function __construct()
    {
        $this->status = 1;
        $this->dateCreate = new \DateTime();
        $this->isOpened = false;
        $this->maxDailyAmount = 10000000;
        $this->transactions = new ArrayCollection();
    }

    /**
     * Retourne la valeur de $transactions
     *
     * @return Transaction[]|ArrayCollection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * Définit la valeur de $transactions
     *
     * @param Transaction[]|ArrayCollection $transactions
     */
    public function setTransactions($transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * Retourne la valeur de $maxDailyAmount
     *
     * @return integer
     */
    public function getmaxDailyAmount()
    {
        return $this->maxDailyAmount;
    }

    /**
     * Définit la valeur de $maxDailyAmount
     *
     * @param integer $maxDailyAmount
     */
    public function setmaxDailyAmount($maxDailyAmount)
    {
        $this->maxDailyAmount = $maxDailyAmount;
    }

    /**
     * Retourne l'objet sous forme de tableau
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'key' => $this->key,
            'status' => $this->status,
            'isOpened' => $this->isOpened,
            'dateCreate' => $this->dateCreate->format('Y-m-d H:i:s'),
            'maxAmount' => $this->maxDailyAmount,
            'state' => $this->isOpened ? 'opened' : 'closed',
            'rcolor' => $this->isOpened ? 'danger' : 'primary',
            'color' => $this->isOpened ? 'primary' : 'danger',
            'icon' => $this->isOpened ? 'check' : 'times',
            'branch' => $this->agence->toArray(false),
            'action' => $this->isOpened ? 'close' : 'open',
            'operator' => !is_null($this->operator) ? $this->operator->toArray(false, false) : null,
            'status' => $this->status
        ];
    }

    /**
     * Retourne la valeur de $operator
     *
     * @return Operator
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Définit la valeur de $operator
     *
     * @param Operator $operator
     */
    public function setOperator($operator = null)
    {
        $this->operator = $operator;
    }

    /**
     * Permet d'instancier une nouvelle caisse
     *
     * @param string $codeAgence
     * @return Caisse
     */
    public static function getInstance($code)
    {
        $caisse = new Caisse();
        $caisse->setCode($code);
        $caisse->setKey(CaisseDbAdapter::genCaisseKey());
        return $caisse;
    }

    /**
     * Retourne la valeur de $agence
     *
     * @return Agence
     */
    public function getAgence()
    {
        return $this->agence;
    }

    /**
     * Définit la valeur de $agence
     *
     * @param Agence $agence
     */
    public function setAgence($agence)
    {
        $this->agence = $agence;
    }

    /**
     * Retourne la valeur de $userCreate
     *
     * @return string
     */
    public function getUserCreate()
    {
        return $this->userCreate;
    }

    /**
     * Définit la valeur de $userCreate
     *
     * @param string $userCreate
     */
    public function setUserCreate($userCreate)
    {
        $this->userCreate = $userCreate;
    }

    /**
     * Retourne la valeur de $dateCreate
     *
     * @return \DateTime
     */
    public function getDateCreate()
    {
        return $this->dateCreate;
    }

    /**
     * Définit la valeur de $dateCreate
     *
     * @param \DateTime $dateCreate
     */
    public function setDateCreate($dateCreate)
    {
        $this->dateCreate = $dateCreate;
    }

    /**
     * Retourne la valeur de $isOpened
     *
     * @return boolean
     */
    public function getIsOpened()
    {
        return $this->isOpened;
    }

    /**
     * Définit la valeur de $isOpened
     *
     * @param boolean $isOpened
     */
    public function setIsOpened($isOpened = false)
    {
        $this->isOpened = $isOpened;
    }

    /**
     * Retourne la valeur de $status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Définit la valeur de $status
     *
     * @param integer $status
     */
    public function setStatus($status = 1)
    {
        $this->status = $status;
    }

    /**
     * Retourne la valeur de $key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Définit la valeur de $key
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Retourne la valeur de $code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Définit la valeur de $code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Retourne la valeur de $id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Définit la valeur de $id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
