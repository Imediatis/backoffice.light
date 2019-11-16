<?php
namespace Digitalis\Core\Models\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * LogBranchBox log d'une action sur une caisse/agence
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="branchboxlogs",
 * 									indexes={
 *              				 		@ORM\Index(name="IDX_LOG_COMPONENT", columns={"log_type"}),
 * 										@ORM\Index(name="IDX_LOG_CODE_UNIT", columns={"log_codeunit"}),
 * 										@ORM\Index(name="IDX_LOG_DATE_ACTION", columns={"log_dateaction"})
 *            						}
 * )
 */
class LogBranchBox
{

	/**
	 * Identifiant du log
	 * 
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(name="log_id",type="integer",options={"unsigned":true})
	 * @var integer
	 */
	private $id;

	/**
	 * Composant du système dont on veut enregistrer la trace (Agence/Caisse)
	 * 
	 * @ORM\Column(name="log_type",length=20)
	 * @var string
	 */
	private $component;

	/**
	 * Action effectué sur le composant
	 * 
	 * @ORM\Column(name="log_action",length=4000)
	 * @var string
	 */
	private $action;

	/**
	 * Utilisateur menant l'action
	 * 
	 * @ORM\Column(name="log_useraction",length=80)
	 * @var string
	 */
	private $userAction;

	/**
	 * Code de du composant affecté
	 * 
	 * @ORM\Column(name="log_codeunit",length=20)
	 * @var string
	 */
	private $code;

	/**
	 * Date de mouvement sur le composant
	 * 
	 * @ORM\Column(name="log_dateaction",type="datetime")
	 * @var \DateTime
	 */
	private $dateAction;

	/**
	 * Adresse à partir de laquelle l'utilisateur a réalisé l'action
	 * 
	 * @ORM\Column(name="log_location",length=50,nullable=true)
	 * @var string
	 */
	private $location;
	public function __construct()
	{
		$this->dateAction = new \DateTime();
	}

	/**
	 * Retourne la valeur de $location
	 *
	 * @return string
	 */
	public function getLocation()
	{
		return $this->location;
	}

	/**
	 * Définit la valeur de $location
	 *
	 * @param string $location
	 */
	public function setLocation($location = null)
	{
		$this->location = $location;
	}

	/**
	 * Retourne la valeur de $dateAction
	 *
	 * @return \DateTime
	 */
	public function getDateAction()
	{
		return $this->dateAction;
	}

	/**
	 * Définit la valeur de $dateAction
	 *
	 * @param \DateTime $dateAction
	 */
	public function setDateAction($dateAction)
	{
		$this->dateAction = $dateAction;
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
	 * Retourne la valeur de $userAction
	 *
	 * @return string
	 */
	public function getUserAction()
	{
		return $this->userAction;
	}

	/**
	 * Définit la valeur de $userAction
	 *
	 * @param string $userAction
	 */
	public function setUserAction($userAction)
	{
		$this->userAction = $userAction;
	}

	/**
	 * Retourne la valeur de $action
	 *
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Définit la valeur de $action
	 *
	 * @param string $action
	 */
	public function setAction($action)
	{
		$this->action = $action;
	}

	/**
	 * Retourne la valeur de $component
	 *
	 * @return string
	 */
	public function getComponent()
	{
		return $this->component;
	}

	/**
	 * Définit la valeur de $component
	 *
	 * @param string $component
	 */
	public function setComponent($component)
	{
		$this->component = $component;
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