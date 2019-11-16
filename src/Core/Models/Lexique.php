<?php

namespace Digitalis\Core\Models;
/**
 * IMEDIATIS | IMEDIAWEB *
 * @package    IMEDIAWEB 2.0
 * @copyright  2013 Copyright www.imediatis.net
 * @license    IMEDIATIS ALL RIGHTS RESERVED
 * @version    1.0
 * @author : Cyrille WOUPO (IT Softwares Designer)
 */


class Lexique
{

	public function __construct()
	{
	}
	public function __destruct()
	{
	}

	private static $_lexique;
	public static function setLexique($file)
	{
		self::$_lexique = $file;
	}
	public static function getLexique()
	{
		//return self::$_lexique;
		return EnvironmentManager::getLexiqueFile();
	}


	private static $_itemNode = "string";
	private static $_itemKey = "en_name";
	
	
	/* Read Item Online */
	public static function GetString($currentLang, $nodeCode)
	{
		$str = self::getLexique();

		if (file_exists(self::getLexique())) {
			$xml = simplexml_load_file(self::getLexique());
			$target = $xml->xpath("//" . self::$_itemNode . "[@" . self::$_itemKey . "='" . $nodeCode . "']");

			if (!$target)
				return false;
			return (string)($target[0][$currentLang]);
		}
		return false;
	}

	public static function getCode($lang, $nodeCode)
	{
		$code = join("_", array($lang, "name"));
		if (file_exists(self::getLexique())) {
			$xml = simplexml_load_file(self::getLexique());
			$target = $xml->xpath("//" . self::$_itemNode . "[@" . self::$_itemKey . "='" . $nodeCode . "']");
			if (!$target)
				return false;
			return (string)$target[0][$code];
		}
		return false;
	}

}