<?php

class Cli_Command_Controller_Argument extends Cli_Command_Argument
{
	public function validate ()
	{
		return preg_match ('/^[a-zA-Z]{1,}[a-zA-Z0-9_]{0,}[a-zA-Z0-9]$/', $this->_value);
	}
}