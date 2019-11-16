<?php
namespace Digitalis\Core\Models\Entities;

use Digitalis\Core\Models\Entities\Entreprise;
use Doctrine\ORM\Mapping as ORM;

/**
 * TariffGrid Grille tariffaire pour une entreprise
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="tariffgrids")
 */
class TariffGrid
{

	/**
	 * Identifiant
	 * 
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(name="trfg_id",type="integer")
	 * @var integer
	 */
	private $id;

	/**
	 * Valeur minimale de l'intervalle
	 * 
	 * @ORM\Column(name="trfg_min",type="integer")
	 * @var integer
	 */
	private $min;

	/**
	 * Valeur maximale de l'intervalle
	 * 
	 * @ORM\Column(name="trfg_max",type="integer")
	 * @var integer
	 */
	private $max;

	/**
	 * Valeur de l'intervalle
	 * 
	 * @ORM\Column(name="trfg_value",type="float" )
	 * @var float
	 */
	private $value;

	/**
	 * Type d'application de la grille (fixe/pourcentage)
	 * 
	 * @ORM\Column(name="trfg_nature",type="smallint",options={"default":1})
	 * @var integer
	 */
	private $nature;

	/**
	 * Entreprise appliquant la grille
	 * 
	 * @ORM\ManyToOne(targetEntity=\Digitalis\Core\Models\Entities\Entreprise::class,inversedBy="tariffGrids")
	 * @ORM\JoinColumn(name="ent_id",referencedColumnName="ent_id",nullable=false,onDelete="CASCADE")
	 * @var Entreprise
	 */
	private $entreprise;

	/**
	 * Date de création de la grille
	 * 
	 * @ORM\Column(name="trfg_datecreate",type="datetime")
	 * @var \DateTime
	 */
	private $dateCreate;

	/**
	 * Utilisateur qui enregistre la grille
	 * 
	 * @ORM\Column(name="trfg_usercreate",length=80)
	 * @var string
	 */
	private $userCreate;

	public function __construct()
	{
		$this->dateCreate = new \DateTime();
		$this->nature = 1;
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
	 * Retourne la valeur de $nature
	 *
	 * @return integer
	 */
	public function getNature()
	{
		return $this->nature;
	}

	/**
	 * Définit la valeur de $nature
	 *
	 * @param integer $nature
	 */
	public function setNature($nature = 1)
	{
		$this->nature = $nature;
	}

	/**
	 * Retourne la valeur de $value
	 *
	 * @return float
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Définit la valeur de $value
	 *
	 * @param float $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * Retourne la valeur de $max
	 *
	 * @return integer
	 */
	public function getMax()
	{
		return $this->max;
	}

	/**
	 * Définit la valeur de $max
	 *
	 * @param integer $max
	 */
	public function setMax($max)
	{
		$this->max = $max;
	}

	/**
	 * Retourne la valeur de $min
	 *
	 * @return integer
	 */
	public function getMin()
	{
		return $this->min;
	}

	/**
	 * Définit la valeur de $min
	 *
	 * @param integer $min
	 */
	public function setMin($min)
	{
		$this->min = $min;
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
