<?php
/**
 *
 * @desc Помощник для построения зависимостей от положения сайта
 * @author Yury Shvedov
 * @package IcEngine
 *
 */
class Helper_Site_Location
{

	/**
	 * @desc Определение положения
	 * @var string
	 */
	protected static $_location = null;


	/**
	 * @desc Параметры
	 * @var array
	 */
	protected static $_config = array (
		'127.0.0.1'	=> array (
			'host'	=> 'localhost'
		)
	);

	/**
	 * @desc Возвращает значение параметра для текущего положения
	 * @param string $params
	 * @return mixed
	 */
	public static function get ($param)
	{
		if (is_array (self::$_config))
		{
			self::load ();
		}

		$location = self::$_location;

		while (is_string (self::$_config [$location]))
		{
			$location = self::$_config [$location];
		}

		if (strpos ($param, '::') !== false)
		{
			list ($location, $param) = explode ('::', $param);
		}

		return self::$_config [$location][$param];
	}

	/**
	 * @desc Возвращает положение
	 * @return string
	 */
	public static function getLocation ()
	{
		if (is_array (self::$_config))
		{
			self::load ();
		}
		return self::$_location;
	}

	/**
	 * @desc Загрузка данных о положении из файла.
	 */
	public static function load ()
	{

		if (!self::$_location)
		{

			$file = IcEngine::root () . 'Ice/Var/Helper/Site/Location.txt';

			if (file_exists ($file))
			{
				self::$_location = trim (file_get_contents ($file));
			}
			else
			{
				self::$_location = $_SERVER ['HTTP_HOST'];
			}
		}

		self::$_config = Config_Manager::get (__CLASS__, self::$_config);
	}

	/**
	 * @desc Устанавливает положение.
	 * @param string $value
	 */
	public static function setLocation ($value)
	{
		self::$_location = $value;
	}

}
