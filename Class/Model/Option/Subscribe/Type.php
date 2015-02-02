<?php

/**
 * Опшин по Subscribe_Type__id
 *
 * @author markov
 */
class Model_Option_Subscribe_Type extends Model_Option
{
    /**
     * @inheritdoc
     */
    public function before()
    {
        $this->query->where('Subscribe_Type__id', $this->params['id']);
    }
}