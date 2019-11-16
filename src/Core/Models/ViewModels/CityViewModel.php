<?php
namespace Digitalis\Core\Models\ViewModels;

use Digitalis\Core\Models\DbAdapters\RegionDbAdapter;
use Digitalis\Core\Models\Entities\City;


/**
 * CityViewModel Modèle de gestion des interface pour les villes
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class CityViewModel implements ViewModelInterface
{
	/**
	 * Identifiant
	 *
	 * @IME\Id
	 * @IME\DataType{"type":"integer","nullable":true}
	 * @var integer
	 */
	public $tb_cty_id;

	/**
	 * code de la région
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":"20","errMsg":"Ce champ n'admet pas plus de 20 caractères"}
	 * @var string
	 */
	public $tb_cty_code;

	/**
	 * Libellé/Description de la région
	 *
	 * @IME\Length{"max":"128","errMsg":"Ce champ n'admet pas plus de 128 caractères"}
	 * @IME\DataType{"nullable":true}
	 * @var string
	 */
	public $tb_cty_label;

	/**
	 * Pays de localisation de la région
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"integer"}
	 * @var integer
	 */
	public $tb_cty_region;

	public function toArray()
	{
		return [
			'id' => $this->tb_cty_id,
			'code' => $this->tb_cty_code,
			'label' => $this->tb_cty_label,
			'region' => $this->tb_cty_region
		];
	}

	/**
	 * Contruit une région destiné à la base de données
	 *
	 * @return City
	 */
	public function convertToEntity()
	{
		$ncity = new City();
		$ncity->setId($this->tb_cty_id);
		$ncity->setCode($this->tb_cty_code);
		$ncity->setLabel($this->tb_cty_label);
		$region = RegionDbAdapter::getById($this->tb_cty_region);
		if ($region) {
			$ncity->setRegion($region);
		}
		return $ncity;
	}

	/**
	 * Construit le viewMode à partir de l'objet City
	 *
	 * @param City $region
	 * @return CityViewModel
	 */
	public static function buildFromEntity($region = null)
	{
		$vmodel = new CityViewModel();
		if (!is_null($region)) {
			$vmodel->tb_cty_id = $region->getId();
			$vmodel->tb_cty_code = $region->getCode();
			$vmodel->tb_cty_label = $region->getLabel();
			$vmodel->tb_cty_region = $region->getRegion()->getId();
		}
		return $vmodel;
	}
}
