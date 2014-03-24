<?php

/**
 * Валилатор "больше чем"
 * 
 * @author markov
 */
class Validator_Greater_Than extends Validator
{
    /**
     * @inheritdoc
     */
    public function validate()
    {
        $value = $this->getData();
        return $this->getDataValidator()
            ->validate($value, $this->getParams()[0]);
    }
}