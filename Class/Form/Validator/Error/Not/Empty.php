<?php

/**
 * Для не пустых значений
 *
 * @author markov
 */
class Form_Validator_Error_Not_Empty extends Form_Validator_Error
{
    /**
     * @inheritdoc
     */
    public function errorMessage($value = null) 
    {
        return 'Значение не должно быть пустым';
    }
}