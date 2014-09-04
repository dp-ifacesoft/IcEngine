<?php

/**
 * Фильтр получения даты
 *
 * @author goorus, neon
 */
class Data_Filter_Date extends Data_Filter_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($data)
	{
		$locator = IcEngine::serviceLocator();
		$helperDate = $locator->getService('helperDate');
		return $helperDate->toUnix($helperDate->strToTimestamp($data));
	}

	/**
	 * @inheritdoc
	 */
	public function filterEx($field, $data, $scheme)
	{
		$locator = IcEngine::serviceLocator();
		$helperDate = $locator->getService('helperDate');
		$timestamp =
			(isset($scheme->input) && $scheme->input == 'php') ?
			strtotime($data->$field) :
			$helperDate->strToTimestamp($data->$field);
		if (isset($scheme->output) && $scheme->output == 'timestamp') {
			return $timestamp;
		}
		return $helperDate->toUnix($timestamp);
	}
}