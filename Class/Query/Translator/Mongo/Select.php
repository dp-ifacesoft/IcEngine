<?php

/**
 * Транслятор в Mongo запрос
 * 
 * @author goorus, morph
 */
class Query_Translator_Mongo_Select extends Query_Translator_Abstract
{
    const COLLECTION        = 'collection';
    const CRITERIA          = 'criteria';
    const OPTIONS           = 'options';
    const METHOD            = 'method';
    const QUERY             = 'query';
    const FIELDS            = 'fields';
   	const SQL_WILDCARD		= '*';
	const WHERE_VALUE_CHAR	= '?';
    
    /**
     * Возможные операции
     * 
     * @var array
     */
    protected static $operations = array(
        '!='		=> '$ne',
        '>='		=> '$gte',
        '<='		=> '$lte',
        '='			=> null,
        '>'			=> '$gt',
        '<'			=> '$lt',
        ' NOT IN '	=> '$nin',
        ' IN '		=> '$in'
    );

    /**
	 * Рендеринг SELECT (find) запроса.
	 * 
     * @param Query_Abstract $query Запрос
	 * @return string Сформированный Mongo запрос
	 */
	public function doRenderSelect(Query_Abstract $query)
	{
        $fields = array();
        $limit = $query->part(Query::LIMIT);
        $count = $limit && !empty($limit[Query::LIMIT_COUNT]) 
            ? $limit[Query::LIMIT_COUNT] : 0;
        $offset = $count && !empty($limit[Query::LIMIT_OFFSET])
            ? $limit[Query::LIMIT_OFFSET] : 0;
		return array (
			self::COLLECTION        => $this->getFromCollection($query),
			self::QUERY             => $this->getCriteria($query),
			self::FIELDS            => $fields,
			'sort'                  => $this->getSort($query),
			'skip'                  => $offset,
			'limit'                 => $count,
			'find_one'              => $count == 1 && !$offset,
			Query::CALC_FOUND_ROWS  => $query->part(Query::CALC_FOUND_ROWS)
		);
	}
    
	/**
	 * Формирует условие выбора. OR не поддерживается.
     * 
	 * @param Query_Abstract $query
	 * @return array
	 */
	protected function getCriteria(Query_Abstract $query)
	{
		$wheres = $query->part(Query::WHERE);
		if (!$wheres) {
			return array();
		}
		$criteria = array();
		foreach ($wheres as $where) {
			$this->getCriteriaPart($criteria, $where);
		}
		return $criteria;
	}

    /**
     * Сформировать критерию
     * 
     * @staticvar array $operations
     * @param array $criteria
     * @param array $where
     * @return null
     * @throws Exception
     */
	protected function getCriteriaPart(&$criteria, $where)
	{
		$whereValue = $where[Query::WHERE];
        $dotPos = strpos($whereValue, '.');
        if ($dotPos !== false) {
            $whereValue = substr($whereValue, $dotPos + 1);
        }
		$value = true;
		foreach (self::$operations as $operation => $solve) {
			$operationPos = strpos($whereValue, $operation);
			if ($operationPos === false) {
                continue;
            }
            $value = trim(
                substr($whereValue, $operationPos + strlen ($operation))
            );
            $whereValue = trim(substr($whereValue, 0, $operationPos));
            // В случае условия вида '? <= age'
            if ($whereValue == '?') {
                $temp = $value; $value = $whereValue; $whereValue = $temp;
            }
            if ($operation == '=') {
                $criteria[$whereValue] =  (string) $value;
                return;
            } elseif (is_string($solve)) {
                if (array_key_exists(Query::VALUE, $where)) {
                    $criteria[$whereValue][$solve] = $where[Query::VALUE];
                    return;
                }
                $criteria[$whereValue][$solve] = $value;
                return;
            }
            throw new Exception('Unknown operation');
        }
		if (array_key_exists(Query::VALUE, $where)) {
			$value = $where[Query::VALUE];
			$keys = is_array($value) ? array_keys($value) : array('$id');
			if (is_array($value) || $keys[0] != '$id') {
				$criteria[$whereValue]['$in'] = $value;
				return;
			}
		}
        if (is_scalar($value)) {
            $value = (string) $value;
        }
		$criteria[$whereValue] = $value;
	}

	/**
	 * Возвращает название коллекции.
	 *
     * @param Query_Abstract $query
     * @param boolean $useAlias
	 * @return string
	 */
	protected function getFromCollection(Query_Abstract $query, 
        $useAlias = true)
	{
		$modelScheme = $this->modelScheme();
		$from = $query->part(Query::FROM);
		if (!$from) {
			return;
		}
		if (count($from) > 1) {
			throw new Exception('Multi from not supported.');
		}
		reset($from);
		return strtolower($modelScheme->table(key($from)));
	}
    
	/**
	 * Сортировка
	 * 
     * @param Query_Abstract $query
	 * @return string
	 */
	protected function getSort(Query_Abstract $query)
	{
		$orders = $query->part(Query::ORDER);
		if (!$orders) {
			return array();
		}
		$sort = array();
		foreach ($orders as $order) {
			if ($order[1] == Query::DESC) {
				$sort[$order[0]] = -1;
			} else {
				$sort[$order[0]] = 1;
			}
		}
		return $sort;
	}
}