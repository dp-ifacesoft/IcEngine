<?php

/**
 * Является ли значение поля идентичным переданому в транспортe
 * 
 * @author morph
 */
class Data_Validator_Equal extends Data_Validator_Abstract
{
    /**
     * @inheritdoc
     */
	public function validate($data, $value=NULL)
	{
		return $data == $value;
	}
}