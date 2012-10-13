<?php

class Cli_Parser_Command_Controller extends Cli_Parser_Command
{
	public function __construct ()
	{
		if (class_exists ('Cli_Command_Controller'))
		{
			$this->_command = new Cli_Command_Controller ();
		}
		if (class_exists ('Cli_Parser_Command_Controller_Token'))
		{
			$this->_token = new Cli_Parser_Command_Controller_Token ();
		}
		else
		{
			$this->_token = new Cli_Parser_Command_Token ();
		}
	}
}