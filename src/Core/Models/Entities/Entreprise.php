<?php
namespace Digitalis\Core\Models\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entreprise Différentes société ayant adhéré au programme
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="entreprises")
 */
class Entreprise
{
    /**
     * Identifiant de l'entreprise
     * 
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="ent_id",type="integer")
     * @var integer
     */
    private $id;

    /**
     * Reférence de la société
     * 
     * @ORM\Column(name="ent_ref",length=20,unique=true)
     * @var string
     */
    private $reference;

    /**
     * Nom de la société
     * 
     * @ORM\Column(name="ent_name",length=120)
     * @var string
     */
    private $name;

    /**
     * Nom de domaine de l'entreprise
     * 
     * @ORM\Column(name="ent_domain",length=50,unique=true)
     * @var string
     */
    private $domain;

    /**
     * Adresse de l'entreprise
     * 
     * @ORM\Column(name="ent_address",length=255,nullable=true)
     * @var string
     */
    private $address;

    /**
     * Date de creation de l'entreprise
     * 
     * @ORM\Column(name="ent_datecreate",type="datetime")
     * @var \DateTime
     */
    private $dateCreate;

    /**
     * Ville de localisation de l'entreprise
     * 
     * @ORM\ManyToOne(targetEntity=\Digitalis\Core\Models\Entities\City::class,cascade={"merge","persist"},inversedBy="entreprises")
     * @ORM\JoinColumn(name="cty_id",referencedColumnName="cty_id",nullable=false,onDelete="RESTRICT")
     * @var City
     */
    private $city;

    /**
     * Les agences de l'entreprise
     * 
     * @ORM\OneToMany(targetEntity=\Digitalis\Core\Models\Entities\Agence::class,cascade={"merge","persist"},mappedBy="entreprise",fetch="EXTRA_LAZY")
     * @var Agence[]|ArrayCollection
     */
    private $agences;

    /**
     * Statut de l'entreprise
     * 
     * @ORM\Column(name="ent_status",type="smallint",options={"default":1})
     * @var integer
     */
    private $status;

    /**
     * Numéro de téléphone 1
     * 
     * @ORM\Column(name="ent_phone1",length=20,nullable=true)
     * @var string
     */
    private $phone1;

    /**
     * Deuxième numéro de téléphone
     * 
     * @ORM\Column(name="ent_phone2",length=20,nullable=true)
     * @var string
     */
    private $phone2;

    /**
     * Adresse mail 1
     * 
     * @ORM\Column(name="ent_email1",length=80,nullable=true)
     * @var string
     */
    private $email1;

    /**
     * Email 2
     * 
     * @ORM\Column(name="ent_email2",length=80,nullable=true)
     * @var string
     */
    private $email2;

    /**
     * Liste des opérateurs de cette entreprise
     * 
     * @ORM\OneToMany(targetEntity=\Digitalis\Core\Models\Entities\Operator::class,cascade={"merge","persist"},mappedBy="entreprise",fetch="EXTRA_LAZY")
     * @var Operator[]|ArrayCollection
     */
    private $operators;

    /**
     * Grille tariffaire de l'entreprise
     * 
     * @ORM\OneToMany(targetEntity=\Digitalis\Core\Models\Entities\TariffGrid::class,cascade={"merge","persist"},mappedBy="entreprise",fetch="EXTRA_LAZY")
     * @var TariffGrid[]|ArrayCollection
     */
    private $tariffGrids;

    /**
     * Les Partenaires financiers associés à l'entreprise
     * 
     * @ORM\OneToMany(targetEntity=\Digitalis\Core\Models\Entities\Affectation::class, cascade={"merge","persist"}, mappedBy="entreprise", fetch="EXTRA_LAZY")
     * @var Affectation[]|ArrayCollection
     */
    private $affectations;

    public function __construct()
    {
        $this->agences = new ArrayCollection();
        $this->operators = new ArrayCollection();
        $this->tariffGrids = new ArrayCollection();
        $this->affectations = new ArrayCollection();
        $this->dateCreate = new \DateTime();
        $this->status = 1;
    }

    /**
     * Retourne l'entreprise sous forme de tableau
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'ref' => $this->reference,
            'name' => $this->name,
            'domain' => $this->domain,
            'address' => $this->address,
            'createdOn' => $this->dateCreate->format('Y-m-d H:i:s'),
            'city' => $this->city->getCode(),
            'region' => $this->city->getRegion()->getCode(),
            'country' => $this->city->getRegion()->getCountry()->getFrName(),
            'status' => $this->status,
            'phone1' => $this->phone1,
            'phone2' => $this->phone2,
            'email1' => $this->email1,
            'email2' => $this->email2,
            'operators' => $this->operatorsToArray()
        ];
    }

    /**
     * Retourne la valeur de $affectations
     *
     * @return Affectation[]|ArrayCollection
     */
    public function getAffectations()
    {
        return $this->affectations;
    }

    /**
     * Définit la valeur de $affectations
     *
     * @param Affectation[]|ArrayCollection $affectations
     */
    public function setAffectations($affectations = [])
    {
        $this->affectations = $affectations;
    }

    /**
     * Retourne la valeur de $tariffGrids
     *
     * @return TariffGrid[]|ArrayCollection
     */
    public function getGrilleTariffaires()
    {
        return $this->tariffGrids;
    }

    /**
     * Définit la valeur de $tariffGrids
     *
     * @param TariffGrid[]|ArrayCollection $tariffGrids
     */
    public function setGrilleTariffaires($tariffGrids = [])
    {
        $this->tariffGrids = $tariffGrids;
    }

    /**
     * Retourne la liste des opérateurs sour forme de tableau
     *
     * @return array
     */
    public function operatorsToArray()
    {
        $output = [];
        foreach ($this->operators as $value) {
            $output[] = $value->toArray(true, false);
        }
        return $output;
    }

    /**
     * Retourne la valeur de $operators
     *
     * @return Operator[]|ArrayCollection
     */
    public function getOperators()
    {
        return $this->operators;
    }

    /**
     * Définit la valeur de $operators
     *
     * @param Operator[]|ArrayCollection $operators
     */
    public function setOperators($operators = null)
    {
        $this->operators = $operators;
    }

    /**
     * Ajoute un opérateur à la collection
     *
     * @param Operator $operator
     * @return Entreprise
     */
    public function addOperator($operator)
    {
        if (!$this->operators->contains($operator)) {
            $operator->setEntreprise($this);
            $this->operators->add($operator);
        }
        return $this;
    }

    /**
     * Retourne la valeur de $email2
     *
     * @return string
     */
    public function getEmail2()
    {
        return $this->email2;
    }

    /**
     * Définit la valeur de $email2
     *
     * @param string $email2
     */
    public function setEmail2($email2 = null)
    {
        $this->email2 = $email2;
    }

    /**
     * Retourne la valeur de $email1
     *
     * @return string
     */
    public function getEmail1()
    {
        return $this->email1;
    }

    /**
     * Définit la valeur de $email1
     *
     * @param string $email1
     */
    public function setEmail1($email1 = null)
    {
        $this->email1 = $email1;
    }

    /**
     * Retourne la valeur de $phone2
     *
     * @return string
     */
    public function getPhone2()
    {
        return $this->phone2;
    }

    /**
     * Définit la valeur de $phone2
     *
     * @param string $phone2
     */
    public function setPhone2($phone2 = null)
    {
        $this->phone2 = $phone2;
    }

    /**
     * Retourne la valeur de $phone1
     *
     * @return string
     */
    public function getPhone1()
    {
        return $this->phone1;
    }

    /**
     * Définit la valeur de $phone1
     *
     * @param string $phone1
     */
    public function setPhone1($phone1 = null)
    {
        $this->phone1 = $phone1;
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
     * Retourne la valeur de $agences
     *
     * @return Agence[]|ArrayCollection
     */
    public function getAgences()
    {
        return $this->agences;
    }

    /**
     * Définit la valeur de $agences
     *
     * @param Agence[]|ArrayCollection $agences
     */
    public function setAgences($agences)
    {
        $this->agences = $agences;
    }

    /**
     * Retourne la valeur de $city
     *
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Définit la valeur de $city
     *
     * @param City $city
     */
    public function setCity($city)
    {
        $this->city = $city;
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
     * Retourne la valeur de $address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Définit la valeur de $address
     *
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Retourne la valeur de $domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Définit la valeur de $domain
     *
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Retourne la valeur de $name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Définit la valeur de $name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Retourne la valeur de $reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Définit la valeur de $reference
     *
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
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
