<?php

/**
 * Фильтр для декодирования Json
 *
 * @author goorus, neon
 */
class Data_Filter_Json_Decode extends Data_Filter_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($data)
	{
		return json_decode($data, true);
	}
}