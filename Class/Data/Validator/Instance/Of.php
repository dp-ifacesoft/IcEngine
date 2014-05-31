<?php

/**
 * Является ли поле модели экземпяром класса
 * 
 * @author morph
 */
class Data_Validator_Instance_Of extends Data_Validator_Abstract
{
    /**
     * @inheritdoc
     */
	public function validate($data, $value=NULL)
	{
        return is_a($data, $value);
	}
}