<?php

/**
 * По полю email
 * 
 * @author morph
 */
class Model_Option_Email extends Model_Option
{
    /**
     * @inheritdoc
     */
    public function before()
    {
        $this->query->where('email', $this->params['value']);
    }
}