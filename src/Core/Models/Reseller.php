<?php

namespace Digitalis\Core\Models;


/**
 * Reseller 
 *
 * This class allows you to graphically 
 * format the reseller interface. 
 * 
 * The host is considered here without the port.
 *
 * @copyright  2018 IMEDIATIS SARL
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @siteweb      http://www.imediatis.net
 * @author     Cyrille WOUPO (UX Designer)
 */
class Reseller implements \Serializable
{
    public static $defaultHost = '192.168.100.84';
    public static $file;

    /**
     * Détermine si le revendeur courant existe ou pas
     *
     * @var boolean
     */
    public $currentExist;

    public $ref;
    public $uri;
    public $keyof;
    public $logo;
    public $css;
    public $bg;
    public $icon;
    public $dbHost;
    public $dbPort;
    public $dbUser;
    public $dbPwd;
    public $dbName;
    public $name;
    public $website;
    public $email;
    public $clientName;
    public $rCapPublic;
    public $rCapSecret;
    public $apiToken;
    public $folder;
    public $apiUrl;
    public $authUrl;
    public $apiUsername;
    public $apiPwd;

    
    public function __construct($_file = null)
    {
        self::$file = $_file;
        $this->currentExist = false;
        $this->initCurrent();
    }

    public function getAuthToken()
    {
        return base64_encode(join(":", [$this->apiUsername, $this->apiPwd]));
    }
    /**
     * Formate le reseller pour l'injection dans twig
     */
    public function forTwig()
    {
        return [
            'ref' => $this->ref,
            'uri' => $this->uri,
            'logo' => $this->logo,
            'keyof' => $this->keyof,
            'icon' => $this->icon,
            'bg' => $this->bg,
            'css' => explode(";", $this->css),
            'folder' => $this->folder,
            'apiToken' => $this->apiToken,
            'name' => $this->name,
            'webSite' => $this->website,
            'email' => $this->email,
            'rCapPublic' => $this->rCapPublic,
            'rCapSecret' => $this->rCapSecret,
            'apiUrl' => $this->apiUrl,
            'authUrl'=> $this->authUrl,
            'apiUsername' => $this->apiUsername,
            'apiPwd' => $this->apiPwd,
            'clientName'=> $this->clientName
        ];
    }

    public function serialize()
    {
        return serialize(array(
            $this->ref,
            $this->uri,
            $this->keyof,
            $this->logo,
            $this->bg,
            $this->css,
            $this->icon,
            $this->currentExist,
            $this->dbHost,
            $this->dbPort,
            $this->dbUser,
            $this->dbPwd,
            $this->dbName,
            $this->folder,
            $this->apiToken,
            $this->name,
            $this->website,
            $this->email,
            $this->rCapPublic,
            $this->rCapSecret,
            $this->apiUrl,
            $this->authUrl,
            $this->apiUsername,
            $this->apiPwd,
            $this->clientName
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->ref,
            $this->uri,
            $this->keyof,
            $this->logo,
            $this->bg,
            $this->css,
            $this->icon,
            $this->currentExist,
            $this->dbHost,
            $this->dbPort,
            $this->dbUser,
            $this->dbPwd,
            $this->dbName,
            $this->folder,
            $this->apiToken,
            $this->name,
            $this->website,
            $this->email,
            $this->rCapPublic,
            $this->rCapSecret,
            $this->apiUrl,
            $this->authUrl,
            $this->apiUsername,
            $this->apiPwd,
            $this->clientName
        ) = unserialize($serialized);
    }

    /**
     * Permete de récupérer l'hôte qui traite le fichier
     *
     * @return string
     */
    public static function getHost()
    {
        $thost = explode(":", (isset($_SERVER['HTTP_HOST']) && !is_null($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : "");
        $uri = (isset($thost[0]) && !is_null($thost[0]) && strlen($thost[0]) > 0) ? $thost[0] : self::$defaultHost;
        if (strlen($uri) > 3) {
            $www = strtolower(substr($uri, 0, 4));
            if ($www == "www.") {
                $uri = substr($uri, 4);
            }
        }
        return $uri;
    }

    /**
     * Initialise les champs de la classe
     *
     * @param array $items Tableau des éléments
     * @return void
     */
    public function fullSet($items)
    {
        $this->ref = isset($items['ref']) ? (string) $items['ref'] : null;
        $this->uri = isset($items['uri']) ? (string) $items['uri'] : null;
        $this->keyof = isset($items['keyof']) ? (string) $items['keyof'] : null;
        $this->logo = isset($items['logo']) ? (string) $items['logo'] : null;
        $this->css = isset($items['css']) ? (string) $items['css'] : null;
        $this->bg = isset($items['bg']) ? (string) $items['bg'] : null;
        $this->icon = isset($items['icon']) ? (string) $items['icon'] : null;
        $this->dbHost = isset($items['dbhost']) ? (string) $items['dbhost'] : null;
        $this->dbPort = isset($items['dbPort']) ? (string) $items['dbPort'] : null;
        $this->dbUser = isset($items['dbuser']) ? (string) $items['dbuser'] : null;
        $this->dbPwd = isset($items['dbpwd']) ? (string) $items['dbpwd'] : null;
        $this->dbName = isset($items['dbname']) ? (string) $items['dbname'] : null;
        $this->folder = isset($items['folder']) ? (string) $items['folder'] : null;
        $this->apiToken = isset($items['apiToken']) ? (string) $items['apiToken'] : null;
        $this->name = isset($items['name']) ? (string) $items['name'] : null;
        $this->website = isset($items['website']) ? (string) $items['website'] : null;
        $this->email = isset($items['email']) ? (string) $items['email'] : null;
        $this->rCapPublic = isset($items['rCapPublic']) ? (string) $items['rCapPublic'] : null;
        $this->rCapSecret = isset($items['rCapSecret']) ? (string) $items['rCapSecret'] : null;
        $this->apiUrl = isset($items['apiUrl']) ? (string) $items['apiUrl'] : null;
        $this->authUrl = isset($items['authUrl']) ? (string) $items['authUrl'] : null;
        $this->apiUsername = isset($items['apiUsername']) ? (string) $items['apiUsername'] : null;
        $this->apiPwd = isset($items['apiPwd']) ? (string) $items['apiPwd'] : null;
        $this->clientName = isset($items['clientName']) ? (string) $items['clientName'] : null;
    }

    /**
     * Permet de récupérer les paramètres du Dealer
     *
     * @param string $_file
     * @param string $code
     * @return array|null
     */
    public static function getItems($_file = null, $code = null)
    {
        $items = null;
        $xml = null;
        $_file = is_null($_file) ? self::$file : $_file;

        if (file_exists($_file)) {
            try {
                $xml = simplexml_load_file($_file);
            } catch (\Exception $exc) {
                \Digitalis\core\Handlers\ErrorHandler::writeLog($exc);
            }
        }
        if (!is_null($xml)) {
            if (is_null($code)) {
                $target = $xml->xpath("//dealer[@uri='" . self::getHost() . "']");
            } else {
                $target = $xml->xpath("//dealer[@ref='" . $code . "']");
            }
            $items = ($target && is_array($target)) ? $target[0] : null;
        }

        return $items;
    }

    /**
     * Initialise le Dealer courant en se basant sur l'uri de travail courant
     * 
     * @return Boolean (true) if set/found or (false) else
     */
    private function initCurrent()
    {
        $items = self::getItems();
        if ($items) {
            $this->currentExist = true;
            $this->fullSet($items);
            return true;
        }
        return false;
    }

    /**
     * Permet de récupérer un Dealer en fonction de son code
     *
     * @param string $file
     * @param string $code
     * @return Reseller
     */
    public static function getByCode($file, $code)
    {
        $items = self::getItems($file, $code);
        if ($items) {
            $reseller = new Reseller();
            $reseller->fullSet($items);
            return $reseller;
        }
        return null;
    }

    /**
     * get a list of all resellers
     * 
     * @param string $file 
     * @return Reseller[] of resellers false if not
     */
    public static function getAll($file)
    {
        $resellers = array();
        if (file_exists($file)) {
            $xml = simplexml_load_file($file);
            if (is_object($xml) && (strcmp(get_class($xml), 'SimpleXMLElement') == 0) && count($xml)) {
                foreach ($xml as $target) {
                    $C = new Reseller();
                    $C->fullSet($target[0]);
                    $resellers[] = $C;
                }
                unset($C);
            }
            unset($xml);
        }
        return $resellers;
    }
}
