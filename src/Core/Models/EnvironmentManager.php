<?php
namespace Digitalis\Core\Models;
/**
 * Cette classe permet de génére le paramètres d'environnement de l'application
 *
 * @author Sylvin
 */
class EnvironmentManager
{
    private static $pathToEnv = ENV_FILE;// This constant is defined in the file src/Handler/ShutdownErrorHandler.php;
    private static $env;
    private static $isSetEnv = false;
    private static $authLang = ['fr', 'en'];

    public function __construct($pathToFile = null)
    {
        if (!self::$isSetEnv) {
            self::$pathToEnv = is_null($pathToFile) ? self::$pathToEnv : $pathToFile;
            if (file_exists(self::$pathToEnv)) {
                self::$env = require self::$pathToEnv;
                self::$isSetEnv = true;
            } else {
                self::$env = [];
            }
        }
    }
    public static function isAuthLang($lang)
    {
        return in_array($lang, self::$authLang);
    }

    public static function envIsset()
    {
        return self::$isSetEnv;
    }
    /**
     * Permet de récupérer toute les configuration de l'environnment du projet
     *
     * @return array
     */
    public static function getEnvironment()
    {
        return self::$env;
    }

    /**
     * Retourne le nom de l'application
     *
     * @return string
     */
    public static function getAppname()
    {
        return isset(self::$env["appName"]) ? self::$env["appName"] : null;
    }

    /**
     * Retourne la racine du projet
     *
     * @return string
     */
    public static function getBaseDir()
    {
        return isset(self::$env['baseDir']) ? self::$env['baseDir'] : join(DIRECTORY_SEPARATOR, [__DIR__, '..', '..']) . DIRECTORY_SEPARATOR;
    }

    /**
     * Détermine si le débogage est activé sur le système
     *
     * @return boolean
     */
    public static function isDebug()
    {
        return isset(self::$env['debug']) ? self::$env['debug'] : false;
    }

    /**
     * Détermine si le système est configuré pour le mode production ou non
     *
     * @return boolean
     */
    public static function isProduction()
    {
        return isset(self::$env["isProduction"]) ? self::$env["isProduction"] : false;
    }

    /**
     * Retourne le chemin complet vers le fichiers de journalisation des actions des utilisateurs
     *
     * @return string
     */
    public static function getActionLogFile()
    {
        return isset(self::$env['actionLog']) ? self::$env['actionLog'] : null;
    }

    /**
     * Retourne les configurations pour l'envoi des mail dans le système
     *
     * @return array|null
     */
    public static function getMailerSettings()
    {
        return isset(self::$env['mailer']) ? self::$env['mailer'] : null;
    }

    /**
     * Retourne le niveau de débogage de l'envoi de mail
     *
     * @return integer
     */
    public static function getMailerSmtpDebug()
    {
        $mailer = self::getMailerSettings();
        return (!is_null($mailer) && !isset($mailer['smtpDebut'])) ? $mailer['smtpDebug'] : 2;
    }

    /**
     * Retourne le nom de l'hôte au travel lequel le système envois ses mail
     *
     * @return string
     */
    public static function getMailerHost()
    {
        $mailer = self::getMailerSettings();
        return (!is_null($mailer) && !isset($mailer['host'])) ? $mailer['host'] : 'localhost';
    }

    /**
     * Détermine si il faut une authentification smtp lors de l'envoi de mail
     *
     * @return boolean
     */
    public static function getMailerSmtpAuth()
    {
        $mailer = self::getMailerSettings();
        return (!is_null($mailer) && !isset($mailer['smtpAuth'])) ? $mailer['smtpAuth'] : false;
    }

    /**
     * Retourne l'utilisateur smtp à utiliser pour l'envois des mail smtp
     *
     * @return string
     */
    public static function getMailerUsername()
    {
        $mailer = self::getMailerSettings();
        return (!is_null($mailer) && !isset($mailer['username'])) ? $mailer['username'] : "username@domain.com";
    }

    /**
     * Retourne le mot de passe de l'utilisateur pour la connexion smtp
     *
     * @return string
     */
    public static function getMailerPassword()
    {
        $mailer = self::getMailerSettings();
        return (!is_null($mailer) && !isset($mailer['password'])) ? $mailer['password'] : "password";
    }

    /**
     * Retourne le protocol de sécurité Smtp utilisé pour l'envoi des mail
     *
     * @return string
     */
    public static function getMailerSmtpSecure()
    {
        $mailer = self::getMailerSettings();
        return (!is_null($mailer) && !isset($mailer['smtpSecure'])) ? $mailer['smtpSecure'] : "tls";
    }

    /**
     * Retourne le port de communication utilisé par le système pour l'envoi des mail
     *
     * @return integer
     */
    public static function getMailerPort()
    {
        $mailer = self::getMailerSettings();
        return (!is_null($mailer) && !isset($mailer['port'])) ? $mailer['port'] : 1025;
    }

    /**
     * Retourne les configurations de la gestion des erreurs du système
     *
     * @return array|null
     */
    public static function getErrorSettings()
    {
        return isset(self::$env['error']) ? self::$env['error'] : null;
    }

    /**
     * Retourne la valeur d'environnement déterminant si les détails des erreurs survenu dans le système s'affiche ou pas
     *
     * @return boolean
     */
    public static function displayError()
    {
        $error = self::getErrorSettings();
        return (!is_null($error) && isset($error['displayError'])) ? $error['displayError'] : false;
    }

    /**
     * Retourne la valeur du niveau d'erreur considéré comme fatale pour le système
     *
     * @return integer
     */
    public static function eFatal()
    {
        $error = self::getErrorSettings();
        return (!is_null($error) && isset($error['eFatal'])) ? $error['eFatal'] : E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR;
    }

    /**
     * Retourne le type d'erreur à prendre en compte dans le système
     *
     * @return string
     */
    public static function errorRepporting()
    {
        $error = self::getErrorSettings();
        return (!is_null($error) && isset($error['errorReporting'])) ? $error['errorReporting'] : E_ALL | E_STRICT;
    }

    /**
     * Détermine si les erreurs survenues dans le système sont journalisées
     *
     * @return boolean
     */
    public static function logErrors()
    {
        $error = self::getErrorSettings();
        return (!is_null($error) && isset($error['logErrors'])) ? $error['logErrors'] : true;
    }

    /**
     * Retourne la valeur déterminant s'il faut écrire le log système
     *
     * @return boolean
     */
    public static function logErrorOnSystemLog()
    {
        $error = self::getErrorSettings();
        return (!is_null($error) && isset($error['logErrorOnSystemLog'])) ? $error['logErrorOnSystemLog'] : true;
    }

    /**
     * Détermine si les erreurs personnalisé sont généré
     *
     * @return boolean
     */
    public static function customErrorLog()
    {
        $error = self::getErrorSettings();
        return (!is_null($error) && isset($error['customErrorLog'])) ? $error['customErrorLog'] : true;
    }

    /**
     * Retourne le nom complet du fichier log des erreurs
     *
     * @return string
     */
    public static function getErrorLogFile()
    {
        $error = self::getErrorSettings();
        return (!is_null($error) && isset($error['errorLogFile'])) ? $error['errorLogFile'] : null;
    }

    /**
     * Retourne la configuration déterminant si les détails des erreurs doivent être affiché ou pas
     *
     * @return boolean
     */
    public static function displayErrorTrace()
    {
        $error = self::getErrorSettings();
        return (!is_null($error) && isset($error['displayTrace'])) ? $error['displayTrace'] : false;
    }

    /**
     * Détermine s'il faut écrire le trace de l'erreur survenu
     *
     * @return boolean
     */
    public static function logErrorTrace()
    {
        $error = self::getErrorSettings();
        return (!is_null($error) && isset($error['logTrace'])) ? $error['logTrace'] : false;
    }

    /**
     * Retourne le message d'erreur à afficher lorsqu'une erreur se produit dans le système
     *
     * @return string
     */
    public static function getdefaultMessage()
    {
        $error = self::getErrorSettings();
        return (!is_null($error) && isset($error['defMessage'])) ? $error['defMessage'] : 'Le serveur a rencontr&eacute; une erreur inattendu qui ne lui a pas permis de terminer la requ&ecirc;te . Nous nous excusons!<br/>';
    }

    /**
     * Retourne le chemin complet vers le fichier de formatage des erreur interne du système
     *
     * @return string
     */
    public static function getHtmlFileName()
    {
        $error = self::getErrorSettings();
        return (!is_null($error) && isset($error['htmlFileName'])) ? $error['htmlFileName'] : null;
    }

    /**
     * Retourne le formatage d'affichage des erreurs survenu dans le système
     *
     * @return string
     */
    public static function getformatHtmlError()
    {
        if (file_exists(self::getHtmlFileName())) {
            return file_get_contents(self::getHtmlFileName());
        } else {
            return "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
                "<title>Internal server error</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana," .
                "sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{" .
                "display:inline-block;width:65px;}</style></head><body>#ERRORMESSAGE#</body></html>";
        }
    }

    /**
     * retourne le chemin vers le fichier de formatage des page not found
     *
     * @return string
     */
    public static function get404PageNotFoundHtmlFile()
    {
        return isset(self::$env['pageNotFoundHtmlFile']) ? self::$env['pageNotFoundHtmlFile'] : null;
    }

    /**
     * Retourne la pattern permettant de formater la gestion des pages innexistantes
     *
     * @return string
     */
    public static function getformat404Pagenotfound()
    {
        if (file_exists(self::get404PageNotFoundHtmlFile())) {
            return file_get_contents(self::get404PageNotFoundHtmlFile());
        } else {
            return "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
                "<title>404 Page not found</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana," .
                "sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{" .
                "display:inline-block;width:65px;}</style></head><body><h1>ERREUR 404 Page not found</h1></body></html>";
        }
    }

    /**
     * Retourne le chemin vers le fichier des différents clients enregistré, et null si celui-ci n'existe pas
     *
     * @return string|null
     */
    public static function getResellerFile()
    {
        return (isset(self::$env['reseller']) && file_exists(self::$env['reseller'])) ? self::$env['reseller'] : null;
    }

    /**
     * Retourne le chemin vers le fichier des traductions des libellées et null si celui-ci n'existe pas
     *
     * @return string|null
     */
    public static function getLexiqueFile()
    {
        return (isset(self::$env['lexique']) && file_exists(self::$env['lexique'])) ? self::$env['lexique'] : null;
    }

    /**
     * retourne le dossier des fichiers temporaire
     *
     * @return string
     */
    public static function getTempFolder()
    {
        return (isset(self::$env['tempFolder']) && file_exists(self::$env['tempFolder'])) ? self::$env['tempFolder'] : null;
    }
}