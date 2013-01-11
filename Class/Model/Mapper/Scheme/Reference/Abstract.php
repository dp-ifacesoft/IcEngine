<?php

/**
 * Абстрактная модель ссылки схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 */
abstract class Model_Mapper_Scheme_Reference_Abstract
{
	/**
	 * Поле ссылки
	 * 
     * @var string
	 */
	protected $field;

	/**
	 * Модель для ссылки
	 * 
     * @var string
	 */
	protected $model;

	/**
	 * Получить данные ссылки
	 * 
     * @param string $model_name
	 * @param string $id
	 * @return Model_Collection
	 */
	abstract public function data($modelName, $id);

	/**
	 * Получить имя фабрики
	 * 
     * @return string
	 */
	public function factoryName()
	{
		return 'Reference';
	}

	/**
	 * Получить имя поля ссылки
	 * 
     * @return array
	 */
	public function getField()
	{
		return $this->field;
	}

	/**
	 * Получить имя модели для ссылки
	 * 
     * @return string
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Получить имя индекса
	 * 
     * @return string
	 */
	public function getName()
	{
		return substr(
            get_class($this), strlen('Model_Mapper_Scheme_Reference_')
        );
	}

	/**
	 * Возвращает ресурс схемы связей модели
	 * 
     * @return Model_Mapper_Scheme_Resource
	 */
	public function resource()
	{
		return new Model_Mapper_Scheme_Resource($this);
	}

	/**
	 * Изменить поле ссылки
	 * 
     * @param string $field
	 */
	public function setField($field)
	{
		$this->field = $field;
	}

	/**
	 * Изменить модель ссылки
	 * 
     * @param string $model
	 */
	public function setModel($model)
	{
		$this->model = $model;
	}
}