<?php

class Data_Validator_Array extends Data_Validator_Abstract
{
	
	public function validate ($data)
	{
		return (bool) is_array ($data);
	}
	
}