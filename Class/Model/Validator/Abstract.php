<?php

class Model_Validator_Abstract
{
	protected static $_scheme = array (

	);

	public static function validate ($model, $input)
	{
		return Model_Validator::validate ($model, static::$_scheme, $input);
	}
}