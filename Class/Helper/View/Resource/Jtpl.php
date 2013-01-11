<?php

/**
 * Делегат для упаровки jtpl
 *
 * @author morph
 */
class Helper_View_Resource_Jtpl
{
	/**
	 * Упаковывает файл
	 *
	 * @param string $content
	 * @return string
	 */
	public static function pack($content, $filename)
	{
        $replacedContent = str_replace(
			array('\\',	'"', "\r\n", "\n", "\r"),
			array('\\\\', '\\"', '"+"\\r\\n"+"', '"+"\\n"+"', '"+"\\r"+"'),
			$content
		);
        $filename = str_replace(IcEngine::root() . 'Ice/View/', '', $filename);
        $result = 'View_Render.templates[\'' . $filename . '\']="' .
            $replacedContent . '";';
		return $result;
	}
}