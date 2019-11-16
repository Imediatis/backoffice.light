<?php
error_reporting(0);
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\EnvironmentManager;


//TODO : Implémenter le retour d'erreur en accord avec le content-type demandé par le client. S'inspirer de l'implémentation de la classe PhpError de Slim

$basedir = realpath(join(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..'])) . DIRECTORY_SEPARATOR;
define('BASE_DIR', $basedir);
define('ENV_FILE', BASE_DIR . join(DIRECTORY_SEPARATOR, ['src', 'environment.php']));

include BASE_DIR . join(DIRECTORY_SEPARATOR, ['vendor', 'autoload.php']);
set_exception_handler(function ($e) {
    ErrorHandler::displayError($e);
    ErrorHandler::writeLog($e);
    die;
});

//
//TODO: Implémenter une meilleur gestion d'erreur afin d'intercepter toutes les erreurs possibles.
//
set_error_handler(function ($Code, $Message, $File = null, $Line = 0, $Context = []) {
    if (!($Code & EnvironmentManager::errorRepporting()))
        return;
    ErrorHandler::displayError(new Exception($Message, $Code), $File, $Line);
    ErrorHandler::writeLog(new Exception($Message, $Code), $File, $Line);
    die;
});

register_shutdown_function(function () {
    $error = error_get_last();
    if (!($error['type'] & EnvironmentManager::errorRepporting()))
        return;
    ErrorHandler::displayError(new Exception($error['message'], $error['type']), $error['file'], $error['line']);
    ErrorHandler::writeLog(new Exception($error['message'], $error['type']), $error['file'], $error['line']);
    die;
});
