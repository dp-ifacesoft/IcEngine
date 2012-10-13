<?php

class Data_Validator_Collection 
{
	protected $_validators;
	
	public function append ($validator)
	{
		$this->_validators [] = $validator;
		return $this;
	}
	
	public function getValidators ()
	{
		return $this->_validators;
	}
	
	public function validate ($data)
	{
		for ($i = 0, $count = sizeof ($this->_validators); $i < $count; $i++)
		{
			if (!$this->_validators [$i]->validate ($data))
			{
				return false;
			}	
		}
		return true;
	}
}