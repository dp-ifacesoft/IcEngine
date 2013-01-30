<?php

/**
 * 
 * @desc Базовый парсер комманд
 * 
 * @author Илья
 * @package IcEngine
 * 
 * @var Cli_Parser_Strategy $_strategy
 * 
 * @method commands (array <string> $args)
 * @method getStrategy
 * @method parse (array <string> $args)
 * @method setStrategy (Cli_Parser_Strategy $strategy)
 */
abstract class Cli_Parser
{
	/**
	 * 
	 * @desc Текущая стратегия парсинга
	 * @var Cli_Parser_Strategy
	 */
	private static $_strategy;
	
	/**
	 * 
	 * @desc Получить группированный список комманд
	 * @param array <string> $args
	 * @return array <string>
	 */
	public function commands ($args)
	{
		$commands = array ();
		$n = 0;
		for ($i = 0, $icount = sizeof ($args); $i < $icount; $i++)
		{
			if (!isset ($commands [$n]))
			{
				$commands [$n] = array ();
			}
			if ($args [$i] == '+')
			{
				$n++;
			}
			else
			{
				$commands [$n][] = $args [$i];
			}
		}
		return $commands;
	}
	
	/**
	 * 
	 * @desc Получить текущую стратегию
	 * @return Cli_Parser_Strategy
	 */
	public static function getStrategy ()
	{
		return self::$_strategy;
	}
	
	/**
	 * 
	 * @desc Начать парсинг
	 * @param array <string> $args
	 */
	public static function parse ($args)
	{
		$commands = self::commands ($args);
		
		for ($i = 0, $icount = sizeof ($commands); $i < $icount; $i++)
		{
			self::$_strategy->resolve ($commands [$i]);
		}
	}
	
	/**
	 * 
	 * @desc Изменить текущую стратегию
	 * @param Cli_Parser_Strategy $strategy
	 */
	public static function setStrategy (Cli_Parser_Strategy $strategy)
	{
		self::$_strategy = $strategy; 
	}
}