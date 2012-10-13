<?php
/**
 * 
 * @desc Ресурс для хранения в сессии.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Session_Resource extends Objective
{
	
	/**
	 * @desc Создает и возвращает ресурс сесии.
	 * @param string $name Название ресурса в сессии.
	 */
	public function __construct ($name)
	{
		if (!isset ($_SESSION [$name]) || !is_array ($_SESSION [$name]))
		{
			$_SESSION [$name] = array ();
		}
		
		$this->_data = &$_SESSION [$name];
	}
	
}