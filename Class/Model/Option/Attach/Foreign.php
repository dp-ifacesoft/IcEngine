<?php

/**
 * Прикручивает таблцу по внешнему ключу
 *
 * @author Apostle
 */
class Model_Option_Attach_Foreign extends Model_Option
{
    use Trait_Service_Locator;
    /**
     *
     * @inheritdoc
     */
    public function after()
    {
        if (!isset($this->params['table']) && !isset($this->params['field']))
        {
            return;
        }
        
        if (isset($this->params['table'])) {
            $table = $this->params['table'];
            if (!isset($this->params['field'])) {
                $field = $table . '__id';
            }
        } 
        if (isset($this->params['field'])) {
            $field = $this->params['field'];
            if (!isset($this->params['table'])) {
                $pos = strpos($field, '__id');
                $table = substr($field, 0, $pos);
            }
        }
        $ids = [];
        foreach ($this->collection as $model) {
            $ids[] = $model[$field];
        }
        if (!$ids) {
            return;
        }
        $foreignTableQuery = $this->getService('queryBuilder')->select('*')
            ->from($table);
        if (!isset($this->params['withParent'])) {
            $foreignTableQuery->where('id', $ids);
        }
        $foreignTable = $this->getService('dds')->execute($foreignTableQuery)
            ->getResult()
            ->asTable();
        $foreignTableReindexed = $this->getService('helperArray')
            ->reindex($foreignTable, 'id');
        foreach ($this->collection as $model) {
            if (!isset($foreignTableReindexed[$model[$field]])) {
                continue;
            }
            $model['data'][$field] = $foreignTableReindexed[$model[$field]];
            if (!isset($this->params['withParent']) || !$this->params['withParent'] 
                || !isset($foreignTableReindexed[$model[$field]]['parentId'])) {
                continue;
            }
            $parentId =  $foreignTableReindexed[$model[$field]]['parentId'];
            if (!isset($foreignTableReindexed[$parentId])) {
                continue;
            }
            $model['data'][$field]['data']['parent'] = $foreignTableReindexed[$parentId];
        }
    }
    
}
