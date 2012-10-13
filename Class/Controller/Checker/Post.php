<?php
/**
 * 
 * @desc Для определения вызова контроллеров POST запросов
 * @author Shvedov_U
 * @package IcEngine
 * 
 */
class Controller_Checker_Post
{
	
	/**
	 * @desc Проверяет, является ли вызов консольным.
	 * @return boolean
	 */
	public static function check ()
	{
		return Request::isPost ();
	}
	
}
