<?php

/**
 * 
 * @desc Стратегию парсинга
 * 
 * @author Илья
 * @package IcEngine
 * 
 * @var array <Cli_Parser_Command> $_commands
 * 
 * @method append (Cli_Parser_Command $command)
 * @method getCommands
 * @method resolve (array <string> $args)
 */
class Cli_Parser_Strategy
{
	/**
	 * 
	 * @desc Список комманд
	 * @var array <Cli_Parser_Command> $_commands
	 */
	private $_commands = array ();
	
	/**
	 * 
	 * @desc Добавить комманду
	 * @param Cli_Parser_Command $command
	 */
	public function append (Cli_Parser_Command $command)
	{
		$this->_commands [] = $command;
	}
	
	/**
	 * 
	 * @desc Получить список комманд
	 * @return array <Cli_Parser_Command>
	 */
	public function getCommands ()
	{
		return $this->_commands;
	}
	
	/**
	 * 
	 * @desc Выполнить комманды стратегии
	 * @param array <string>
	 */
	public function resolve ($args)
	{
		foreach ($this->_commands as $i=>$command)
		{
			$command->exec ($args);
		}
	}
}