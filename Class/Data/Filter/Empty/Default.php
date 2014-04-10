<?php

/**
 * Фильтр проверяет на пустоту данных
 *
 * @author neon
 */
class Data_Filter_Empty_Default extends Data_Filter_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($data)
	{
		return $data ? $data : '';
	}

	/**
	 * @inheridoc
	 */
	public function filterEx($field, $data, $scheme)
	{
		$default = isset($scheme->field['default']) ?
			$scheme->field['default'] : '';
		return $data ? $data : $default;
	}
}