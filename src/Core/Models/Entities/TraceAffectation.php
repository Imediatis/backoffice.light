<?php
namespace Digitalis\Core\Models\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * TraceAffectation Traceur des affectation d'un opérateur à une caisse
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="traceaffectations")
 */
class TraceAffectation
{

	/**
	 * Identifiant
	 * 
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(name="trc_id",type="integer")
	 * @var integer
	 */
	private $id;

	/**
	 * Code de l'opérateur affecté à la caisse
	 * 
	 * @ORM\Column(name="trc_codeoperator",length=80)
	 * @var string
	 */
	private $codeOperator;

	/**
	 * Code de la caisse d'affectation
	 * 
	 * @ORM\Column(name="trc_codecaisse",length=20)
	 * @var string
	 */
	private $codeCaisse;

	/**
	 * Date de début de travail dans cette caisse
	 * 
	 * @ORM\Column(name="trc_startdate",type="datetime")
	 * @var \DateTime
	 */
	private $startDate;

	/**
	 * Date de fin de travaill dans la caisse
	 * 
	 * @ORM\Column(name="trc_enddate",type="datetime",nullable=true)
	 * @var \DateTime
	 */
	private $endDate;

	/**
	 * L'utilisateur qui a fait l'affectation de l'opérateur
	 * 
	 * @ORM\Column(name="trc_useraffect",length=80)
	 * @var string
	 */
	private $userAffect;

	/**
	 * Utilisateur qui a désaffecter l'opérateur
	 * 
	 * @ORM\Column(name="trc_userremove",length=80,nullable=true)
	 * @var string
	 */
	private $userRemove;

	public function __construct($codeOperator, $codeCaisse)
	{
		$this->codeOperator = $codeOperator;
		$this->codeCaisse = $codeCaisse;
		$this->startDate = new \DateTime();
	}

	/**
	 * Retourne la valeur de $userRemove
	 *
	 * @return string
	 */
	public function getUserRemove()
	{
		return $this->userRemove;
	}

	/**
	 * Définit la valeur de $userRemove
	 *
	 * @param string $userRemove
	 */
	public function setUserRemove($userRemove = null)
	{
		$this->userRemove = $userRemove;
	}

	/**
	 * Retourne la valeur de $userAffect
	 *
	 * @return string
	 */
	public function getUserAffect()
	{
		return $this->userAffect;
	}

	/**
	 * Définit la valeur de $userAffect
	 *
	 * @param string $userAffect
	 */
	public function setUserAffect($userAffect)
	{
		$this->userAffect = $userAffect;
	}

	/**
	 * Retourne la valeur de $endDate
	 *
	 * @return \DateTime
	 */
	public function getEndDate()
	{
		return $this->endDate;
	}

	/**
	 * Définit la valeur de $endDate
	 *
	 * @param \DateTime $endDate
	 */
	public function setEndDate($endDate = null)
	{
		$this->endDate = $endDate;
	}

	/**
	 * Retourne la valeur de $startDate
	 *
	 * @return \DateTime
	 */
	public function getStartDate()
	{
		return $this->startDate;
	}

	/**
	 * Définit la valeur de $startDate
	 *
	 * @param \DateTime $startDate
	 */
	public function setStartDate($startDate)
	{
		$this->startDate = $startDate;
	}

	/**
	 * Retourne la valeur de $codeCaisse
	 *
	 * @return string
	 */
	public function getCodeCaisse()
	{
		return $this->codeCaisse;
	}

	/**
	 * Définit la valeur de $codeCaisse
	 *
	 * @param string $codeCaisse
	 */
	public function setCodeCaisse($codeCaisse)
	{
		$this->codeCaisse = $codeCaisse;
	}

	/**
	 * Retourne la valeur de $codeOperator
	 *
	 * @return string
	 */
	public function getCodeOperator()
	{
		return $this->codeOperator;
	}

	/**
	 * Définit la valeur de $codeOperator
	 *
	 * @param string $codeOperator
	 */
	public function setCodeOperator($codeOperator)
	{
		$this->codeOperator = $codeOperator;
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