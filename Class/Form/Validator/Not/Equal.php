<?php

/**
 * Валидатор проверяет на не идентичность
 *
 * @author markov
 */
class Form_Validator_Not_Equal extends Form_Validator
{
    /**
     * @inheritdoc
     */
    public function validate($value) 
    {
        return $this->getDataValidator()->validate(
            $value, $this->getParams()[0]
        );
    }
    
    /**
     * @inheritdoc
     */
    public function errorMessage($value = null) 
    {
        return 'Значение ' . $value . ' равно ' . $this->getParams()[0];
    }
}

