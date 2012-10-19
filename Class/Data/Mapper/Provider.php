<?php

/**
 * @desc Мэппер данных через провайдер.
 * @author Юрий
 * @package IcEngine
 *
 */
class Data_Mapper_Provider extends Data_Mapper_Abstract
{

	/**
	 * @desc Используемый транслятор
	 * @var string
	 */
	const TRANSLATOR = 'KeyValue';

    protected $_errno = 0;
    protected $_error = '';

    protected $_affectedRows = 0;
    protected $_foundRows = 0;
    protected $_insertId = null;

    /**
     *
     * @var array
     */
    protected $_translated;

    /**
     *
     * @var Data_Provider_Abstract
     */
    protected $_provider;

    protected $_queryMethods = array (
        Query::SELECT    => '_executeSelect',
//        Query::SHOW      => '_executeSelect',
        Query::DELETE    => '_executeDelete',
        Query::UPDATE    => '_executeUpdate',
        Query::INSERT    => '_executeInsert'
    );

	protected $_query;

    /**
     * @desc Удаление
     * @param Query_Abstract $query
     * @param Query_Options $options
     */
	protected function _executeDelete (Query_Abstract $query, Query_Options $options)
	{
		$this->_query = $query;

		$this->_affectedRows = $this->_fullDeleteByPatterns (
			$this->translator ()->extractTable ($query),
			$this->_translated
		);

		return true;
	}

	/**
	 * @desc Выполнения запроса на вставку
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 */
    protected function _executeInsert (Query_Abstract $query, Query_Options $options)
    {
		$this->_query = $query;

        foreach ($this->_translated [0] as $key)
        {
        	$this->_provider->set ($key, $this->_translated [1]);
        }

		$this->_affectedRows = count ($this->_translated [0]);

		return true;
	}

	/**
	 *
	 *
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return array
	 */
    protected function _executeSelect (Query_Abstract $query, Query_Options $options)
    {
		$this->_query = $query;

		$translator = $this->translator ();

		$ids = array ();
		$rows = array ();

		// Выбираем ID всех записей, подходящих под условие
		foreach ($this->_translated as $pattern)
		{
			$keys =
				(strpos ($pattern, '*') === false) ?
					array ($pattern) :
					$this->_provider->keys ($pattern);

			foreach ($keys as $key)
			{
				$id = $translator->extractId ($key);

				if (!isset ($ids [$id]))
				{
					$ids [$id] = $id;
					$row = $this->_provider->get ($key);
					if ($row)
					{
						$rows [] = $row;
					}
				}
			}
		}

        return $rows;
    }

    /**
     *
     * @param Query_Abstract $query
     * @param Query_Options $options
     * @return boolean
     */
    protected function _executeUpdate (Query_Abstract $query, Query_Options $options)
    {
		$this->_query = $query;

    	// Удаление ненужных индексов
		$this->_fullDeleteByPatterns (
			$this->translator ()->extractTable ($query),
			$this->_translated [0]
		);

		// Установка новых значений
		foreach ($this->_translated [1] as $key)
		{
			$this->_provider->set ($key, $this->_translated [2]);
		}

		return true;
	}

	/**
	 * @desc Полный список ключей по маскам.
	 * @param string $table
	 * @param array $patterns
	 * @return integer Количество удаленных первичных ключей.
	 */
	protected function _fullDeleteByPatterns ($table, array $patterns)
	{
		$translator = $this->translator ();

		$ids = array ();

		// Выбираем ID всех записей, подходящих под условие
		foreach ($patterns as $pattern)
		{
			$keys = $this->_provider->keys ($pattern);
			foreach ($keys as $key)
			{
				$id = $translator->extractId ($key);
				$ids [$id] = $id;
			}
		}

		// Для каждого ID выбираем запись,
		// строим ключи согласно индексам и удаляем их.
		foreach ($ids as $id)
		{
			$key =
				$table . $translator->tableIndexDelim .
				'k' . $translator->indexKeyDelim .
				$id;

			$keys = $translator->_compileKeys (
				$table,
				$this->_provider->get ($key)
			);

			$this->_provider->delete ($keys);
		}

		return count ($ids);
	}

    /**
     * (non-PHPdoc)
     * @see Data_Mapper_Abstract::execute()
     */
	public function execute (Data_Source_Abstract $source, Query_Abstract $query, $options = null)
	{
		$clone = clone $query;

		$where = $clone->getPart (Query::WHERE);
		$this->_filters->apply ($where, Query::VALUE);
		$clone->setPart (Query::WHERE, $where);

		$this->_translated = $clone->translate (self::TRANSLATOR);

		$result = null;
		$this->_errno = 0;
		$this->_error = '';
		$this->_affectedRows = 0;
		$this->_foundRows = 0;
		$this->_numRows = 0;
		$this->_insertId = null;

		if (!$options)
		{
		    $options = $this->getDefaultOptions ();
		}

		$m = $this->_queryMethods [$query->type ()];
		$result = $this->{$m} ($query, $options);

		if ($this->_errno)
		{
			throw new Data_Mapper_Mysqli_Exception (
			    $this->_error . "\n" . $this->_sql,
			    $this->_errno
			);
		}

		if (!$this->_errno && is_null ($result))
		{
			$result = array ();
		}

		return new Query_Result (array (
			'error'			=> $this->_error,
			'errno'			=> $this->_errno,
			'query'			=> $clone,
		    'foundRows'		=> $this->_foundRows,
			'result'		=> $result,
			'touchedRows'	=> $this->_numRows + $this->_affectedRows,
			'insertKey'		=> $this->_insertId,
			'currency'		=> $this->_isCurrency ($result, $options),
			'source'		=> $source
		));
	}

	/**
	 * @return Data_Provider_Abstract
	 */
	public function getProvider ()
	{
		return $this->_provider;
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Abstract::saveResult()
	 */
	public function saveResult (Query_Abstract $query, $options, Query_Result $result)
	{

	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Abstract::setOption()
	 */
	public function setOption ($key, $value = null)
	{
		switch ($key)
		{
			case 'provider':
				$this->setProvider (Data_Provider_Manager::get ($value));
				return true;
		}
		return false;
	}

	/**
	 *
	 * @param Data_Provider_Abstract $provider
	 * @return Data_Mapper_Provider
	 */
	public function setProvider (Data_Provider_Abstract $provider)
	{
		$this->_provider = $provider;
		return $this;
	}

	/**
	 * @return Query_Translator_KeyValue
	 */
	public function translator ()
	{
		return Query_Translator::byName (
			self::TRANSLATOR . '_' . $this->_query->getName ()
		);
	}

}