<?php

class Cli_Parser_Command_Project_Token extends Cli_Parser_Command_Token
{
	public function check ($args)
	{
		if (count ($args) >= 2)
		{
			if (strtolower ($args [0]) == 'project')
			{
				return true;
			}
		}
		return false;
	}
}