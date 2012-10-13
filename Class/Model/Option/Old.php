<?php
/**
 * 
 * @desc Опция, подключающая старые опции - потомки 
 * от Model_Collection_Option_Abstract.
 * @author Юрий Шведов
 * 
 */
class Model_Option_Old extends Model_Option
{
	
	/**
	 * @desc Опция, которая будет применяться к коллекции.
	 * @var Model_Collection_Option_Abstract
	 */
	public $option;
	
	/**
	 *
	 * @param Model_Collection $collection
	 * @param array $params 
	 */
	public function __construct (Model_Collection $collection, array $params)
	{
		Loader::load ('Model_Collection_Option');
		parent::__construct ($collection, $params);
	}
	
	/**
	 * @desc После выполнения запроса.
	 */
	public function after ()
	{
		$this->option->after (
			$this->collection,
			$this->query,
			$this->params
		);
	}
	
	/**
	 * @desc До выполнения запроса
	 */
	public function before ()
	{
		$this->option ()->before (
			$this->collection,
			$this->query,
			$this->params
		);
	}
	
	/**
	 * @desc Возвращает объект старой опции.
	 * @return Model_Collection_Option_Abstract
	 */
	public function option ()
	{
		if (!$this->option)
		{
			if (isset ($this->params ['option']))
			{
				// Передана сама опция
				$this->option = $this->option;
			}
			else
			{
				// Должно быть передано имя
				Loader::load ('Model_Collection_Option_Manager');
				$this->option = Model_Collection_Option_Manager::get (
					$this->params ['name'],
					$this->collection
				);
			}
		}
		return $this->option;
	}
	
}
