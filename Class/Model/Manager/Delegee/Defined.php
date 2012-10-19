<?php
/**
 *
 * @desc Класс для создания определенных моделей.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Manager_Delegee_Defined
{
	/**
	 * @desc
	 * @param string $model Название модели
	 * @param string $key Ключ (id)
	 * @param Model|array $object Объект или данные
	 * @return Model В случае успеха объект, иначе null.
	 */
	public static function get ($model, $key, $object)
	{
		$rows = $model::$rows;

		foreach ($rows as $row)
		{
			if ($row ['id'] == $key)
			{
				if (isset ($row ['name']))
				{
					if (Loader::tryLoad($model . '_' . $row['name'])) {
						$model .= '_' . $row ['name'];
					}
				}
				return new $model ($row);
			}
		}

		return new $model (is_array ($object) ? $object : array ());
	}

}