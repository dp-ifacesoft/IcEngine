<?php

/**
 * @desc Часть схемы моделей
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Part
{
	/**
	 * @desc Конфигурация
	 * @var array
	 */
	protected static $_config = array (
		'parts'	=> array (
			'Field',
			'Index',
			'Reference'
		)
	);

	/**
	 * @desc Получить часть схемы по имени
	 * @param string $name
	 * @return Model_Mapper_Scheme_Part_Abstract
	 */
	public static function byName ($name)
	{
		$class_name = 'Model_Mapper_Scheme_Part_' . $name;
		if (!Loader::load ($class_name))
		{
			throw new Model_Mapper_Scheme_Part_Exception ('Index had not found');
		}
		return new $class_name;
	}

	/**
	 * @desc Получить конфигурацию
	 * @return Objective
	 */
	public static function config ()
	{
		if (!is_object (self::$_config))
		{
			self::$_config = Config_Manager::get (__CLASS__, self::$_config);
		}
		return self::$_config;
	}

	/**
	 * @desc Получить часть схемы по критериям
	 * @param string $name
	 * @param Model_Mapper_Scheme_Abstract $scheme
	 * @param Objective $values
	 * @return Model_Mapper_Scheme_Abstract
	 */
	public static function getAuto ($name, $scheme, $values)
	{
		$parts = self::config ()->parts;
		if (!$parts)
		{
			return;
		}

		foreach ($parts as $part)
		{
			$part = self::byName ($part);
			if ($part->getSpecification () == $name)
			{
				$part->execute ($scheme, $values);
			}
		}
		return $scheme;
	}
}