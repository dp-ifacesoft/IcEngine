<?php

/**
 * 
 * @desc Загружает Cli-комманды
 * @author Илья
 * @package IcEngine
 * 
 * @method load (array <string> $command)
 */
class Cli_Command_Loader
{
	public static function load (array $commands)
	{
		$classes = array ();
		$loading_dir = rtrim (dirname (__FILE__), '/').'../../../../';
		foreach ($commands as $command)
		{
			$command_class = 'Cli_Command_'.$command;
			$command_file = $loading_dir.str_replace ('_', '/', $command_class).'.php';
			
			$parser_class = 'Cli_Parser_Command_'.$command;
			$parser_file = $loading_dir.str_replace ('_', '/', $parser_class).'.php';
			
			if (is_file ($command_file) && is_file ($command_file))
			{
				include_once ($command_file);
				include_once ($parser_file);
				
				if (class_exists ($parser_class) && class_exists ($command_class))
				{
					$token_class = $parser_class.'_Token';
					$token_file = $loading_dir.str_replace ('_', '/', $token_class).'.php';
					
					if (is_file ($token_file))
					{
						include_once ($token_file);
					}
					
					$classes [] = new $parser_class ();
				}
			}
		}
		return $classes;
	}
}