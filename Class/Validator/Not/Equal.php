<?php

/**
 * Валидатор проверяет на не идентичность
 *
 * @author markov
 */
class Validator_Not_Equal extends Validator
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

