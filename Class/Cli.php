<?php

/**
 * 
 * @desc Базовый класс для коммандной строки
 * 
 * @author Илья
 * @package IcEngine
 * 
 * @var array <string> $_args
 * 
 * @method append (string $arg)
 * @method get 
 * @method set (array <string> $args)
 */
abstract class Cli
{
	/**
	 * 
	 * @desc Комманды
	 * @var array <string>
	 */
	private static $_args = array ();
	
	/**
	 * 
	 * @desc Добавить комманду
	 * @param string $arg
	 */
	public function append ($arg)
	{
		self::$_args [] = $arg;
	}
	
	/**
	 * 
	 * @desc Получить список комманд
	 * @return array <string>
	 */
	public static function get ()
	{
		return self::$_args;
	}
	
	/**
	 * 
	 * @desc Изменить список комманд
	 * @param array <string>
	 */
	public static function set ($args)
	{
		self::$_args = $args;
	}
}