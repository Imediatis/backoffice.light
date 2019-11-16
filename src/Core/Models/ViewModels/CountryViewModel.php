<?php
namespace Digitalis\Core\Models\ViewModels;

use Digitalis\Core\Models\Entities\Country;


/**
 * CountryViewModel Model pour pour la gestion des pays
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class CountryViewModel implements ViewModelInterface
{

	/**
	 * Identifiant du pays
	 *
	 * @IME\Id
	 * @IME\DataType{"type":"integer","nullable":true}
	 * @var integer
	 */
	public $tb_coun_id;

	/**
	 * Code iso à 2 caractères pour le pays
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":2,"min":2,"errMsg":"Ce champ attend exactement 2 caractères"}
	 * @var string
	 */
	public $tb_code_alpha_2;

	/**
	 * Code iso à 3 caractères pour le pays
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":3,"errMsg":"Ce champ attend 3 caractères au maximum"}
	 * @var string
	 */
	public $tb_code_alpha_3;

	/**
	 * Nom en anglais du pays
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":120,"errMsg":"Ce champ n'accepte pas plus de 128 caractères"}
	 * @var string
	 */
	public $tb_coun_en_name;

	/**
	 * Nom en français du pays
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":120,"errMsg":"Ce champ n'accepte pas plus de 128 caractères"}
	 * @var string
	 */
	public $tb_coun_fr_name;

	public function toArray()
	{
		return [
			'id' => $this->tb_coun_id,
			'alpha2' => $this->tb_code_alpha_2,
			'alpha3' => $this->tb_code_alpha_3,
			'enName' => $this->tb_coun_en_name,
			'frName' => $this->tb_coun_fr_name
		];
	}

	/**
	 * 
	 *
	 * @return Country
	 */
	public function convertToEntity()
	{
		$vmodel = new Country();
		$vmodel->setFrName($this->tb_coun_fr_name);
		$vmodel->setEnName($this->tb_coun_en_name);
		$vmodel->setAlpha2(strtoupper($this->tb_code_alpha_2));
		$vmodel->setAlpha3(strtoupper($this->tb_code_alpha_3));
		$vmodel->setId($this->tb_coun_id);

		return $vmodel;
	}

	/**
	 *
	 * @param Country $country
	 * @return CountryViewModel
	 */
	public static function buildFromEntity($country = null)
	{
		$vmodel = new CountryViewModel();
		if (!is_null($country)) {
			$vmodel->tb_coun_id = $country->getId();
			$vmodel->tb_coun_fr_name = $country->getFrName();
			$vmodel->tb_coun_en_name = $country->getEnName();
			$vmodel->tb_code_alpha_2 = $country->getAlpha2();
			$vmodel->tb_code_alpha_3 = $country->getAlpha3();
		}
		return $vmodel;
	}
}