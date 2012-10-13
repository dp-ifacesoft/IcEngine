<?php

/**
 * 
 * @desc Комманда для парсинга
 * 
 * @author Илья
 * @package IcEngine
 * 
 * @method check (array <string> $args)
 */
class Cli_Parser_Command_Token
{
	/**
	 * 
	 * @desc Базовый токен комманды парсера
	 * @param array <string> $args
	 * @return boolean
	 */
	function check (array $args)
	{
		if ($args)
		{
			return true;
		}
	}
}