<?php

/**
 * Упаковщик css файлов
 *
 * @author morph
 */
class Helper_View_Resource_Css
{
	/**
	 * Упаковывает
	 *
	 * @param string $content
     * @param string $filename
	 * @return string
	 */
	public static function pack($content, $filename)
	{
		$replaceContent = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '',
			$content);
		$content = str_replace(array ("\r", "\t", '@CHARSET "UTF-8";'), '',
			$replaceContent);
		return $content;
	}
}