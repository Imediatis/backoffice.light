<?php
namespace Digitalis\Core\Models\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Transaction Transaction validée
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="transactions")
 */
class Transaction implements \JsonSerializable
{

	/**
	 * Identifiant
	 * 
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(name="trans_id",type="integer")
	 * @var integer
	 */
	private $id;

	/**
	 * Reférence de la transaction
	 * 
	 * @ORM\Column(name="trans_reference",length=20,unique=true)
	 * @var string
	 */
	private $reference;

	/**
	 * Client qui effectue l'opération
	 * 
	 * @ORM\Column(name="trans_customer",length=120)
	 * @var string
	 */
	private $customer;

	/**
	 * Numéro de compte du client
	 * 
	 * @ORM\Column(name="trans_accountnumber",length=80)
	 * @var string
	 */
	private $accountNumber;

	/**
	 * Code du partenaire où le retrait est fait
	 * 
	 * @ORM\Column(name="trans_partnercode",length=20)
	 * @var string
	 */
	private $partnerCode;

	/**
	 * Montant de l'opération
	 * 
	 * @ORM\Column(name="trans_amount",type="integer")
	 * @var integer
	 */
	private $amount;

	/**
	 * Frais de l'opération
	 * 
	 * @ORM\Column(name="trans_fees",type="integer")
	 * @var integer
	 */
	private $fees;

	/**
	 * Date de l'opération
	 * 
	 * @ORM\Column(name="trans_date",type="datetime")
	 * @var \DateTime
	 */
	private $transDate;

	/**
	 * Statut de la transaction
	 * 
	 * @ORM\Column(name="trans_status",type="smallint",options={"default":1})
	 * @var integer
	 */
	private $status;

	/**
	 * Caisse qui réalise l'opération
	 * 
	 * @ORM\ManyToOne(targetEntity=\Digitalis\Core\Models\Entities\Caisse::class,cascade={"merge","persist"}, inversedBy="transactions")
	 * @ORM\JoinColumn(name="box_id",referencedColumnName="box_id",nullable=false,onDelete="RESTRICT")
	 * @var Caisse
	 */
	private $caisse;

	/**
	 * Opérateur qui valide l'opération
	 * 
	 * @ORM\ManyToOne(targetEntity=\Digitalis\Core\Models\Entities\Operator::class, cascade={"merge","persist"}, inversedBy="transactions")
	 * @ORM\JoinColumn(name="ope_id",referencedColumnName="ope_id",nullable=false, onDelete="RESTRICT")
	 * @var Operator
	 */
	private $operator;

	/**
	 * Type de document avec lequel le client fait l'opération
	 * 
	 * @ORM\Column(name="trans_doctype",length=15,nullable=true)
	 * @var string
	 */
	private $docType;

	/**
	 * Numéro de la pièce du client
	 * 
	 * @ORM\Column(name="trans_docnumer",length=20,nullable=true)
	 * @var string
	 */
	private $docNumber;

	/**
	 * Lieu de délivrance de la pièce
	 * 
	 * @ORM\Column(name="trans_issueplace",length=80,nullable=true)
	 * @var string
	 */
	private $issuePlace;

	/**
	 * Date de délivrance de la pièce
	 * 
	 * @ORM\Column(name="trans_issuedate",type="date", nullable=true)
	 * @var \DateTime
	 */
	private $issueDate;

	public function __construct()
	{
		$this->transDate = new \DateTime();
		$this->status = 1;
	}

	public function toArray()
	{
		return [
			'id' => $this->id,
			'reference' => $this->reference,
			'customer' => $this->customer,
			'accountNumber' => $this->accountNumber,
			'bankCode' => $this->partnerCode,
			'amount' => $this->amount,
			'fees' => $this->fees,
			'transDate' => !is_null($this->transDate) ? $this->transDate->format("Y-m-d H:i:s") : null,
			'caisse' => !is_null($this->caisse) ? $this->caisse->getId() : null,
			'operator' => !is_nan($this->operator) ? $this->operator->getId() : null
		];
	}

	public function jsonSerialize()
	{
		return $this->toArray();
	}

	/**
	 * Retourne la valeur de $issueDate
	 *
	 * @return \DateTime
	 */
	public function getIssueDate()
	{
		return $this->issueDate;
	}

	/**
	 * Définit la valeur de $issueDate
	 *
	 * @param \DateTime $issueDate
	 */
	public function setIssueDate($issueDate = null)
	{
		$this->issueDate = $issueDate;
	}

	/**
	 * Retourne la valeur de $issuePlace
	 *
	 * @return string
	 */
	public function getIssuePlace()
	{
		return $this->issuePlace;
	}

	/**
	 * Définit la valeur de $issuePlace
	 *
	 * @param string $issuePlace
	 */
	public function setIssuePlace($issuePlace = null)
	{
		$this->issuePlace = $issuePlace;
	}

	/**
	 * Retourne la valeur de $docNumber
	 *
	 * @return string
	 */
	public function getDocNumber()
	{
		return $this->docNumber;
	}

	/**
	 * Définit la valeur de $docNumber
	 *
	 * @param string $docNumber
	 */
	public function setDocNumber($docNumber = null)
	{
		$this->docNumber = $docNumber;
	}

	/**
	 * Retourne la valeur de $docType
	 *
	 * @return string
	 */
	public function getDocType()
	{
		return $this->docType;
	}

	/**
	 * Définit la valeur de $docType
	 *
	 * @param string $docType
	 */
	public function setDocType($docType = null)
	{
		$this->docType = $docType;
	}
	/**
	 * Retourne la valeur de $operator
	 *
	 * @return Operator
	 */
	public function getOperator()
	{
		return $this->operator;
	}

	/**
	 * Définit la valeur de $operator
	 *
	 * @param Operator $operator
	 */
	public function setOperator($operator)
	{
		$this->operator = $operator;
	}

	/**
	 * Retourne la valeur de $caisse
	 *
	 * @return Caisse
	 */
	public function getCaisse()
	{
		return $this->caisse;
	}

	/**
	 * Définit la valeur de $caisse
	 *
	 * @param Caisse $caisse
	 */
	public function setCaisse($caisse)
	{
		$this->caisse = $caisse;
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
	 * Retourne la valeur de $transDate
	 *
	 * @return \DateTime
	 */
	public function getTransDate()
	{
		return $this->transDate;
	}

	/**
	 * Définit la valeur de $transDate
	 *
	 * @param \DateTime $transDate
	 */
	public function setTransDate($transDate)
	{
		$this->transDate = $transDate;
	}

	/**
	 * Retourne la valeur de $fees
	 *
	 * @return integer
	 */
	public function getFees()
	{
		return $this->fees;
	}

	/**
	 * Définit la valeur de $fees
	 *
	 * @param integer $fees
	 */
	public function setFees($fees)
	{
		$this->fees = $fees;
	}

	/**
	 * Retourne la valeur de $amount
	 *
	 * @return integer
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * Définit la valeur de $amount
	 *
	 * @param integer $amount
	 */
	public function setAmount($amount)
	{
		$this->amount = $amount;
	}

	/**
	 * Retourne la valeur de $partnerCode
	 *
	 * @return string
	 */
	public function getPartnerCode()
	{
		return $this->partnerCode;
	}

	/**
	 * Définit la valeur de $partnerCode
	 *
	 * @param string $partnerCode
	 */
	public function setPartnerCode($partnerCode)
	{
		$this->partnerCode = $partnerCode;
	}

	/**
	 * Retourne la valeur de $accountNumber
	 *
	 * @return string
	 */
	public function getAccountNumber()
	{
		return $this->accountNumber;
	}

	/**
	 * Définit la valeur de $accountNumber
	 *
	 * @param string $accountNumber
	 */
	public function setAccountNumber($accountNumber)
	{
		$this->accountNumber = $accountNumber;
	}

	/**
	 * Retourne la valeur de $customer
	 *
	 * @return string
	 */
	public function getCustomer()
	{
		return $this->customer;
	}

	/**
	 * Définit la valeur de $customer
	 *
	 * @param string $customer
	 */
	public function setCustomer($customer)
	{
		$this->customer = $customer;
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