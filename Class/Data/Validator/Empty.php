<?php

class Data_Validator_Empty extends Data_Validator_Abstract
{
	public function validate ($data)
	{
		return (bool) empty ($data);
	}
}