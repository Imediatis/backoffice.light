<?php
namespace Digitalis\Core\Models\Security;

use Digitalis\Core\Models\ViewModels\ViewModelInterface;
use Imediatis\EntityAnnotation\ModelState;


/**
 * ChangePwdViewModel ViewModel pour le changement de mot de passe
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class ChangePwdViewModel implements ViewModelInterface
{
	/**
	 * Login de la personne qui doit changer le mot de passe
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @var string
	 */
	public $tb_usr_login;

	/**
	 * Mot de passe actuel de l'utilisateur
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"password"}
	 * @var string
	 */
	public $tb_usr_curentPwd;

	/**
	 * Nouveau mot de passe
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"min":8,"errMsg":"Le mot de passe doit avoir au moins 8 caractères"}
	 * @IME\DataType{"type":"password"}
	 * @var string
	 */
	public $tb_usr_newPwd;

	/**
	 * Confirmation du nouveau mot de passe
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"min":8,"errMsg":"Le mot de passe doit avoir au moins 8 caractères"}
	 * @IME\DataType{"type":"password"}
	 * @var string
	 */
	public $tb_usr_confirmNewPwd;

	public function toArray()
	{
		return [
			'login' => $this->tb_usr_login,
			'currentPwd' => $this->tb_usr_curentPwd,
			'newPwd' => $this->tb_usr_newPwd,
			'confNewPwd' => $this->tb_usr_confirmNewPwd
		];
	}
	public function convertToEntity()
	{

	}

	public function __construct($login = null)
	{
		$this->tb_usr_login = $login;
	}

	/**
	 * Construit le modèle à partir d'un autre objet
	 *
	 * @param LoggedUser $var
	 * @return ChangePwdViewModel
	 */
	public static function buildFromEntity($var = null)
	{
		$vmodel = new ChangePwdViewModel();
		if (!is_null($var)) {
			$vmodel->tb_usr_login = $var->getLogin();
		}
		return $vmodel;
	}

	public function validate()
	{
		if (strcmp($this->tb_usr_curentPwd, $this->tb_usr_newPwd) == 0) {
			ModelState::setValidity(false);
			ModelState::setMessage('tb_usr_newPwd', 'Le nouveau mot de passe dois être différent du mot de passe actuel');
		}

		if (strcmp($this->tb_usr_newPwd, $this->tb_usr_confirmNewPwd) != 0) {
			ModelState::setValidity(false);
			ModelState::setMessage('tb_usr_confirmNewPwd', 'Le mot de passe de confirmation dois être identique au nouveau mot de passe');
		}
	}
}