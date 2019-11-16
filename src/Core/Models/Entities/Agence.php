<?php
namespace Digitalis\Core\Models\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Agence Représente une agence du système
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="branches")
 */
class Agence
{

    /**
	 * Identifiant de l'agence
	 * 
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(name="agc_id",type="smallint")
	 * @var integer
	 */
    protected $id;

    /**
	 * Code de l'agence
	 * 
	 * @ORM\Column(name="agc_code",length=10,unique=true)
	 * @var string
	 */
    protected $code;

    /**
	 * Nom de l'agence
	 * 
	 * @ORM\Column(name="agc_label",length=155)
	 * @var string
	 */
    protected $lable;

    /**
	 * Adresse de l'agence
	 * 
	 * @ORM\Column(name="agc_address",length=300,nullable=true)
	 * @var string
	 */
    protected $address;

    /**
	 * Numéro de téléphone de l'agence
	 * 
	 * @ORM\Column(name="agc_phone1",length=20)
	 * @var string
	 */
    protected $phone1;

    /**
	 * Deuxième numéro de téléphone de l'agence
	 * 
	 * @ORM\Column(name="agc_phone2",length=20,nullable=true)
	 * @var string
	 */
    protected $phone2;

    /**
	 * Adresse mail de l'agence
	 * 
	 * @ORM\Column(name="agc_email",length=50,nullable=true)
	 * @var string
	 */
    protected $email;

    /**
	 * Clé d'identification de l'agence (nécessaire lors de l'opération d'ouverture de celle-ci)
	 * 
	 * @ORM\Column(name="agc_key",length=12,unique=true)
	 * @var string
	 */
    protected $key;

    /**
	 * Détermine si l'agences ouverte ou pas
	 * 
	 * @ORM\Column(name="agc_isopened",type="boolean",options={"default":false})
	 * @var boolean
	 */
    protected $isOpened;

    /**
	 * Statut de l'agence
	 * 
	 * @ORM\Column(name="agc_statut",type="smallint",options={"default":1})
	 * @var integer
	 */
    protected $statut;

    /**
	 * Entreprise à laquelle appartient l'agence
	 * 
	 * @ORM\ManyToOne(targetEntity=\Digitalis\Core\Models\Entities\Entreprise::class,cascade={"merge","persist"},inversedBy="agences")
	 * @ORM\JoinColumn(name="ent_id",referencedColumnName="ent_id",nullable=false,onDelete="RESTRICT")
	 * @var Entreprise
	 */
    protected $entreprise;

    /**
	 * Ville où se trouve l'agence
	 * 
	 * @ORM\ManyToOne(targetEntity=\Digitalis\Core\Models\Entities\City::class,cascade={"merge","persist"},inversedBy="agences")
	 * @ORM\JoinColumn(name="cty_id",referencedColumnName="cty_id",nullable=false,onDelete="RESTRICT")
	 * @var City
	 */
    protected $city;

    /**
	 * Les caisses de l'agence
	 * 
	 * @ORM\OneToMany(targetEntity=\Digitalis\Core\Models\Entities\Caisse::class,cascade={"merge","persist"},mappedBy="agence",fetch="EXTRA_LAZY")
	 * @var Caisse[]|ArrayCollection
	 */
    protected $caisses;

    /**
	 * Date de création de l'agence
	 *
	 * @ORM\Column(name="agc_datecreate",type="datetime")
	 * @var \DateTime
	 */
    protected $dateCreate;

    public function __construct()
    {
        $this->caisses = new ArrayCollection();
        $this->dateCreate = new \DateTime();
        $this->isOpened = false;
        $this->statut = 1;
    }

    /**
     * Converti l'objet en tableau
     *
     * @param boolean $addCaisse détermine s'il faut ajouter les caisses à l'agence ou pas
     * @return array
     */
    public function toArray($addCaisse = true)
    {
        $agence =  [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->lable,
            'address' => $this->address,
            'phone1' => $this->phone1,
            'phone2' => $this->phone2,
            'email' => $this->email,
            'key' => $this->key,
            'isOpened' => $this->isOpened,
            'status' => $this->statut,
            'state' => $this->isOpened ? 'opened' : 'closed',
            'color' => $this->isOpened ? 'primary' : 'danger',
            'rcolor' => $this->isOpened ? 'danger' : 'primary',
            'entreprise' => !is_null($this->entreprise) ? $this->entreprise->getName() : null,
            'city' => !is_null($this->city) ? $this->city->getCode() : null,
            'dateCreate' => $this->dateCreate->format('Y-m-d H:i:s'),
            'action' => $this->isOpened ? 'close' : 'open'
        ];
        if ($addCaisse) {
            $agence['caisses'] = $this->caissesToArray();
        }
        return $agence;
    }

    /**
	 * Ajoute une caisse à la collection de caisse de l'agence
	 *
	 * @param Caisse $caisse
	 * @return Agence
	 */
    public function addCaisse(Caisse $caisse)
    {
        if (!$this->caisses->contains($caisse)) {
            $caisse->setAgence($this);
            $this->caisses->add($caisse);
        }
        return $this;
    }

    /**
	 * Permet de récupérer les codes caisse de l'agence
	 *
	 * @return array
	 */
    public function getCodeCaisses()
    {
        $output = [];
        foreach ($this->caisses as $item) {
            $output[] = $item->getCode();
        }
        return $output;
    }

    private function caissesToArray()
    {
        $output = [];
        foreach ($this->caisses as $item) {
            $output[] = $item->toArray();
        }
        return $output;
    }

    /**
	 * Retourne la valeur de $caisses
	 *
	 * @return Caisse[]|ArrayCollection
	 */
    public function getCaisses()
    {
        return $this->caisses;
    }

    /**
	 * Définit la valeur de $caisses
	 *
	 * @param Caisse[]|ArrayCollection $caisses
	 */
    public function setCaisses($caisses)
    {
        $this->caisses = $caisses;
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
	 * Retourne la valeur de $entreprise
	 *
	 * @return Entreprise
	 */
    public function getEntreprise()
    {
        return $this->entreprise;
    }

    /**
	 * Définit la valeur de $entreprise
	 *
	 * @param Entreprise $entreprise
	 */
    public function setEntreprise($entreprise)
    {
        $this->entreprise = $entreprise;
    }

    /**
	 * Retourne la valeur de $statut
	 *
	 * @return integer
	 */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
	 * Définit la valeur de $statut
	 *
	 * @param integer $statut
	 */
    public function setStatut($statut = 1)
    {
        $this->statut = $statut;
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
	 * Retourne la valeur de $email
	 *
	 * @return string
	 */
    public function getEmail()
    {
        return $this->email;
    }

    /**
	 * Définit la valeur de $email
	 *
	 * @param string $email
	 */
    public function setEmail($email = null)
    {
        $this->email = $email;
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
    public function setAddress($address = null)
    {
        $this->address = $address;
    }

    /**
	 * Retourne le nom de l'agence
	 *
	 * @return string
	 */
    public function getLabel()
    {
        return $this->lable;
    }

    /**
	 * Définit la valeur de $lable
	 *
	 * @param string $lable
	 */
    public function setLabel($lable = null)
    {
        $this->lable = $lable;
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
