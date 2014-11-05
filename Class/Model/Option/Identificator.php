<?php

/**
 * Опшин по identificator
 *
 * @author markov
 */
class Model_Option_Identificator extends Model_Option
{
    /**
     * @inheritdoc
     */
    public function before()
    {
        $this->query->where('identificator', $this->params['value']);
    }
}