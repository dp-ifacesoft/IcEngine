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
        $dataProvider = App::dataProviderManager()->get('Mysqli_Redis');
        echo $query->translate();
        $queryBase = App::queryBuilder()
            ->select('id');
        $from = $query->getPart(Query::FROM);
        $queryBase->setPart(Query::FROM, $from);
        $hashs = [];
        foreach ($query->getPart(Query::WHERE) as $item) {
            echo ' <pre>' . print_r($item, 1) . '</pre>'; 
            $newQuery = clone $queryBase;
            $newQuery->setPart(Query::WHERE, [
                $item
            ]);
            echo $newQuery->translate();
            $hash = md5($newQuery->translate());
            $hashs[] = $hash;
            if (!$dataProvider->exists($hash)) {
                $values = $this->sourceDriver->execute($query, $options)->asColumn();
                $dataProvider->sAdd($hash, $values);
            }
        }
        $data = $dataProvider->sInter($hashs);
        print_r($data);
        die();
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