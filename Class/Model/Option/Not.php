<?php

/**
 * Опшен для получения
 *
 * @author neon
 */
class Model_Option_Not extends Model_Option
{

    /**
     * @inheritdoc
     */
    public function before()
    {
        $this->query->where($this->params['field'] . ' != ?',  $this->params['value']);
    }

}