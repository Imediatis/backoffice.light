<?php
namespace Digitalis\Core\Models\ViewModels;

use Digitalis\Core\Models\Entities\TypePiece;

/**
 * TypePieceViewModel Vue pour la gestion des types de pièces
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class TypePieceViewModel implements ViewModelInterface
{
	/**
	 * Identifiant
	 *
	 * @IME\Id
	 * @IME\DataType{"type":"integer","nullable":true}
	 * @var integer
	 */
	public $tb_tdoc_id;

	/**
	 * Code du type de pièce
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":"15","errMsg":"Ce champ n'admet pas plus de 15 caractères"}
	 * @var string
	 */
	public $tb_tdoc_code;

	/**
	 * Libellé du type de pièce de document
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":80,"errMsg":"Ce champ n'admet pas plus de 80 caractères"}
	 * @var string
	 */
	public $tb_tdoc_label;

	public function toArray()
	{
		return [
			'id' => $this->tb_tdoc_id,
			'code' => $this->tb_tdoc_code,
			'label' => $this->tb_tdoc_label
		];
	}

	/**
	 * Crée un objet pour la base de données
	 *
	 * @return TypePieceViewModel
	 */
	public function convertToEntity()
	{
		$ntdoc = new TypePiece();
		$ntdoc->setId($this->tb_tdoc_id);
		$ntdoc->setCode(strtoupper($this->tb_tdoc_code));
		$ntdoc->setLabel($this->tb_tdoc_label);
		return $ntdoc;
	}

	/**
	 * Contruit le modèle à partir d'un objet de la base de données
	 *
	 * @param \Digitalis\Core\Models\Entities\TypePiece $tpiece
	 * @return TypePieceViewModel
	 */
	public static function buildFromEntity($tpiece = null)
	{
		$vmodel = new TypePieceViewModel();
		if (!is_null($tpiece)) {
			$vmodel->tb_tdoc_id = $tpiece->getId();
			$vmodel->tb_tdoc_code = $tpiece->getCode();
			$vmodel->tb_tdoc_label = $tpiece->getLabel();
		}
		return $vmodel;
	}
}