<?php
namespace Digitalis\Core\Models\ViewModels;

use Digitalis\Core\Models\DbAdapters\ProfileDbAdapter;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DbAdapters\EntrepriseDbAdapter;
use Digitalis\Core\Models\DbAdapters\OperatorDbAdapter;
use Digitalis\Core\Models\Entities\Operator;

/**
 * OperatorViewModel View model pour la gestion des opérateurs
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class OperatorViewModel
{
	/**
	 * Identifiant
	 *
	 * @IME\Id
	 * @IME\DataType{"type":"integer","nullable":true,"inputType":"hidden"}
	 * @var integer
	 */
	public $ope_id;

	/**
	 * Login de l'utilisateur
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"errMsg":"Valeur invalide pour ce champ"}
	 * @IME\Length{"max":80,"errMsg":"Ce champ n'admet pas plus de 80 caractères"}
	 * @var string
	 */
	public $login_ope;

	/**
	 * Profil de l'utilisateur
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"integer","errMsg":"Valeur invalide pour ce champ","inputType":"select"}
	 * @var integer
	 */
	public $profile_ope;

	/**
	 * Prénom de l'utilisateur
	 *
	 * @IME\DataType{"nullable":true}
	 * @IME\Length{"max":80,"errMsg":"Ce champ n'admet pas plus de 80 caractères"}
	 * @var string
	 */
	public $fname_ope;

	/**
	 * Nom de l'utilisateur
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":80,"errMsg":"Ce champ n'admet pas plus de 80 caractères"}
	 * @var string
	 */
	public $lname_ope;

	/**
	 * Fonction de l'utilisateur
	 *
	 * @IME\DataType{"type":"email", "nullable":true,"errMsg":"Valeur invalide pour ce champ"}
	 * @IME\Length{"max":80,"errMsg":"Ce champ n'admet pas plus de 100 caractères"}
	 * @var string
	 */
	public $email_ope;

	/**
	 * Mot de passe
	 *
	 * @IME\DataType{"nullable":true}
	 * @var string
	 */
	public $pwd_ope;

	/**
	 * Entreprise où travaille l'opérateur
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"integer","errMsg":"Valeur invalide pour ce champ"}
	 * @var integer
	 */
	public $entrep_ope;

	public function fullName()
	{
		return trim($this->fname_ope . ' ' . $this->lname_ope);
	}

	public function toArray()
	{
		return [
			'id' => $this->ope_id,
			'login' => $this->login_ope,
			'pwd' => $this->pwd_ope,
			'profile' => $this->profile_ope,
			'fname' => $this->fname_ope,
			'lname' => $this->lname_ope,
			'email' => $this->email_ope,
			'entrep' => $this->entrep_ope
		];
	}

	/**
	 * Convertit en objet User
	 *
	 * @return Operator
	 */
	public function convertToEntity()
	{
		$this->pwd_ope = OperatorDbAdapter::DEF_PWD; // Data::randomString(10, 1, 1, 1);
		$nuser = new Operator();
		$nuser->setId($this->ope_id);
		$nuser->setLogin($this->login_ope);
		$nuser->setPassword(Data::cryptPwd($this->pwd_ope));
		if ($this->profile_ope) {
			$profile = ProfileDbAdapter::getById($this->profile_ope);
			if ($profile) {
				$nuser->setProfile($profile);
			}
		}
		$entrep = EntrepriseDbAdapter::getById($this->entrep_ope);
		if ($entrep) {
			$nuser->setEntreprise($entrep);
		}
		$nuser->setLastName($this->lname_ope);
		$nuser->setFirstName($this->fname_ope);
		$nuser->setEmail($this->email_ope);
		return $nuser;
	}

	/**
	 * Construit le UserViewModel
	 *
	 * @param Operator $var
	 * @return OperatorViewModel
	 */
	public static function buildFromEntity($var = null)
	{
		$uservm = new OperatorViewModel();
		if (!is_null($var)) {
			$uservm->ope_id = $var->getId();
			$uservm->login_ope = $var->getLogin();
			$uservm->pwd_ope = $var->getPassword();
			$uservm->profile_ope = $var->getProfile()->getId();
			$uservm->fname_ope = $var->getFirstName();
			$uservm->lname_ope = $var->getLastName();
			$uservm->email_ope = $var->getEmail();
			$uservm->entrep_ope = $var->getEntreprise()->getId();
		}
		return $uservm;
	}
}
