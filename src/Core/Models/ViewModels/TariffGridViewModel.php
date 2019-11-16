<?php
namespace Digitalis\Core\Models\ViewModels;

use Digitalis\Core\Models\DbAdapters\EntrepriseDbAdapter;
use Digitalis\Core\Models\Entities\TariffGrid;
use Imediatis\EntityAnnotation\ModelState;

/**
 * TariffGridViewModel Modèle d'interface de la gestion des tariffe
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class TariffGridViewModel implements ViewModelInterface
{

	/**
	 * Identifiant
	 *
	 * @IME\Id
	 * @IME\DataType{"type":"integer","nullable":true,"errMsg":"Valeur invalide pour ce champ"}
	 * @var integer
	 */
	public $tb_grt_id;

	/**
	 * Valeur minimale de la grille
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"integer","errMsg":"Valeur invalide pour ce champ"}
	 * @var integer
	 */
	public $tb_grt_min;

	/**
	 * Valeur maximale de la grille
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"integer","errMsg":"Valeur invalide pour ce champ"}
	 * @var integer
	 */
	public $tb_grt_max;

	/**
	 * Valeur de l'intervalle
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"float","errMsg":"Valeur invalide pour ce champ"}
	 * @var float
	 */
	public $tb_grt_value;

	/**
	 * Nature de la valeur de l'intervalle
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"integer","errMsg":"Valeur invalide pour ce champ"}
	 * @var integer
	 */
	public $tb_grt_nature;

	/**
	 * Entreprise concerné
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"integer","errMsg":"Valeur invalide pour ce champ"}
	 * @var integer
	 */
	public $ld_ent_id;

	public function __construct()
	{
		$this->tb_grt_nature = 1;
	}

	public function validateModel()
	{
		$output = true;
		$msgMin = 'La valeur minimale pour ce champ est 1';
		if ($this->tb_grt_min < 1) {
			ModelState::setValidity(false);
			ModelState::setMessage('tb_grt_min', $msgMin);
		}
		if ($this->tb_grt_max < 1) {
			ModelState::setValidity(false);
			ModelState::setMessage('tb_grt_max', $msgMin);
		}
		if ($this->tb_grt_max <= $this->tb_grt_min) {
			ModelState::setValidity(false);
			ModelState::setMessage('tb_grt_max', "La valeur maximale doit être supérieur à la valeur minimale");
		}
		if ($this->tb_grt_value < 1) {
			ModelState::setValidity(false);
			ModelState::setMessage('tb_grt_value', $msgMin);
		}
	}
	public function toArray()
	{
		return [
			'id' => $this->tb_grt_id,
			'min' => $this->tb_grt_min,
			'max' => $this->tb_grt_max,
			'value' => $this->tb_grt_value,
			'nature' => $this->tb_grt_nature,
			'entId' => $this->ld_ent_id
		];
	}


	/**
	 * Permet de construire l'objet à enregistrer dans la base de données
	 *
	 * @return TariffGrid
	 */
	public function convertToEntity()
	{
		$ngtf = new TariffGrid();
		$ngtf->setId($this->tb_grt_id);
		$ngtf->setMin($this->tb_grt_min);
		$ngtf->setMax($this->tb_grt_max);
		$ngtf->setValue($this->tb_grt_value);
		$ngtf->setNature($this->tb_grt_nature);
		$entrp = EntrepriseDbAdapter::getById($this->ld_ent_id);
		if (!is_null($entrp)) {
			$ngtf->setEntreprise($entrp);
		}
		return $ngtf;
	}

	/**
	 * Permet de construire le modèle à partir de l'objet de la base de données
	 *
	 * @param TariffGrid $entity
	 * @return TariffGridViewModel
	 */
	public static function buildFromEntity($entity = null)
	{
		$vmodel = new TariffGridViewModel();
		if (!is_null($entity)) {
			$vmodel->tb_grt_id = $entity->getId();
			$vmodel->tb_grt_min = $entity->getMin();
			$vmodel->tb_grt_max = $entity->getMax();
			$vmodel->tb_grt_value  = $entity->getValue();
			$vmodel->tb_grt_nature = $entity->getNature();
			$vmodel->ld_ent_id = $entity->getEntreprise()->getId();
		}
		return $vmodel;
	}
}
