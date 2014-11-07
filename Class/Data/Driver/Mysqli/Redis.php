<?php

/**
 * Драйвер для работы с mysql, с кэшированием запросов.
 *
 * @author markov
 */
class Data_Driver_Mysqli_Redis extends Data_Driver_Abstract
{
    /**
     * Драйвер
     *
     * @var Data_Source
     */
    protected $sourceDriver;
    
    /**
     * @inheritdoc
     */
	public function execute(\Query_Abstract $query, $options = null)
    {
        ini_set('memory_limit', '3G');
        set_time_limit(3600);
        $dataProvider = App::dataProviderManager()->get('Mysqli_Redis');
        echo $query->translate() . '<br>';
        $queryBase = App::queryBuilder()
            ->select('id');
        $from = $query->getPart(Query::FROM);
        $queryBase->setPart(Query::FROM, $from);
        $hashs = [];
        $newQueries = [];
        foreach ($query->getPart(Query::WHERE) as $item) {
            $newQuery = clone $queryBase;
            $newQuery->setPart(Query::WHERE, [
                $item
            ]);
            $newQueries[] = [
                'type'  => Query::WHERE,
                'query' => $newQuery
            ];
        }
        
        $orderQuery = clone $queryBase;
        $orderPart = $query->getPart(Query::ORDER);
        $orderQuery->setPart(Query::ORDER, $orderPart);
        $newQueries[] = [
            'type'  => Query::ORDER,
            'query' => $orderQuery
        ];
        
        foreach ($newQueries as $item) {
            //echo $item['query']->translate() . '<br>';
            $hash = md5($item['query']->translate());
            $hashs[] = $hash;
            if (!$dataProvider->exists($hash)) {
                //echo $item['query']->translate() . '<br>';
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
        }
        $keyOut = md5(implode('_', $hashs));
        $dataProvider->zIntersect($keyOut, $hashs);
        $foundRows = $dataProvider->zCount($keyOut);
        
        $queryLimit = $query->getPart(Query::LIMIT);
        $start = 0;
        $end = -1;
        if ($queryLimit) {
            $start = $queryLimit['LIMITOFFSET'];
            $end = $start + $queryLimit['LIMITOFFSET'] - 1;
        }
        echo '----------------------------';
        var_dump($start, $end);
        print_r($dataProvider->zRange($keyOut, $start, $end, true));
        print_r($dataProvider->zRange($keyOut, $start, $end, false));
        print_r($dataProvider->zRevRange($keyOut, $start, $end, true));
        print_r($dataProvider->zRevRange($keyOut, $start, $end, false));
        echo '/----------------------------';
        $ids = $dataProvider->zRange($keyOut, $start, $end, true);
        $query->resetPart(Query::WHERE);
        $query->resetPart(Query::ORDER);
        $query->resetPart(Query::LIMIT);
        $query->resetPart(Query::CALC_FOUND_ROWS);
        $query->where('id', $ids);
        echo $query->translate();
        $queryResult = $this->sourceDriver->execute($query, $options);
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
    
    public function __construct()
    {
        $sourceConfig = App::dds()->getDataSource()->getConfig();
        $config = isset($sourceConfig['options'])
            ? $sourceConfig['options'] : array();
        $this->sourceDriver = App::dataDriverManager()->get('Mysqli_Cached', $config);
    }
}