<?php
/**
 *
 * @desc Класс для создания моделей через фабрики.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Manager_Delegee_Search_Index
{

	/**
	 * @desc Фабрики моделей
	 * @var array
	 */
	protected static $_factories;

	/**
	 * @desc Находит фабрику модели
	 * @param Model $model Модель.
	 * @return Model_Factory Фабрика.
	 */
	public static function factory ($model)
	{
		$parents = class_parents ($model);
		foreach ($parents as $parent)
		{
			if (substr ($parent, -9, 9) == '_Abstract')
			{
				$factory = substr ($parent, 0, -9);
				if (isset (self::$_factories [$factory]))
				{
					return self::$_factories [$factory];
				}
				return self::$_factories [$factory] = new $factory ();
			}
		}
	}

	/**
	 * @desc Получение данных модели
	 * @param string $model Название модели
	 * @param string $key Ключ (id)
	 * @param Model|array $object Объект или данные
	 * @return Model В случае успеха объект, иначе null.
	 */
	public static function get ($model, $key, $object)
	{
		$factory_name = $model;
		if (!isset (self::$_factories [$factory_name]))
		{
			self::$_factories [$factory_name] = new $model ();
			$abstract = $factory_name . '_Abstract';
		}

		$dmodel = self::$_factories [$factory_name]
			->delegateClass ($model, $key, $object);


		$result = new $dmodel (array ());

		$result->setModelFactory (self::$_factories [$factory_name]);

		if (is_array ($object) && $object)
		{
			$result->set ($object);
		}

		return $result;
	}

}