<?php

/**
 * Проверка на соответствие регулярному выражению
 * 
 * @author morph
 */
class Data_Validator_Regexp extends Data_Validator_Abstract
{
    /**
     * @inheritdoc
     */
	public function validate($data, $value)
	{
		return preg_match('#' . $value. '#', $data);
	}   
}