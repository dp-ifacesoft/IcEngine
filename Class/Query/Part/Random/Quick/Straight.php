<?php

/**
 * query part для Quick_Random_Straight
 *
 * @author Apostle
 */
class Query_Part_Random_Quick_Straight extends Query_Part
{
    use Trait_Service_Locator;
    
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
        $random = rand(0, $rowCount-1);
        if (isset($this->params['count'])) {
            if ($rowCount < $this->params['count']) {
                return;
            } elseif($rowCount < $this->params['count'] + $random) {
                while ($rowCount < $this->params['count'] + $random) {
                    $random = rand(0, $rowCount-1);
                }
            } 
            $this->query->limit($this->params['count'], $random);
        } else {
            $this->query->limit(1, $random);
        }
	}
}