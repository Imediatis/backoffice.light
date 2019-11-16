<?php
namespace Digitalis\Core\Models\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Partner Institution financiaire chez qui les retrait sont initié
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="partners")
 */
class Partner
{

	/**
	 * Identifiant
	 * 
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(name="part_id",type="smallint")
	 * @var integer
	 */
	private $id;

	/**
	 * Code de l'institution
	 * 
	 * @ORM\Column(name="part_code",length=10,unique=true)
	 * @var string
	 */
	private $code;

	/**
	 * Nom de l'institution
	 * 
	 * @ORM\Column(name="part_name",length=120)
	 * @var string
	 */
	private $name;

	/**
	 * Statut de l'institution financière
	 * 
	 * @ORM\Column(name="part_status",type="boolean",options={"default":true})
	 * @var bool
	 */
	private $status;

	/**
	 * Date ce création de l'institution dans le système
	 * 
	 * @ORM\Column(name="part_datecreate",type="datetime")
	 * @var \DateTime
	 */
	private $dateCreate;

	/**
	 * Entreprise associé à ce partenaire financier
	 * 
	 * @ORM\OneToMany(targetEntity=\Digitalis\Core\Models\Entities\Affectation::class, cascade={"merge","persist"}, mappedBy="partner", fetch="EXTRA_LAZY")
	 * @var Affectation[]|ArrayCollection
	 */
	private $affectations;

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
	public function __construct()
	{
		$this->dateCreate = new \DateTime();
		$this->status = true;
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
	 * Retourne la valeur de $status
	 *
	 * @return bool
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Définit la valeur de $status
	 *
	 * @param bool $status
	 */
	public function setStatus($status = true)
	{
		$this->status = $status;
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