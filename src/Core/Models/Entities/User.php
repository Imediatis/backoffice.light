<?php

namespace Digitalis\Core\Models\Entities;

use DateInterval;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Digitalis\Core\Models\Entities\Profile;

/**
 * User Utilisateur du système
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="syst_user")
 */
class User
{

	/**
	 * Identifiant de l'utilisateur
	 * 
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(name="usr_id",type="integer")
	 * @var integer
	 */
	private $id;

	/**
	 * Login de l'utilisateur (adresse mail de l'utilisateur)
	 * 
	 * @ORM\Column(name="usr_login",length=80,unique=true)
	 * @var string
	 */
	private $login;

	/**
	 * Mot de passe de l'utilisateur
	 * 
	 * @ORM\Column(name="usr_pwd",length=255)
	 * @var string
	 */
	private $password;

	/**
	 * Prénom de l'utilisateur
	 * 
	 * @ORM\Column(name="usr_fname",length=80,nullable=true)
	 * @var string
	 */
	private $firstName;

	/**
	 * Nom de l'utilisateur
	 * 
	 * @ORM\Column(name="usr_lname",length=80)
	 * @var string
	 */
	private $lastName;

	/**
	 * Fonction de l'utilisateur au sein de l'organisation
	 * 
	 * @ORM\Column(name="usr_function",length=150,nullable=true)
	 * @var string
	 */
	private $function;

	/**
	 * Statut de l'utilisateur
	 * 
	 * @ORM\Column(name="usr_status",type="smallint",options={"default":2})
	 * @var integer
	 */
	private $status;

	/**
	 * Date à laquelle ce utilisateur a été ajouté au système
	 * 
	 * @ORM\Column(name="usr_datecreate",type="datetime")
	 * @var \DateTime
	 */
	private $dateCreate;

	/**
	 * Date de la dernière connexion au système
	 * 
	 * @ORM\Column(name="usr_lastlogin",type="datetime",nullable=true)
	 * @var \DateTime
	 */
	private $lastLogin;

	/**
	 * Adresse ip du poste à partir duquel l'utilisateur s'est connecté pour la dernière fois
	 * 
	 * @ORM\Column(name="usr_lastiplogin",length=50,nullable=true)
	 * @var string
	 */
	private $lastIpLogin;

	/**
	 * Date à laquell l'utilisateur s'est déconnecté pour la dernière fois
	 * 
	 * @ORM\Column(name="usr_lastlogout",type="datetime",nullable=true)
	 * @var \DateTime
	 */
	private $lastLogout;

	/**
	 * Adresse ip du dernier poste où l'utilisateur s'est déconnété
	 * 
	 * @ORM\Column(name="usr_lastiplogout",length=50,nullable=true)
	 * @var string
	 */
	private $lastIpLogout;

	/**
	 * Profile de l'utilisateur
	 * 
	 * @ORM\ManyToOne(targetEntity=\Digitalis\Core\Models\Entities\Profile::class, inversedBy="users")
	 * @ORM\JoinColumn(name="prof_id",referencedColumnName="prof_id",nullable=false,onDelete="RESTRICT")
	 * @var Profile
	 */
	private $profile;

	/**
	 * date de la dènière action de l'utilisateur
	 * 
	 * @ORM\Column(name="usr_lastaction",type="datetime",nullable=true)
	 * @var \DateTime
	 */
	private $lastAction;

	/**
	 * Détermine si l'utilisateur est connecté ou pas
	 * 
	 * @ORM\Column(name="usr_islogged",type="boolean",options={"default":false})
	 * @var boolean
	 */
	private $isLogged;

	/**
	 * Token qui permet de controler le statut de connexion de l'utilisateur
	 * 
	 * @ORM\Column(name="usr_token",length=500,nullable=true)
	 * @var string
	 */
	private $userToken;

	/**
	 * Délais de validité du token
	 * 
	 * @ORM\Column(name="usr_token_expire_date",type="datetime",nullable=true)
	 * @var \DateTime
	 */
	private $tokenExpireDate;

	public function __construct()
	{
		$this->dateCreate = new \DateTime();
		$this->status = 2;
		$this->isLogged = false;
	}

	/**
	 * Détermine si le token est expiré
	 *
	 * @return boolean
	 */
	public function tokenExpired()
	{
		$current = new DateTime();
		if (is_null($this->tokenExpireDate))
			return true;
		return ($this->tokenExpireDate->getTimestamp() - $current->getTimestamp()) < 0;
	}

	/**
	 * Retourne la valeur de $tokenExpireDate
	 *
	 * @return \DateTime
	 */
	public function getTokenExpireDate()
	{
		return $this->tokenExpireDate;
	}

	/**
	 * Définit la valeur de $tokenExpireDate
	 *
	 * @param \DateTime $tokenExpireDate
	 */
	public function setTokenExpireDate($tokenExpireDate)
	{
		$this->tokenExpireDate = $tokenExpireDate;
	}


	/**
	 * Retourne la valeur de $userToken
	 *
	 * @return string
	 */
	public function getUserToken()
	{
		return $this->userToken;
	}

	/**
	 * Définit la valeur de $userToken
	 *
	 * @param string $userToken
	 */
	public function setUserToken($userToken)
	{
		$this->userToken = $userToken;
	}

	/**
	 * Retourne la valeur de $isLogged
	 *
	 * @return boolean
	 */
	public function getIsLogged()
	{
		return $this->isLogged;
	}

	/**
	 * Définit la valeur de $isLogged
	 *
	 * @param boolean $isLogged
	 */
	public function setIsLogged($isLogged = true)
	{
		$this->isLogged = $isLogged;
	}

	/**
	 * Retourne la valeur de $lastAction
	 *
	 * @return \DateTime
	 */
	public function getLastAction()
	{
		return $this->lastAction;
	}

	/**
	 * Définit la valeur de $lastAction
	 *
	 * @param \DateTime $lastAction
	 */
	public function setLastAction($lastAction)
	{
		$this->lastAction = $lastAction;
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
	 * Retourne la valeur de $profile
	 *
	 * @return Profile
	 */
	public function getProfile()
	{
		return $this->profile;
	}

	/**
	 * Définit la valeur de $profile
	 *
	 * @param Profile $profile
	 */
	public function setProfile(Profile $profile)
	{
		$this->profile = $profile;
	}
	/**
	 * Retourne la valeur de $lastIpLogout
	 *
	 * @return string
	 */
	public function getLastIpLogout()
	{
		return $this->lastIpLogout;
	}

	/**
	 * Définit la valeur de $lastIpLogout
	 *
	 * @param string $lastIpLogout
	 */
	public function setLastIpLogout($lastIpLogout = null)
	{
		$this->lastIpLogout = $lastIpLogout;
	}

	/**
	 * Retourne la valeur de $lastLogout
	 *
	 * @return \DateTime
	 */
	public function getLastLogout()
	{
		return $this->lastLogout;
	}

	/**
	 * Définit la valeur de $lastLogout
	 *
	 * @param \DateTime $lastLogout
	 */
	public function setLastLogout($lastLogout = null)
	{
		$this->lastLogout = $lastLogout;
	}

	/**
	 * Retourne la valeur de $lastIpLogin
	 *
	 * @return string
	 */
	public function getLastIpLogin()
	{
		return $this->lastIpLogin;
	}

	/**
	 * Définit la valeur de $lastIpLogin
	 *
	 * @param string $lastIpLogin
	 */
	public function setLastIpLogin($lastIpLogin = null)
	{
		$this->lastIpLogin = $lastIpLogin;
	}

	/**
	 * Retourne la valeur de $lastLogin
	 *
	 * @return \DateTime
	 */
	public function getLastLogin()
	{
		return $this->lastLogin;
	}

	/**
	 * Définit la valeur de $lastLogin
	 *
	 * @param \DateTime $lastLogin
	 */
	public function setLastLogin($lastLogin = null)
	{
		$this->lastLogin = $lastLogin;
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
	 * Retourne la valeur de $function
	 *
	 * @return string
	 */
	public function getFunction()
	{
		return $this->function;
	}

	/**
	 * Définit la valeur de $function
	 *
	 * @param string $function
	 */
	public function setFunction($function = null)
	{
		$this->function = $function;
	}

	/**
	 * Retourne la valeur de $lastName
	 *
	 * @return string
	 */
	public function getLastName()
	{
		return $this->lastName;
	}

	/**
	 * Définit la valeur de $lastName
	 *
	 * @param string $lastName
	 */
	public function setLastName($lastName = null)
	{
		$this->lastName = $lastName;
	}
	/**
	 * Retourne la valeur de $password
	 *
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * Définit la valeur de $password
	 *
	 * @param string $password
	 */
	public function setPassword($password)
	{
		$this->password = $password;
	}

	/**
	 * Retourne la valeur de $firstName
	 *
	 * @return string
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * Définit la valeur de $firstName
	 *
	 * @param string $firstName
	 */
	public function setFirstName($firstName = null)
	{
		$this->firstName = $firstName;
	}

	/**
	 * Retourne la valeur de $login
	 *
	 * @return string
	 */
	public function getLogin()
	{
		return $this->login;
	}

	/**
	 * Définit la valeur de $login
	 *
	 * @param string $login
	 */
	public function setLogin($login)
	{
		$this->login = $login;
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
