<?php
namespace Digitalis\Core\Models\ViewModels;

use Digitalis\Core\Models\Entities\Partner;

/**
 * PartnerViewModel Modèle de gestion de la vue
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class PartnerViewModel implements ViewModelInterface
{

	/**
	 * identifiant
	 *
	 * @IME\Id
	 * @IME\DataType{"type":"integer","errMsg":"Valeur invalide pour ce champ","nullable":true}
	 * @var integer
	 */
	public $tb_id_part;

	/**
	 * Code de l'institution
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"errMsg":"Valeur invalide pour ce champ"}
	 * @IME\Length{"max":10,"errMsg":"Ce champ n'admet pas plus de 10 caractères"}
	 * @var string
	 */
	public $tb_code_part;

	/**
	 * Nom de l'institution
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"errMsg":"Valeur invalide pour ce champ"}
	 * @IME\Length{"max":120,"errMsg":"Ce champ n'admet pas plus 120 caractères"}
	 * @var string
	 */
	public $tb_name_part;

	/**
	 * Retourne le modèle sous forme de tableau
	 *
	 * @return array
	 */
	public function toArray()
	{
		return [
			'id' => $this->tb_id_part,
			'code' => $this->tb_code_part,
			'name' => $this->tb_name_part
		];
	}

	public function convertToEntity()
	{
		$part = new Partner();
		$part->setCode($this->tb_code_part);
		$part->setName($this->tb_name_part);
		$part->setId($this->tb_id_part);
		return $part;
	}

	/**
	 * Permet de construit le modèle de vue à partir de l'objet de la base de données
	 *
	 * @param Partner $partner
	 * @return PartnerViewModel
	 */
	public static function buildFromEntity($partner = null)
	{
		$vmodel = new PartnerViewModel();
		if (!is_null($partner)) {
			$vmodel->tb_id_part = $partner->getId();
			$vmodel->tb_code_part = $partner->getCode();
			$vmodel->tb_name_part = $partner->getName();
		}
		return $vmodel;
	}
}