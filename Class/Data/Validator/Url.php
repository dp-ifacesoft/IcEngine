<?php

class Data_Validator_Url extends Data_Validator_Abstract
{
	
	public function validate ($data)
	{
		if (filter_var ($data, FILTER_VALIDATE_URL))
		{
			return true;
		}
		return false;
	}
	
}