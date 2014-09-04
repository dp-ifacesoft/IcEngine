<?php

/**
 * Валидатор проверяет на идентичность
 *
 * @author markov
 */
class Validator_Equal extends Validator
{
    /**
     * @inheritdoc
     */
    public function validate()
    {
        $value = $this->getData();
        return $this->getDataValidator()->validate(
            $value, $this->getParams()[0]
        );
    }
}

