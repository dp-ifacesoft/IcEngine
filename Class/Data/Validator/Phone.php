<?php

class Data_Validator_Phone extends Data_Validator_Abstract
{
	
	public function validate ($data)
	{
		if (preg_match ('/^\+?(?:\d|\s|(?:\(\d+\))|-)+$/', $data))
		{
			return true;
		}
		return false;
	}
	
}