<?php

/**
 * Проверка на максимальную длину
 * 
 * @author markov
 */
class Form_Validator_Max_Length extends Form_Validator
{
    /**
     * @inheritdoc
     */
	public function validate($value)
	{
        $max = $this->getParams()[0];
        return $this->getDataValidator()->validate($value, $max);
	}
}