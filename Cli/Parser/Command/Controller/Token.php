<?php

class Cli_Parser_Command_Controller_Token extends Cli_Parser_Command_Token
{
	public function check ($args)
	{
		if (count ($args) >= 4)
		{
			if 
			(
				strtolower ($args [0]) == 'controller' && strtolower ($args [2]) == 'in'
			)
			{
				if (strtolower ($args [4]) == 'actions')
				{
					if (count ($args) > 4)
					{
						return true;
					}
					return false;
				}
				return true;
			}
		}
		return false;
	}
}