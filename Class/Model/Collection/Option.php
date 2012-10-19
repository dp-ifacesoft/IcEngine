<?php
/**
 *
 * @desc Опция коллекции.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Model_Collection_Option
{

	/**
	 * @desc Название опции
	 * @var string
	 */
	protected $_name;

	/**
	 * @desc Параметры
	 * @var array
	 */
	protected $_params;

	/**
	 * @desc Опция
	 * @var Model_Collection_Option_Abstract
	 */
	protected $_option;

	/**
	 * @desc Создает и возвращает опцию
	 * @param string $name Название опции.
	 * @param array $params Параметры применения.
	 */
	public function __construct ($name, $params = array ())
	{
		$this->_name = $name;
		$this->_params = $params;
	}

	/**
	 * @desc Наложение опции.
	 * @param string $type "before" or "after"
	 * @param Model_Collection $collection
	 * @param Query_Abstract $query
	 * @return mixed Результат наложения.
	 */
	public function execute ($type, $collection, Query_Abstract $query)
	{
		if (!$this->_option)
		{
			$this->_option = Model_Collection_Option_Manager::get (
				$this->_name,
				$collection
			);
		}

		return Executor::execute (
			array ($this->_option, $type),
			array ($collection, $query, $this->_params)
		);
	}

	/**
	 * @desc Получить имя опшина
	 * @return string
	 */
	public function getName ()
	{
		return $this->_name;
	}

	/**
	 * @desc Возвращает параметры опции.
	 * @return array
	 */
	public function getParams ()
	{
		return $this->_params;
	}

}