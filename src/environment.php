<?php
$basedir = realpath(__DIR__ . join(DIRECTORY_SEPARATOR, [DIRECTORY_SEPARATOR, '..'])) . DIRECTORY_SEPARATOR;
return [
    "appName" => "Light v1.0",
    "baseDir" => $basedir,
    "debug" => true, //set this to false in production mode
    "isProduction" => false,
    "error" => [
        "displayError" => true,
        "displayTrace" => false,
        "eFatal" => E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR,
        "errorReporting" => E_ALL | E_STRICT,
        "logErrors" => true,
        "customErrorLog" => true,
        "logErrorOnSystemLog" => true,
        "errorLogFile" => $basedir . join(DIRECTORY_SEPARATOR, ["logs", "error.log"]),
        "defMessage" => 'Le serveur a rencontré une erreur inattendu qui ne lui a pas permis de terminer la requête . Nous nous excusons!',
        "htmlFileName" => $basedir . join(DIRECTORY_SEPARATOR, ["src", "Core", "Views", "shared", "500.html"]),
        "logTrace" => false
    ],
    "pageNotFoundHtmlFile" => $basedir . join(DIRECTORY_SEPARATOR, ["src", "Core", "Views", "shared", "404.html"]),
    "actionLog" => $basedir . join(DIRECTORY_SEPARATOR, ["logs", "actionlogs.log"]),
    "tempFolder" => $basedir . join(DIRECTORY_SEPARATOR, ['tmp']) . DIRECTORY_SEPARATOR,
    "mailer" => [
        "smtpDebug" => 2,
        "host" => "localhost",
        "smtpAuth" => true,
        "username" => "username@domain.com",
        "password" => "password",
        "smtpSecure" => "tls",
        "port" => 1025
    ],
    "reseller" => $basedir . join(DIRECTORY_SEPARATOR, ['repository', 'resellers.xml']),
    "lexique" => $basedir . join(DIRECTORY_SEPARATOR, ['repository', 'strings.xml'])
];