<?php
/**
 * 
 * @desc Помощник для каптчи.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Helper_Captcha
{
	
	const SF_AUTO_CODE = 'Captcha_Auto_Code';
	
	/**
	 * @desc Проверка сгенерированного ранее кода, переданного от пользователя.
	 * @param array $src Массив со входными данными.
	 * @return boolean
	 */
	public static function acheck ($src = null)
	{
		return self::check (
			$src
			? (isset ($src ['acaptcha']) ? $src ['acaptcha'] : null)
			: (isset ($_POST ['acaptcha']) ? $_POST ['acaptcha'] : null)
		);
	}
	
	/**
	 * 
	 * @return string
	 */
	public static function generateAutocode ()
	{ 
		return substr (md5 (time () . __METHOD__), 3, 10);
	}
	
	/**
	 * 
	 * @return array
	 */
	public static function generatePair ()
	{
		$code = time ();
		return array ($code, md5 ($code));
	}
	
	/**
	 * @desc Проверка введенного пользователем кода.
	 * @param string $code Полученный от пользователя код.
	 * @return boolean
	 */
	public static function check ($code)
	{
		return 
			isset ($_SESSION [Helper_Captcha::SF_AUTO_CODE]) &&
			$_SESSION [Helper_Captcha::SF_AUTO_CODE] == $code;
	}
	
}