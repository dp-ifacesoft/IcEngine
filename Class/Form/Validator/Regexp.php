<?php

/**
 * Проверка на соответствие регулярному выражению
 * 
 * @author markov
 */
class Form_Validator_Regexp extends Form_Validator
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