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
            $newQueries[] = $newQuery;
        }
        foreach ($newQueries as $item) {
            $hash = md5($item->translate());
            $hashs[] = $hash;
            var_dump($dataProvider->exists($hash));
            if (!$dataProvider->exists($hash)) {
                echo $item->translate() . '<br>';
                $values = $this->sourceDriver->execute($item, $options)->asColumn();
                $dataProvider->sAdd($hash, $values);
            }
        }
        $ids = $dataProvider->sInter($hashs);
        $query->resetPart(Query::WHERE);
        $query->resetPart(Query::ORDER);
        $query->where('id', $ids);
        return $this->sourceDriver->execute($query, $options);
    }
    
    public function __construct()
    {
        $sourceConfig = App::dds()->getDataSource()->getConfig();
        $config = isset($sourceConfig['options'])
            ? $sourceConfig['options'] : array();
        $this->sourceDriver = App::dataDriverManager()->get('Mysqli_Cached', $config);
    }
}