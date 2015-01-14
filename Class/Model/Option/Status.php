<?php

/**
 * По полю статус
 * 
 * @author morph
 */
class Model_Option_Status extends Model_Option
{
    /**
     * @inheritdoc
     */
    public function before()
    {
        $this->query->where('status', $this->params['status']);
    }
}