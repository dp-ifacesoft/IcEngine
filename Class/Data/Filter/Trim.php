<?php

/**
 * Фильтр удаления начальных, конечных пробелов
 *
 * @author neon
 */
class Data_Filter_Trim extends Data_Filter_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($data)
	{
		return trim($data);
	}

	/**
	 * @inheritdoc
	 */
	public function filterEx($field, $data, $scheme)
	{
		$chars =
			isset($scheme->field['trimChars']) ?
			$scheme->field['trimChars'] : null;
		return trim($data->$field, $chars);
	}
}