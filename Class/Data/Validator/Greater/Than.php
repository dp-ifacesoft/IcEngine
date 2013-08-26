<?php

/**
 * Валилатор "больше чем"
 * 
 * @author morph
 */
class Data_Validator_Greater_Than extends Data_Validator_Abstract
{
    /**
     * @inheritdoc
     */
    public function validate($data, $value)
    {
        return (bool) ($data > $value);
    }
}