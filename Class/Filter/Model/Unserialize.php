<?php
/**
 *
 * @desc Фильтр для десериализации моделей
 * @author Юрий
 * @package IcEngine
 *
 */
class Filter_Model_Unserialize
{

	/**
	 * @desc Десириализация строки в модель
	 * @param string $data
	 * @return Model
	 */
	public function filter ($data)
	{
		if (!$data)
		{
			return null;
		}

		$p = strpos ($data, ':');
		$class = substr ($data, 0, $p);
		return new $class (json_decode (substr ($data, $p + 1), true));
	}

}