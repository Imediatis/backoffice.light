<?php

namespace Digitalis\Core\Models\Security;

/**
 * LoggedUser Utilisateur connecté au système
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class LoggedUser implements \Serializable, \JsonSerializable
{

    /**
     * Login de l'utilisateur connecté
     *
     * @var string
     */
    private $login;

    /**
     * Nom de l'utilisateur
     *
     * @var string
     */
    private $lastName;

    /**
     * Prénom de l'utilisateur
     *
     * @var string
     */
    private $firstName;

    /**
     * avatar de l'utilisateur
     *
     * @var string
     */
    private $avatar;

    /**
     * Profile de l'utilisateur
     *
     * @var string
     */
    private $profile;

    /**
     * Titre de la personne
     *
     * @var string
     */
    private $function;

    /**
     * Code de la caisse
     * 
     * @var string
     */
    private $boxCode;

    /**
     * Détermine si la caisse est ouverte ou pas
     *
     * @var boolean
     */
    private $boxIsOpened;

    /**
     * Code de l'agence
     * 
     * @var string
     */
    private $branchCode;

    /**
     * Détermine si l'agence est ouverte ou pas
     *
     * @var boolean
     */
    private $branchIsOpened;

    /**
     * Nom de l'agence
     *
     * @var string
     */
    private $branchName;

    /**
     * Statut de l'utilisateur
     *
     * @var integer
     */
    private $status;

    /**
     * Token de l'utilisateur lorsqu'il est connecté
     *
     * @var string
     */
    private $token;

    public function __construct($login, $lastName, $firstName, $profile, $function, $avatar = null)
    {
        $this->login = $login;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->function = $function;
        $this->profile = $profile;
        $this->avatar = is_null($avatar) ? "user.svg" : $avatar;
    }

    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Retourne la valeur de firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Retourne la valeur de lastName
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Retourne la valeur de function
     *
     * @return string
     */
    public function getFunction(): string
    {
        return $this->function;
    }

    /**
     * Retourne la valeur de profile
     *
     * @return string
     */
    public function getProfile(): string
    {
        return $this->profile;
    }

    /**
     * Retourne la valeur de avatar
     *
     * @return string
     */
    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function serialize()
    {
        return serialize(array(
            $this->login,
            $this->lastName,
            $this->firstName,
            $this->avatar,
            $this->profile,
            $this->function,
            $this->boxCode,
            $this->boxIsOpened,
            $this->branchCode,
            $this->branchIsOpened,
            $this->branchName,
            $this->status,
            $this->token
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->login,
            $this->lastName,
            $this->firstName,
            $this->avatar,
            $this->profile,
            $this->function,
            $this->boxCode,
            $this->boxIsOpened,
            $this->branchCode,
            $this->branchIsOpened,
            $this->branchName,
            $this->status,
            $this->token
        ) = unserialize($serialized);
    }

    public function forTwig()
    {
        return array(
            'login' => $this->login,
            'lastName' => $this->lastName,
            'firstName' => $this->firstName,
            'avatar' => $this->avatar,
            'profile' => $this->profile,
            'function' => $this->function,
            'boxCode' => $this->boxCode,
            'boxIsOpened' => $this->boxIsOpened,
            'branchCode' => $this->branchCode,
            'branchIsOpened' => $this->branchIsOpened,
            'branchName' => $this->branchName,
            'status' => $this->status,
            'token' => $this->token
        );
    }
    public function jsonSerialize()
    {
        return $this->forTwig();
    }
}
