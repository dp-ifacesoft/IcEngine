<?php

/**
 * 
 * @desc Базовая аргумент комманды
 * 
 * @author Илья
 * @package IcEngine
 * 
 * @var string $_value
 * 
 * @method getValue
 * @method setValue (string $value)
 * @method validate
 */
class Cli_Command_Argument
{
	/**
	 * 
	 * @desc Текущее значение
	 * @var string
	 */
	protected $_value;
	
	/**
	 * 
	 * @param string $value
	 */
	public function __construct ($value)
	{
		$this->_value = $value;
	}
	
	/**
	 * 
	 * @desc Получить текущее значение аргумента
	 * @return string
	 */
	public function getValue ()
	{
		return $this->_value;
	}
	
	/**
	 * 
	 * @desc Изменить текущее значение аргумента
	 * @param string $value
	 */
	public function setValue ($value)
	{
		$this->_value = $value;
	}
	
	/**
	 * 
	 * @desc Базовый метод валидации аргумента
	 * @return boolean
	 */
	public function validate ()
	{
		if ($this->_value)
		{
			return true;
		}
	}
}