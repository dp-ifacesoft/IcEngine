<?php

/**
 * Unit of work
 *
 * @author neon
 * @Service("unitOfWork")
 */
class Unit_Of_Work
{
	/**
	 * Очередь запросов
	 * @var array
	 */
	private $queries = array();

	/**
	 * Необработанные запросы
	 * @var array
	 */
	private $raw = array();
	/**
	 * Количество запросов в очереди
	 * @var int
	 */
	private $rawCount = 0;
	/**
	 * Количество запросов на автостарт
	 * @var int
	 */
	private $autoFlush = 0;

	/**
	 * Незагруженные модели, для селекта
	 * @var array
	 * @deprecated
	 */
	private $rawModel = array();

	/**
	 * Загрузчик по умолчанию, для SELECT
	 * @var type
	 * @deprecated
	 */
	private $loader = null;

	/**
	 * Максимальное число запросов в буфере
	 *
	 * @param type $value
	 */
	public function setAutoFlush($value)
	{
		$this->autoFlush = (int) $value;
	}

	/**
	 * Обработка $raw данных и сборка запросов
	 */
	private function build()
	{
		$locator = IcEngine::serviceLocator();
		$unitOfWorkManager = $locator->getService('unitOfWorkManager');
		foreach ($this->raw as $type=>$array) {
			foreach ($array as $key=>$data) {
				$uowQuery = $unitOfWorkManager->byName($type);
				$query = $uowQuery->build($key, $data);
				$this->pushQuery($query, $key);
			}
		}
	}

	/**
	 * Загрузить одну модель и сопутствующие ей, по raw
	 *
	 * @param string $key
	 */
	private function buildOne($key)
	{
		$uowQuery = $this->byName(QUERY::SELECT);
		$query = $uowQuery->build($key, $this->getRaw(QUERY::SELECT, $key));
		$this->pushQuery($query, $key);
	}

	/**
	 * Освободить очередь
	 */
	public function flush()
	{
		$this->build();
		foreach ($this->queries as $key=>$query) {
			$this->_execute($key, $query);
		}
		$this->reset();
	}

	/**
	 * Выполнить группу запросов по ключу
	 *
	 * @param string $key
	 * @return void
	 */
	private function _execute($key)
	{
		$locator = IcEngine::serviceLocator();
		$modelScheme = $locator->getService('modelScheme');
		$unitOfWorkLoaderManager = $locator->getService(
			'unitOfWorkLoaderManager'
		);
		$query = $this->queries[$key];
        $ds = $modelScheme->dataSource($query['modelName']);
		$result = $ds->execute($query['query']);
        $mapper = $ds->getDataMapper();
        if (method_exists($mapper, 'getCacher')) {
            $cacher = $mapper->getCacher();
            $tag = $modelScheme->table($query['modelName']);
            $mapper->tagDelete($tag);
            $cacher->tagDelete($tag);
        }
		$this->rawCount--;
		if (isset($query['loader'])) {
			$loader = $unitOfWorkLoaderManager->get($query['loader']);
			$loader->load($key, $result);
		}
		unset($this->raw[$key]);
		unset($this->queries[$key]);
	}

	/**
	 * Получить необработанные данные
	 * поставить потом в private
	 *
	 * @return array
	 */
	public function getRaw($type = null, $key = null)
	{
		if ($type) {
			if (isset($key)) {
				return $this->raw[$type][$key];
			}
			if (!isset($this->raw[$type])) {
				$this->raw[$type] = array();
			}
			return $this->raw[$type];
		}
		return $this->raw;
	}

	/**
	 * Функция загрузки моделей, работает только для SELECT
	 *
	 * @param Model $object
	 */
	public function load($object)
	{
		$uniqName = get_class($object) . '@' .
			implode(':', array_keys($object->getFields()));
		$this->buildOne($uniqName);
		$this->_execute($uniqName);
	}

	/**
	 * Добавить запрос в очередь
	 *
	 * @param Query_Abstract $query
	 * @param Model $object модель, для возврата данных SELECT
	 * @param string|null $loaderName
	 */
	public function push(Query_Abstract $query, $object = null,
        $loaderName = null)
	{
		$locator = IcEngine::serviceLocator();
		$unitOfWorkManager = $locator->getService('unitOfWorkManager');
		$uowQuery = $unitOfWorkManager->get($query);
		$uowQuery->push($query, $object, $loaderName);
		if ($this->autoFlush && $this->autoFlush == $this->rawCount) {
			$this->flush();
		}
		$this->rawCount++;
	}

	/**
	 * Добавить запрос в очередь
	 *
	 * @param string $query
	 */
	public function pushQuery($query, $key = null)
	{
		$this->queries[$key] = $query;
	}

	/**
	 * Добавить необработанные данные
	 *
	 * @param string $type тип запроса
	 * @param string $table таблица запроса
	 * @param array $data данные запроса
	 */
	public function pushRaw($type, $uniqName, $data, $loaderName = null)
	{
		if (!isset($this->raw[$type])) {
			$this->raw[$type] = array();
		}
		if (!isset($this->raw[$type][$uniqName])) {
			$this->raw[$type][$uniqName] = array();
		}
		if ($loaderName) {
			if (!isset($this->raw[$type][$uniqName])) {
				$this->raw[$type][$uniqName][$loaderName] = array();
			}
			$this->raw[$type][$uniqName][$loaderName][] = $data;
		} else {
			$this->raw[$type][$uniqName][] = $data;
		}
	}

	/**
	 * Сброс
	 */
	public function reset()
	{
		$this->queries = array();
		$this->raw = array();
		$this->rawCount = 0;
		$this->rawModel = array();
	}
}