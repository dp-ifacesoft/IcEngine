<?php

class Data_Validator_Numeric extends Data_Validator_Abstract
{
	public function validate ($data)
	{
		return (bool) is_numeric ($data);
	}
}