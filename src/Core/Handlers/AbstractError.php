<?php

namespace Digitalis\Core\Handlers;

use DateTime;
use Digitalis\Core\Models\Data;
use Exception;
use Psr\Http\Message\RequestInterface;
use Throwable;
use const APPNAME;
use Digitalis\Core\Models\EnvironmentManager;

/**
 * Description of AbstractError
 * Part of this is copied from Slim\Handler\AbstractHandler
 * @author sylvin.kamdem
 */
abstract class AbstractError extends AbstractHandler
{
    const MASKLOG = "[%s][OS: %s][" . APPNAME . "][ACTION:%s][%s]" . PHP_EOL;
    /**
     * @var bool
     */
    protected $displayErrorDetails;

    /**
     * Constructor
     *
     * @param bool $displayErrorDetails Set to true to display full details
     */
    public function __construct($displayErrorDetails = false)
    {
        $this->displayErrorDetails = (bool)$displayErrorDetails;
    }

    /**
     * Write to the error log if displayErrorDetails is false
     *
     * @param Exception|Throwable $throwable
     *
     * @return void
     */
    protected function writeToErrorLog($throwable)
    {
        if ($this->displayErrorDetails) {
            return;
        }

        $message .= $this->renderAsText($throwable) . PHP_EOL
            . 'Pour afficher ce message changez le mode de travaille et passez en mode développement.' . PHP_EOL;

        $this->logError($message);
    }

    public function renderAsText($throwable)
    {
        $message = '[' . APPNAME . ': Erreur ]';
        $message .= $this->renderThrowableAsText($throwable);
        while ($throwable = $throwable->getPrevious()) {
            $message .= '#Erreur précédente:';
            $message .= $this->renderThrowableAsText($throwable);
        }
        return $message;
    }

    /**
     * Render error as Text.
     *
     * @param Exception|Throwable $throwable
     *
     * @return string
     */
    protected function renderThrowableAsText($throwable)
    {
        $text = sprintf('Type: %s|', get_class($throwable));
        $code = $throwable->getCode();
        if ($code) {
            $text .= sprintf('Code: %s|', $code);
        }
        $message = $throwable->getMessage();
        if ($message) {
            $text .= sprintf('Message: %s|', htmlentities($message));
        }
        $file = $throwable->getFile();
        if ($file) {
            $text .= sprintf('File: %s|', $file);
        }
        $line = $throwable->getLine();
        if ($line) {
            $text .= sprintf('Line: %s|', $line);
        }
        if (EnvironmentManager::logErrorTrace()) {
            $trace = $throwable->getTraceAsString();
            if ($trace) {
                $text .= sprintf('Trace: %s', $trace);
            }
        }

        return $text;
    }

    /**
     * Wraps the error_log function so that this can be easily tested
     *
     * @param $message
     */
    protected function logError($message)
    {
        error_log($message);
    }

    protected function writeLog(RequestInterface $request, $exception, $logFile)
    {
        $date = (new DateTime())->format('D M d H:i:s.u Y');
        $os = Data::cgetOS($request->getServerParam("HTTP_USER_AGENT"));
        $action = $request->getUri()->getPath();
        //
        //CREATION DU FICHIER S'IL N'EXISTE PAS
        //
        $fp = fopen($logFile, 'a');
        if ($fp) {
            if (flock($fp, LOCK_EX | LOCK_NB)) {
                fwrite($fp, sprintf(self::MASKLOG, $date, $os, $action, $this->renderAsText($exception)));
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }
    }

}
