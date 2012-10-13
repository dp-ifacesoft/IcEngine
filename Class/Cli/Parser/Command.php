<?php

/**
 * 
 * @desc Комманда для парсинга
 * 
 * @author Илья
 * @package IcEngine
 * 
 * @var array <string> $_args
 * @var Cli_Command $_command
 * @var Cli_Parser_Command_Token $_token
 * 
 * @method getArgs
 * @method getCommand
 * @method getToken
 * @method exec (array <string> $args)
 * @method setArgs (array <string> $args)
 * @method setCommand (Cli_Command $command)
 * @method setToken (Cli_Parser_Command_Token $token)
 */
class Cli_Parser_Command
{
	/**
	 * @desc Список аргументов 
	 * @var array <string> $_args
	 */
	protected $_args = array ();
	
	/**
	 * 
	 * @desc Текущая комманда
	 * @var Cli_Command
	 */
	protected $_command;
	
	/**
	 * 
	 * @desc Текущий токен
	 * @var Cli_Parser_Command_Token
	 */
	protected $_token;
	
	public function __construct ()
	{
		$this->_token = new Cli_Parser_Command_Token ();
		$this->_command = new Cli_Parser_Command ();
	}
	
	/**
	 * 
	 * @desc Получить список аргументов
	 * @return array <string>
	 */
	public function getArgs ()
	{
		return $this->_args;
	}
	
	/**
	 * 
	 * @desc Получить текущую комманду
	 * @return Cli_Command
	 */
	public function getCommand ()
	{
		return $this->_command;
	}
	
	/**
	 * 
	 * @desc Получить текущий токен
	 * @return Cli_Parser_Command_Token
	 */
	public function getToken ()
	{
		return $this->_token;
	}
	
	/**
	 * 
	 * Выполнить комманду
	 * @param array <string> $args
	 */
	public function exec ($args)
	{
		$this->_args = $args;
		
		$checked = true;

		if ($this->_command)
		{
			if ($this->_token)
			{
				if (!$this->_token->check ($this->_args))
				{
					$checked = false;
				}
			}
			if ($checked)
			{
				return $this->_command
					->setArgs ($this->_args)
					->exec ();
			}
		}
		$this->_args = null;
	}
	
	/**
	 * 
	 * @desc Изменить список аргументов
	 * @param array <string> $args
	 */
	public function setArgs (array $args)
	{
		$this->_args = $args;
	}
	
	/**
	 * 
	 * @desc Изменить текущую комманду
	 * @param Cli_Command $command
	 */
	public function setCommand (Cli_Command $command)
	{
		$this->_command = $command;
	}
	
	/**
	 * 
	 * @desc Изменить текущий токен
	 * @param Cli_Parser_Command_Token $token
	 */
	public function setToken (Cli_Parser_Command_Token $token)
	{
		$this->_token = $token;
	}
}