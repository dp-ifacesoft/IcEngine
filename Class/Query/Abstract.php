<?php

/**
 * Абстрактный запрос строителя запросов
 *
 * @author morph, goorus, neon
 */
class Query_Abstract
{
    /**
     * Брокер частей запроса
     *
     * @var Query_Command_Broker
     */
    protected static $commandBroker;

    /**
     * Пул частей запроса
     *
     * @var Query_Command_Pool
     */
    protected static $commandPool;

    /**
	 * Флаги запроса
	 *
	 * @var array
	 */
	protected $flags;

    /**
     * Флаг указывает, позволять ли вызывать сигналы
     *
     * @var boolean
     */
    protected $notSignal = false;

	/**
	 * Части запроса
	 *
     * @var array
	 */
	protected $parts = array();

	/**
	 * Тип запроса
	 *
     * @var string
	 */
	protected $type;

    /**
     * Магический метод
     *
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        $commandName = $this->commandBroker()->getClassName($method);
        $queryCommand = $this->commandPool()->get($commandName);
        $partName = $queryCommand->getPart();
        $data = $queryCommand->process($this, $args);
        $mergeStrategy = $queryCommand->getMergeStrategy();
        if (empty($data[$partName])) {
            return $this;
        }
        if ($mergeStrategy != Query::REPLACE &&
            !isset($this->parts[$partName])) {
            $this->parts[$partName] = array();
        }
        switch ($mergeStrategy) {
            case Query::PUSH:
                $this->parts[$partName][] = $data[$partName];
                break;
            case Query::MERGE:
                $this->parts[$partName] = array_merge_recursive(
                    $this->parts[$partName], $data[$partName]
                );
                break;
            case Query::REPLACE:
                $this->parts[$partName] = $data[$partName];
                break;
        }
        $queryCommand->free();
        return $this;
    }

	/**
	 * Конструктор
	 */
	public function __construct()
	{
		$this->reset();
	}

	/**
	 * Преобразует части запроса в строку
	 *
     * @return string
	 */
	public function __toString()
	{
		return $this->translate();
	}

	/**
	 * Добавить часть запроса
	 *
	 * @return Query_Abstract
	 */
	public function addPart($parts)
	{
		$args = func_get_args();
		$modelName = null;
		$from = $this->getPart(Query::FROM);
		if ($from) {
			$from = reset($from);
			$modelName = $from[Query::TABLE];
		}
		foreach ($args as $arg) {
			$name = $arg;
			$params = array();
			if (is_array($arg)) {
				list($name, $params) = $arg;
			}
			$className = 'Query_Part_' . $name;
			$part = new $className($modelName, $params);
			$part->inject($this);
		}
		return $this;
	}

    /**
     * Получить (инициализировать) брокер частей запроса
     *
     * @return Query_Command_Broker
     */
    public function commandBroker()
    {
        if (is_null(self::$commandBroker)) {
            self::$commandBroker = IcEngine::serviceLocator()->getService(
                'queryCommandBroker'
            );
        }
        return self::$commandBroker;
    }

    /**
     * Получить (инициализировать) пул частей запроса
     *
     * @return Query_Command_Pool
     */
    public function commandPool()
    {
        if (is_null(self::$commandPool)) {
            self::$commandPool = IcEngine::serviceLocator()->getService(
                'queryCommandPool'
            );
        }
        return self::$commandPool;
    }

	/**
	 * Получить значение флага
	 *
	 * @param type $key
	 * @return boolean
	 */
	public function getFlag($key)
	{
		if (isset($this->flags[$key])) {
			return $this->flags[$key];
		}
		return false;
	}

	/**
	 * Возвращает имя запроса
	 *
     * @return string
	 */
	public function getName()
	{
		return substr(get_class($this), strlen('Query_'));
	}

    /**
     * Получить значение флага сигналов
     *
     * @return boolean
     */
    public function getNotSignal()
    {
        return $this->notSignal;
    }

	/**
	 * Возвращает часть запроса
	 *
     * @param string $name
	 * @return mixed
	 */
	public function getPart($name)
	{
		return isset($this->parts[$name]) ? $this->parts[$name] : array();
	}

    /**
	 * Возвращает все части запроса
	 *
     * @param string $name
	 * @return mixed
	 */
	public function getParts()
	{
		return $this->parts;
	}

	/**
	 * Возвращает тэги
	 *
     * @return array
	 */
	public function getTags()
	{

	}

	/**
	 * Возвращает часть запроса
	 *
     * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function part($name, $default = null)
	{
		$part = $this->getPart($name);
        return $part ?: $default;
	}

	/**
	 * Возвращает все части запроса.
	 *
     * @return array
	 */
	public function parts()
	{
		return $this->parts;
	}

	/**
	 * Сброс всех частей запроса.
	 *
     * @return Query Этот запрос.
	 */
	public function reset()
	{
		$this->parts = array();
        return $this;
	}

	/**
	 * Сбрасывает часть запроса
	 *
     * @param string|array $parts
	 * @return Query Этот запрос.
	 */
	public function resetPart($parts)
	{
		if (!is_array($parts)) {
			$parts = func_get_args();
		}
		foreach ($parts as $partName) {
            $this->parts[$partName] = null;
        }
		return $this;
	}

	/**
	 * Установить флаг
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function setFlag($key, $value)
	{
		$this->flags[$key] = $value;
	}

	/**
	 * Подменяет часть запроса
	 *
     * @param string $name Часть запроса.
	 * @param mixed $value Новое значение.
	 * @return Query Этот запрос.
	 */
	public function setPart($name, $value)
	{
        $this->parts[$name] = $value;
	}

    /**
     * Установить значение флага сигналов
     *
     * @param boolean $value
     */
    public function setNotSignal($value)
    {
        $this->notSignal = $value;
    }

    /**
     * Получить имя таблицы
     *
     * @return string
     */
    public function tableName()
    {
        return $this->getPart($this->type);
    }

	/**
	 * Транслирует запрос указанным транслятором
	 *
     * @param string $translator Транслятор.
	 * @return mixed Транслированный запрос.
	 */
	public function translate($translator = 'Mysql')
	{
        $serviceLocator = IcEngine::serviceLocator();
        $queryTranslator = $serviceLocator->getService('queryTranslator');
		return $queryTranslator->byName($translator . '_' . $this->getName())
			->translate($this);
	}

	/**
	 * Тип запроса
	 *
     * @return string
	 */
	public function type()
	{
		return $this->type;
	}
}