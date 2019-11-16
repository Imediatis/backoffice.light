<?php
namespace Digitalis\Core\Models\ViewModels;

use Digitalis\Core\Models\Entities\Region;
use Digitalis\Core\Models\DbAdapters\CountryDbAdapter;


/**
 * RegionViewModel Modèle de gistion des régions
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class RegionViewModel implements ViewModelInterface
{
	/**
	 * Identifiant
	 *
	 * @IME\Id
	 * @IME\DataType{"type":"integer","nullable":true}
	 * @var integer
	 */
	public $tb_rg_id;

	/**
	 * code de la région
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":"20","errMsg":"Ce champ n'admet pas plus de 20 caractères"}
	 * @var string
	 */
	public $tb_rg_code;

	/**
	 * Libellé/Description de la région
	 *
	 * @IME\Length{"max":"128","errMsg":"Ce champ n'admet pas plus de 128 caractères"}
	 * @IME\DataType{"nullable":true}
	 * @var string
	 */
	public $tb_rg_label;

	/**
	 * Pays de localisation de la région
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"integer"}
	 * @var integer
	 */
	public $tb_rg_country;

	public function toArray()
	{
		return [
			'id' => $this->tb_rg_id,
			'code' => $this->tb_rg_code,
			'label' => $this->tb_rg_label,
			'country' => $this->tb_rg_country
		];
	}

	/**
	 * Contruit une région destiné à la base de données
	 *
	 * @return Region
	 */
	public function convertToEntity()
	{
		$nregion = new Region();
		$nregion->setId($this->tb_rg_id);
		$nregion->setCode($this->tb_rg_code);
		$nregion->setLabel($this->tb_rg_label);
		$country = CountryDbAdapter::getById($this->tb_rg_country);
		if ($country) {
			$nregion->setCountry($country);
		}
		return $nregion;
	}

	/**
	 * Construit le viewMode à partir de l'objet Region
	 *
	 * @param Region $region
	 * @return RegionViewModel
	 */
	public static function buildFromEntity($region = null)
	{
		$vmodel = new RegionViewModel();
		if (!is_null($region)) {
			$vmodel->tb_rg_id = $region->getId();
			$vmodel->tb_rg_code = $region->getCode();
			$vmodel->tb_rg_label = $region->getLabel();
			$vmodel->tb_rg_country = $region->getCountry()->getId();
		}
		return $vmodel;
	}
}