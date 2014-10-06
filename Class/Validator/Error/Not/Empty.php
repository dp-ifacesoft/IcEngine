<?php

/**
 * Для не пустых значений
 *
 * @author markov
 */
class Validator_Error_Not_Empty extends Validator_Error
{
    /**
     * @inheritdoc
     */
    public function errorMessage($value = null) 
    {
        return 'Значение не должно быть пустым';
    }
}