<?php

/**
 * Стандартный фильтр чисел.
 *
 * @author goorus, neon
 */
class Data_Filter_Integer extends Data_Filter_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($data)
	{
		$locator = IcEngine::serviceLocator();
		$helperString = $locator->getService('helperString');
		return $helperString->str2int(trim($data));
	}
}