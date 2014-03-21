<?php
/**
 * 
 * @desc Для определения вызова консольных контроллеров
 * @author Shvedov_U
 * @package IcEngine
 * 
 */
class Controller_Checker_Console
{
	
	/**
	 * @desc Проверяет, является ли вызов консольным.
	 * @return boolean
	 */
	public static function check ()
	{
		return Request::isConsole ();
	}
	
}
