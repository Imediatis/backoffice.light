<?php
namespace Digitalis\Core\Models\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Country Pays
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="countries")
 */
class Country
{
	/**
	 * Code numérique du pays
	 *
	 * @var int
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(name="coun_id",type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(name="coun_alpha2",length=2)
	 * @var string
	 */
	private $alpha2;
	/**
	 * @ORM\Column(name="coun_alpha3",length=3)
	 * @var string
	 */
	private $alpha3;
	/**
	 * @ORM\Column(name="coun_dialcode",type="integer",nullable=true)
	 * @var string
	 */
	private $dialCode;
	/**
	 * @ORM\Column(name="coun_enname",length=128)
	 * @var string
	 */
	private $enName;
	/**
	 * @ORM\Column(name="coun_frname",length=128)
	 * @var string
	 */
	private $frName;

	/**
	 * Ensemble des villes du pays
	 *
	 * @ORM\OneToMany(targetEntity=\Digitalis\Core\Models\Entities\Region::class, cascade={"persist","merge"},mappedBy="country",fetch="EXTRA_LAZY")
	 * @var Region[]|ArrayCollection
	 */
	private $regions;

	public function __construct()
	{
		$this->regions = new ArrayCollection();
	}

	/**
	 * Get id numérique du pays
	 *
	 * @return  int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set id numérique du pays
	 *
	 * @param  int  $id  id numérique du pays
	 *
	 * @return  self
	 */
	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * Get the value of alpha2
	 *
	 * @return  string
	 */
	public function getAlpha2()
	{
		return $this->alpha2;
	}

	/**
	 * Set the value of alpha2
	 *
	 * @param  string  $alpha2
	 *
	 * @return  self
	 */
	public function setAlpha2($alpha2)
	{
		$this->alpha2 = $alpha2;

		return $this;
	}

	/**
	 * Get the value of alpha3
	 *
	 * @return  string
	 */
	public function getAlpha3()
	{
		return $this->alpha3;
	}

	/**
	 * Set the value of alpha3
	 *
	 * @param  string  $alpha3
	 *
	 * @return  self
	 */
	public function setAlpha3($alpha3)
	{
		$this->alpha3 = $alpha3;

		return $this;
	}

	/**
	 * Get the value of dialCode
	 *
	 * @return  string
	 */
	public function getDialCode()
	{
		return $this->dialCode;
	}

	/**
	 * Set the value of dialCode
	 *
	 * @param  string  $dialCode
	 *
	 * @return  self
	 */
	public function setDialCode($dialCode)
	{
		$this->dialCode = $dialCode;

		return $this;
	}

	/**
	 * Get the value of enName
	 *
	 * @return  string
	 */
	public function getEnName()
	{
		return $this->enName;
	}

	/**
	 * Set the value of enName
	 *
	 * @param  string  $enName
	 *
	 * @return  self
	 */
	public function setEnName($enName)
	{
		$this->enName = $enName;

		return $this;
	}

	/**
	 * Get the value of frName
	 *
	 * @return  string
	 */
	public function getFrName()
	{
		return $this->frName;
	}

	/**
	 * Set the value of frName
	 *
	 * @param  string  $frName
	 *
	 * @return  self
	 */
	public function setFrName($frName)
	{
		$this->frName = $frName;

		return $this;
	}

	/**
	 * Get ensemble des villes du pays
	 *
	 * @return  Regions[]|ArrayCollection
	 */
	public function getRegions()
	{
		return $this->regions;
	}

	/**
	 * Set ensemble des villes du pays
	 *
	 * @param  Region[]|ArrayCollection  $regions  Ensemble des villes du pays
	 *
	 * @return  self
	 */
	public function setRegions($regions)
	{
		$this->regions = $regions;

		return $this;
	}

	/**
	 * Récupère le nom du pays en fonction de la langue passé en paramètre
	 *
	 * @param string $lang Langue pour laquelle on veut récupérer le nom du pays; fr par défaut
	 * @return string
	 */
	public function getName($lang = "fr")
	{
		if (strtolower($lang) == "en") {
			return $this->getEnName();
		} else {
			return $this->getFrName();
		}
	}
}