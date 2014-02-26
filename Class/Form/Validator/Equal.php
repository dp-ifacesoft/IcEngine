<?php

/**
 * Валидатор проверяет на идентичность
 *
 * @author markov
 */
class Form_Validator_Equal extends Form_Validator
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
}

