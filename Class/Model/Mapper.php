<?php

/**
 * @desc Реализация ORM
 * @author Илья Колесников
 */
class Model_Mapper
{
	/**
	 * @desc Конфигурация схемы связей моделей
	 * @var Objective
	 */
	protected static $_config;

	/**
	 * @desc Схемы связей моделей
	 * @var array
	 */
	private static $_schemes = array ();

	/**
	 * (non-PHPDoc)
	 */
	public static function __callStatic ($method, $params)
	{
		$method = Model_Mapper_Method::normalizaName ($method);
		$method = Model_Mapper_Method::byName ($method);
		$method->setParams ($params);
		return $method->execute ();
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
	 * @desc Получить схему для моделей
	 * @param string $model_name
	 */
	public static function scheme ($model)
	{
		if (is_object($model)) {
			$model_name = $model->modelName ();
		} else {
			$model = $model_name;
		}
		$key = $model_name . '_' . $model->key ();

		if (!isset (self::$_schemes [$key]))
		{
			$config = Config_Manager::get ('Model_Mapper_' . $model_name);
			if (!$config)
			{
				return;
			}
            
			$scheme_name = 'Simple';
			if (isset ($config->scheme))
			{
				$scheme_name = $config->scheme;
			}
			$scheme = Model_Mapper_Scheme::byName ($scheme_name);
			$scheme->setModel ($model);
			foreach ($config as $name => $values)
			{
				if ($values)
				{
					$scheme = Model_Mapper_Scheme_Part::getAuto (
						$name, $scheme, $values
					);
				}
			}
			self::$_schemes [$key] = $scheme;
		}
		return clone self::$_schemes [$key];
	}
}