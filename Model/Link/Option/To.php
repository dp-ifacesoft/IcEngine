<?php

/**
 * Получить все связи модели
 * 
 * @author Apostle
 */
class Link_Option_To extends Model_Option
{
    /**
     * @inheritdoc
     */
    public function before()
    {
        if(isset($this->params['table'])) {
            $this->query->where('Link.toTable=?', $this->params['table']);
        }
        if(isset($this->params['rowId'])) {
            $this->query->where('Link.toRowId=?', $this->params['rowId']);
        }
    }

}