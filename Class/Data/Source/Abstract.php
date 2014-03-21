<?php
/**
 *
 * @desc Абстрактный класс сорса
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Source_Abstract
{

	/**
	 * @desc Текущий запрос
	 * @var Query
	 */
	private $_query;

	/**
	 *
	 * @var Data_Mapper_Abstract
	 */
	protected $_mapper;

	/**
	 * @desc Результат выполнения запроса
	 * @var Query_Result
	 */
	private $_result;

	/**
	 * @desc
	 * @var integer
	 */
	protected static $_objCount = 0;

	/**
	 * @desc
	 * @var integer
	 */
	protected $_objIndex = null;

	/**
	 * @desc Проверяет доступность источника данных
	 * @return boolean
	 */
	public function available ()
	{
		return is_object ($this->_mapper);
	}

	/**
	 *
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return Data_Source_Abstract $this
	 */
	public function execute ($query = null, $options = null)
	{
		$this->setQuery ($query);
		$this->setResult ($this->_mapper->execute ($this, $this->_query, $options));
		return $this;
	}

	/**
	 * @return Data_Mapper_Abstract
	 */
	public function getDataMapper ()
	{
		return $this->_mapper;
	}

	/**
	 * @return integer
	 */
	public function getIndex ()
	{
		if (is_null ($this->_objIndex))
		{
			$this->_objIndex = ++self::$_objCount;
		}
		return $this->_objIndex;
	}

	/**
	 * @desc Возвращает запрос
	 * @params null|string $translator
	 * 		Ожидаемый вид запроса.
	 * 		Если необходим объект запроса, ничего не указывется (по умолчанию).
	 * 		Если указать транслятор, то результом будет результат трансляции.
	 * @return Query|mixed
	 */
	public function getQuery ($translator = null)
	{
		return
			$translator ?
			$this->_query->translate ($translator) :
			$this->_query;
	}

	/**
	 * @return Query_Result
	 */
	public function getResult ()
	{
		return $this->_result;
	}

	/**
	 *
	 * @param Data_Source_Collection $sources
	 * @return Data_Source_Abstract $this
	 */
	public function setIndexSources (Data_Source_Collection $sources)
	{
		$this->_indexSources = $sources;
		return $this;
	}

	/**
	 * @desc Устанавливает результат запроса.
	 * @param Query_Result $result
	 * @return Data_Source_Abstract
	 */
	public function setResult (Query_Result $result)
	{
		$this->_result = $result;
		return $this;
	}

	/**
	 * @desc Устанавливает запрос.
	 * @param Query_Abstract $query
	 * @return Data_Source_Abstract
	 */
	public function setQuery (Query_Abstract $query)
	{
		$this->_query = $query;
		return $this;
	}

	/**
	 * @desc Устанавливает мэппер.
	 * @param Data_Mapper_Abstract $mapper
	 */
	public function setDataMapper (Data_Mapper_Abstract $mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	/**
	 * @desc Проверяет, что последний запрос выполнен успешно.
	 * @return boolean
	 */
	public function success ()
	{
		if ($this->_result)
		{
			return (bool) ($this->_result->touchedRows () > 0);
		}
		return false;
	}

}