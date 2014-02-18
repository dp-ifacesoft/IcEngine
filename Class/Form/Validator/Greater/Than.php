<?php

/**
 * Валилатор "больше чем"
 * 
 * @author markov
 */
class Form_Validator_Greater_Than extends Form_Validator
{
    /**
     * @inheritdoc
     */
    public function validate($value)
    {
        return $this->getDataValidator()
            ->validate($value, $this->getParams()[0]);
    }
}