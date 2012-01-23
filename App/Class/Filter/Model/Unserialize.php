<?php

namespace Ice;

/**
 *
 * @desc Фильтр для десериализации моделей
 * @author Yury Shvedov
 * @package Ice
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
		Loader::load ($class);
		return new $class (json_decode (substr ($data, $p + 1), true));
	}

}