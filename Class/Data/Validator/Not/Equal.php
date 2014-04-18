<?php

/**
 * Является ли значение поля не идентичным переданому в транспортe
 * 
 * @author markov
 */
class Data_Validator_Not_Equal extends Data_Validator_Abstract
{
    /**
     * @inheritdoc
     */
	public function validate($data, $value)
	{
		return $data != $value;
	}
}