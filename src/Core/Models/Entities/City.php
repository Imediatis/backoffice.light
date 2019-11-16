<?php
namespace Digitalis\Core\Models\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * City Ville du système
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="cities")
 */
class City
{
	/**
	 * Identifiant de la ville
	 * 
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(name="cty_id",type="integer")
	 * @var integer
	 */
	protected $id;

	/**
	 * Code de la ville
	 * 
	 * @ORM\Column(name="cty_code",length=80,unique=true)
	 * @var string
	 */
	protected $code;

	/**
	 * Nom de la ville
	 * 
	 * @ORM\Column(name="cty_label",length=128,nullable=true)
	 * @var string
	 */
	protected $label;

	/**
	 * Région où se trouve la ville
	 * 
	 * @ORM\ManyToOne(targetEntity=\Digitalis\Core\Models\Entities\Region::class,cascade={"merge","persist"},inversedBy="cities")
	 * @ORM\JoinColumn(name="reg_id",referencedColumnName="reg_id",nullable=false,onDelete="RESTRICT")
	 * @var Region
	 */
	protected $region;

	/**
	 * Les agences localisés dans cette ville
	 * 
	 * @ORM\OneToMany(targetEntity=\Digitalis\Core\Models\Entities\Agence::class,cascade={"merge","persist"},mappedBy="city",fetch="EXTRA_LAZY")
	 * @var Agence[]|ArrayCollection
	 */
	protected $agences;

	/**
	 * Date de création de la ville
	 * 
	 * @ORM\Column(name="cty_datecreate",type="datetime")
	 * @var \DateTime
	 */
	protected $dateCreate;

	/**
	 * Les entreprises localisé dans cette ville
	 * 
	 * @ORM\OneToMany(targetEntity=\Digitalis\Core\Models\Entities\Entreprise::class,cascade={"merge","persist"},mappedBy="city",fetch="EXTRA_LAZY")
	 * @var Entreprise[]|ArrayCollection
	 */
	private $entreprises;

	/**
	 * Retourne la valeur de $entreprises
	 *
	 * @return Entreprise[]|ArrayCollection
	 */
	public function getEntreprises()
	{
		return $this->entreprises;
	}

	/**
	 * Définit la valeur de $entreprises
	 *
	 * @param Entreprise[]|ArrayCollection $entreprises
	 */
	public function setEntreprises($entreprises)
	{
		$this->entreprises = $entreprises;
	}

	public function __construct()
	{
		$this->agences = new ArrayCollection();
		$this->dateCreate = new \DateTime();
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
	public function setAgences($agences = null)
	{
		$this->agences = $agences;
	}

	/**
	 * Retourne la valeur de $region
	 *
	 * @return Region
	 */
	public function getRegion()
	{
		return $this->region;
	}

	/**
	 * Définit la valeur de $region
	 *
	 * @param Region $region
	 */
	public function setRegion($region)
	{
		$this->region = $region;
	}

	/**
	 * Retourne la valeur de $label
	 *
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * Définit la valeur de $label
	 *
	 * @param string $label
	 */
	public function setLabel($label)
	{
		$this->label = $label;
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