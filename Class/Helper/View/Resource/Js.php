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
     * @param string $filename
	 * @return string
	 */
	public static function pack($content, $filename)
	{
        $loader = IcEngine::getLoader();
		$loader->requireOnce('class.JavaScriptPacker.php', 'Vendor');
		$packer = new JavaScriptPacker($content, 0);
		$result = $packer->pack();
		return $result;
	}
}