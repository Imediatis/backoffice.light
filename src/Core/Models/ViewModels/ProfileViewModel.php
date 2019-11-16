<?php
namespace Digitalis\Core\Models\ViewModels;

use Digitalis\Core\Models\Entities\Profile;
use Digitalis\Core\Models\ViewModels\ViewModelInterface;



/**
 * ProfileViewModel Modèle de gestion de profil
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class ProfileViewModel implements ViewModelInterface
{
	/**
	 * Identifiant du profil
	 *
	 * @IME\Id
	 * @IME\DataType{"type":"integer","nullable":true,"inputType":"hidden"}
	 * @var integer
	 */
	public $prof_id;

	/**
	 * code du profil
	 *
	 * @IME\Required{"errMsg":"Le code est obligatoire"}
	 * @IME\Length{"max":15,"errMsg":"Valeur invalide pour ce champ; pas plus de 15 caractères"}
	 * @var string
	 */
	public $code;

	/**
	 * Description du profile
	 *
	 * @IME\DataType{"nullable":true}
	 * @IME\Length{"max":155,"errMsg":"Valeur invalide pour ce champ; pas plus de 155 caractères"}
	 * @var string
	 */
	public $description;

	public function toArray()
	{
		return [
			'id' => $this->prof_id,
			'code' => $this->code,
			'description' => $this->description
		];
	}

	public function convertToEntity()
	{
		$profile = new Profile($this->code, $this->description);
		$profile->setId($this->prof_id);
		return $profile;
	}

	public static function buildFromEntity($profile = null)
	{
		$vmprof = new ProfileViewModel();
		if (!is_null($profile)) {
			$vmprof->prof_id = $profile->getId();
			$vmprof->code = $profile->getCode();
			$vmprof->description = $profile->getDescription();
		}

		return $vmprof;
	}

}