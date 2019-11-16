<?php
namespace Digitalis\Core\Models\ViewModels;

use Digitalis\Core\Models\DbAdapters\ProfileDbAdapter;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\Entities\User;
use Digitalis\Core\Models\ViewModels\ViewModelInterface;


/**
 * UserViewModel Modèle pour la gestion des utilisateurs
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class UserViewModel implements ViewModelInterface
{
	/**
	 * Identifiant
	 *
	 * @IME\Id
	 * @IME\DataType{"type":"integer","nullable":true,"inputType":"hidden"}
	 * @var integer
	 */
	public $usr_id;

	/**
	 * Login de l'utilisateur
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"email","errMsg":"Valeur invalide pour ce champ"}
	 * @IME\Length{"max":80,"errMsg":"Ce champ n'admet pas plus de 80 caractères"}
	 * @var string
	 */
	public $login_usr;

	/**
	 * Profil de l'utilisateur
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"integer","errMsg":"Valeur invalide pour ce champ","inputType":"select"}
	 * @var integer
	 */
	public $profile_usr;

	/**
	 * Prénom de l'utilisateur
	 *
	 * @IME\DataType{"nullable":true}
	 * @IME\Length{"max":80,"errMsg":"Ce champ n'admet pas plus de 80 caractères"}
	 * @var string
	 */
	public $fname_usr;

	/**
	 * Nom de l'utilisateur
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":80,"errMsg":"Ce champ n'admet pas plus de 80 caractères"}
	 * @var string
	 */
	public $lname_usr;

	/**
	 * Fonction de l'utilisateur
	 *
	 * @IME\DataType{"nullable":true,"errMsg":"Valeur invalide pour ce champ"}
	 * @IME\Length{"max":100,"errMsg":"Ce champ n'admet pas plus de 100 caractères"}
	 * @var string
	 */
	public $function_usr;

	/**
	 * Mot de passe
	 *
	 * @IME\DataType{"nullable":true}
	 * @var string
	 */
	public $pwd_usr;

	public function fullName()
	{
		return trim($this->fname_usr . ' ' . $this->lname_usr);
	}

	public function toArray()
	{
		return [
			'id' => $this->usr_id,
			'login' => $this->login_usr,
			'pwd' => $this->pwd_usr,
			'profile' => $this->profile_usr,
			'fname' => $this->fname_usr,
			'lname' => $this->lname_usr,
			'function' => $this->function_usr
		];
	}

	/**
	 * Convertit en objet User
	 *
	 * @return User
	 */
	public function convertToEntity()
	{
		$this->pwd_usr = '220512cdce'; // Data::randomString(10, 1, 1, 1);
		$nuser = new User();
		$nuser->setId($this->usr_id);
		$nuser->setLogin($this->login_usr);
		$nuser->setPassword(Data::cryptPwd($this->pwd_usr));
		if ($this->profile_usr) {
			$profile = ProfileDbAdapter::getById($this->profile_usr);
			if ($profile) {
				$nuser->setProfile($profile);
			}
		}
		$nuser->setLastName($this->lname_usr);
		$nuser->setFirstName($this->fname_usr);
		$nuser->setFunction($this->function_usr);
		return $nuser;
	}

	/**
	 * Construit le UserViewModel
	 *
	 * @param User $var
	 * @return UserViewModel
	 */
	public static function buildFromEntity($var = null)
	{
		$uservm = new UserViewModel();
		if (!is_null($var)) {
			$uservm->usr_id = $var->getId();
			$uservm->login_usr = $var->getLogin();
			$uservm->pwd_usr = $var->getPassword();
			$uservm->profile_usr = $var->getProfile()->getId();
			$uservm->fname_usr = $var->getFirstName();
			$uservm->lname_usr = $var->getLastName();
			$uservm->function_usr = $var->getFunction();
		}
		return $uservm;
	}
}

