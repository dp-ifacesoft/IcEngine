<?php

/**
 * Делегат для упаровки ngtpl
 *
 * @author morph, markov
 */
class Helper_View_Resource_Ngtpl
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
            IcEngine::root() . 'Ice/Static/ngtpl/', '', $filename
        );
        $name = isset($params['name']) ? $params['name'] : $filename;
        $result = 'Ng_Template.templates[\'' . $name . '\']="' .
            $replacedContent . '";';
		return $result;
	}
}