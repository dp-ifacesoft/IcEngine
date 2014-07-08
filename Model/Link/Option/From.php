<?php

/**
 * Получить все связи модели
 * 
 * @author morph, apostle
 */
class Link_Option_From extends Model_Option
{
    /**
     * @inheritdoc
     */
    public function before()
    {
        if(isset($this->params['table'])) {
            $this->query->where('Link.fromTable=?', $this->params['table']);
        }
        if(isset($this->params['rowId'])) {
            $this->query->where('Link.fromRowId=?', $this->params['rowId']);
        }
    }

}