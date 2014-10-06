<?php

/**
 * query part для Quick_Random
 *
 * @author Apostle
 */
class Query_Part_Random_Quick extends Query_Part
{
    use Trait_Service_Locator;
    
    protected $config = [
        //коэффициент перестраховки от удаленных строк берем в 10 раз больше
        'spareRatio' => 10,
    ];
	/**
	 * @inheritdoc
	 */
	public function query()
	{
        $queryBuilder = $this->getService('queryBuilder');
        $rowCountQuery = $queryBuilder->select('COUNT(*)')
            ->from($this->modelName);
        $rowCount = $this->getService('dds')->execute($rowCountQuery)
            ->getResult()->asValue();
        if (isset($this->params['count'])) {
            $count = $this->params['count'];
        } else {
            $count = 1;
        }
        $countSpared = $count * $this->config['spareRatio'];
        $ids = range(0, $rowCount-1);
        $idsSliced = array_slice($ids, 0, $countSpared);
        $this->query->where('id IN (?)', $idsSliced)
            ->limit($count);
	}
}
