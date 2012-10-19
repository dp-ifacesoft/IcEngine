<?php
/**
 *
 * @desc Менеджер хелперов представления
 * @author Юрий
 * @package IcEngine
 *
 */
abstract class View_Helper_Manager
{
	/**
	 * @desc подключенные хелперы
	 * @var array <View_Helper_Abstract>
	 */
	protected static $_helpers;

	/**
	 * @desc Возвращает результат работы хелпера.
	 * @param string $name Название помощника
	 * @param array $params Параметры, передаваемые помощнику
	 * @return View_Helper_Abstract
	 */
	public static function get ($name, $params = array ())
	{
		if (!isset (self::$_helpers [$name]))
		{
			$helperName = 'View_Helper_' . $name;
			self::$_helpers [$name] = new $helperName;
		}
		return self::$_helpers [$name]->get ($params);
	}
}