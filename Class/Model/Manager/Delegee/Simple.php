<?php
/**
 * 
 * @desc Класс для создания простых моделей.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Manager_Delegee_Simple
{
	
	/**
	 * @desc 
	 * @param string $model
	 * @param string $key
	 * @param mixed $object
	 */
	public static function get ($model, $key, $object)
	{
		return new $model (is_array ($object) ? $object : array ());
	}
	
}