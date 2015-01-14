<?php

/**
 * Опшн для group by
 *
 * @author nastya
 */
class Model_Option_Group extends Model_Option
{
    /**
     * @inheritdoc
     */
    public function before() {
        $this->query->group($this->params['field']);
    }
}
