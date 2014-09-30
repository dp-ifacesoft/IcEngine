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
	public static function pack($content, $filename, $params)
	{
        $replacedContent = str_replace(
			array('\\',	'"', "\r\n", "\n", "\r"),
			array('\\\\', '\\"', '"+"\\r\\n"+"', '"+"\\n"+"', '"+"\\r"+"'),
			$content
		);
        $filename = str_replace(
            IcEngine::root() . 'Ice/Static/jtpl/', '', $filename
        );
        $name = isset($params['name']) ? $params['name'] : $filename;
        $result = 'View_Render.templates[\'' . $name . '\']="' .
            $replacedContent . '";';
		return $result;
	}
}