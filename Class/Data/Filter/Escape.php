<?php

/**
 * Экранирование данных через Data_Source
 *
 * @author neon
 */
class Data_Filter_Escape extends Data_Filter_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($data)
	{
		$locator = IcEngine::serviceLocator();
		$dds = $locator->getService('dds');
		return $dds->escape($data);
	}
}