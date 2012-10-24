<?php

Loader::load('UOW_Manager');
Loader::load('UOW_Query_Abstract');
Loader::load('UOW_Query_Insert');
Loader::load('UOW_Query_Update');
Loader::load('UOW_Query_Delete');

/**
 * Unit of work
 *
 * @author neon
 */
class UOW
{
	/**
	 * Очередь запросов
	 * @var array
	 */
	private static $queries = array();

	/**
	 * Необработанные запросы
	 * @var array
	 */
	private static $raw = array();
	/**
	 * Количество запросов в очереди
	 * @var int
	 */
	private static $rawCount = 0;
	/**
	 * Количество запросов на автостарт
	 * @var int
	 */
	private static $autoFlush = 0;

	/**
	 * Максимальное число запросов в буфере
	 *
	 * @param type $value
	 */
	public static function setAutoFlush($value)
	{
		self::$autoFlush = (int) $value;
	}

	/**
	 * Добавить запрос в очередь
	 *
	 * @param Query_Abstract $query
	 */
	public static function push(Query_Abstract $query)
	{
		$uowQuery = UOW_Manager::get($query);
		$uowQuery->push($query);
		if (self::$autoFlush && self::$autoFlush == self::$rawCount) {
			self::flush();
		}
		self::$rawCount++;
	}

	/**
	 * Добавить необработанные данные
	 *
	 * @param string $type тип запроса
	 * @param string $table таблица запроса
	 * @param array $data данные запроса
	 */
	public static function pushRaw($type, $uniqName, $data)
	{
		if (!isset(self::$raw[$type])) {
			self::$raw[$type] = array();
		}
		if (!isset(self::$raw[$type][$uniqName])) {
			self::$raw[$type][$uniqName] = array();
		}
		self::$raw[$type][$uniqName][] = $data;
	}

	public static function pushQuery($query)
	{
		self::$queries[] = $query;
	}

	/**
	 * Удалить потом
	 * @return type
	 */
	public static function getRaw()
	{
		return self::$raw;
	}

	/**
	 * Обработка $raw данных и сборка запросов
	 */
	private static function build()
	{
		foreach (self::$raw as $type=>$array) {
			foreach ($array as $key=>$data) {
				$uowQuery = UOW_Manager::byName($type);
				$query = $uowQuery->build($key, $data);
				self::pushQuery($query);
			}
		}
	}

	/**
	 * Освободить очередь
	 */
	public static function flush()
	{
		echo 'flush' . "\n";
		$link = DDS::getDataSource('default')
			->getDataMapper()->linkIdentifier();
		self::build();
		foreach (self::$queries as $query) {
			mysql_query($query, $link);
			//DDS::execute($query);
		}
		self::reset();
	}

	/**
	 * Сброс
	 */
	private static function reset()
	{
		self::$queries = array();
		self::$raw = array();
		self::$rawCount = 0;
	}
}