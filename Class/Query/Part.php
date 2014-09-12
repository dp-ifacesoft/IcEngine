<?php

/**
 * Свернутая часть запроса
 *
 * @author morph
 */
class Query_Part
{
    /**
     * Фильтры
     *
     * @var string
     */
    protected $filters;

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
        $serviceLocator = IcEngine::serviceLocator();
        $query = $serviceLocator->getService('query');
		$this->query = $query->factory('Select');
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
        if ($this->filters) {
            $this->injectWithParams($query);
        } else {
            $this->injectWithMethod($query);
        }
	}

    /**
	 * Внедрение части запроса через метод query
	 *
	 * @param Query_Abstract $query
	 */
    public function injectWithMethod($query)
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
		$limit = $this->query->getPart(Query::LIMIT);
		if ($limit) {
			$query->setPart(Query::LIMIT, $limit);
		}
    }

    /**
	 * Внедрение части запроса через параметры запроса
	 *
	 * @param Query_Abstract $query
	 */
    public function injectWithParams($query)
    {
        $modelName = $this->modelName;
        foreach ($this->filters as $fieldName => $value) {
            if ($value[0] == '$') {
                $value = $this->params[substr($value, 1)];
            }
            $condition = $modelName ? $modelName . '.' . $fieldName : $fieldName;
            $query->where($condition, $value);
        }
    }

	/**
	 * Метод, где будут писан запроса
	 */
	public function query()
	{

	}
}