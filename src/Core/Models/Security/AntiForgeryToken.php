<?php

namespace Digitalis\Core\Models\Security;

use Serializable;
use JsonSerializable;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\SessionManager;

/** AntiForgeryToken class v1.0 | IMEDIATIS | IMEDIAWEB
 * @package    imediaweb
 * @copyright  2017 Copyright www.imediatis.cm
 * @license    IMEDIATIS ALL RIGHTS RESERVED
 * @version    2.0
 * @author : Sylvin KAMDEM (IT Softwares Ingineer)
 */
class AntiForgeryToken implements Serializable, JsonSerializable
{
    //TODO : implémenter une library uniquement pour cette classe

    const CRYPT_COST = 10;
    const BLOWFISH_ALGO = '2y';
    const SESSION_NAME = '__AntiForgeryToken';
    const _PWD_AFT = '__AntiForgeryToken_PWD';
    const _SALT_AFT = '__AntiForgeryToken_SALT';
    const HIDDEN_FIELD = '__RequestVerificationToken';
    const OBJECT_SESSION = '__ObjectAntiForgery';

    public static $Number = 0;
    protected $_salt;
    protected $_hash;
    protected $_password;
    protected $_param;
    protected $_lengthPwd;

    public function __construct($length = 100)
    {
        $this->_lengthPwd = $length;
        $this->genPassword();
        $this->genSalt();
        $this->_param = $this->buildParam($this->_salt);
        $this->_hash = $this->genHash($this->_password, $this->_salt);
        $this->writeToSession();
        return $this;
    }

    public function getSalt()
    {
        return $this->_salt;
    }

    public function getHash()
    {
        return $this->_hash;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function getParam()
    {
        return $this->_param;
    }

    public function getLenth()
    {
        return $this->_lengthPwd;
    }

    public function setLength($length)
    {
        $this->_lengthPwd = $length;
    }

    public function writeToSession()
    {
        $_SESSION[SysConst::ROOT_NAME][self::SESSION_NAME] = serialize($this);
        $_GLOBALS[self::SESSION_NAME] = serialize($this);
    }

    /**
     *
     * @return \Digitalis\Core\Models\Security\AntiForgeryToken
     */
    public static function getAntiForgeryToken()
    {
        if (isset($_SESSION[SysConst::ROOT_NAME][self::SESSION_NAME])) {
            return unserialize($_SESSION[SysConst::ROOT_NAME][self::SESSION_NAME]);
        } else {
            return new AntiForgeryToken();
        }
    }

    /**
     *
     * @return \Digitalis\Core\Models\Security\AntiForgeryToken
     */
    public static function getAntiForgeryToken_G()
    {
        if (isset($GLOBALS[self::SESSION_NAME])) {
            return \unserialize($GLOBALS[self::SESSION_NAME]);
        } else {
            return new AntiForgeryToken();
        }
    }

    public function genPassword()
    {
        $this->_password = str_replace(array("+", "/"), "", substr(base64_encode(openssl_random_pseudo_bytes($this->_lengthPwd)), 0, $this->_lengthPwd));
        return $this->_password;
    }

    public function genSalt()
    {
        $this->_salt = str_replace("+", ".", substr(base64_encode(openssl_random_pseudo_bytes($this->_lengthPwd)), 0, $this->_lengthPwd));
        return $this->_salt;
    }

    private function buildParam($salt)
    {
        return '$' . implode('$', array(self::BLOWFISH_ALGO, self::CRYPT_COST, $salt));
    }

    public function genHash($password, $salt)
    {
        return crypt($password, $this->buildParam($salt));
    }

    /**
     * Valide la valeur transmise à partir du formulaire
     *
     * @param string $password
     * @return boolean
     */
    public function validate($password)
    {
        $hash = $this->genHash($password, $this->_salt);
        $output = crypt($this->_password, $this->_hash) == $hash;
        new AntiForgeryToken();
        return $output;
    }

    /**
     * Détermine que c'est bien le token précédement envoyé qui est retourné
     *
     * @return boolean
     */
    public function validateAntiForgeryToken()
    {
        $password = filter_input(INPUT_POST, self::HIDDEN_FIELD, FILTER_SANITIZE_STRING);

        if ($password) {
            return $this->validate($password);
        } else {
            return false;
        }
    }

    public function regenerate()
    {
        $this->genPassword();
        $this->genSalt();
        $this->_param = self::buildParam($this->_salt);
        $this->_hash = self::genHash($this->_password, $this->_salt);
    }

    public function serialize()
    {
        return serialize(array(
            $this->_salt,
            $this->_hash,
            $this->_password,
            $this->_param,
            $this->_lengthPwd
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->_salt,
            $this->_hash,
            $this->_password,
            $this->_param,
            $this->_lengthPwd
        ) = unserialize($serialized);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    public function __toString()
    {
        return '<input type="hidden" name="' . self::HIDDEN_FIELD . '" value="' . $this->_password . '" />';
    }

    public function genField()
    {
        return '<input type="hidden" name="' . self::HIDDEN_FIELD . '" value="' . $this->_password . '" />';
    }

}
