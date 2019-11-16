<?php
namespace Digitalis\Core\Handlers;

use DateTime;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\EnvironmentManager;


/**
 * ErrorHandler Gestionnaire des erreurs intercepté avec le conteneur try-catch
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class ErrorHandler
{
	const MASKLOG = "#[%s][OS: %s][" . APPNAME . "][ACTION:%s][%s]" . PHP_EOL;
	private static $file = null;
	private static $line = null;

	protected static function renderAsText($throwable, $newLine = '', $plaineText = true)
	{
		$message = '[' . APPNAME . ': Erreur ]';
		$message .= self::renderThrowableAsText($throwable, $plaineText);
		while ($throwable = $throwable->getPrevious()) {
			$message .= $newLine . '##Erreur précédente:';
			$message .= self::renderThrowableAsText($throwable, $plaineText);
		}
		return $message . $newLine;
	}

	/**
	 * Render error as Text.
	 *
	 * @param Exception|Throwable $throwable
	 *
	 * @return string
	 */
	protected static function renderThrowableAsText($throwable, $plaineText = true)
	{
		$text = sprintf('Type: %s | ', get_class($throwable));
		$code = $throwable->getCode();
		if ($code) {
			$text .= sprintf('Code: %s | ', $code);
		}
		$message = $throwable->getMessage();
		if ($message) {
			$text .= sprintf('Message: %s | ', ($plaineText ? $message : htmlentities($message)));
		}
		if (!is_null(self::$file)) {
			$file = self::$file;
			self::$file = null;
		} else {
			$file = $throwable->getFile();
		}
		if ($file) {
			$text .= sprintf('File: %s', $file);
		}

		if (!is_null(self::$line)) {
			$line = self::$line;
			self::$line = null;
		} else {
			$line = $throwable->getLine();
		}
		if ($line) {
			$text .= sprintf(' | Line: %s', $line);
		}
		if (EnvironmentManager::logErrorTrace()) {
			$trace = $throwable->getTraceAsString();
			if ($trace) {
				$text .= sprintf(' | Trace: %s', $trace);
			}
		}

		return preg_replace("/[\t\n\r]/", ' ', $text);
	}


	public static function getErrorType($errno)
	{
		switch ($errno) {

			case E_ERROR: // 1 //
				$typestr = 'E_ERROR';
				break;
			case E_WARNING: // 2 //
				$typestr = 'E_WARNING';
				break;
			case E_PARSE: // 4 //
				$typestr = 'E_PARSE';
				break;
			case E_NOTICE: // 8 //
				$typestr = 'E_NOTICE';
				break;
			case E_CORE_ERROR: // 16 //
				$typestr = 'E_CORE_ERROR';
				break;
			case E_CORE_WARNING: // 32 //
				$typestr = 'E_CORE_WARNING';
				break;
			case E_COMPILE_ERROR: // 64 //
				$typestr = 'E_COMPILE_ERROR';
				break;
			case E_CORE_WARNING: // 128 //
				$typestr = 'E_COMPILE_WARNING';
				break;
			case E_USER_ERROR: // 256 //
				$typestr = 'E_USER_ERROR';
				break;
			case E_USER_WARNING: // 512 //
				$typestr = 'E_USER_WARNING';
				break;
			case E_USER_NOTICE: // 1024 //
				$typestr = 'E_USER_NOTICE';
				break;
			case E_STRICT: // 2048 //
				$typestr = 'E_STRICT';
				break;
			case E_RECOVERABLE_ERROR: // 4096 //
				$typestr = 'E_RECOVERABLE_ERROR';
				break;
			case E_DEPRECATED: // 8192 //
				$typestr = 'E_DEPRECATED';
				break;
			case E_USER_DEPRECATED: // 16384 //
				$typestr = 'E_USER_DEPRECATED';
				break;
			default:
				$typestr = "E_OTHER";
				break;
		}
		return $typestr;
	}

	public static function writeLog($exception, $file = null, $line = null)
	{
		if (EnvironmentManager::logErrors()) {
			self::$file = !is_null($file) ? $file : $exception->getFile();
			self::$line = !is_null($line) ? $line : $exception->getLine();

			$date = (new DateTime())->format('D M d H:i:s.u Y');
			$os = SessionManager::get(SysConst::CLIENT_OS);
			$action = SessionManager::get(SysConst::R_ROUTE);
			$message = sprintf(self::MASKLOG, $date, $os, $action, self::renderAsText($exception));
			//
			//CREATION DU FICHIER S'IL N'EXISTE PAS
			//
			try {
				$fp = @fopen(EnvironmentManager::getErrorLogFile(), 'a');
				if ($fp) {
					if (flock($fp, LOCK_EX | LOCK_NB)) {
						fwrite($fp, $message);
						flock($fp, LOCK_UN);
					}
					fclose($fp);
				}
			} catch (\Exception $exc) {
				error_log($exc->getMessage(), $exc->getCode());
			}
		}
		if (EnvironmentManager::logErrorOnSystemLog()) {
			error_log(strip_tags($message), 0);
		}
	}

	public static function displayError($exception, $file = null, $line = null, $newLine = '<br/>', $plaineText = true)
	{
		if (EnvironmentManager::displayError()) {
			self::$file = !is_null($file) ? $file : $exception->getFile();
			self::$line = !is_null($line) ? $line : $exception->getLine();
			$message = self::renderAsText($exception, $newLine, $plaineText);
			if (file_exists(EnvironmentManager::getHtmlFileName())) {
				$formatMessage = file_get_contents(EnvironmentManager::getHtmlFileName());
				$formatMessage = str_replace('#ERRORMESSAGE#', (EnvironmentManager::isProduction() ? EnvironmentManager::getdefaultMessage() : $message), $formatMessage);
			} else {
				$formatMessage = EnvironmentManager::isProduction() ? EnvironmentManager::getdefaultMessage() : $message;
			}
			printf('%s', $formatMessage);
		}

	}
}