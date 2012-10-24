<?php
/**
 *
 * @desc Мэппер для соеденения с mysql
 * @author Гурус
 * @package IcEngine
 *
 */
class Data_Mapper_Mysqli extends Data_Mapper_Abstract
{

	const SELECT_FOUND_ROWS_QUERY = 'SELECT FOUND_ROWS()';

	/**
	 * @desc Соединение с mysql.
	 * @var resource
	 */
	protected $_linkIdentifier = null;

	/**
	 * @desc Параметры соединения
	 * @var array
	 */
	public $_connectionOptions = array (
		'host'		=> 'localhost',
		'username'	=> '',
		'password'	=> '',
		'database'	=> 'unknown',
		'charset'	=> 'utf8'
	);

	/**
	 * @desc Последний оттранслированный запрос.
	 * @var string
	 */
	protected $_sql = '';

	protected $_errno = 0;
	protected $_error = '';

	protected $_affectedRows = 0;
	protected $_foundRows = 0;
	protected $_numRows = 0;
	protected $_insertId = null;

	protected $options;

	/**
	 * @desc Обработчики по видам запросов.
	 * @var array
	 */
	protected $_queryMethods = array (
		Query::SELECT	=> '_executeSelect',
		Query::SHOW		=> '_executeSelect',
		Query::DELETE	=> '_executeChange',
		Query::UPDATE	=> '_executeChange',
		Query::INSERT	=> '_executeInsert'
	);

	/**
	 * @desc Запрос на изменение данных (Update или Delete).
	 * @param Query_Abstract $query Запрос
	 * @param Query_Options $options Параметры запроса.
	 * @return boolean
	 */
	protected function _executeChange (Query_Abstract $query, Query_Options $options)
	{
		if (!mysql_query ($this->_sql, $this->_linkIdentifier))
		{
			$this->_errno = mysql_errno ($this->_linkIdentifier);
			$this->_error = mysql_error ($this->_linkIdentifier);
			return false;
		}

		$this->_affectedRows = mysql_affected_rows ($this->_linkIdentifier);

		return true;
	}

	/**
	 * @desc Запрос на вставку.
	 * @param Query_Abstract $query Запрос.
	 * @param Query_Options $options Параметры запроса.
	 * @return boolean
	 */
	protected function _executeInsert (Query_Abstract $query, Query_Options $options)
	{
		if (!mysql_query ($this->_sql, $this->_linkIdentifier))
		{
			$this->_errno = mysql_errno ($this->_linkIdentifier);
			$this->_error = mysql_error ($this->_linkIdentifier);
			return false;
		}

		$this->_affectedRows = mysql_affected_rows ($this->_linkIdentifier);

		$this->_insertId = mysql_insert_id ($this->_linkIdentifier);

		return true;
	}

	/**
	 * @desc Запрос на выборку.
	 * @param Query_Abstract $query Запрос.
	 * @param Query_Options $options Параметры запроса.
	 * @return array|null
	 */
	protected function _executeSelect (Query_Abstract $query, Query_Options $options)
	{
		$result = mysql_query ($this->_sql, $this->_linkIdentifier);

		if (!$result)
		{
			$this->_errno = mysql_errno ($this->_linkIdentifier);
			$this->_error = mysql_error ($this->_linkIdentifier);
			return null;
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
			$result = mysql_query (
				self::SELECT_FOUND_ROWS_QUERY,
				$this->_linkIdentifier
			);
			$row = mysql_fetch_row ($result);
			$this->_foundRows = reset ($row);
			mysql_free_result ($result);
		}

		return $rows;
	}

	/**
	 *
	 * @param mixed $result
	 * @param mixed $options
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
	 * @desc Подключение к БД
	 * @param Objective|array $config [optional]
	 */
	public function connect ($config = null)
	{
		if ($this->_linkIdentifier)
		{
			return ;
		}

		if ($config)
		{
			$this->setOption($config);
		}

		$this->_linkIdentifier = mysql_connect (
			$this->_connectionOptions ['host'],
			$this->_connectionOptions ['username'],
			$this->_connectionOptions ['password']
		);

		mysql_select_db (
			$this->_connectionOptions ['database'],
			$this->_linkIdentifier
		);

		if ($this->_connectionOptions ['charset'])
		{
			mysql_query (
				'SET NAMES ' . $this->_connectionOptions ['charset'],
				$this->_linkIdentifier
			);
		}
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
		if (!$this->_linkIdentifier) {
			$this->connect();
		}
		$this->connect ();

		$start = microtime (true);

		$clone = clone $query;

		$where = $clone->getPart (Query::WHERE);
		$this->_filters->apply ($where, Query::VALUE);
		$clone->setPart (Query::WHERE, $where);

		$this->_sql = $clone->translate ('Mysql');

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
	 * @desc Возвращает ресурс соединения с mysql.
	 * @return resource
	 */
	public function linkIdentifier ()
	{
		$this->connect ();
		return $this->_linkIdentifier;
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Abstract::setOption()
	 */
	public function setOption ($key, $value = null)
	{
		if (is_array ($key) || !is_scalar ($key))
		{
			foreach ($key as $k => $v)
			{
				$this->setOption ($k, $v);
			}
			return;
		}

		if (isset ($this->_connectionOptions [$key]))
		{
			$this->_connectionOptions [$key] = Crypt_Manager::autoDecode ($value);
			return;
		}
		return parent::setOption ($key, $value);
	}

}
