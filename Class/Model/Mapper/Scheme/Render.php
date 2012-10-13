<?php

/**
 * @desc Фабрика рендеров схемы моделей
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render
{
	/**
	 * @desc Получить рендер по параметрам
	 * @param string $translator_name Имя траслятор дата сорса
	 * @param string $factory_name Имя фабрики сущности
	 * @param string $name Имя сущности
	 * @return Model_Mapper_Scheme_Render_Abstract
	 */
	public static function byArgs ($translator_name, $factory_name, $name)
	{
		$values = array ($translator_name, $factory_name, $name);
		foreach ($values as $i => $value)
		{
			if (!$value)
			{
				unset ($values [$i]);
			}
		}
		return self::byName (implode ('_', $values));
	}

	/**
	 * @desc Получить рендер по имени
	 * @param string $name
	 */
	public static function byName ($name)
	{
		$class_name = 'Model_Mapper_Scheme_Render_' . $name;
		if (!Loader::load ($class_name))
		{
			throw new Model_Mapper_Scheme_Render_Exception (
				'Render had not found'
			);
		}
		return new $class_name;
	}
}