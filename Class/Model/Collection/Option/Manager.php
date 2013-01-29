<?php
/**
 *
 * @desc Менеджер опций.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Collection_Option_Manager
{

	/**
	 * @desc Опции.
	 * @var array <Model_Collection_Option_Abstract>
	 */
	protected static $_options = array ();

	/**
	 * @desc Возвращает название класса опции для коллекции.
	 * @param string $option Название опции
	 * @param Model_Collection $collection Коллекция.
	 * @return string
	 */
	protected static function getClassName ($option, $collection)
	{
		$p = strpos ($option, '::');
		if ($p === false)
		{
			return
				$collection->modelName () .
				'_Collection_Option_' .
				$option;
		}
		elseif ($p === 0)
		{
			return 'Model_Collection_Option_' . substr ($option, $p + 2);
		}
		else
		{
			return
				substr ($option, 0, $p) .
				'_Collection_Option_' .
				substr ($option, $p + 2);
		}
	}

	/**
	 * @desc Создание новой опции.
	 * @param string $name Название опции. Может содержать название модели.
	 * Active - Опция Active текущей коллекции.
	 * Car::Active - Car_Collection_Option_Active
	 * ::Active - Model_Collection_Option_Active
	 * @param array $params
	 * @param Model_Collection $collection
	 * @return Model_Collection_Option_Abstract
	 */
	public static function get ($name, $collection)
	{
		$class = self::getClassName ($name, $collection);
		if (!isset (self::$_options [$class]))
		{
			self::$_options [$class] = new $class ();
		}
		return self::$_options [$class];
	}

}