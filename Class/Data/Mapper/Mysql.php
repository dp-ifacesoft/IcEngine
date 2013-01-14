<?php
/**
 *
 * @desc Мэппер для работы с мускулем через Серегин Mysql
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Mapper_Mysql extends Data_Mapper_Abstract
{

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
	 *
	 * @param Query $query
	 * @return array
	 */
	public function _getTags (Query_Abstract $query)
	{
		$tags = array ();

		$from = $query->getPart (Query::FROM);
		foreach ($from as $info)
		{
			$tags [] = Model_Scheme::table ($info [Query::TABLE]);
		}

		$insert = $query->getPart (QUERY::INSERT);
		if ($insert)
		{
	   		$tags [] = Model_Scheme::table ($insert);
		}

		$update = $query->getPart (QUERY::UPDATE);
		if ($update)
		{
			$tags [] = Model_Scheme::table ($update);
		}

		return array_unique ($tags);
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
			$this->setOption ($config);
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

	public function execute (Data_Source_Abstract $source, Query_Abstract $query, $options = null)
	{
		if (!($query instanceof Query))
		{
			return new Query_Result (null);
		}

		$start = microtime (true);

		$clone = clone $query;

		$where = $clone->getPart (Query::WHERE);
		$this->_filters->apply ($where, Query::VALUE);
		$clone->setPart (Query::WHERE, $where);

		$sql = $clone->translate ('Mysql');

		$result = null;
		$insert_id = null;
		$tags = implode ('.', $this->_getTags ($clone));
		switch ($query->type ())
		{
			case Query::DELETE:
				Mysql::delete ($tags, $sql);
				$touched_rows = Mysql::affectedRows ();
				break;
			case Query::INSERT:
				$insert_id = Mysql::insert ($tags, $sql);
				$touched_rows = Mysql::affectedRows ();
				break;
			case Query::SELECT; case Query::SHOW:
				$result = Mysql::select ($tags, $sql);
				$touched_rows = Mysql::numRows ();
				break;
			case Query::UPDATE:
				Mysql::update ($tags, $sql);
				$touched_rows = Mysql::affectedRows ();
				break;
		}
		$errno = mysql_errno ($this->_linkIdentifier);
		$error = mysql_error ($this->_linkIdentifier);

		if (!empty ($errno))
		{
			throw new Data_Mapper_Mysql_Exception ($error . "\n$sql", $errno);
		}

		if (empty ($errno) && is_null ($result))
		{
			$result = array ();
		}

		$finish = microtime (true);

		$found_rows = 0;
		if (
			$query->part (Query::CALC_FOUND_ROWS) &&
			method_exists ('Mysql', 'foundRows')
		)
		{
			$found_rows = Mysql::foundRows ();
		}

		return new Query_Result (array (
			'error'			=> $error,
			'errno'			=> $errno,
			'query'			=> $clone,
			'startAt'		=> $start,
			'finishedAt'	=> $finish,
			'foundRows'		=> $found_rows,
			'result'		=> $result,
			'touchedRows'	=> $touched_rows,
			'insertKey'		=> $insert_id,
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
