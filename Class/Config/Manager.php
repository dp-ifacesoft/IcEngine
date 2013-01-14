<?php
/**
 *
 * @desc Мэнеджер конфигов.
 * @author Юрий
 * @package IcVipgeo
 *
 */
class Config_Manager
{

	/**
	 * @desc Путь до конфигов от корня сайта
	 * @var string
	 */
	protected static $_pathToConfig = array ('Ice/Config/');

	/**
	 * @desc Флаг означающий, что идет процесс загрузки конфига,
	 * необходим для предотвращения бесконечной рекурсии при
	 * загрузке конфигов для менеджера ресурсов.
	 * @var boolean
	 */
	protected static $_inLoading = false;

	/**
	 * @desc Загружает конфиг из файла и возвращает класс конфига.
	 * @param string $type Тип конфига.
	 * @param string|array $config Название конфига или конфиг по умолчанию.
	 * @return Config_Array|Objective Заруженный конфиг.
	 */
	protected static function _load ($type, $config = '')
	{
		$paths = (array) self::$_pathToConfig;
		$result = null;
		foreach ($paths as $path)
		{
			$filename =
				IcEngine::root () . $path.
				str_replace ('_', '/', $type) .
				(is_string ($config) && $config ? '/' . $config : '') .
				'.php';

			if (is_file ($filename))
			{
				$ext = ucfirst (strtolower (substr (strrchr ($filename, '.'), 1)));
				$class = 'Config_' . $ext;

				if (is_null ($result))
				{
					$result = new $class ($filename);
				}
				else
				{
					$result = $result->merge (new $class ($filename));
				}
			}
			else
			{
				$result = self::emptyConfig ();
			}

			if ($result)
			{
				return is_array ($config) ? $result->merge ($config) : $result;
			}
		}
	}

	/**
	 * @desc Добавляет путь для загрузки конфигураций
	 * @param string $path
	 */
	public static function addPath ($path)
	{
		self::$_pathToConfig [] = $path;
	}

	/**
	 * Получить конфиг по пути, результат не кешируется
	 */
	public static function byPath($modelName, $moduleName = 'Ice')
	{
		$filename = IcEngine::root() . 'Ice/Config/Module/' . $moduleName . '/'
			. str_replace('_', '/', $modelName) .
			'.php';
		if (!file_exists($filename)) {
			$filename = IcEngine::root() .
				$moduleName . '/Config/' . str_replace('_', '/', $modelName) .
				'.php';
		}
		if (is_file($filename)) {
			$ext = ucfirst(strtolower(substr(strrchr($filename, '.'), 1)));
			$class = 'Config_' . $ext;
			$result = new $class ($filename);
			return $result;
		}
		return array();
	}

	/**
	 * @desc Пустой конфиг.
	 * @return Config_Array
	 */
	public static function emptyConfig ()
	{
		return new Config_Array (array ());
	}

	public static function getPaths()
	{
		return self::$_pathToConfig;
	}

	/**
	 * @desc Загружает и возвращает конфиг.
	 * @param string $type Тип конфига.
	 * @param string|array $config [optional] Название или конфиг по умолчанию.
	 * 		Если параметром $config переданы настройки по умолчанию,
	 * 		результатом функции будет смержованный конфиг.
	 * @return Objective
	 */
	public static function get ($type, $config = '')
	{
		$rname = $type . (is_string ($config) && $config ? '/' . $config : '');

		if (self::$_inLoading)
		{
			return self::_load ($type, $config);
		}

		self::$_inLoading = true;
		$cfg = Resource_Manager::get ('Config', $rname);
		self::$_inLoading = false;

		if (!$cfg)
		{
			$cfg = self::_load ($type, $config);
			Resource_Manager::set ('Config', $rname, $cfg);
		}

		return $cfg;
	}

	/**
	 * @desc Загрузка реального конфига, игнорируя менеджер ресурсов.
	 * @param string $type Тип конфига.
	 * @param string|array $config [optional] Название или конфиг по умолчанию.
	 */
	public static function getReal ($type, $config = null)
	{
		return self::_load ($type, $config);
	}

	/**
	 * @desc Меняет путь до конфига
	 * @param mixed $path
	 */
	public static function setPathToConfig ($path)
	{
		self::$_pathToConfig = $path;
	}
}