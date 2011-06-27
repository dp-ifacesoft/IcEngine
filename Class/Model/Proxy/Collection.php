<?php
/**
 * 
 * @desc Прокси класс для коллекции моделей
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Proxy_Collection extends Model_Collection
{
	
	/**
	 * Проксируемая модель.
	 * @var string
	 */
	protected $_modelName;
	
	public function __construct ($model_name)
	{
		$this->_modelName = $model_name;
		Loader::load ('Model_Proxy');
		Loader::load ('Model_Collection_Option_Item_Collection');
    	$this->_options =
    	    new Model_Collection_Option_Item_Collection ($this->modelName ());
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Model_Collection::fromArray()
	 */
	public function fromArray (array $rows, $clear = true)
	{
		if ($clear)
		{
			$this->_items = array ();
		}
		
		foreach ($rows as $row)
		{
			$this->_items [] = new Model_Proxy ($this->_modelName, $row);
		}
		
		return $this;
	}
	
}