<?php

/**
 * Валидатор проверяет на не пустое значение
 *
 * @author markov
 */
class Form_Validator_Not_Empty extends Form_Validator
{
    /**
     * @inheritdoc
     */
    public function errorMessage($value = null) 
    {
        return 'Значение не должно быть пустым';
    }
}

