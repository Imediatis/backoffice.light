<?php
namespace Digitalis\Core\Models\ViewModels;

use Digitalis\Core\Models\DbAdapters\EntrepriseDbAdapter;
use Digitalis\Core\Models\DbAdapters\PartnerDbAdapter;
use Digitalis\Core\Models\Entities\Affectation;

/**
 * AffectationViewModel Modèle d'interface pour la gestion des affectations
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class AffectationViewModel implements ViewModelInterface
{

	/**
	 * Identifiant de l'entreprise
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"integer":"errMsg":"Valeur invalide pour ce champ"}
	 * @var integer
	 */
	public $ld_aff_ent_id;

	/**
	 * Identifiant du partenaire financier
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"integer","errMsg":"Valeur invalide pour ce champ"}
	 * @var integer
	 */
	public $ld_aff_part_id;
	public $isValide = true;

	public function toArray()
	{
		return ['entId' => $this->ld_aff_ent_id, 'partId' => $this->ld_aff_part_id];
	}

	/**
	 * Permet de convertir en objet pour la base de données
	 *
	 * @return Affectation
	 */
	public function convertToEntity()
	{
		$naffect = new Affectation();
		$entrep = EntrepriseDbAdapter::getById($this->ld_aff_ent_id);
		$partner = PartnerDbAdapter::getById($this->ld_aff_part_id);
		if (!is_null($entrep) && !is_null($partner)) {
			$naffect->setEntreprise($entrep);
			$naffect->setPartner($partner);
		} else {
			$this->isValide = false;
		}
		return $naffect;
	}

	public function idValid()
	{
		return $this->isValide;
	}

	/**
	 * Construit le modèle à partir de l'objet de la base de données
	 *
	 * @param Affectation $affectation
	 * @return AffectationViewModel
	 */
	public static function buildFromEntity($affectation = null)
	{
		$vmodel = new AffectationViewModel();
		if (!is_null($affectation)) {
			$vmodel->ld_aff_ent_id = $affectation->getEntreprise()->getId();
			$vmodel->ld_aff_part_id = $affectation->getPartner()->getId();
		}
		return $vmodel;
	}
}
