<?php

/**
 * Свернутая часть запроса
 *
 * @author morph
 */
class Query_Part
{
	/**
	 * Имя модели
	 *
	 * @var string
	 */
	protected $modelName;

	/**
	 * Параментры запроса
	 *
	 * @var array
	 */
	protected $params;

	/**
	 * Запроса, в который добавляется часть
	 *
	 * @var Query_Abstract
	 */
	protected $query;

	/**
	 * Конструктор
	 *
	 * @param string $modelName
	 * @param array $params
	 */
	public function __construct($modelName, $params)
	{
		$this->query = Query::factory('Select');
		$this->modelName = $modelName;
		$this->params = $params;
	}

	/**
	 * Внедрение части запроса
	 *
	 * @param Query_Abstract $query
	 */
	public function inject($query)
	{
		$this->query();
		$select = $this->query->getPart(Query::SELECT);
		if (!$select && !$query->getPart(Query::SELECT)) {
			$select = '*';
		}
		$query->select($select);
		$from = $query->getPart(Query::FROM);
		$thisFrom = $this->query->getPart(Query::FROM);
		if (!$from && !$thisFrom && $this->modelName) {
			$query->from($this->modelName);
		} elseif ($thisFrom) {
			if (!$from) {
				$from = array();
			}
			foreach ($thisFrom as $alias => $fromPart) {
				$from[$alias] = $fromPart;
			}
			$query->setPart(Query::FROM, $from);
		}
		$where = $this->query->getPart(Query::WHERE);
		if ($where) {
			$queryWhere = $query->getPart(Query::WHERE);
			if (!$queryWhere) {
				$queryWhere = array();
			}
			foreach ($where as $condition) {
				$queryWhere[] = $condition;
			}
			$query->setPart(Query::WHERE, $queryWhere);
		}
		$distinct = $this->query->getPart(Query::DISTINCT);
		$query->distinct($distinct);
		$having = $this->query->getPart(Query::HAVING);
		$query->having($having);
		$calcFoundRows = $this->query->getPart(Query::CALC_FOUND_ROWS);
		if ($calcFoundRows) {
			$query->calcFoundRows();
		}
		$index = $this->query->getPart(Query::INDEX);
		if ($index) {
			$query->setPart(Query::INDEX, $index);
		}
		$thisGroup = $this->query->getPart(Query::GROUP);
		if ($thisGroup) {
			$group = $query->getPart(Query::GROUP);
			if (!$group) {
				$group = array();
			}
			foreach ($thisGroup as $currentGroup) {
				$group[] = $currentGroup;
			}
			$query->setPart(Query::GROUP, $group);
		}
		$thisOrder = $this->query->getPart(Query::ORDER);
		if ($thisOrder) {
			$order = $query->getPart(Query::ORDER);
			if (!$order) {
				$order = array();
			}
			foreach ($thisOrder as $currentOrder) {
				$order[] = $currentOrder;
			}
			$query->setPart(Query::ORDER, $order);
		}
		$limit = $this->query->getPart(Query::LIMIT_COUNT);
		if ($limit) {
			$query->setPart(Query::LIMIT_COUNT, $limit);
		}
		$offset = $this->query->getPart(Query::LIMIT_OFFSET);
		if ($offset) {
			$query->setPart(Query::LIMIT_OFFSET, $offset);
		}
	}

	/**
	 * Метод, где будут писан запроса
	 */
	public function query()
	{

	}
}