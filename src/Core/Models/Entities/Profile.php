<?php
namespace Digitalis\Core\Models\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Digitalis\Core\Models\Entities\User;

/**
 * Profile Profile de l'utilisateur connecté
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 * @ORM\Entity
 * @ORM\Table(name="profile")
 */
class Profile
{

	/**
	 * Identifiant du profile
	 * 
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(name="prof_id",type="integer")
	 * @var integer
	 */
	private $id;

	/**
	 * Code du profil
	 * 
	 * @ORM\Column(name="prof_code",length=15,unique=true)
	 * @var string
	 */
	private $code;

	/**
	 * Description du profil
	 * 
	 * @ORM\Column(name="prof_desc",length=155,nullable=true)
	 * @var string
	 */
	private $description;

	/**
	 * Statut du profil
	 * 
	 * @ORM\Column(name="prof_status",type="boolean",options={"default":true})
	 * @var boolean
	 */
	private $status;

	/**
	 * Date de création du profil
	 * 
	 * @ORM\Column(name="prof_datecreate",type="datetime")
	 * @var \DateTime
	 */
	private $dateCreate;


	/**
	 * Les utilisateurs ayant ce profil
	 * @ORM\OneToMany(targetEntity=\Digitalis\Core\Models\Entities\User::class,cascade={"persist","merge"},mappedBy="profile",fetch="EXTRA_LAZY")
	 * @var User[]|ArrayCollection
	 */
	private $users;

	public function __construct($code = null, $description = null)
	{
		$this->code = $code;
		$this->description = $description;
		$this->users = new ArrayCollection();
		$this->dateCreate = new \DateTime();
		$this->status = true;
	}

	/**
	 * Retourne la valeur de $status
	 *
	 * @return boolean
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Définit la valeur de $status
	 *
	 * @param boolean $status
	 */
	public function setStatus($status = true)
	{
		$this->status = $status;
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
	 * Retourne la valeur de $users
	 *
	 * @return User[]|ArrayCollection
	 */
	public function getUsers()
	{
		return $this->users;
	}

	/**
	 * Définit la valeur de $users
	 *
	 * @param User $users
	 */
	public function setUsers($users)
	{
		$this->users = $users;
	}

	/**
	 * Permet d'ajouter un utilisateur à la collection d'utilisateur du profil
	 *
	 * @param User $user
	 * @return void
	 */
	public function addUser(User $user)
	{
		if (!$this->users->contains($user)) {
			$user->setProfile($this);
			$this->users->add($user);
		}
	}

	/**
	 * Retourne la valeur de $description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Définit la valeur de $description
	 *
	 * @param string $description
	 */
	public function setDescription($description = null)
	{
		$this->description = $description;
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