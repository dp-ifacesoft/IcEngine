<?php

/**
 * Интерфейс для связи данных
 *
 * @author morph
 */
class Data_Link
{
	/**
	 * Текущая обрабатываемая строка
	 *
	 * @var array
	 */
	protected $currentRow = array();

	/**
	 * Добавленные агрегаторы данных
	 *
	 * @var array
	 */
	protected $dataAggregators = array();

	/**
	 * Добавленные фидеры данных
	 *
	 * @var array
	 */
	protected $dataFeeders = array();

	/**
	 * Добавленные фидеры данных
	 *
	 * @var array
	 */
	protected $dataFilters = array();

	/**
	 * Добавленные группировщики данных
	 *
	 * @var array
	 */
	protected $dataGroupers = array();

	/**
	 * Добавленные источники данных
	 *
	 * @var array
	 */
	protected $dataSources = array();

	/**
	 * Операторы фильтрации
	 *
	 * @var array
	 */
	protected static $operands = array(
		'=='		=> 'Equal',
		'<>'		=> 'Not_Equal',
		'>'			=> 'Greater',
		'>='		=> 'Greater_Or_Equal',
		'<'			=> 'Lesser',
		'<='		=> 'Lesser_Or_Equal'
	);

	/**
	 * Результирующие строки
	 *
	 * @var array
	 */
	protected $resultRows = array();

	/**
	 * Добавить агрегатор данных
	 *
	 * @param string $sourceKey
	 * @param string $targetKey
	 * @param mixed $callback
	 * @return \Data_Link
	 */
	public function aggregate($dataAggregator)
	{
		$args = func_get_args();
		if (!($dataAggregator instanceof Data_Link_Aggregator) &&
			count($args) > 1) {
			$dataAggregator = new Data_Link_Aggregator(
				$args[0], $args[1], isset($args[2]) ? $args[2] : null
			);
		}
		list($dataSource,) = $this->parseSide($dataAggregator->getTargetKey());
		$this->dataAggregators[$dataSource] = $dataAggregator;
		return $this;
	}

	/**
	 * Процесс агрегации данных
	 */
	protected function aggregateData()
	{
		if (!$this->resultRows || !$this->dataAggregators) {
			return;
		}
		foreach ($this->dataAggregators as $dataAggregator) {
			$keys = array();
			list($sourceDataSource, $sourceKey) = $this->parseSide(
				$dataAggregator->getSourceKey()
			);
			foreach ($this->resultRows as $i => $row) {
				if (!isset($row[$sourceDataSource])) {
					continue;
				}
				$keys[$i] = $row[$sourceDataSource][$sourceKey];
			}
			list($targetDataSource, $targetKey) = $this->parseSide(
				$dataAggregator->getTargetKey()
			);
			$data = array();
			if (isset($this->dataSources[$targetDataSource])) {
				$data = $this->dataSources[$targetDataSource]->getData();
			} else {
				$callback = $dataAggregator->getCallback();
				if (!is_object($callback)) {
					$data = call_user_func($callback, array_values($keys));
				} else {
					$data = $callback(array_values($keys));
				}
			}
			if (!$data) {
				continue;
			}
			$this->from($targetDataSource, $data);
			foreach ($keys as $i => $key) {
				foreach ($data as $row) {
					if ($key != $row[$targetKey]) {
						continue;
					}
					$this->resultRows[$i][$targetDataSource] = $row;
				}
			}
		}
	}

	/**
	 * Получение данных
	 *
	 * @return array
	 */
	protected function fetchData()
	{
		$result = array();
		foreach ($this->resultRows as $resultRow) {
			$currentRow = array();
			foreach ($this->dataFeeders as $dataFeeder) {
				$keys = $dataFeeder->getKeys();
				$deferredKeys = array();
				foreach ($keys as $key) {
					if (strpos($key, '.') !== false) {
						$index = null;
						list($dataSource, $key) = $this->parseSide($key);
						$source = &$currentRow[$dataSource];
						if (!isset($currentRow[$dataSource])) {
							$currentRow[$dataSource] = array();
						}
						if (isset($this->dataGroupers[$dataSource])) {
							$deferredKeys[] = $key;
						} else {
							$index = $key;
							$value = isset($resultRow[$dataSource][$key])
								? $resultRow[$dataSource][$key] : null;
						}
					} else {
						$source = &$currentRow;
						if (!isset($this->dataSources[$key])) {
							$index = count($currentRow);
							$value = $key;
						} else {
							$index = $key;
							$value = isset($resultRow[$key])
								? $resultRow[$key] : null;
						}
					}
					if (is_null($index)) {
						continue;
					}
					$source[$index] = $value;
				}
				if ($deferredKeys) {
					foreach ($resultRow[$dataSource] as $i => $row) {
						foreach ($deferredKeys as $key) {
							$source[$i][$key] = isset($row[$key])
								? $row[$key] : null;
						}
					}
				}
			}
			$result[] = $currentRow;
		}
		return $result;
	}

	/**
	 * Фильтрация источников данных
	 */
	protected function filterDataSources()
	{
		$eof = false;
		$data = array();
		while (!$eof) {
			$this->currentRow = array();
			$eof = true;
			foreach ($this->dataSources as $dataSource) {
				$alias = $dataSource->getAlias();
				if (!isset($data[$alias])) {
					$data[$alias] = $dataSource->getData();
				}
				if ($data[$alias]) {
					$eof = false;
					$row = reset($data[$alias]);
					$this->currentRow = array_merge(
						$this->currentRow,
						array(
							$alias	=> $row
						)
					);
					array_shift($data[$alias]);
				}
			}
			if ($this->currentRow) {
				$isValid = true;
				foreach ($this->dataFilters as $filter) {
					$left = $this->prepareSide($filter->getLeft());
					$right = $filter->getRight();
					if ($right) {
						$right = $this->prepareSide($right);
					}
					$isValid = $isValid & $filter->getOperand()->filter(
						$left, $right
					);
				}
				if ($isValid) {
					$this->resultRows[] = $this->currentRow;
				}
			}
		}
	}

	/**
	 * Добавить источник данных
	 *
	 * @param string $alias
	 * @param array $dataSource
	 * @return \Data_Link
	 */
	public function from($alias, $dataSource)
	{
		if (!($dataSource instanceof Data_Link_Source)) {
			$dataSource = new Data_Link_Source($dataSource, $alias);
		}
		$index = $dataSource->getAlias() ?: count($this->dataSources);
		$this->dataSources[$index] = $dataSource;
		return $this;
	}

	/**
	 * Добавить группировщик
	 *
	 * @param string $sourceKey
	 * @param string $targetKey
	 * @param mixed $callback
	 * @return \Data_Link
	 */
	public function group($dataGrouper)
	{
		$args = func_get_args();
		if (!($dataGrouper instanceof Data_Link_Aggregator) &&
			count($args) > 1) {
			$dataGrouper = new Data_Link_Aggregator(
				$args[0], $args[1], isset($args[2]) ? $args[2] : null
			);
		}
		list($dataSource,) = $this->parseSide($dataGrouper->getTargetKey());
		$this->dataGroupers[$dataSource] = $dataGrouper;
		return $this;
	}

	/**
	 * Выполнить группировку данных
	 */
	protected function groupData()
	{
		if (!$this->resultRows || !$this->dataGroupers) {
			return;
		}
		foreach ($this->dataGroupers as $dataGrouper) {
			$keys = array();
			list($sourceDataSource, $sourceKey) = $this->parseSide(
				$dataGrouper->getSourceKey()
			);
			foreach ($this->resultRows as $i => $row) {
				if (!isset($row[$sourceDataSource])) {
					continue;
				}
				$keys[$i] = $row[$sourceDataSource][$sourceKey];
			}
			list($targetDataSource, $targetKey) = $this->parseSide(
				$dataGrouper->getTargetKey()
			);
			$data = array();
			if (isset($this->dataSources[$targetDataSource])) {
				$data = $this->dataSources[$targetDataSource]->getData();
			} else {
				$callback = $dataGrouper->getCallback();
				if (!is_object($callback)) {
					$data = call_user_func($callback, array_values($keys));
				} else {
					$data = $callback(array_values($keys));
				}
			}
			if (!$data) {
				continue;
			}
			$this->from($targetDataSource, $data);
			foreach ($keys as $i => $key) {
				foreach ($data as $row) {
					if ($key != $row[$targetKey]) {
						continue;
					}
					$this->resultRows[$i][$targetDataSource][] = $row;
				}
			}
		}
	}

	/**
	 * Разобрать условие
	 *
	 * @param string $side
	 * @return array
	 */
	protected function parseSide($side)
	{
		return explode('.', $side);
	}

	/**
	 * Получить экземпляр линкера
	 *
	 * @return \self
	 */
	public static function instance()
	{
		return new self;
	}

	/**
	 * Разыменовать выражение
	 *
	 * @param string $side
	 * @return mixed
	 */
	protected function prepareSide($side)
	{
		if (strpos($side, '.') !== false) {
			list($dataSource, $key) = $this->parseSide($side);
			return $this->currentRow[$dataSource][$key];
		} else {
			return $side;
		}
	}

	/**
	 * Добавить дата фидер
	 *
	 * @param string $alias
	 * @param array $dataSource
	 * @return \Data_Link
	 */
	public function select($dataFeeder)
	{
		if (!($dataFeeder instanceof Data_Link_Feeder)) {
			$dataFeeder = new Data_Link_Feeder($dataFeeder);
		}
		$this->dataFeeders[] = $dataFeeder;
		return $this;
	}

	/**
	 * Получить данные из линкера
	 *
	 * @return array
	 */
	public function toArray()
	{
		foreach ($this->dataSources as $dataSource) {
			$data = $dataSource->getData();
			if (is_string($data)) {
				$dataSource->setData(call_user_func($data));
			} elseif (is_object($data)) {
				$dataSource->setData($data());
			}
		}
		foreach ($this->dataFeeders as $dataFeader) {
			$dataFeader->setDataSources($this->dataSources);
		}
		$this->filterDataSources();
		if (!$this->resultRows) {
			return array();
		}
		$this->aggregateData();
		$this->groupData();
		$result = $this->fetchData();
		return $result;
	}

	/**
	 * Добавить фильтр
	 *
	 * @param string $left
	 * @param string $operand
	 * @param string $right
	 * @return \Data_Link
	 */
	public function where($dataFilter)
	{
		$args = func_get_args();
		if (!($dataFilter instanceof Data_Link_Filter) && count($args) > 1) {
			$operandName = self::$operands[$args[1]];
			$operandClass = 'Data_Link_Filter_Operand_' . $operandName;
			$operand = new $operandClass;
			$dataFilter = new Data_Link_Filter(
				$args[0], $operand, isset($args[2]) ? $args[2] : null
			);
		}
		$this->dataFilters[] = $dataFilter;
		return $this;
	}
}