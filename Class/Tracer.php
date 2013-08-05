<?php

/**
 * Трейсер
 * 
 * @author morph, goorus
 */
class Tracer
{
	/**
	 * Время медленного запроса
	 */
	const LOW_QUERY_TIME = 0.01;

	/**
	 * Время на инициализацию БД
	 *
	 * @var decimal
	 */
	protected static $bootstrapInitDbTime;

	/**
	 * Время инициализация менеджера атрибутов
	 *
	 * @var decimal
	 */
	protected static $bootstrapInitAttributeManagerTime;

	/**
	 * Время инициализации схемы моделей
	 *
	 * @var decimal
	 */
	protected static $bootstrapInitModelSchemeTime;

	/**
	 * Время инициализации менеджера моделей
	 *
	 * @var decimal
	 */
	protected static $bootstrapInitModelManagerTime;

	/**
	 * Время инициализация АКЛ
	 *
	 * @var decimal
	 */
	protected static $bootstrapInitAclTime;

	/**
	 * Время инициализации пользователя
	 *
	 * @var decimal
	 */
	protected static $bootstrapInitUserTime;

	/**
	 * Время роутинга
	 *
	 * @var decimal
	 */
	protected static $routingTime;

	/**
	 * Время инициализации сессии пользователя
	 *
	 * @var decimal
	 */
	protected static $bootstrapInitUserSessionTime;

	/**
	 * Количество загруженных классов
	 *
	 * @var integer
	 */
	protected static $loadedClassCount = 0;

	/**
	 * Время бутстрапинга
	 *
	 * @var decimal
	 */
	protected static $bootstraperTime;

	/**
	 * Время диспетчеризации
	 *
	 * @var decimal
	 */
	protected static $dispatherTime;

	/**
	 * Количество выполненных контроллеров
	 *
	 * @var integer
	 */
	protected static $controllerCount = 0;

	/**
	 * Время на рендеринг
	 *
	 * @var decimal
	 */
	protected static $renderTime = 0;

	/**
	 * Количество запросов
	 *
	 * @var integer
	 */
	protected static $selectQueryCount = 0;

	/**
	 * Медленные запросы
	 *
	 * @var array
	 */
	protected static $lowQueryVector = array();

    /**
     * Все select запросы к бд
     * 
     * @var array
     */
    protected static $allQueryVector = array();
    
	/**
	 * Время на запросы
	 *
	 * @var decimal
	 */
	protected static $selectQueryTime = 0;

	/**
	 * Количество get запросов к redis
	 *
	 * @var integer
	 */
	protected static $redisGetCount = 0;

	/**
	 * Количество set запросов к redis
	 *
	 * @var integer
	 */
	protected static $redisSetCount = 0;

	/**
	 * Количество key запросов к redis
	 *
	 * @var integer
	 */
	protected static $redisKeyCount = 0;

	/**
	 * Количество delete запросов к redis
	 *
	 * @var integer
	 */
	protected static $redisDeleteCount = 0;

	/**
	 * Время get запросов к redis
	 *
	 * @var decimal
	 */
	protected static $redisGetTime = 0;

	/**
	 * Время set запросов к redis
	 *
	 * @var decimal
	 */
	protected static $redisSetTime = 0;

	/**
	 * Время key запросов к redis
	 *
	 * @var decimal
	 */
	protected static $redisKeyTime = 0;

	/**
	 * Время delete запросов к redis
	 *
	 * @var decimal
	 */
	protected static $redisDeleteTime = 0;

	/**
	 * Время работы фронт контроллера
	 *
	 * @var decimal
	 */
	protected static $frontControllerTime;

	/**
	 * Количество update запросов к БД
	 *
	 * @var integer
	 */
	protected static $updateQueryCount = 0;

	/**
	 * Количество delete запросов к БД
	 *
	 * @var integer
	 */
	protected static $deleteQueryCount = 0;

	/**
	 * Количество insert запросов к БД
	 *
	 * @var integer
	 */
	protected static $insertQueryCount = 0;

	/**
	 * Время insert запросов к БД
	 *
	 * @var decimal
	 */
	protected static $insertQueryTime = 0;

	/**
	 * Время update запросов к БД
	 *
	 * @var decimal
	 */
	protected static $updateQueryTime = 0;

	/**
	 * Время delete запросов к БД
	 *
	 * @var decimal
	 */
	protected static $deleteQueryTime = 0;

	/**
	 * Количество модель после конкретного действия
	 *
	 * @var integer
	 */
	protected static $deltaModelCount = 0;

	/**
	 * Общее количество моделей
	 *
	 * @var integer
	 */
	protected static $totalModelCount = 0;

	/**
	 * Дельта счетчик запросов к дб
	 *
	 * @var integer
	 */
	protected static $deltaQueryCount = 0;

	/**
	 * Количество закэшированных вызовов контроллеров
	 *
	 * @var integer
	 */
	protected static $cachedControllerCount = 0;

	/**
	 * Количество закэшированных запросов select к БД
	 *
	 * @var integer
	 */
	protected static $cachedSelectQueryCount = 0;

	/**
	 * Общее время работы приложения
	 *
	 * @var decimal
	 */
	protected static $totalTime;

	/**
	 * @desc Состояние трейсера.
	 * @var boolean
	 */
	public static $enabled = false;

	/**
	 * @desc
	 * @var array
	 */
	public static $sessions = array ();

	/**
	 * Текущее смещение
	 *
	 * @var integer
	 */
	public static $offset = 0;

	/**
	 * Уровень вложения
	 *
	 * @var integer
	 */
	public static $level = 0;

	/**
	 * Стэк вложений профайлера
	 *
	 * @var array
	 */
	public static $stack = array();

    /**
     * Время выполнения стратегии фронт контроллера
     * 
     * @var integer 
     */
    protected static $controllerFrontStrategyTime;
    
    /**
     * Дельта количество get-запросов к редису
     * 
     * @var integer
     */
    protected static $redisGetDelta;
    
    /**
     * Время инициализация менеджера модулей
     * 
     * @var integer 
     */
    protected static $boostrapInitModuleManagerTime;
    
    /**
     * Получить время инициализация менеджера модулей
     * 
     * @return integer 
     */
    public static function getBootstrapModuleManagerInitTime()
    {
        return self::$boostrapInitModuleManagerTime;
    }
    
    /**
     * Изменить время инициализация менеджера модулей
     * 
     * @param integer $time
     */
    public static function setBootstrapModuleManagerInitTime($time)
    {
        self::$boostrapInitModuleManagerTime = $time;
    }
    
    /**
     * Инкрементировать количество дельта get-запросов к редису
     */
    public static function incRedisGetDelta()
    {
        self::$redisGetDelta++;
    }
    
    /**
     * Сбросить количество дельта get-запросов к редису
     */
    public static function resetRedisGetDelta()
    {
        self::$redisGetDelta = 0;
    }
    
    /**
     * Получить количество дельта get-запросов к редису
     * 
     * @return integer 
     */
    public static function getRedisGetDelta()
    {
        return self::$redisGetDelta;
    }
    
    /**
     * Изменить время выполнения стратегии фронт контроллера
     * 
     * @param integer $time
     */
    public static function setControllerFrontStrategyTime($time)
    {
        self::$controllerFrontStrategyTime = $time;
    }
    
    /**
     * Получить время выполнения стратегии фронт контроллера
     * 
     * @return integer 
     */
    public static function getControllerFrontStrategyTime()
    {
        return self::$controllerFrontStrategyTime;
    }
    
    /**
     * Добавить запрос в вектор
     * 
     * @param string $query
     */
    public static function appendQueryToVector($query)
    {
        self::$allQueryVector[] = $query;
    }
    
    /**
     * Получить вектор запросов
     * 
     * @return array
     */
    public static function getAllQueryVector()
    {
        return self::$allQueryVector;
    }
    
	/**
	 * Изменить время инициализации БД
	 *
	 * @param decimal $time
	 */
	public static function setBootstapInitDbTime($time)
	{
		self::$bootstrapInitDbTime = $time;
	}

	/**
	 * Получить время инициализации БД
	 *
	 * @return decimal
	 */
	public static function getBootstrapInitDbTime()
	{
		return self::$bootstrapInitDbTime;
	}

	/**
	 * Изменить время инициализации менеджера атрибутов
	 *
	 * @param decimal $time
	 */
	public static function setBootstapInitAttributeManagerTime($time)
	{
		self::$bootstrapInitAttributeManagerTime = $time;
	}

	/**
	 * Получить время инициализации менеджера атрибутов
	 *
	 * @return decimal
	 */
	public static function getBootstrapInitAttributeManagerTime()
	{
		return self::$bootstrapInitAttributeManagerTime;
	}

	/**
	 * Изменить время инициализации схемы моделей
	 *
	 * @param decimal $time
	 */
	public static function setBootstapInitModelSchemeTime($time)
	{
		self::$bootstrapInitModelSchemeTime = $time;
	}

	/**
	 * Получить время инициализации схемы моделей
	 *
	 * @return decimal
	 */
	public static function getBootstrapInitModelSchemeTime()
	{
		return self::$bootstrapInitModelSchemeTime;
	}

	/**
	 * Изменить время инициализации менеджера моделей
	 *
	 * @param decimal $time
	 */
	public static function setBootstapInitModelManagerTime($time)
	{
		self::$bootstrapInitModelManagerTime = $time;
	}

	/**
	 * Получить время инициализации менеджера моделей
	 *
	 * @return decimal
	 */
	public static function getBootstrapInitModelManagerTime()
	{
		return self::$bootstrapInitModelManagerTime;
	}

	/**
	 * Изменить время инициализации ACL
	 *
	 * @param decimal $time
	 */
	public static function setBootstapInitAclTime($time)
	{
		self::$bootstrapInitAclTime = $time;
	}

	/**
	 * Получить время инициализации ACL
	 *
	 * @return decimal
	 */
	public static function getBootstrapInitAclTime()
	{
		return self::$bootstrapInitAclTime;
	}

	/**
	 * Изменить время инициализации пользователя
	 *
	 * @param decimal $time
	 */
	public static function setBootstapInitUserTime($time)
	{
		self::$bootstrapInitUserTime = $time;
	}

	/**
	 * Получить время инициализации пользователя
	 *
	 * @return decimal
	 */
	public static function getBootstrapInitUserTime()
	{
		return self::$bootstrapInitUserTime;
	}

	/**
	 * Изменить время инициализации сессии пользователя
	 *
	 * @param decimal $time
	 */
	public static function setBootstapInitUserSessionTime($time)
	{
		self::$bootstrapInitUserSessionTime = $time;
	}

	/**
	 * Получить время инициализации сессии пользователя
	 *
	 * @return decimal
	 */
	public static function getBootstrapInitUserSessionTime()
	{
		return self::$bootstrapInitUserSessionTime;
	}

	/**
	 * Изменить время роутинга
	 *
	 * @param decimal $time
	 */
	public static function setRoutingTime($time)
	{
		self::$routingTime = $time;
	}

	/**
	 * Получить время роутинга
	 *
	 * @return decimal
	 */
	public static function getRoutingTime()
	{
		return self::$routingTime;
	}

	/**
	 * Получить лог
	 *
	 * @return array
	 */
	public static function getSessions()
	{
		return self::$sessions;
	}

	/**
	 * Изменить общее время работы приложения
	 *
	 * @param decimal $time
	 */
	public static function setTotalTime($time)
	{
		self::$totalTime = $time;
	}

	/**
	 * Получить общее время работы приложения
	 *
	 * @return decimal
	 */
	public static function getTotalTime()
	{
		return self::$totalTime;
	}

	/**
	 * Получить общее количество моделей
	 *
	 * @return integer
	 */
	public static function getTotalModelCount()
	{
		return self::$totalModelCount;
	}

	/**
	 * Получить количество запросов get к redis
	 *
	 * @return integer
	 */
	public static function getRedisGetCount()
	{
		return self::$redisGetCount;
	}

	/**
	 * Получить количество запросов set к redis
	 *
	 * @return integer
	 */
	public static function getRedisSetCount()
	{
		return self::$redisSetCount;
	}

	/**
	 * Получить количество запросов keys к redis
	 *
	 * @return integer
	 */
	public static function getRedisKeyCount()
	{
		return self::$redisKeyCount;
	}

	/**
	 * Получить количество запросов delete к redis
	 *
	 * @return integer
	 */
	public static function getRedisDeleteCount()
	{
		return self::$redisDeleteCount;
	}

	/**
	 * Получить суммарное время get запросов к redis
	 *
	 * @return decimal
	 */
	public static function getRedisGetTime()
	{
		return self::$redisGetTime;
	}

	/**
	 * Получить суммарное время set запросов к redis
	 *
	 * @return decimal
	 */
	public static function getRedisSetTime()
	{
		return self::$redisSetTime;
	}

	/**
	 * Получить суммарное время keys запросов к redis
	 *
	 * @return decimal
	 */
	public static function getRedisKeyTime()
	{
		return self::$redisKeyTime;
	}

	/**
	 * Получить суммарное время delete запросов к redis
	 *
	 * @return decimal
	 */
	public static function getRedisDeleteTime()
	{
		return self::$redisDeleteTime;
	}

	/**
	 * Получить количество select запросов к БД
	 *
	 * @return integer
	 */
	public static function getSelectQueryCount()
	{
		return self::$selectQueryCount;
	}

	/**
	 * Получить количество update запросов к БД
	 *
	 * @return integer
	 */
	public static function getUpdateQueryCount()
	{
		return self::$updateQueryCount;
	}

	/**
	 * Получить количество delete запросов к БД
	 *
	 * @return integer
	 */
	public static function getDeleteQueryCount()
	{
		return self::$deleteQueryCount;
	}

	/**
	 * Получить количество insert запросов к БД
	 *
	 * @return integer
	 */
	public static function getInsertQueryCount()
	{
		return self::$insertQueryCount;
	}

	/**
	 * Получить вектор медленных запросов
	 *
	 * @return array
	 */
	public static function getLowQueryVector()
	{
		return self::$lowQueryVector;
	}

	/**
	 * Получить общее время update запросов к БД
	 *
	 * @return decimal
	 */
	public static function getUpdateQueryTime()
	{
		return self::$updateQueryTime;
	}

	/**
	 * Получить общее время select запросов к БД
	 *
	 * @return decimal
	 */
	public static function getSelectQueryTime()
	{
		return self::$selectQueryTime;
	}

	/**
	 * Получить общее время delete запросов к БД
	 *
	 * @return decimal
	 */
	public static function getDeleteQueryTime()
	{
		return self::$deleteQueryTime;
	}

	/**
	 * Получить общее время insert запросов к БД
	 *
	 * @return decimal
	 */
	public static function getInsertQueryTime()
	{
		return self::$insertQueryTime;
	}

	/**
	 * Получить количество загруженных классов
	 *
	 * @return integer
	 */
	public static function getLoadedClassCount()
	{
		return self::$loadedClassCount;
	}

	/**
	 * Получить время, затраченное на рендеринг
	 *
	 * @return integer
	 */
	public static function getRenderTime()
	{
		return self::$renderTime;
	}

	/**
	 * Получить время бутстрапинга
	 *
	 * @return decimal
	 */
	public static function getBootstrapTime()
	{
		return self::$bootstraperTime;
	}

	/**
	 * Получить время диспетчиризации
	 *
	 * @return decimal
	 */
	public static function getDispatcherTime()
	{
		return self::$dispatherTime;
	}

	/**
	 * Получить время фронт контроллера
	 *
	 * @return decimal
	 */
	public static function getFrontControllerTime()
	{
		return self::$frontControllerTime;
	}

	/**
	 * Получить дельта счетчик запросов
	 *
	 * @return integer
	 */
	public static function getDeltaQueryCount()
	{
		return self::$deltaQueryCount;
	}

	/**
	 * Обнулить дельта счетчик запросов
	 */
	public static function resetDeltaQueryCount()
	{
		self::$deltaQueryCount = 0;
	}

	/**
	 * Увеличить дельта счетчик запросов
	 */
	public static function incDeltaQueryCount()
	{
		self::$deltaQueryCount++;
	}

	/**
	 * Увеличить время рендеринга
	 *
	 * @param decimal $time
	 */
	public static function incRenderTime($time)
	{
		self::$renderTime += $time;
	}

	/**
	 * Получить общее количество вызранных контроллеров
	 *
	 * @return integer
	 */
	public static function getControllerCount()
	{
		return self::$controllerCount;
	}

	/**
	 * Получить количество контроллеров, вызранных из кэша
	 *
	 * @return integer
	 */
	public static function getCachedControllerCount()
	{
		return self::$cachedControllerCount;
	}

	/**
	 * Получить количество закэшированных запросов select к БД
	 *
	 * @return integer
	 */
	public static function getCachedSelectQueryCount()
	{
		return self::$cachedSelectQueryCount;
	}

	/**
	 * Увеличить количество закэшированных запросов select к БД
	 */
	public static function incCachedSelectQueryCount()
	{
		self::$cachedSelectQueryCount++;
	}

	/**
	 * Увеличить счетчик закэшированных контроллеров
	 */
	public static function incCachedControllerCount()
	{
		self::$cachedControllerCount++;
	}

	/**
	 * Увеличить количество контроллеров
	 */
	public static function incControllerCount()
	{
		self::$controllerCount++;
	}

	/**
	 * Получить дельта счетчик моделей
	 *
	 * @return integer
	 */
	public static function getDeltaModelCount()
	{
		return self::$deltaModelCount;
	}

	/**
	 * Обнулить дельта счетчик моделей
	 */
	public static function resetDeltaModelCount()
	{
		self::$deltaModelCount = 0;
	}

	/**
	 * Инкрементировать дельта счетчик моделей
	 */
	public static function incDeltaModelCount()
	{
		self::$deltaModelCount++;
	}

	/**
	 * Инкрементрировать общий счетчик моделей
	 */
	public static function incTotalModelCount()
	{
		self::$totalModelCount++;
	}

	/**
	 * Увеличить количество запросов delete к redis
	 */
	public static function incRedisDeleteCount()
	{
		self::$redisDeleteCount++;
	}

	/**
	 * Увеличить количество запросов set к redis
	 */
	public static function incRedisSetCount()
	{
		self::$redisSetCount++;
	}

	/**
	 * Увеличить количество запросов get к redis
	 */
	public static function incRedisGetCount()
	{
		self::$redisGetCount++;
	}

	/**
	 * Увеличить количество запросов keys к redis
	 */
	public static function incRedisKeyCount()
	{
		self::$redisKeyCount++;
	}

	/**
	 * Увеличить время запросов delete к redis
	 *
	 * @param decimal $time
	 */
	public static function incRedisDeleteTime($time)
	{
		self::$redisDeleteTime += $time;
	}

	/**
	 * Увеличить время запросов set к redis
	 *
	 * @param decimal $time
	 */
	public static function incRedisSetTime($time)
	{
		self::$redisSetTime += $time;
	}

	/**
	 * Увеличить время запросов get к redis
	 *
	 * @param decimal $time
	 */
	public static function incRedisGetTime($time)
	{
		self::$redisGetTime += $time;
	}

	/**
	 * Увеличить время запросов keys к redis
	 *
	 * @param decimal $time
	 */
	public static function incRedisKeyTime($time)
	{
		self::$redisKeyTime += $time;
	}

	/**
	 * Добавить медленный select запрос
	 *
	 * @param string $query
	 * @param decimal $time
	 */
	public static function addLowQuery($query, $time)
	{
		self::$lowQueryVector[] = array($query, $time);
	}

	/**
	 * Увеличить количество запросов select к mysql
	 */
	public static function incSelectQueryCount()
	{
		self::$selectQueryCount++;
	}

	/**
	 * Увеличить количество запросов delete к mysql
	 */
	public static function incDeleteQueryCount()
	{
		self::$deleteQueryCount++;
	}

	/**
	 * Увеличить количество запросов update к mysql
	 */
	public static function incUpdateQueryCount()
	{
		self::$updateQueryCount++;
	}

	/**
	 * Увеличить количество запросов insert к mysql
	 */
	public static function incInsertQueryCount()
	{
		self::$deleteQueryCount++;
	}

	/**
	 * Увеличить время запросов insert к БД
	 *
	 * @param decimal $time
	 */
	public static function incInsertQueryTime($time)
	{
		self::$insertQueryTime += $time;
	}

	/**
	 * Увеличить время запросов select к БД
	 *
	 * @param decimal $time
	 */
	public static function incSelectQueryTime($time)
	{
		self::$selectQueryTime += $time;
	}

	/**
	 * Увеличить время запросов delete к БД
	 *
	 * @param decimal $time
	 */
	public static function incDeleteQueryTime($time)
	{
		self::$deleteQueryTime += $time;
	}

	/**
	 * Увеличить время запросов update к БД
	 *
	 * @param decimal $time
	 */
	public static function incUpdateQueryTime($time)
	{
		self::$updateQueryTime += $time;
	}

	/**
	 * Инкрементировать счетчик загруженных классов
	 */
	public static function incLoadedClassCount()
	{
		self::$loadedClassCount++;
	}

	/**
	 * Изменить время бутстрапинга
	 *
	 * @param decimal $time
	 */
	public static function setBootstrapTime($time)
	{
		self::$bootstraperTime = $time;
	}

	/**
	 * Изменить время диспетчеризации
	 *
	 * @param decimal $time
	 */
	public static function setDispatcherTime($time)
	{
		self::$dispatherTime = $time;
	}

	/**
	 * Изменить время работы фронт контроллера
	 *
	 * @param decimal $time
	 */
	public static function setFrontControllerTime($time)
	{
		self::$frontControllerTime = $time;
	}

	/**
	 * Начало блока
	 */
	public static function begin()
	{
		array_push(self::$stack, self::$offset);
		self::$level++;
		self::$sessions[self::$offset] = array(
			'args'		=> func_get_args(),
			'offset'	=> self::$offset,
			'level'		=> self::$level,
			'parent'	=> self::$level - 1,
			'begin'		=> microtime(true),
			'logs'		=> array()
		);
		self::$offset++;
	}

	/**
	 * Окончание блока
	 */
	public static function end()
	{
		$args = func_get_args();
		$offset = array_pop(self::$stack);
		if ($args) {
			self::log($offset, $args);
		}
		self::$sessions[$offset]['end'] = microtime(true);
		self::$level--;
	}

	/**
	 * Запись в лог метки
	 */
	public static function log($offset, $args)
	{
		$time = microtime(true);
		$logs = self::$sessions[$offset]['logs'];
		$currentIndex = sizeof($logs);
		$delta = $time - (
			isset ($logs[$currentIndex - 1])
				? $logs[$currentIndex - 1]['time']
				: self::$sessions[$offset]['begin']
			);
		self::$sessions[$offset]['logs'][] = array(
			'args'	=> $args,
			'time'	=> $time,
			'delta'	=> $delta
		);
	}
}