<?php

/**
 * Проверка на максимальную длину
 * 
 * @author markov
 */
class Validator_Min_Length extends Validator
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