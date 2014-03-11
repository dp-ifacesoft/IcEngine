<?php

/**
 * Для не идентичных значений
 *
 * @author markov
 */
class Form_Validator_Error_Not_Equal extends Form_Validator_Error
{
    /**
     * @inheritdoc
     */
    public function errorMessage($value = null) 
    {
        return 'Значение ' . $value . ' равно ' . $this->getParams()[0];
    }
}