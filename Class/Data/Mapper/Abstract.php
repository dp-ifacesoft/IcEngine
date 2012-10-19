<?php

abstract class Data_Mapper_Abstract
{

	/**
	 * Префикс источников индексов
	 * @var string
	 */
	const INDEX_PREFIX = 'index_';

	/**
	 *
	 * @var Query_Options
	 */
	protected $_defaultOptions;

	/**
	 *
	 * @var Filter_Collection
	 */
	protected $_filters;

	public function __construct ()
	{
		$this->initFilters ();
	}

	/**
	 * @desc Создание индексов для записи
	 * @param string $source
	 * @param array $record
	 */
	protected function _createIndexes ($source, array $record)
	{
		$indexes = $this->_getIndexes ($source);
		$query = new Query ();
		$prefix = self::INDEX_PREFIX . $source . '_';
		foreach ($indexes as $index)
		{
			$index_source = $prefix . $index['id'];

			$data = array(
				'id'	=> $record['id']
			);

			$fields = json_decode ($index['fields'], true);
			foreach ($fields as $field => $info)
			{
				if (isset ($record[$field]))
				{
					$data[$field] = $this->_toType ($record[$field], $info['type']);
				}
				else
				{
					break 2;
				}
			}


			$query->reset ();
			$query
				->insert ($index_source)
				->values ($data);

			$this->_execute ($query);
		}
	}

	/**
	 *
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return mixed
	 */
	public function _execute(Query_Abstract $query, $options = null)
	{
		return null;
	}

	/**
	 * @desc Обновление данных сущности
	 * @param string $id Первичный ключ
	 * @param string $table Название сущности
	 * @param array $data Данные
	 */
	public function _extendRecord ($id, $source, array $data)
	{
		$record = $this->getRecord ($id, $source);
		$this->_removeIndexes ($source, $record);

		$record = array_merge ($record, $data);

		$this->_setRecord ($id, $source);
		$this->_createIndexes ($source, $record);
	}

	protected function _getIndexes ($source)
	{
		$query = new Query ();
		$query
			->select ('id', 'name')
			->from ($source);

		return $this->_execute ($query)->result();
	}

	/**
	 *
	 * @param mixed $result
	 * @param Query_Options $options
	 * @return boolean
	 */
	protected function _isCurrency ($result, $options)
	{
		if (!$options)
		{
			return true;
		}
		return $options->getNotEmpty () && empty ($result) ? false : true;
	}

	/**
	 * @desc Удаление индексов для записи
	 * @param string $source
	 * @param array $record
	 */
	protected function _removeIndexes ($source, array $record)
	{
		$indexes = $this->_getIndexes ($source);
		$query = new Query ();
		$prefix = self::INDEX_PREFIX . $source . '_';
		foreach ($indexes as $index)
		{
			$index_source = $prefix . $index['id'];

			$query->reset ();

			$query
				->delete ()
				->from ($index_source)
				->where ('id', $record['id']);
		}
	}

	/**
	 * @desc Перезапись данных
	 * @param string $id
	 * @param string $source
	 * @param array $data
	 */
	protected function _setRecord ($id, $source, array $data)
	{
		assert (!empty($id) || $id === 0 || $id === '0');
		$query = new Query();

		$query->delete ()->from ($source)->where ('id', $id);
		$this->_execute($query);

		$query->reset ()->insert ($source)->values ($data);
		$this->_execute ($query);
	}

	/**
	 *
	 * @param mixed $value
	 * @param string $type
	 */
	protected function _toType ($value, $type)
	{
		if ($type == 'boolean')
		{
			return $value ? 1 : 0;
		}
		settype ($value, $type);
		return $value;
	}

	/**
	 *
	 * @param Data_Source_Abstract $source
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return Query_Result
	 */
	public function execute (Data_Source_Abstract $source, Query_Abstract $query, $options = null)
	{
		if (!($query instanceof Query))
		{
			return new Query_Result (null);
		}

		$start = microtime (true);

		$result = $this->_execute ($query, $options);

		$finish = microtime (true);

		$result = new Query_Result (array (
			'query'			=> $query,
			'startAt'		=> $start,
			'result'		=> $result,
			'touchedRows'	=> count ($result),
			'insertKey'		=> 0,
			'currency'		=> $this->_isCurrency ($result, $options),
			'finishedAt'	=> $finish,
			'source'		=> $source
		));

		return $result;
	}

	/**
	 * @return Query_Options
	 */
	public function getDefaultOptions ()
	{
		if (!$this->_defaultOptions)
		{
			$this->_defaultOptions = new Query_Options ();
		}
		return $this->_defaultOptions;
	}

	/**
	 * @return Filter_Collection
	 */
	public function getFilters ()
	{
		return $this->_filters;
	}

	public function initFilters ()
	{
		$this->_filters = new Filter_Collection ();
	}

	/**
	 *
	 * @param Query_Options $options
	 * @return Data_Mapper_Abstract
	 */
	public function setDefaultOptions (Query_Options $options)
	{
		$this->_defaultOptions = $options;
		return $this;
	}

	/**
	 * @desc Установка параметров
	 * @param string|Objective $key Параметр.
	 * @param string $value [optional] Значение.
	 * @return boolean true, если удачно, иначе - false.
	 */
	public function setOption ($key, $value = null)
	{
		return false;
	}

}