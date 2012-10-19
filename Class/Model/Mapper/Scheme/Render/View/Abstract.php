<?php

/**
 * @desc Абстрактное представление рендера схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_View_Abstract
{
	/**
	 * @desc Конфигурация
	 * @var array
	 */
	protected static $_config = array (
		'models'	=> array (
			'default'	=> array (

			)
		)
	);

	/**
	 * @desc Получить конфигурацию схемы
	 * @return array
	 */
	public static function config ()
	{
		if (!is_object (self::$_config))
		{
			self::$_config = Config_Manager::get (
				get_called_class (),
				static::$_config
			);
		}
		return self::$_config;
	}

	/**
	 * @desc Получить имя схемы
	 * @return array
	 */
	public function getName ()
	{
		return substr (get_class ($this), 27);
	}

	/**
	 * @desc Получить отрендеренные части схемы для рендеринга
	 * @param Model_Mapper_Scheme_Abstract $scheme
	 * @return string
	 */
	public function getParts ($mapper_scheme)
	{
		$model = $mapper_scheme->getModel ();
		$model_name = $model->modelName ();
		$mapper_scheme_config = $mapper_scheme->config ();
		$mapper_scheme_config = !isset ($mapper_scheme_config->$model_name)
			? $mapper_scheme_config->default
			: $mapper_scheme_config->$model_name;
		$parts = array ();
		$entities = $mapper_scheme->entities ();
		if (!$entities)
		{
			return;
		}
		foreach ($entities as $name => $entity)
		{
			$factory_name = $entity->getValue ()->factoryName ();
			if (!isset ($parts [$factory_name]))
			{
				$parts [$factory_name] = array ();
			}
			$render = Model_Mapper_Scheme_Render::byArgs (
				$mapper_scheme_config->translator,
				$factory_name,
				$entity->getValue ()->getName ()
			);
			$result = $render->render ($entity);
			$parts [$factory_name][$name] = $result;
		}
		return $parts;
	}

	/**
	 * @desc Отрендерить схему модели
	 * @param Model_Mapper_Scheme_Abstract $scheme
	 * @return string
	 */
	public static function render ($scheme)
	{

	}
}