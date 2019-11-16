<?php

namespace Digitalis\Core\Models\Entities;

use DateTime;
use Digitalis\Core\Models\Security\LoggedUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Operator Opérateur travaillant dans une agence
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="operators")
 */
class Operator
{
	/**
	 * Identifiant de l'utilisateur
	 * 
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(name="ope_id",type="integer")
	 * @var integer
	 */
	private $id;

	/**
	 * Login de l'utilisateur (adresse mail de l'utilisateur)
	 * 
	 * @ORM\Column(name="ope_login",length=80,unique=true)
	 * @var string
	 */
	private $login;

	/**
	 * Mot de passe de l'utilisateur
	 * 
	 * @ORM\Column(name="ope_pwd",length=255)
	 * @var string
	 */
	private $password;

	/**
	 * Prénom de l'utilisateur
	 * 
	 * @ORM\Column(name="ope_fname",length=80,nullable=true)
	 * @var string
	 */
	private $firstName;

	/**
	 * Nom de l'utilisateur
	 * 
	 * @ORM\Column(name="ope_lname",length=80)
	 * @var string
	 */
	private $lastName;

	/**
	 * Statut de l'utilisateur
	 * 
	 * @ORM\Column(name="ope_status",type="smallint",options={"default":2})
	 * @var integer
	 */
	private $status;

	/**
	 * Date à laquelle ce utilisateur a été ajouté au système
	 * 
	 * @ORM\Column(name="ope_datecreate",type="datetime")
	 * @var \DateTime
	 */
	private $dateCreate;

	/**
	 * Date de la dernière connexion au système
	 * 
	 * @ORM\Column(name="ope_lastlogin",type="datetime",nullable=true)
	 * @var \DateTime
	 */
	private $lastLogin;

	/**
	 * Adresse ip du poste à partir duquel l'utilisateur s'est connecté pour la dernière fois
	 * 
	 * @ORM\Column(name="ope_lastiplogin",length=50,nullable=true)
	 * @var string
	 */
	private $lastIpLogin;

	/**
	 * Date à laquell l'utilisateur s'est déconnecté pour la dernière fois
	 * 
	 * @ORM\Column(name="ope_lastlogout",type="datetime",nullable=true)
	 * @var \DateTime
	 */
	private $lastLogout;

	/**
	 * Adresse ip du dernier poste où l'utilisateur s'est déconnété
	 * 
	 * @ORM\Column(name="ope_lastiplogout",length=50,nullable=true)
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
	 * @ORM\Column(name="ope_lastaction",type="datetime",nullable=true)
	 * @var \DateTime
	 */
	private $lastAction;

	/**
	 * Détermine si l'utilisateur est connecté ou pas
	 * 
	 * @ORM\Column(name="ope_islogged",type="boolean",options={"default":false})
	 * @var boolean
	 */
	private $isLogged;

	/**
	 * Adresse mail de l'utilisateur
	 * 
	 * @ORM\Column(name="ope_email",length=80,nullable=true)
	 * @var string
	 */
	private $email;

	/**
	 * Caisse dans laquelle travaille l'opérateur
	 * 
	 * @ORM\OneToOne(targetEntity=\Digitalis\Core\Models\Entities\Caisse::class,mappedBy="operator")
	 * @var Caisse
	 */
	private $caisse;

	/**
	 * Entreprise d'appartenance de l'opérateur
	 * 
	 * @ORM\ManyToOne(targetEntity=\Digitalis\Core\Models\Entities\Entreprise::class,cascade={"merge","persist"},inversedBy="operators")
	 * @ORM\JoinColumn(name="ent_id",referencedColumnName="ent_id",nullable=false,onDelete="RESTRICT")
	 * @var Entreprise
	 */
	private $entreprise;

	/**
	 * Les transaction réalisé par cet opérateur
	 * 
	 * @ORM\OneToMany(targetEntity=\Digitalis\Core\Models\Entities\Transaction::class, mappedBy="operator",cascade={"merge","persist"},fetch="EXTRA_LAZY")
	 * @ORM\OrderBy({"transDate"="desc"})
	 * @var Transaction[]|ArrayCollection
	 */
	private $transactions;

	/**
	 * Token de contrôle de l'action de l'utilisateur
	 * 
	 * @ORM\Column(name="ope_token",length=500,nullable=true)
	 * @var string
	 */
	private $opToken;

	/**
	 * Temps de validité du token
	 * 
	 * @ORM\Column(name="ope_token_expire_date",type="datetime", nullable=true)
	 * @var \DateTime
	 */
	private $tokenExpireDate;

	public function __construct()
	{
		$this->dateCreate = new \DateTime();
		$this->transactions = new ArrayCollection();
		$this->status = 2;
		$this->isLogged = false;
	}

	/**
	 * Détermine si le token de sécurité est expiré
	 *
	 * @return boolean
	 */
	public function tokenExpired()
	{
		if (is_null($this->tokenExpireDate))
			return true;
		$current = new DateTime();
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
	 * Retourne la valeur de $opToken
	 *
	 * @return string
	 */
	public function getToken()
	{
		return $this->opToken;
	}

	/**
	 * Définit la valeur de $opToken
	 *
	 * @param string $opToken
	 */
	public function setToken($opToken)
	{
		$this->opToken = $opToken;
	}

	/**
	 * Retourne l'opérateur sour forme de tableau
	 *
	 * @param boolean $addcaisse détermine s'il faut inclure la caisse ou pas
	 * @param boolean $addentrep Détermine s'il faut ajouter l'entreprise au résultat
	 * @return array
	 */
	public function toArray($addcaisse = true, $addentrep = true)
	{
		$ope = [
			'login' => $this->login,
			'firstName' => $this->firstName,
			'lastName' => $this->lastName,
			'status' => $this->status,
			'profile' => !is_null($this->profile) ? $this->profile->getCode() : null,
			'isLogged' => $this->isLogged,
			'email' => $this->email,
			'lastLogin' => !is_null($this->lastLogin) ? $this->lastLogin->format('Y-m-d H:i:s') : null,
			'lastLogout' => !is_null($this->lastLogout) ? $this->lastLogout->format('Y-m-d H:i:s') : null,
			'token' => $this->opToken
		];
		if ($addentrep) {
			$ope['entreprise'] = $this->entreprise->toArray();
		}
		if ($addcaisse) {
			$ope['caisse'] = !is_null($this->caisse) ? $this->caisse->toArray() : null;
		}
		return $ope;
	}

	/**
	 * Retourne l'opérateur sous forme d'utilisateur connecté
	 *
	 * @return LoggedUser
	 */
	public function toLoggedUser()
	{
		$loggeduser = new LoggedUser($this->login, $this->lastName, $this->firstName, $this->profile->getDescription(), 'Caissier');
		$loggeduser->boxCode = $this->caisse->getCode();
		$loggeduser->branchCode = $this->caisse->getAgence()->getCode();
		$loggeduser->branchName = $this->caisse->getAgence()->getLabel();
		$loggeduser->boxIsOpened = $this->caisse->getIsOpened();
		$loggeduser->branchIsOpened = $this->caisse->getAgence()->getIsOpened();
		$loggeduser->token = $this->opToken;
		return $loggeduser;
	}

	/**
	 * Retourne la valeur de $transactions
	 *
	 * @return Transaction[]|ArrayCollection
	 */
	public function getTransactions()
	{
		return $this->transactions;
	}

	/**
	 * Définit la valeur de $transactions
	 *
	 * @param Transaction[]|ArrayCollection $transactions
	 */
	public function setTransactions($transactions)
	{
		$this->transactions = $transactions;
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
	public function setCaisse($caisse = null)
	{
		$this->caisse = $caisse;
	}

	/**
	 * Retourne la valeur de $email
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Définit la valeur de $email
	 *
	 * @param string $email
	 */
	public function setEmail($email = null)
	{
		$this->email = $email;
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
