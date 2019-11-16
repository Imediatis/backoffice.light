<?php
namespace Digitalis\Core\Models\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Region Regions géographique du pays
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="regions")
 */
class Region
{

	/**
	 * Identifiant de la région
	 * 
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(name="reg_id",type="smallint")
	 * @var integer
	 */
	protected $id;

	/**
	 * Code de la régions
	 * 
	 * @ORM\Column(name="reg_code",length=20,unique=true)
	 * @var string
	 */
	protected $code;

	/**
	 * Libellé de la région (nom)
	 * 
	 * @ORM\Column(name="reg_label",length=128,nullable=true)
	 * @var string
	 */
	protected $label;

	/**
	 * Date de création de la région
	 * 
	 * @ORM\Column(name="reg_datecreate",type="datetime")
	 * @var \DateTime
	 */
	protected $dateCreate;

	/**
	 * Pays dans lequel se trouve le pays
	 * 
	 * @ORM\ManyToOne(targetEntity=\Digitalis\Core\Models\Entities\Country::class,cascade={"merge","persist"}, inversedBy="regions")
	 * @ORM\JoinColumn(name="coun_id",referencedColumnName="coun_id",nullable=false,onDelete="RESTRICT")
	 * @var Country
	 */
	protected $country;

	/**
	 * Les villes de la régions
	 * 
	 * @ORM\OneToMany(targetEntity=\Digitalis\Core\Models\Entities\City::class,cascade={"merge","persist"},mappedBy="region",fetch="EXTRA_LAZY")
	 * @var City[]|ArrayCollection
	 */
	protected $cities;

	public function __construct()
	{
		$this->cities = new ArrayCollection();
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
	 * Retourne la valeur de $cities
	 *
	 * @return City[]|ArrayCollection
	 */
	public function getCities()
	{
		return $this->cities;
	}

	/**
	 * Définit la valeur de $cities
	 *
	 * @param City[]|ArrayCollection $cities
	 */
	public function setCities($cities)
	{
		$this->cities = $cities;
	}

	/**
	 * Retourne la valeur de $country
	 *
	 * @return Country
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * Définit la valeur de $country
	 *
	 * @param Country $country
	 */
	public function setCountry($country)
	{
		$this->country = $country;
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
	public function setLabel($label = null)
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