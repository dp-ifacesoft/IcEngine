<?php

/**
 * Абстрактный драйвер источника данных
 *
 * @author goorus, morph
 */
abstract class Data_Driver_Abstract
{
	/**
	 * Опции по умолчанию
     *
	 * @var Query_Options
	 */
	protected $defaultOptions;

    /**
	 * Обработчики по видам запросов.
	 *
     * @var array
	 */
	protected $queryMethods = array();

    /**
     * Кол-во затронутых строк
     *
     * @var int
     */
    protected $touchedRows = 0;

    /**
     * Вызвать метод
     *
     * @param string $method
     * @param Query_Options $options
     * @return Query_Result
     */
    public function callMethod($query, $options)
    {
        $callable = $this->queryMethods[$query->type()];
        if (is_string($callable)) {
            $callable = array($this, $callable);
        }
        return call_user_func_array($callable, array($query, $options));
    }

	/**
	 * Выполнить запрос через драйвер данных
     *
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return Query_Result
	 */
	public function execute(Query_Abstract $query, $options = null)
	{
		if (!($query instanceof Query_Abstract)) {
			return new Query_Result(null);
		}
		$start = microtime(true);
        if (!$options) {
            $options = new Query_Options();
        }
		$rows = $this->executeCommand($query, $options);
		$finish = microtime(true);
		$result = new Query_Result(array(
			'query'			=> $query,
			'startAt'		=> $start,
			'result'		=> $rows,
            'foundRows'     => count($rows),
			'touchedRows'	=> count($rows),
			'insertKey'		=> 0,
			'finishedAt'	=> $finish
		));
		return $result;
	}

    /**
     * Выполняет базовый запрос к драйверу
     *
     * @param Query_Abstract $query
     * @param Query_Options $options
     * @return array
     */
    public function executeCommand(Query_Abstract $query,
        Query_Options $options)
    {
        return array();
    }

	/**
     * Получить опции по умолчанию
     *
	 * @return Query_Options
	 */
	public function getDefaultOptions()
	{
		if (!$this->defaultOptions) {
			$this->defaultOptions = new Query_Options();
		}
		return $this->defaultOptions;
    }

    /**
     * Получить метод
     *
     * @param string $methodType
     * @return mixed
     */
    public function getQueryMethod($methodType)
    {
        return $this->queryMethods[$methodType];
    }

	/**
	 * Изменить опции по умолчанию
     *
	 * @param Query_Options $options
	 * @return Data_Driver_Abstract
	 */
	public function setDefaultOptions(Query_Options $options)
	{
		$this->defaultOptions = $options;
		return $this;
	}

    /**
     * Изменить метод выполнения
     *
     * @param string $methodType
     * @param mixed $callable
     */
    public function setQueryMethod($methodType, $callable)
    {
        $this->queryMethods[$methodType] = $callable;
    }

	/**
	 * Установка параметров
	 *
     * @param string|Objective $key Параметр.
	 * @param string $value [optional] Значение.
	 * @return boolean true, если удачно, иначе - false.
	 */
	public function setOption($key, $value = null)
	{
		return false;
    }

    /**
     * Засетить
     * 
     * @param int $touchedRows
     */
    public function setTouchedRows($touchedRows)
    {
        $this->touchedRows = $touchedRows;
    }
}