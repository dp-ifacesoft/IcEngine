<?php
/**
 * 
 * @desc 
 * @package IcEngine
 * 
 */
class Model_Map
{
	
	/**
	 * @desc Правила получения названия таблиц по названию модели
	 * (model_name => table_name)
	 * @var array
	 */
	protected $_modelTable = array();
	
	/**
	 * @desc
	 * @param string $table 
	 * @return string
	 */
	public function getModel ($table)
	{
		return array_search ($table, $this->_modelTable);
	}
	
	/**
	 * @desc
	 * @param string $model 
	 * @return mixed
	 */
	public function getTable ($model)
	{
		return $this->_modelTable [$model];
	}
	
	/**
	 * @desc
	 * @param string|array $model 
	 * @param string $table [optional]
	 */
	public function setTable ($model, $table = null)
	{
		$this->_modelTable [$model] = $table;
	}
	
}