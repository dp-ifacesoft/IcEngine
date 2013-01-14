<?php

/**
 * 
 * @desc Базовая комманда
 * 
 * @author Илья
 * @package IcEngine
 * 
 * @var array <Cli_Command_Argument> $_args
 * 
 * @method check (array <string> $args)
 * @method append (Cli_Command_Argument $arg)
 * @method getArgs
 * @method exec 
 * @method setArgs (array <mixed> $args)
 */
abstract class Cli_Command
{
	/**
	 * 
	 * @desc Список аргументов комманды
	 * @var array <Cli_Command_Argument>
	 */
	private $_args = array ();
	
	/**
	 * 
	 * @desc Добавить аргумент
	 * @param Cli_Command_Argument $arg
	 */
	public function append (Cli_Command_Argument $arg)
	{
		$this->_args [] = $arg;
	}
	
	/**
	 * 
	 * @desc Получить список аргументов
	 * @return array <Cli_Command_Argument>
	 */
	public function getArgs ()
	{
		return $this->_args;
	}
	
	/**
	 * 
	 * @desc Абстрактный метод выполнения комманды
	 */
	abstract public function exec ();
	
	/**
	 * 
	 * @desc Изменить список аргументов
	 * @param array <mixed> $args
	 */
	public function setArgs (array $args)
	{
		foreach ($args as $arg)
		{
			if ($arg instanceof Cli_Command_Argument)
			{
				continue;
			}
			$arg = new Cli_Command_Argument ($arg);
			if ($arg->validate ())
			{
				$this->append ($arg);
			}
			else
			{
				unset ($this->_args);
				return;
			}
		}
	}
}