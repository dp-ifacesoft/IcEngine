<?php

/**
 * Фильтр для кодирования Json
 *
 * @author goorus, neon
 */
class Data_Filter_Json_Encode extends Data_Filter_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($data)
	{
		return json_encode($data);
	}
}