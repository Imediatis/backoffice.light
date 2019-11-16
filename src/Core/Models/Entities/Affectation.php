<?php
namespace Digitalis\Core\Models\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Affectation Représente la souscription d'une entreprise au partenaire financier
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="affectations",uniqueConstraints={@ORM\UniqueConstraint(name="entrep_partner_unique",columns={"ent_id","part_id"})})
 */
class Affectation
{
	/**
	 * Identifiant
	 * 
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(name="aff_id",type="integer")
	 * @var integer
	 */
	private $id;

	/**
	 * Entreprise participant à l'association
	 * 
	 * @ORM\ManyToOne(targetEntity=\Digitalis\Core\Models\Entities\Entreprise::class, inversedBy="affectations")
	 * @ORM\JoinColumn(name="ent_id",referencedColumnName="ent_id",nullable=false,onDelete="RESTRICT")
	 * @var Entreprise
	 */
	private $entreprise;

	/**
	 * Partenaire financier
	 * 
	 * @ORM\ManyToOne(targetEntity=\Digitalis\Core\Models\Entities\Partner::class, inversedBy="affectations")
	 * @ORM\JoinColumn(name="part_id", referencedColumnName="part_id", nullable=false, onDelete="RESTRICT")
	 * @var Partner
	 */
	private $partner;

	/**
	 * Date d'affectation de l'entreprise et le partenaire financier
	 * 
	 * @ORM\Column(name="aff_datecreate",type="datetime")
	 * @var \DateTime
	 */
	private $dateCreate;

	public function __construct()
	{
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
	 * Retourne la valeur de $partner
	 *
	 * @return Partner
	 */
	public function getPartner()
	{
		return $this->partner;
	}

	/**
	 * Définit la valeur de $partner
	 *
	 * @param Partner $partner
	 */
	public function setPartner($partner)
	{
		$this->partner = $partner;
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
}