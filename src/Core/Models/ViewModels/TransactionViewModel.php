<?php
namespace Digitalis\Core\Models\ViewModels;

use Digitalis\Core\Models\DbAdapters\CaisseDbAdapter;
use Digitalis\Core\Models\DbAdapters\OperatorDbAdapter;
use Digitalis\Core\Models\Entities\Transaction;
use Imediatis\EntityAnnotation\ModelState;
use Digitalis\Core\Models\Lexique;

/**
 * TransactionViewModel Model de validation des données
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class TransactionViewModel implements ViewModelInterface
{
	/**
	 * identifiant
	 *
	 * @IME\Id
	 * @IME\DataType{"type":"integer","errMsg":"Valeur invalide pour l'identifiant","nullable":true}
	 * @var integer
	 */
	public $id;

	/**
	 * Rérérence de la transaction
	 *
	 * @IME\DataType{"nullable":true,"errMsg":"Valeur invalide pour la référence"}
	 * @IME\Length{"max":20,"errMsg":"La référence n'admet pas plus de 20 caractères"}
	 * @var string
	 */
	public $reference;

	/**
	 * Nom client
	 *
	 * @IME\Required{"errMsg":"Veuillez renseigner le nom du client"}
	 * @var string
	 */
	public $customer;

	/**
	 * Numéro de compte
	 *
	 * @IME\Required{"errMsg":"Veuillez renseigner le numéro de compte du client"}
	 * @IME\Length{"max":80,"errMsg":"Le numéro de compte n'admet pas plus de 80 caractères"}
	 * @var string
	 */
	public $accountNum;

	/**
	 * Code du partenaire où le retrait est fait
	 * 
	 * @IME\Required{"errMsg":"Veuillez renseigner la banque partenaire"}
	 * @var string
	 */
	public $partner;

	/**
	 * Montant de l'opération
	 * 
	 * @IME\Required{"errMsg":"Veuillez renseigner le montant de la transaction"}
	 * @IME\DataType{"type":"integer","errMsg":"Valeur invalide pour le montant"}
	 * @var integer
	 */
	public $amount;

	/**
	 * Frais de l'opération
	 * 
	 * @IME\DataType{"nullable":true,"errMsg":"Valeur invalide pour les frais","type":"integer"}
	 * @var integer
	 */
	public $fees;

	/**
	 * Date de l'opération
	 * 
	 * @IME\DataType{"type":"datetime","nullable":true}
	 * @var \DateTime
	 */
	public $transDate;

	/**
	 * Caisse qui réalise l'opération
	 * 
	 * @IME\Required{"errMsg":"Veuillez renseigner la caisse qui effectue l'opération"}
	 * @var string
	 */
	public $caisse;

	/**
	 * Opérateur qui valide l'opération
	 * 
	 * @IME\Required{"errMsg":"Veuillez renseigner l'opérateur qui valide l'opération"}
	 * @var string
	 */
	public $operator;

	/**
	 * Type de pièce d'identité
	 *
	 * @IME\Required{"errMsg":"Veuillez renseigner le type de pièce d'identificatin du client"}
	 * @var string
	 */
	public $tPiece;

	/**
	 * Numéro de la pièce d'identification du client
	 *
	 * @IME\Required{"errMsg":"Veuillez renseigner le numéro de pièce"}
	 * @var string
	 */
	public $docNum;

	/**
	 * Lieu de délivrance de la pièce d'identité
	 *
	 * @IME\Required{"nullable":true}
	 * @var string
	 */
	public $issuePlace;

	/**
	 * Date de délivrance de la pièce
	 *
	 * @IME\DataType{"type":"date","errMsg":"Valeur invalide pour le champ"}
	 * @IME\Required{"errMsg":"Veuillez renseigner la date d'émission de la pièce d'identité"}
	 * @var \DateTime
	 */
	public $issueDate;

	public function toArray()
	{
		return [
			'id' => $this->id,
			'reference' => $this->reference,
			'customer' => $this->customer,
			'accountNum' => $this->accountNum,
			'partner' => $this->partner,
			'amount' => $this->amount,
			'fees' => $this->fees,
			'transDate' => !is_null($this->transDate) ? $this->transDate->format('Y-m-d H:i:s') : null,
			'caisse' => $this->caisse,
			'operator' => $this->operator,
			'tPiece' => $this->tPiece,
			'docNum' => $this->docNum,
			'issuePlace' => $this->issuePlace,
			'issueDate' => $this->issueDate instanceof \DateTime ? $this->issueDate->format(DATE_ISO8601) : null
		];
	}

	/**
	 * Permet de convertir le modèle en une trasaction pour la base de données
	 *
	 * @return Transaction
	 */
	public function convertToEntity()
	{
		$ntrans = new Transaction();
		$ntrans->setId($this->id);
		$ntrans->setReference($this->reference);
		$ntrans->setCustomer($this->customer);
		$ntrans->setAccountNumber($this->accountNum);
		$ntrans->setPartnerCode($this->partner);
		$ntrans->setAmount($this->amount);
		$ntrans->setFees($this->fees);
		$ntrans->setDocType($this->tPiece);
		$ntrans->setDocNumber($this->docNum);
		$ntrans->setIssuePlace($this->issuePlace);
		$ntrans->setIssueDate($this->issueDate);
		$caisse = CaisseDbAdapter::getByCode($this->caisse);
		if ($caisse) {
			$ntrans->setCaisse($caisse);
		} else {
			ModelState::setMessage('caisse', Lexique::GetString(CUR_LANG, 'please-enter-the-cash-reg'));
		}

		$operator = !is_null($caisse) ? $caisse->getOperator() : null;
		if ($operator) {
			if ($operator->getLogin() == $this->operator) {
				$ntrans->setOperator($operator);
			} else {
				ModelState::setMessage('operator', Lexique::GetString(CUR_LANG, 'you-don-t-have-the-author'));
			}
		} else {
			ModelState::setMessage('operator', Lexique::GetString(CUR_LANG, 'please-enter-the-operator'));
		}
		return $ntrans;
	}

	/**
	 * Pemet de construire le modèle d'interface à partir de la transaction de la base de données
	 *
	 * @param Transaction $trans
	 * @return TransactionViewModel
	 */
	public static function buildFromEntity($trans = null)
	{
		$vmodel = new TransactionViewModel();
		if (!is_null($trans)) {
			$vmodel->id = $trans->getId();
			$vmodel->reference = $trans->getReference();
			$vmodel->customer = $trans->getCustomer();
			$vmodel->accountNum = $trans->getAccountNumber();
			$vmodel->partner = $trans->getPartnerCode();
			$vmodel->amount = $trans->getAmount();
			$vmodel->fees = $trans->getFees();
			$vmodel->transDate = $trans->getTransDate();
			$vmodel->caisse = !is_null($trans->getCaisse()) ? $trans->getCaisse()->getCode() : null;
			$vmodel->operator = !is_null($trans->getOperator()) ? $trans->getOperator()->getLogin() : null;
			$vmodel->tPiece = $trans->getDocType();
			$vmodel->docNum = $trans->getDocNumber();
			$vmodel->issuePlace = $trans->getIssuePlace();
			$vmodel->issueDate = $trans->getIssueDate();
		}
		return $vmodel;
	}
}