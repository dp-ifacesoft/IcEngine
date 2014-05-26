<?php
/**
 *
 * @desc Менеджер провайдеров данных.
 * По переданному названию создает и возвращает соответсвующего провайдера.
 * @author Юрий
 * @package IcEngine
 *
 */
class Data_Provider_Manager extends Manager_Abstract
{

	/**
	 * @desc Загруженные провайдеры.
	 * @var array <Data_Provider_Abstract>
	 */
	protected static $_providers = array ();

	/**
	 * @desc Конфиг
	 * @var array|Objective
	 */
	protected static $_config = array ();

	/**
	 * @desc Возвращает провайдера.
	 * @param string $name Название провайдера в конфиге.
	 * @return Data_Provider_Abstract
	 */
	public static function get ($name)
	{
		if (isset (self::$_providers [$name]))
		{
			return self::$_providers [$name];
		}

		$cfg = self::config ()->$name;

		if ($cfg && $cfg ['provider'])
		{
			$provider_name = $cfg ['provider'];
			$provider_params = $cfg ['params'];
		}
		else
		{
			$provider_name = $name;
			$provider_params = null;
		}

		$class_name = 'Data_Provider_' . $provider_name;

		/**
		 * @desc Новый провайдер данных
		 * @var Data_Provider_Abstract
		 */
		$provider = new $class_name ($provider_params);

		return self::$_providers [$name] = $provider;
	}

}