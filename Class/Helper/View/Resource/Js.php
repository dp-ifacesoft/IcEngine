<?php

/**
 * Делегат для упаровки js
 *
 * @author morph
 */
class Helper_View_Resource_Js
{
	/**
	 * Упаковывает файл
	 *
	 * @param string $content
	 * @return string
	 */
	public static function pack($content)
	{
		Loader::requireOnce('class.JavaScriptPacker.php', 'includes');
		$packer = new JavaScriptPacker($content, 0);
		$result = $packer->pack();
		return $result;
	}
}