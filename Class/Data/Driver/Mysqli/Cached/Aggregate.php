<?php

/**
 * Драйвер для работы с mysql, с кэшированием запросов.
 *
 * @author markov
 */
class Data_Driver_Mysqli_Cached_Aggregate extends Data_Driver_Abstract
{
    /**
     * Драйвер
     *
     * @var Data_Source
     */
    protected $sourceDriver;
    
    /**
     * Параметры
     *
     * @var array
     */
    protected $params = [
    ];
    
    /**
     * Возвращает базовый запрос для разбиения на части
     * 
     * @param Query_Abstract $query запрос
     * @return Query_Abstract
     */
    public function getQueryBase($query)
    {
        $queryBase = App::queryBuilder()
            ->select('id');
        $from = $query->getPart(Query::FROM);
        $queryBase->setPart(Query::FROM, $from);
        return $queryBase;
    }
    
    /**
     * Возвращает подготовленные запросы
     * 
     * @param Query_Abstract $query запрос
     * @return Query_Abstract
     */
    public function getQueries($query)
    {
        $queryBase = $this->getQueryBase($query);
        $queries = [];
        foreach ($query->getPart(Query::WHERE) as $item) {
            $queryItem = clone $queryBase;
            $queryItem->setPart(Query::WHERE, [
                $item
            ]);
            $queries[] = [
                'type'  => Query::WHERE,
                'query' => $queryItem
            ];
        } 
        $orderQuery = clone $queryBase;
        $orderPart = $query->getPart(Query::ORDER);
        $orderQuery->setPart(Query::ORDER, $orderPart);
        $queries[] = [
            'type'  => Query::ORDER,
            'query' => $orderQuery
        ];
        return $queries;
    }
    
    /**
     * Возвращает id для окончательного запроса
     * 
     * @param Query_Abstract $query запрос
     * @param Data_Provider_Abstract $dataProvider
     * @param string $key ключ результирующего множества редиса
     * @return Query_Abstract
     */
    public function getIds($query, $dataProvider, $key)
    {
        $queryLimit = $query->getPart(Query::LIMIT);
        $start = 0;
        $end = -1;
        if ($queryLimit) {
            $start = $queryLimit[Query::LIMIT_OFFSET];
            $end = $start + $queryLimit[Query::LIMIT_COUNT] - 1;
        }
        $ids = $dataProvider->zRange($key, $start, $end);
        return $ids;
    }
    
    /**
     * Возвращает результирующий запрос
     * 
     * @param Query_Abstract $query запрос
     * @param array $ids айдишники
     * @return Query_Abstract
     */
    public function getResultQueryCreate($query, $ids)
    {
        $query->resetPart(Query::WHERE);
        $query->resetPart(Query::ORDER);
        $query->resetPart(Query::LIMIT);
        $query->where('id', $ids);
        return $query;
    }
    
    /**
     * Кладет в редис результат запроса
     * 
     * @param array $data
     */
    public function zAddItem($data)
    {
        $item = $data['item'];
        $dataProvider = $data['dataProvider'];
        $options = $data['options'];
        $hash = $data['hash'];
        if ($this->params['memoryLimit']) {
            ini_set('memory_limit', $this->params['memoryLimit']);
        }
        if ($this->params['timeLimit']) {
            set_time_limit($this->params['timeLimit']);
        }
        $values = $this->sourceDriver->execute($item['query'], $options)->asColumn();
        $zArrayValues = [];
        if ($item['type'] == Query::ORDER) {
            $i = 0;
            foreach ($values as $value) {
                $i ++;
                $zArrayValues[] = [
                    'value' => $value,
                    'score' => $i
                ];
            }
        }
        if ($item['type'] == Query::WHERE) {
            foreach ($values as $value) {
                $zArrayValues[] = [
                    'value' => $value,
                    'score' => 0
                ];
            }
        }
        $dataProvider->zAddArray($hash, $zArrayValues);
    }   
    
    /**
	 * Выполнить запрос через драйвер данных на select
     *
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return Query_Result
	 */
    public function executeSelect(\Query_Abstract $query, $options = null) 
    {
        $providerName = $this->params['provider'];
        $dataProvider = App::dataProviderManager()->get($providerName);
        $hashs = [];
        $queries = $this->getQueries($query);
        foreach ($queries as $item) {
            $hash = md5($item['query']->translate());
            $hashs[] = $hash;
            if (!$dataProvider->exists($hash)) {
                $this->zAddItem([
                    'dataProvider'  => $dataProvider,
                    'item'          => $item,
                    'options'       => $options,
                    'hash'          => $hash
                ]);
                        echo $item['query']->translate();
                        die();
            }
        }
        $keyOut = md5(implode('_', $hashs));
        $dataProvider->zIntersect($keyOut, $hashs);
        $foundRows = $dataProvider->zCount($keyOut);
        $ids = $this->getIds($query, $dataProvider, $keyOut);
        $queryModified = $this->getResultQueryCreate($query, $ids);
        $queryResult = $this->sourceDriver->execute($queryModified, $options);
        $queryResultData = $queryResult->result();
        $queryResultDataSorted = [];
        $queryResultDataReindexed = App::helperArray()->reindex($queryResultData, 'id');
        foreach ($ids as $id) {
            if (!isset($queryResultDataReindexed[$id])) {
                continue;
            }
            $queryResultDataSorted[] = $queryResultDataReindexed[$id];
        }
        $queryResult->setResult($queryResultDataSorted);
        $queryResult->setFoundRows($foundRows);
        return $queryResult;
    }
    
    /**
	 * Выполнить запрос через драйвер данных - все кроме select
     *
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return Query_Result
	 */
    public function executeOther(\Query_Abstract $query, $options = null) 
    {
        return $this->sourceDriver->execute($query, $options);
    }

    /**
     * @inheritdoc
     */
	public function execute(\Query_Abstract $query, $options = null)
    {
        if ($query->getPart(Query::SELECT)) {
            return $this->executeSelect($query, $options);
        }
        return $this->executeOther($query, $options);
    }
    
    public function __construct()
    {
        $sourceConfig = App::dds()->getDataSource()->getConfig();
        $config = isset($sourceConfig['options'])
            ? $sourceConfig['options'] : array();
        $this->sourceDriver = App::dataDriverManager()->get('Mysqli_Cached', $config);
    }
    
    /**
	 * @inheritdoc
	 */
	public function setOption($key, $value = null)
	{
		if (!is_scalar($key)) {
			foreach ($key as $optionName => $optionValue) {
				$this->setOption($optionName, $optionValue);
			}
			return;
		}
		$this->params[$key] = $value;
	}
}