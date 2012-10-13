<?php

class Cli_Parser_Command_Project extends Cli_Parser_Command
{
	public function __construct ()
	{
		if (class_exists ('Cli_Command_Project'))
		{
			$this->_command = new Cli_Command_Project ();
		}
		if (class_exists ('Cli_Parser_Command_Project_Token'))
		{
			$this->_token = new Cli_Parser_Command_Project_Token ();
		}
		else
		{
			$this->_token = new Cli_Parser_Command_Token ();
		}
	}
}