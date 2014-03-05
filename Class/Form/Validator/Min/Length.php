<?php

/**
 * Проверка на максимальную длину
 * 
 * @author markov
 */
class Form_Validator_Min_Length extends Form_Validator
{
    /**
     * @inheritdoc
     */
	public function validate($value)
	{
		$min = $this->getParams()[0];
        return $this->getDataValidator()->validate($value, $min);
	}
}