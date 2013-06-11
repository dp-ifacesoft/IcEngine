<?php

/**
 * Источник данных
 *
 * @author goorus, morph
 * @Service("dataSource")
 */
class Data_Source
{
    /**
     * Собственная конфигурация источника данных
     *
     * @var array
     */
    protected $config;

    /**
	 * Текущий драйвер источника данных
     *
	 * @var Data_Driver_Abstract
	 */
	protected $driver;

	/**
	 * Текущий запрос
	 *
     * @var Query
	 */
	private $query;

    /**
     * Зарегистирированные таблицы
     *
     * @var array
     */
    protected $registeredTables = array();

	/**
	 * Результат последнего выполненного запроса
	 *
     * @var Query_Result
	 */
	private $result;

	/**
	 * Проверяет доступность источника данных
	 *
     * @return boolean
	 */
	public function available()
	{
		return $this->driver()->available();
	}

    /**
     * Получить (инициализировать) драйвер
     *
     * @return Data_Driver_Abstract
     */
    public function driver()
    {
        if (!$this->driver) {
            $serviceLocator = IcEngine::serviceLocator();
            $dataSourceManager = $serviceLocator->getService(
                'dataSourceManager'
            );
            $dataSourceManager->initDataDriver($this);
        }
        return $this->driver;
    }

	/**
	 * Выполняет запрос к источнику данных
     *
	 * @param Query_Abstract $query Запрос
	 * @param Query_Options $options Опции запроса
	 * @return Data_Source_Abstract
	 */
	public function execute(Query_Abstract $query, $options = null)
	{
        $options = $options ?: new Query_Options();
        $this->setQuery($query);
        try {
            $result = $this->driver()->execute($this->query, $options);
        } catch (Exception $e) { 
            throw new Exception($e->getMessage());
        }
        if ($result->touchedRows()) {
            $tableName = $query->tableName();
            $queryType = $query->type();
            $signalName = 'Data_Source_' . ucfirst(strtolower($queryType));
            $isTableRegistered = in_array($tableName, $this->registeredTables);
            if ($queryType != Query::SELECT || $isTableRegistered) {
                $serviceLocator = IcEngine::serviceLocator();
                $eventManager = $serviceLocator->getService('eventManager');
                $signal = $eventManager->getSignal($signalName);
                $signal->setData(array(
                    'result'    => $result,
                    'table'     => $tableName
                ));
                $signal->notify();
            }
        }
		$this->setResult($result);
		return $this;
	}

    /**
     * Получить собственную конфигурацию источника данных
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

	/**
     * Получить текущий драйвер
     *
	 * @return Data_Driver_Abstract
	 */
	public function getDataDriver()
	{
		return $this->driver;
	}

	/**
	 * Возвращает запрос
	 *
     * @params null|string $translator
	 * 		Ожидаемый вид запроса.
	 * 		Если необходим объект запроса, ничего не указывется (по умолчанию).
	 * 		Если указать транслятор, то результом будет результат трансляции.
	 * @return Query|mixed
	 */
	public function getQuery($translator = null)
	{
		return $translator
            ? $this->query->translate($translator) : $this->query;
	}

	/**
     * Получить результат последнего выполненного запроса
     *
	 * @return Query_Result
	 */
	public function getResult()
	{
		return $this->result;
	}

    /**
     * Наполнить схему данных через источник данных
     *
     * @param Data_Scheme $scheme
     * @return Data_Scheme
     */
    public function getScheme(Data_Scheme $scheme)
    {
        $scheme->setDataSource($this);
        $serviceLocator = IcEngine::serviceLocator();
        $dataSchemeManager = $serviceLocator->getService('dataSchemeManager');
        return $dataSchemeManager->getScheme($scheme);
    }

    /**
     * Зарегистировать таблицу
     *
     * @param mixed $table
     */
    public function registerTable($table)
    {
        foreach ((array) $table as $tableName) {
            $this->registeredTables[] = $tableName;
        }
    }

    /**
     * Изменить собственную конфигурация источника данных
     *
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
	 * Устанавливает драйвер
	 *
     * @param Data_Driver_Abstract $driver
	 */
	public function setDataDriver(Data_Driver_Abstract $driver)
	{
		$this->driver = $driver;
		return $this;
	}

    /**
	 * Устанавливает запрос
	 *
     * @param Query_Abstract $query
	 * @return Data_Source_Abstract
	 */
	public function setQuery(Query_Abstract $query)
	{
		$this->query = $query;
		return $this;
	}

	/**
	 * Устанавливает результат запроса.
	 *
     * @param Query_Result $result
	 * @return Data_Source_Abstract
	 */
	public function setResult(Query_Result $result)
	{
		$this->result = $result;
		return $this;
	}
}