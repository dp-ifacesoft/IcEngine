<?php

/**
 * Драйвер для соединения с mysql
 *
 * @author goorus, morph
 */
class Data_Driver_Mysqli extends Data_Driver_Abstract
{
    /**
     * Запрос на получение числа кортежей
     */
	const SELECT_FOUND_ROWS_QUERY = 'SELECT FOUND_ROWS()';

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
     * Количество затронутых последним запросом кортежей
     *
     * @var integer
     */
	protected $affectedRows = 0;
    
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
     * Количество полученных рядов (игнорируя лимит)
     *
     * @var integer
     */
	protected $foundRows = 0;
    
    /**
     * id последней добавленной сущности
     *
     * @var mixed
     */
	protected $insertId = null;
    
	/**
	 * Экземпляр mysqli
	 *
     * @var mysqli
	 */
	protected $handler = null;

    /**
     * Количество полученных рядов
     *
     * @var integer
     */
	protected $numRows = 0;
    
	/**
	 * Последний оттранслированный запрос.
	 *
     * @var string
	 */
	protected $sql = '';
    
    /**
     * Опции маппера
     *
     * @var array
     */
	protected $options;

	/**
	 * @inheritdoc
	 */
	protected $queryMethods = array(
		Query::SELECT	=> 'executeSelect',
		Query::SHOW		=> 'executeSelect',
		Query::DELETE	=> 'executeChange',
		Query::UPDATE	=> 'executeChange',
		Query::INSERT	=> 'executeInsert'
	);

	/**
	 * Запрос на изменение данных (Update или Delete).
	 *
     * @param Query_Abstract $query Запрос
	 * @param Query_Options $options Параметры запроса.
	 * @return boolean
	 */
	protected function executeChange(Query_Abstract $query,
        Query_Options $options)
	{
        $affectedRows = $this->handler->exec($this->sql);
		if ($affectedRows === false) {
			$this->errno = $this->handler->errorInfo()[1];
			$this->error = $this->handler->errorInfo()[2];
			return false;
		}
		$this->affectedRows = $affectedRows;
		return true;
	}

	/**
	 * Запрос на вставку.
	 *
     * @param Query_Abstract $query Запрос.
	 * @param Query_Options $options Параметры запроса.
	 * @return boolean
	 */
	protected function executeInsert(Query_Abstract $query,
        Query_Options $options)
	{
        $affectedRows = $this->handler->exec($this->sql);
		if ($affectedRows === false) {
			$this->errno = $this->handler->errorInfo()[1];
			$this->error = $this->handler->errorInfo()[2];
			return false;
		}
		$this->affectedRows = $affectedRows;
		$this->insertId = $this->handler->lastInsertId();
		return true;
	}

	/**
	 * Запрос на выборку.
	 *
     * @param Query_Abstract $query Запрос.
	 * @param Query_Options $options Параметры запроса.
	 * @return array|null
	 */
	protected function executeSelect(Query_Abstract $query,
        Query_Options $options)
	{
		$result = $this->handler->query($this->sql);
        if (!$result) {
            $this->errno = -1;
            $this->error = 'Incorrect result';
            return array();
        }
        $error = $result->errorInfo();
		if (!empty($error[1])) {
			$this->errno = $error[1];
			$this->error = $error[2];
			return array();
		}
		$rows = $result->fetchAll(PDO::FETCH_ASSOC);
        $this->numRows = count($rows);
		if ($query->part(Query::CALC_FOUND_ROWS)) {
			$result = $this->handler->query(self::SELECT_FOUND_ROWS_QUERY);
			$row = $result->fetch();
			$this->foundRows = reset($row);
		}
		return $rows;
	}

    /**
     * Подключение к БД
     *
     * @param Objective|array $config [optional]
     * @throws Exception
     * @return \mysqli
     */
	public function connect($config = null)
	{
		if ($this->handler) {
			return;
		}
		if ($config) {
			$this->setOption($config);
		}
        try {
            $dsn = 'mysql:dbname=' . $this->connectionOptions['database'] .
                ';host=' . $this->connectionOptions['host'];
            $this->handler = new \PDO(
                $dsn,
                $this->connectionOptions['username'],
                $this->connectionOptions['password']
            );
            $this->handler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        $error = $this->handler->errorInfo();
        if ($error[1]) {
            throw new Exception($error[2], $error[1]);
        }
		if (!empty($this->connectionOptions['charset'])) {
            $this->handler->exec(
                'SET NAMES ' .$this->connectionOptions['charset']
            );
		}
        return $this->handler;
	}

	/**
	 * @inheritdoc
	 */
	public function execute(Query_Abstract $query, $options = null)
	{
		if (!($query instanceof Query_Abstract)) {
			return new Query_Result(null);
		}
		if (!$this->handler) {
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
		$result = $this->callMethod($query, $options);
		if ($this->errno) {
			throw new Exception($this->error . "\n" . $this->sql, $this->errno);
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
			'insertKey'		=> $this->insertId
		));
	}

	/**
	 * Возвращает экземпляр mysql соединения с mysql.
	 *
     * @return \mysqli
	 */
	public function linkIdentifier()
	{
        $this->connect();
		return $this->handler;
	}

	/**
	 * @inheritdoc
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