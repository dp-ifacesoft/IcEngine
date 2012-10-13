<?php
/**
 * 
 * @desc Фильтр к смарти для перекодирования шаблонов.
 * @author Юрий
 * @package IcEngine
 *
 */
class Helper_Smarty_Filter_Encoding
{
	
	/**
	 * @desc Комментарий файла, если необходимо перекодирование.
	 * Должен содержаться в первых строках файла
	 * @var array
	 */
	protected static $_charsets = array (
		'{*utf8*}'		=> 'utf-8'
	);
	
	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		/**
		 * @desc Кодировка результата.
		 */
		'result_charset'	=> 'cp1251',
		/**
		 * @desc Длина проверяемого сегмента на содержание комментария 
		 * с кодировкой.
		 */
		'check_length'		=> 200
	);
	
	/**
	 * @desc Регистрация фильтра в смарти.
	 * @param Smarty $smarty
	 */
	public static function register (Smarty $smarty)
	{
		$smarty->registerFilter ('pre', array (__CLASS__, 'filterEncoding'));
	}
	
	/**
	 * @desc Перекодирование 
	 * @param string $tpl_source Исходный код шаблона
	 * @return string Результат.
	 */
	public static function filterEncoding ($tpl_source)
	{
		if (!$tpl_source)
		{
			return;
		}
		
		$check = substr ($tpl_source, 0, self::$_config ['check_length']);
		foreach (self::$_charsets as $pattern => $charset)
		{
			if (strpos ($check, $pattern) !== false) 
			{
				return iconv (
					$charset, 
					self::$_config ['result_charset'], 
					$tpl_source
				);
			}
		}
		
		return $tpl_source;	
	}
	
}