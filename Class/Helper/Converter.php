<?php

/**
 * Конвертер массивов в строку
 *
 * @author markov, neon
 * @Service("helperConverter")
 */
class Helper_Converter
{
	/**
	 * Создает текстовое представление массива по синтаксису php
	 */
	public function arrayToString($data, $offset = 0)
	{
		$locator = IcEngine::serviceLocator();
		$viewRenderManager = $locator->getService('viewRenderManager');
		$smarty = $viewRenderManager->byName('Smarty');
		$padding = null;
		$padding2 = 0;
		for ($i = 0; $i < $offset; $i++) {
			if ($i == $offset - 1) {
				$padding2 = $padding;
			}
			$padding .= '	';
		}
		$smarty->assign('data', $data);
		$smarty->assign('offset', $offset);
		$smarty->assign('padding', $padding);
		$smarty->assign('padding2', $padding2);
		$content = $smarty->fetch('Helper/Converter/arrayToString');
		unset($smarty);
		return $content;
	}
}