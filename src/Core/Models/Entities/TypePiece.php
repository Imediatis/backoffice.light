<?php
namespace Digitalis\Core\Models\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * TypePiece Type de pièce officièle
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="documenttypes")
 */
class TypePiece implements \JsonSerializable
{
	/**
	 * Identifiant
	 * 
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(name="tdoc_id",type="smallint",options={"unsigned":true})
	 * @var integer
	 */
	private $id;

	/**
	 * Code du type de pièce
	 * 
	 * @ORM\Column(name="tdoc_code",length=15,unique=true)
	 * @var string
	 */
	private $code;

	/**
	 * Libelé du type de pièce
	 * 
	 * @ORM\Column(name="tdoc_label",length=80)
	 * @var string
	 */
	private $label;

	/**
	 * Date d'ajout à la base de données
	 * 
	 * @ORM\Column(name="tdoc_datecreate",type="datetime")
	 * @var \DateTime
	 */
	private $dateCreate;

	public function __construct()
	{
		$this->dateCreate = new \DateTime();
	}

	public function jsonSerialize()
	{
		return [
			'code' => $this->code,
			'label' => $this->label
		];
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