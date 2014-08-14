<?php

/**
 * Абстрактная схема связей модели
 * 
 * @author morph
 */
class Model_Mapper_Scheme_Abstract
{
	/**
	 * Конфигурация
	 * 
     * @var array
	 */
	protected static $config;

	/**
	 * Модель
	 * 
     * @var Model
	 */
	protected $model;
    
    /**
	 * Сущности схемы
	 * 
     * @var array
	 */
	protected $states;

    /**
     * @inheritdoc
     */
	public function __get($name)
	{
		$state = $this->states[$name];
        $serviceLocator = IcEngine::serviceLocator();
        $schemeAccessor = $serviceLocator->getService(
            'modelMapperSchemeAccessor'
        );
		return $schemeAccessor->getAuto($this, $state);
	}

	/**
	 * @inheritdoc
	 */
	public function __set($name, $value)
	{
		$class = null;
		$parents = get_parent_class($value);
		if (is_array($parents)) {
			$class = reset($parents);
		} elseif ($parents) {
			$class = $parents;
		}
		$this->states[$name] = new Model_Mapper_Scheme_State(
			$class, $name, $value
		);
	}

	/**
	 * Получить конфигурацию схемы
	 * 
     * @return array
	 */
	public function config()
	{
		if (!is_object(static::$config)) {
            $serviceLocator = IcEngine::serviceLocator();
            $configManager = $serviceLocator->getService('configManager');
			static::$config = $configManager->get(
				get_called_class(),
				static::$config
			);
		}
		return static::$config;
	}

	/**
	 * Возвращает модель
	 * 
     * @return Model
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Получить имя схемы
	 * 
     * @return array
	 */
	public function getName()
	{
		return substr(get_class($this), strlen('Model_Mapper_Scheme_'));
	}

	/**
	 * Изменяет модель
	 * 
     * @param Model $model
	 */
	public function setModel($model)
	{
		$this->model = $model;
	}
    
    /**
	 * Получить сущности схемы
	 * 
     * @return array
	 */
	public function states()
	{
		return $this->states;
	}
}