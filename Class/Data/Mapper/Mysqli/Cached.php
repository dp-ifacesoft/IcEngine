<?php

/**
 *
 * @desc Мэппер для работы с mysql, с кэшированием запросов.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Mapper_Mysqli_Cached extends Data_Mapper_Mysqli
{
	/**
	 * @desc Кэшер запросов.
	 * @var Data_Provider_Abstract
	 */
	protected $_cacher;

	/**
	 * @desc Получение хэша запроса
	 * @return string
	 */
	protected function _sqlHash ()
	{
		return md5 ($this->_sql);
	}

	/**
	 * @desc Выполняет запрос на изменение данных.
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return boolean
	 */
	protected function _executeChange (Query_Abstract $query, Query_Options $options)
	{
		if (!$this->_linkIdentifier) {
			$this->connect();
		}

		if (Tracer::$enabled) {
			$startTime = microtime(true);
		}

		if (!mysql_query ($this->_sql, $this->_linkIdentifier))
		{
			$this->_errno = mysql_errno ($this->_linkIdentifier);
			$this->_error = mysql_error ($this->_linkIdentifier);
			return false;
		}

		if (Tracer::$enabled) {
			$endTime = microtime(true);
			$delta = $endTime - $startTime;
			if ($query instanceof Query_Delete) {
				Tracer::incDeleteQueryCount();
				Tracer::incDeleteQueryTime($delta);
			} else {
				Tracer::incUpdateQueryCount();
				Tracer::incUpdateQueryTime($delta);
			}
			Tracer::incDeltaQueryCount();
		}

		$this->_affectedRows = mysql_affected_rows ($this->_linkIdentifier);

		if ($this->_affectedRows > 0)
		{
			$tags = $query->getTags ();

			for ($i = 0, $count = sizeof ($tags); $i < $count; ++$i)
			{
				$this->_cacher->tagDelete ($tags [$i]);
			}
		}

		return true;
	}

	/**
	 * @desc Выполняет запрос на вставку данных.
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return boolean
	 */
	protected function _executeInsert (Query_Abstract $query, Query_Options $options)
	{
		if (!$this->_linkIdentifier) {
			$this->connect();
		}

		if (Tracer::$enabled) {
			$startTime = microtime(true);
		}

		if (!mysql_query ($this->_sql, $this->_linkIdentifier))
		{
			$this->_errno = mysql_errno ($this->_linkIdentifier);
			$this->_error = mysql_error ($this->_linkIdentifier);
			return false;
		}

		if (Tracer::$enabled) {
			$endTime = microtime(true);
			$delta = $endTime - $startTime;
			Tracer::incUpdateQueryCount();
			Tracer::incUpdateQueryTime($delta);
			Tracer::incDeltaQueryCount();
		}

		$this->_affectedRows = mysql_affected_rows ($this->_linkIdentifier);
		$this->_insertId = mysql_insert_id ($this->_linkIdentifier);

		if ($this->_affectedRows > 0)
		{
			$tags = $query->getTags ();

			for ($i = 0, $count = sizeof ($tags); $i < $count; $i++)
			{
				$this->_cacher->tagDelete ($tags [$i]);
			}
		}

		return true;
	}

	/**
	 * @desc Выполняет запрос на получение данных.
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return null|array
	 */
	protected function _executeSelect (Query_Abstract $query, Query_Options $options)
	{
		if (Tracer::$enabled) {
			Tracer::incSelectQueryCount();
		}

		$key = $this->_sqlHash ();

		$expiration = $options->getExpiration ();

		$cache = $this->_cacher->get ($key);

		$use_cache = false;

		if ($cache)
		{
			if (
	   			($cache ['a'] + $expiration > time () || $expiration == 0) &&
				$this->_cacher->checkTags ($cache ['t'])
			)
			{
	  			$use_cache = true;
			}

			if (!$this->_cacher->lock ($key, 5, 1, 1))
			{
				$use_cache = true;
			}
		}

		if ($use_cache)
		{
			$this->_numRows = count ($cache ['v']);
			$this->_foundRows = $cache ['f'];
			if (Tracer::$enabled) {
				Tracer::incCachedSelectQueryCount();
			}
			return $cache ['v'];
		}

		if (!$this->_linkIdentifier) {
			$this->connect();
		}

		if (Tracer::$enabled) {
			$startTime = microtime(true);
			Tracer::begin(__CLASS__, __METHOD__, __LINE__);
		}

		$result = mysql_query ($this->_sql, $this->_linkIdentifier);

		if (Tracer::$enabled) {
			$endTime = microtime(true);
			$delta = $endTime - $startTime;
			if ($delta >= Tracer::LOW_QUERY_TIME) {
				Tracer::addLowQuery($this->_sql, $delta);
			} else {
				Tracer::incSelectQueryTime($delta);
			}
			Tracer::end($this->_sql, count(mysql_num_rows($result)),
				memory_get_usage());
			Tracer::incDeltaQueryCount();
		}


		if (!is_resource ($result))
		{
			$this->_errno = mysql_errno ($this->_linkIdentifier);
			$this->_error = mysql_error ($this->_linkIdentifier);
			return;
		}

		$rows = array ();
		while (false != ($row = mysql_fetch_assoc ($result)))
		{
			$rows [] = $row;
		}
		mysql_free_result ($result);

		$this->_numRows = count ($rows);

		if ($query->part (Query::CALC_FOUND_ROWS))
		{
			$result = mysql_query (self::SELECT_FOUND_ROWS_QUERY, $this->_linkIdentifier);
			$row = mysql_fetch_row ($result);
			$this->_foundRows = reset ($row);
			mysql_free_result ($result);
		}

		$tags = $query->getTags ();

		$this->_cacher->set (
			$key,
			array (
				'v' => $rows,
				'a' => time (),
				't' => $this->_cacher->getTags ($tags),
				'f'	=> $this->_foundRows
			)
		);

		if ($cache)
		{
			$this->_cacher->unlock ($key);
		}

		return $rows;
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Abstract::execute()
	 */
	public function execute (Data_Source_Abstract $source, Query_Abstract $query, $options = null)
	{
		if (!($query instanceof Query_Abstract))
		{
			return new Query_Result (null);
		}
		$this->connect ();

		$start = microtime (true);

		$clone = clone $query;

		$where = $clone->getPart (Query::WHERE);
		$this->_filters->apply ($where, Query::VALUE);
		$clone->setPart (Query::WHERE, $where);

		$query_key = 'query_' . md5 (json_encode ($query->parts ()));
		$this->_sql = $this->_cacher->get ($query_key);

		if (!$this->_sql)
		{
			$this->_sql = $clone->translate ('Mysql');

			$this->_cacher->set ($query_key, $this->_sql);
		}
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
			if (class_exists ('Debug'))
			{
				Debug::errorHandler (
					E_USER_ERROR, $this->_sql . '; ' . $this->_error,
					__FILE__, __LINE__
				);
			}
			throw new Data_Mapper_Mysqli_Exception (
				$this->_error . "\n" . $this->_sql,
				$this->_errno
			);
		}

		if (!$this->_errno && is_null ($result))
		{
			$result = array ();
		}

		$finish = microtime (true);

		return new Query_Result (array (
			'error'			=> $this->_error,
			'errno'			=> $this->_errno,
			'query'			=> $clone,
			'startAt'		=> $start,
			'finishedAt'	=> $finish,
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
	public function getCacher ()
	{
		return $this->_cacher;
	}

	/**
	 *
	 * @param Data_Provider_Abstract $cacher
	 */
	public function setCacher (Data_Provider_Abstract $cacher)
	{
		$this->_cacher = $cacher;
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Mysqli::setOption()
	 */
	public function setOption ($key, $value = null)
	{
		switch ($key)
		{
			case 'cache_provider':
				$this->setCacher (Data_Provider_Manager::get ($value));
				return;
			case 'expiration':
				$this->getDefaultOptions ()->setExpiration ($value);
				return;
		}
		return parent::setOption ($key, $value);
	}

}