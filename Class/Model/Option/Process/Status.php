<?php

/**
 * По статусу процесса
 * 
 * @author nastya
 */
class Model_Option_Process_Status extends Model_Option
{
    /**
     * @inheritdoc
     */
    public function before()
    {
        $this->query->where('Process_Status__id', $this->params['id']);
    }
}