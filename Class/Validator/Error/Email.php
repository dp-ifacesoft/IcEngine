<?php

/**
 * Ошибка на корректность email
 * 
 * @author markov
 */
class Validator_Error_Email extends Validator_Error
{    
    /**
     * @inheritdoc
     */
    public function errorMessage($value = null) 
    {
        return 'Вы указали некорректный e-mail';
    }
}