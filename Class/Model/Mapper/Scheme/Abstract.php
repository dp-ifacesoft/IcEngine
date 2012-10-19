<?php

/**
 * @desc Абстрактная схема связей модели
 * @author Илья Колесников
 */

class Model_Mapper_Scheme_Abstract
{
	/**
	 * @desc Конфигурация
	 * @var array
	 */
	protected static $_config;

	/**
	 * @desc Сущности схемы
	 * @var array
	 */
	protected $_entities;

	/**
	 * @desc Модель
	 * @var Model
	 */
	protected $_model;

	public function __get ($name)
	{
		if (!isset ($this->_entities [$name]))
		{
			throw new Model_Mapper_Scheme_Exception ('Entity had not found');
		}
		$entity = $this->_entities [$name];
		return Model_Mapper_Scheme_Accessor::getAuto ($this, $entity);
	}

	/**
	 * (non-PHPDoc)
	 */
	public function __set ($name, $value)
	{
		$class = null;
		$parents = get_parent_class ($value);
		if (is_array ($parents))
		{
			$class = reset ($parents);
		}
		elseif ($parents)
		{
			$class = $parents;
		}
		$this->_entities [$name] = new Model_Mapper_Scheme_Entity (
			$class, $name, $value
		);
	}

	/**
	 * @desc Получить конфигурацию схемы
	 * @return array
	 */
	public static function config ()
	{
		if (!is_object (self::$_config))
		{
			self::$_config = Config_Manager::get (
				'Model_Mapper_Scheme',
				self::$_config
			);
		}
		return self::$_config;
	}

	/**
	 * @desc Получить сущности схемы
	 * @return array
	 */
	public function entities ()
	{
		return $this->_entities;
	}

	/**
	 * @desc Возвращает модель
	 * @return Model
	 */
	public function getModel ()
	{
		return $this->_model;
	}

	/**
	 * @desc Получить имя схемы
	 * @return array
	 */
	public function getName ()
	{
		return substr (get_class ($this), 20);
	}

	/**
	 * @desc Изменяет модель
	 * @param Model $model
	 */
	public function setModel ($model)
	{
		$this->_model = $model;
	}
}