<?php

/**
 * Получить все связи модели
 * 
 * @author morph
 */
class Link_Option_From extends Model_Option
{
    /**
     * @inheritdoc
     */
    public function before()
    {
        $this->query
            ->where('Link.fromTable=?', $this->params['table'])
            ->where('Link.fromRowId=?', $this->params['rowId']);
    }

}