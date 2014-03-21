<?php

/**
 * Проверка на максимальную длину
 * 
 * @author markov
 */
class Validator_Max_Length extends Validator
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