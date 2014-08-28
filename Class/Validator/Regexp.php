<?php

/**
 * Проверка на соответствие регулярному выражению
 * 
 * @author markov
 */
class Validator_Regexp extends Validator
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