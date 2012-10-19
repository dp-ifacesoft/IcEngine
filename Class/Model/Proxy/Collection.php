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
	 * @desc Проксируемая модель.
	 * @var string
	 */
	protected $_modelName;

	/**
	 * @desc Создает и возвращает коллекцию проксируемых моделей.
	 * @param string $model_name Проксируемая модель
	 */
	public function __construct ($model_name)
	{
		$this->_modelName = $model_name;
    	$this->_options = new Model_Collection_Option_Collection ($this);
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

	/**
	 * @desc
	 * @return string Название проксируемой модели
	 */
	public function modelName ()
	{
		return $this->_modelName;
	}

}