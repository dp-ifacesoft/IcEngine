<?php

/**
 * Мэппер для соединения с mysql
 *
 * @author goorus, morph
 */
class Data_Mapper_Mysqli extends Data_Mapper_Abstract
{
    /**
     * Запрос на получение числа кортежей
     */
	const SELECT_FOUND_ROWS_QUERY = 'SELECT FOUND_ROWS()';

	/**
	 * Соединение с mysql
	 *
     * @var resource
	 */
	protected $linkIdentifier = null;

	/**
	 * Параметры соединения
	 *
     * @var array
	 */
	public $connectionOptions = array(
		'host'		=> 'localhost',
		'username'	=> '',
		'password'	=> '',
		'database'	=> 'unknown',
		'charset'	=> 'utf8'
	);

	/**
	 * Последний оттранслированный запрос.
	 *
     * @var string
	 */
	protected $sql = '';

    /**
     * Код ошибки
     *
     * @var integer
     */
	protected $errno = 0;

    /**
     * Сообщение об ошибке
     *
     * @var string
     */
    protected $error = '';

    /**
     * Количество затронутых последним запросом кортежей
     *
     * @var integer
     */
	protected $affectedRows = 0;

    /**
     * Количество полученных рядов (игнорируя лимит)
     *
     * @var integer
     */
	protected $foundRows = 0;

    /**
     * Количество полученных рядов
     *
     * @var integer
     */
	protected $numRows = 0;

    /**
     * id последней добавленной сущности
     *
     * @var mixed
     */
	protected $insertId = null;

    /**
     * Опции маппера
     *
     * @var array
     */
	protected $options;

	/**
	 * Обработчики по видам запросов.
	 *
     * @var array
	 */
	protected $queryMethods = array(
		Query::SELECT	=> '_executeSelect',
		Query::SHOW		=> '_executeSelect',
		Query::DELETE	=> '_executeChange',
		Query::UPDATE	=> '_executeChange',
		Query::INSERT	=> '_executeInsert'
	);

	/**
	 * Запрос на изменение данных (Update или Delete).
	 *
     * @param Query_Abstract $query Запрос
	 * @param Query_Options $options Параметры запроса.
	 * @return boolean
	 */
	protected function _executeChange(Query_Abstract $query,
        Query_Options $options)
	{
		if (!mysql_query($this->sql, $this->linkIdentifier)) {
			$this->errno = mysql_errno($this->linkIdentifier);
			$this->error = mysql_error($this->linkIdentifier);
			return false;
		}
		$this->affectedRows = mysql_affected_rows($this->linkIdentifier);
		return true;
	}

	/**
	 * Запрос на вставку.
	 *
     * @param Query_Abstract $query Запрос.
	 * @param Query_Options $options Параметры запроса.
	 * @return boolean
	 */
	protected function _executeInsert(Query_Abstract $query,
        Query_Options $options)
	{
		if (!mysql_query($this->sql, $this->linkIdentifier)) {
			$this->errno = mysql_errno($this->linkIdentifier);
			$this->error = mysql_error($this->linkIdentifier);
			return false;
		}
		$this->affectedRows = mysql_affected_rows($this->linkIdentifier);
		$this->insertId = mysql_insert_id($this->linkIdentifier);

		return true;
	}

	/**
	 * Запрос на выборку.
	 *
     * @param Query_Abstract $query Запрос.
	 * @param Query_Options $options Параметры запроса.
	 * @return array|null
	 */
	protected function _executeSelect(Query_Abstract $query,
        Query_Options $options)
	{
		$result = mysql_query($this->sql, $this->linkIdentifier);
		if (!$result) {
			$this->errno = mysql_errno($this->linkIdentifier);
			$this->error = mysql_error($this->linkIdentifier);
			return null;
		}
		$rows = array();
		while (false != ($row = mysql_fetch_assoc($result))) {
			$rows[] = $row;
		}
		mysql_free_result($result);
		$this->numRows = count($rows);
		if ($query->part(Query::CALC_FOUND_ROWS)) {
			$result = mysql_query(
				self::SELECT_FOUND_ROWS_QUERY,
				$this->linkIdentifier
			);
			$row = mysql_fetch_row($result);
			$this->foundRows = reset($row);
			mysql_free_result($result);
		}
		return $rows;
	}

	/**
	 * Подключение к БД
	 *
     * @param Objective|array $config [optional]
	 */
	public function connect($config = null)
	{
		if ($this->linkIdentifier) {
			return;
		}
		if ($config) {
			$this->setOption($config);
		}
		$this->linkIdentifier = mysql_connect(
			$this->connectionOptions['host'],
			$this->connectionOptions['username'],
			$this->connectionOptions['password']
		);
		mysql_select_db(
			$this->connectionOptions['database'],
			$this->linkIdentifier
		);
		if (!empty($this->connectionOptions['charset'])) {
			mysql_query(
				'SET NAMES ' . $this->connectionOptions['charset'],
				$this->linkIdentifier
			);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Abstract::execute()
	 */
	public function execute(Data_Source_Abstract $source, Query_Abstract $query,
        $options = null)
	{
		if (!($query instanceof Query_Abstract)) {
			return new Query_Result(null);
		}
		if (!$this->linkIdentifier) {
			$this->connect();
		}
		$start = microtime(true);
		$this->sql = $query->translate('Mysql');
		$this->errno = 0;
		$this->error = '';
		$this->affectedRows = 0;
		$this->foundRows = 0;
		$this->numRows = 0;
		$this->insertId = null;
		if (!$options) {
			$options = $this->getDefaultOptions();
		}
		$m = $this->queryMethods[$query->type()];
		$result = $this->{$m}($query, $options);
		if ($this->errno) {
			throw new Exception(
				$this->error . "\n" . $this->sql,
				$this->errno
			);
		}
		if (!$this->errno && is_null($result)) {
			$result = array();
		}
		$finish = microtime(true);
		return new Query_Result(array(
			'error'			=> $this->error,
			'errno'			=> $this->errno,
			'query'			=> $query,
			'startAt'		=> $start,
			'finishedAt'	=> $finish,
			'foundRows'		=> $this->foundRows,
			'result'		=> $result,
			'touchedRows'	=> $this->numRows + $this->affectedRows,
			'insertKey'		=> $this->insertId,
			'source'		=> $source
		));
	}

	/**
	 * Возвращает ресурс соединения с mysql.
	 *
     * @return resource
	 */
	public function linkIdentifier()
	{
        if (!$this->linkIdentifier) {
            $this->connect();
        }
		return $this->linkIdentifier;
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Abstract::setOption()
	 */
	public function setOption($key, $value = null)
	{
		if (!is_scalar($key)) {
			foreach ($key as $optionName => $optionValue) {
				$this->setOption($optionName, $optionValue);
			}
			return;
		}
		if (isset($this->connectionOptions[$key])) {
            $serviceLocator = IcEngine::serviceLocator();
            $cryptManager = $serviceLocator->getService('cryptManager');
			$this->connectionOptions[$key] = $cryptManager->autoDecode($value);
			return;
		}
		return parent::setOption($key, $value);
	}
}