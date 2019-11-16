<?php
namespace Digitalis\Core\Models;

/**
 * JsonResponse Réponse json des requêtes
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class JsonResponse implements \JsonSerializable
{

	/**
	 * Statut
	 *
	 * @var boolean
	 */
	public $isSuccess;

	/**
	 * Message de la réponse
	 *
	 * @var string
	 */
	public $message;

	/**
	 * donnée à retourner
	 *
	 * @var object
	 */
	public $data;

	public function __construct()
	{
		$this->isSuccess = true;
	}

	public function asArray()
	{
		return [
			'isSuccess' => $this->isSuccess,
			'message' => $this->message,
			'data' => $this->data
		];
	}
	public function jsonSerialize()
	{
		return [
			'isSuccess' => $this->isSuccess,
			'message' => $this->message,
			'data' => $this->data
		];
	}
}