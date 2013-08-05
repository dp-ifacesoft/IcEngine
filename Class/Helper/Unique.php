<?php

/**
 * Генерация уникальный идентификаторов.
 * 
 * @author goorus, morph
 * @Service("helperUnique")
 */
class Helper_Unique
{
	
	/**
	 * Счетчик для избежания генерации одинаковых ID в рамках
	 * одного процесса.
	 * 
     * @var integer
	 */
	private $counter = 0;
	
	/**
	 * Генерирует уникальный идентификатор на основе названия модели
	 * или текущего времени.
	 * 
     * @param Model $model
	 * @return string
	 */
	public function forModel(Model $model)
	{
		$ext = 1000 + $this->counter++;
		return $model->modelName() . uniqid(__CLASS__, true) . $ext;
	}
	
	/**
	 * Получить уникальный хэш
	 * 
     * @return string
	 */
	public function hash()
	{
		return uniqid(null, true);
	}
}