<?php
namespace Digitalis\Core\Models;

/**
 * Client Client faisant le retrait
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class Client implements \Serializable, \JsonSerializable
{
    /**
     * Numéro de compte du client
     * 
     * @var string
     */
    private $numCpt;

    /**
     * Nom du client
     * 
     * @var string
     */
    private $name;

    /**
     * Solde du compte
     * 
     * @var integer
     */
    private $solde;

    public function __construct($numCpt, $name, $solde)
    {
        $this->numCpt = $numCpt;
        $this->name = $name;
        $this->solde = $solde;
    }

    public function serialize()
    {
        return serialize([
            $this->numCpt,
            $this->name,
            $this->solde
        ]);
    }

    public function unserialize($serialised)
    {
        list(
            $this->numCpt,
            $this->name,
            $this->solde
        ) = unserialize($serialised);
    }

    public function jsonSerialize()
    {
        return [
            "numCpt" => $this->numCpt,
            "name" => $this->name,
            "solde" => $this->solde
        ];
    }

    /**
     * Permet de contruire une instance de Client à partir d'un tableau associatif
     *
     * @param array $fields Champs de l'objet
     * @return Client
     */
    public static function buildInstance(array $fields)
    {
        $numcpt = isset($fields['numCpt']) ? $fields['numCpt'] : null;
        $name = isset($fields['name']) ? $fields['name'] : null;
        $sold = isset($fields['solde']) ? $fields['solde'] : null;
        if ($numcpt && $name && $sold) {
            return new Client($numcpt, $name, $sold);
        }
        return null;
    }

    /**
     * Retourne la valeur de $numCpt
     *
     * @return string
     */
    public function getNumCpt()
    {
        return $this->numCpt;
    }

    /**
     * Définit la valeur de $numCpt
     *
     * @param string $numCpt
     */
    public function setNumCpt($numCpt)
    {
        $this->numCpt = $numCpt;
    }

    /**
     * Retourne la valeur de $name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Définit la valeur de $name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Retourne la valeur de $solde
     *
     * @return integer
     */
    public function getSolde()
    {
        return $this->solde;
    }

    /**
     * Définit la valeur de $solde
     *
     * @param integer $solde
     */
    public function setSolde($solde)
    {
        $this->solde = $solde;
    }
}