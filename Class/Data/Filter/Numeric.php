<?php

/**
 * Фильтр преобразования данных к integer
 * Тоже что и Filter_Integer
 *
 * @author neon
 */
class Data_Filter_Numeric extends Data_Filter_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($data)
	{
		return (int) $data;
	}

	/**
	 * @inheritdoc
	 */
	public function filterEx($field, $data)
	{
		return (int) $data->$field;
	}
}