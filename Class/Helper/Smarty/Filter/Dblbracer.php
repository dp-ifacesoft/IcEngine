<?php
/**
 * 
 * @desc Помощник для подмены открывающих скобок на двойные.
 * Вместо "{" и "}" следует использовать "{{" и "}}".
 * @author Юрий
 * @package IcEngine
 *
 */
class Helper_Smarty_Filter_Dblbracer
{
	
	/**
	 * @desc Открывающийся тэг
	 * @var string
	 */
	const SMARTY_TAG = '{dblbracer}';
	
	/**
	 * @desc Завершающий тэг
	 * @var string
	 */
	const SMARTY_TAG_C = '{/dblbracer}';
	
	/**
	 * @desc Временное обозначение открывающего тэга.
	 * @var unknown_type
	 */
	const TEMP_TAG = '<oPeNbRacEr>';
	
	/**
	 * @desc Временное обозначение закрывающего тэга.
	 * @var unknown_type
	 */
	const TEMP_TAG_C = '<ClosEBrAcER>';
	
	/**
	 * @desc Обрабатываемые тэги в массиве.
	 * Так проще реализовать их поиск в шаблоне.
	 * @var array
	 */
	protected static $_tags = array (
		0	=> self::SMARTY_TAG,
		1	=> self::SMARTY_TAG_C
	);
	
	/**
	 * @desc Заменяет закрывающую скобку на ее обозначение в смарти.
	 * @param string $str Исходный код шаблона.
	 */
	public static function _replaceRdelim ($str)
	{
		return str_replace ('}', '{rdelim}', $str);
	}
	
	/**
	 * @desc Регистрация фильтра в смарти.
	 * @param Smarty $smarty
	 */
	public static function register (Smarty $smarty)
	{
		$smarty->registerFilter ('pre', array (__CLASS__, 'filterDblbracer'));
	}
	
	/**
	 * @desc Реализует работу двойных скобок.
	 * @param string $tpl_source Исходный код шаблона
	 * @return string Результат с замененными скобками.
	 */
	public static function filterDblbracer ($tpl_source)
	{
		if (!$tpl_source)
		{
			return;
		}
		
		$last_pos = 0;
		$t = 0;
		$result = '';
		
		/*
		 * 1. Ищем открывающую скобку, текст до нее просто копируем.
		 * 2.1 Ищем закрывающую скобку
		 * 2.2 Заменям двойные скобки на временные обозначения.
		 * 2.3 Заменяем все оставшиеся (одинарные) скобки на их обозначения в 
		 * смарти ({ldelim} и {rdelim}).
		 * 2.4 Заменяем временные обозначения на скобки. 
		 */
		while (true)
		{
			$tag = self::$_tags [$t];
			
			$p = strpos ($tpl_source, $tag, $last_pos);
			
			if ($p === false)
			{
				return $result . substr ($tpl_source, $last_pos);
			}
			
			$chunk = substr ($tpl_source, $last_pos, $p - $last_pos);
			
			if (1 == $t)
			{
				// Заменяемая часть
				$chunk = str_replace (
					array ('{{', '}}'), 
					array (self::TEMP_TAG, self::TEMP_TAG_C),
					$chunk
				);
				
				
				$parts = array_map (
					array (__CLASS__, '_replaceRdelim'),
					explode ('{', $chunk)
				);
			
				$chunk = str_replace (
					array (self::TEMP_TAG, self::TEMP_TAG_C),
					array ('{', '}'),
					implode ('{ldelim}', $parts)
				);
			}
			
			$result .= $chunk;
			
			$last_pos = $p + strlen ($tag);
			$t = 1 - $t;
		}
	}
	
}